import uuid
from flask import Flask, request, jsonify, session, render_template
from recommender import get_recommendations
from chatbot import (
    run_career_chatbot, STATIC_RESPONSES, classify_intent, chat,
    load_industry_stores, make_explain_career_func, build_system_prompt, llm
)
from database import init_db, save_context, get_context, update_last_viewed
from langchain_core.messages import SystemMessage, HumanMessage, ToolMessage

app = Flask(__name__)
app.secret_key = "change_this_to_a_random_secret_string"

init_db()

# In-memory session store (keyed by session_id)
chat_sessions = {}


def extract_text(content) -> str:
    """
    Safely extract a plain string from a Gemini response.
    Gemini returns content as either:
      - a plain str  (rare)
      - a list of dicts like [{"type": "text", "text": "..."}]
    """
    if isinstance(content, str):
        return content
    if isinstance(content, list):
        parts = []
        for block in content:
            if isinstance(block, dict):
                parts.append(block.get("text", ""))
            elif hasattr(block, "text"):          # LangChain content block object
                parts.append(block.text)
            else:
                parts.append(str(block))
        return "".join(parts).strip()
    return str(content)


@app.route("/")
def index():
    return render_template("index.html")


@app.route("/recommend", methods=["POST"])
def recommend():
    data = request.json
    education   = data.get("education", "")
    skills      = data.get("skills", "")
    experience  = data.get("experience", "")

    if not session.get("session_id"):
        session["session_id"] = str(uuid.uuid4())
    sid = session["session_id"]

    top_industries, ranked_careers = get_recommendations(education, skills, experience)
    save_context(sid, education, skills, experience, top_industries, ranked_careers)

    user_profile = {"education": education, "skills": skills, "experience": experience}

    # Load only the recommended industry PDFs to save time
    stores         = load_industry_stores(top_industries)
    explain_tool   = make_explain_career_func(stores)
    tools          = [explain_tool]
    llm_with_tools = llm.bind_tools(tools)
    tool_map       = {t.name: t for t in tools}
    system_prompt  = build_system_prompt(user_profile, top_industries, ranked_careers)

    chat_sessions[sid] = {
        "stores":          stores,
        "tools":           tools,
        "llm_with_tools":  llm_with_tools,
        "tool_map":        tool_map,
        "user_profile":    user_profile,
        "top_industries":  top_industries,
        "ranked_careers":  ranked_careers,
        "messages":        [SystemMessage(content=system_prompt)],
    }

    return jsonify({
        "top_industries": top_industries,
        "ranked_careers": ranked_careers,
    })


@app.route("/chat", methods=["POST"])
def chat_endpoint():
    data       = request.json
    user_input = data.get("message", "").strip()
    sid        = session.get("session_id")

    # ── Session recovery ──────────────────────────────────────────────────────
    if not sid or sid not in chat_sessions:
        ctx = get_context(sid) if sid else None
        if not ctx:
            return jsonify({
                "reply": (
                    "Your session has expired. "
                    "Please submit your profile again to get new recommendations."
                )
            })

        top_industries = ctx["top_industries"]
        ranked_careers = ctx["top_careers"]
        user_profile   = {
            "education":  ctx["education"],
            "skills":     ctx["skills"],
            "experience": ctx["experience"],
        }
        stores         = load_industry_stores(top_industries)
        explain_tool   = make_explain_career_func(stores)
        tools          = [explain_tool]
        llm_with_tools = llm.bind_tools(tools)
        system_prompt  = build_system_prompt(user_profile, top_industries, ranked_careers)

        chat_sessions[sid] = {
            "stores":          stores,
            "tools":           tools,
            "llm_with_tools":  llm_with_tools,
            "tool_map":        {t.name: t for t in tools},
            "user_profile":    user_profile,
            "top_industries":  top_industries,
            "ranked_careers":  ranked_careers,
            "messages":        [SystemMessage(content=system_prompt)],
        }

    s = chat_sessions[sid]

    # ── Rule-based routing ────────────────────────────────────────────────────
    routed = chat(
        user_input,
        s["user_profile"],
        s["top_industries"],
        s["ranked_careers"],
        s["stores"],
    )
    if routed != "langchain":
        return jsonify({"reply": routed})

    # ── LangChain + Gemini tool-calling pipeline ──────────────────────────────
    s["messages"].append(HumanMessage(content=user_input))

    while True:
        response = s["llm_with_tools"].invoke(s["messages"])
        s["messages"].append(response)

        if not response.tool_calls:
            # ✅ Always extract plain text — Gemini returns content as a list of blocks
            return jsonify({"reply": extract_text(response.content)})

        # Resolve every tool call, then loop back so Gemini can compose its answer
        for tc in response.tool_calls:
            result = s["tool_map"][tc["name"]].invoke(tc["args"])
            s["messages"].append(
                ToolMessage(content=str(result), tool_call_id=tc["id"])
            )


if __name__ == "__main__":
    app.run(debug=True)