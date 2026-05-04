"""
chatbot.py — Hybrid Career Advisor Chatbot
Combines rule-based intent matching with LangChain + Gemini tool-calling for career explanations.
PDF files are expected at: industry_pdf_map/<INDUSTRY_NAME>.pdf  (uppercase filenames)
API key is loaded from API.env  →  GOOGLE_API_KEY=AIzaSyAZtNt6gZX1Zccll_6TLAmrchLKyvLJl6U
"""

import os
import re
from pathlib import Path
from dotenv import load_dotenv

# ── Load API key ──────────────────────────────────────────────────────────────
load_dotenv("API.env")
GOOGLE_API_KEY = os.getenv("GOOGLE_API_KEY", "AIzaSyAZtNt6gZX1Zccll_6TLAmrchLKyvLJl6U")

# ── LangChain / Gemini imports ────────────────────────────────────────────────
from langchain_google_genai import ChatGoogleGenerativeAI, GoogleGenerativeAIEmbeddings
from langchain_core.tools import tool
from langchain_community.document_loaders import PyPDFLoader
from langchain_community.vectorstores import FAISS
from langchain_core.messages import SystemMessage, HumanMessage, ToolMessage

# ── PDF map (uppercase filenames in industry_pdf_map/) ───────────────────────
INDUSTRY_PDF_MAP = {
    "ACCOUNTANT":             "ACCOUNTANT.pdf",
    "ADVOCATE":               "ADVOCATE.pdf",
    "AGRICULTURE":            "AGRICULTURE.pdf",
    "APPAREL":                "APPAREL.pdf",
    "ARTS":                   "ARTS.pdf",
    "AUTOMOBILE":             "AUTOMOBILE.pdf",
    "AVIATION":               "AVIATION.pdf",
    "BANKING":                "BANKING.pdf",
    "BPO":                    "BPO.pdf",
    "BUSINESS-DEVELOPMENT":   "BUSINESS-DEVELOPMENT.pdf",
    "CHEF":                   "CHEF.pdf",
    "CONSTRUCTION":           "CONSTRUCTION.pdf",
    "CONSULTANT":             "CONSULTANT.pdf",
    "DESIGNER":               "DESIGNER.pdf",
    "DIGITAL-MEDIA":          "DIGITAL-MEDIA.pdf",
    "ENGINEERING":            "ENGINEERING.pdf",
    "FINANCE":                "FINANCE.pdf",
    "FITNESS":                "FITNESS.pdf",
    "HEALTHCARE":             "HEALTHCARE.pdf",
    "HR":                     "HR.pdf",
    "INFORMATION-TECHNOLOGY": "INFORMATION-TECHNOLOGY.pdf",
    "PUBLIC-RELATIONS":       "PUBLIC-RELATIONS.pdf",
    "SALES":                  "SALES.pdf",
    "TEACHER":                "TEACHER.pdf",
}

PDF_FOLDER = Path("industry_pdf_map")

# ── Intent patterns ───────────────────────────────────────────────────────────
INTENT_PATTERNS = {
    "greeting": [
        r"\bhi\b", r"\bhello\b", r"\bhey\b", r"\bgreetings\b", r"\bgood morning\b",
        r"\bgood afternoon\b", r"\bgood evening\b",
    ],
    "farewell": [
        r"\bbye\b", r"\bgoodbye\b", r"\bsee you\b", r"\bthank you\b", r"\bthanks\b",
        r"\bthank u\b", r"\bcheers\b",
    ],
    "navigation": [
        r"\bhow do i\b", r"\bhow to\b", r"\bedit.*profile\b", r"\bchange.*profile\b",
        r"\bhow does.*chatbot work\b", r"\bhow does this work\b", r"\bwhat can you do\b",
        r"\bwhat is this\b",
    ],
    "faq_data_safety": [
        r"\bdata safe\b", r"\bprivacy\b", r"\bpersonal data\b", r"\bdata shared\b",
        r"\bis my data\b", r"\bshare.*data\b", r"\bdata.*shared\b",
    ],
    "faq_score": [
        r"\bsuitability score\b", r"\bmachine learning score\b", r"\bdifference.*score\b",
        r"\bwhat is.*score\b", r"\bhow.*score.*calculated\b",
    ],
    "contact": [
        r"\bneed.*help\b", r"\bfurther help\b", r"\bconfused\b", r"\blive chat\b",
        r"\bhotline\b", r"\bcareer advisor\b", r"\bcounsellor\b", r"\bunsure.*future\b",
        r"\bspeak to someone\b", r"\btalk to someone\b",
    ],
    "missing_job": [
        r"\bwhy.*not.*recommend\b", r"\bwhy didn.t i get\b", r"\bwhy isn.t\b",
        r"\bwhy is.*not\b", r"\bnot.*suitable for me\b", r"\bmissing.*job\b",
        r"\bwhy no\b", r"\bwhere is\b.*\bjob\b", r"\bnot in.*list\b",
    ],
    "career_explain": [
        r"why .*recommended",
        r"why is .*good",
        r"tell me about",
        r"explain .*career",
        r"what does .* do",
        r"what is .* job",
        r"describe .*role",
        r"more about",
    ],
}

STATIC_RESPONSES = {
    "greeting": (
        "Hello! 👋 I'm your Career Advisor chatbot.\n"
        "I can help you understand your personalised career recommendations, "
        "answer questions about specific roles, or explain why certain careers suit you.\n"
        "What would you like to know?"
    ),
    "farewell": (
        "Thank you for using the Career Advisor! 😊\n"
        "Best of luck on your career journey. Feel free to come back anytime!"
    ),
    "navigation": (
        "Here's what I can help you with:\n\n"
        "• **Career Explanations** — Ask me about any recommended career\n"
        "• **Why a job wasn't recommended** — Ask 'Why didn't I get [job title]?'\n"
        "• **Industry overviews** — Ask about any of your top 5 industries\n"
        "• **Profile editing** — Go to Settings > Edit Profile in the main menu\n"
        "• **How scores work** — Just ask 'What is the suitability score?'\n\n"
        "You can type naturally — I'll understand what you need."
    ),
    "faq_data_safety": (
        "🔒 **Your data is safe.**\n\n"
        "• All personal data is encrypted and stored securely\n"
        "• Your data is never sold or shared with third parties\n"
        "• Only anonymised, aggregated data may be used to improve the recommendation system\n"
        "• You can request deletion of your data at any time via Settings > Privacy\n\n"
        "If you have specific concerns, please contact our support team."
    ),
    "faq_score": (
        "📊 **Suitability Score vs Machine Learning Score**\n\n"
        "**Suitability Score** — A rule-based score calculated from how well your "
        "profile attributes (skills, education, interests) directly match a career's requirements. "
        "Think of it as a checklist match.\n\n"
        "**Machine Learning Score** — A score predicted by our ML model trained on "
        "thousands of career profiles. It identifies patterns that aren't obvious from "
        "a simple checklist — for example, people with your combination of traits who "
        "succeeded in unexpected careers.\n\n"
        "The final ranking combines both scores to give you the most accurate recommendations."
    ),
    "contact": (
        "🤝 **Need to speak with a Career Advisor?**\n\n"
        "Our counsellors are available to provide personalised guidance:\n\n"
        "• 💬 **Live Chat** — Available Mon–Fri, 9am–5pm at [your live chat link]\n"
        "• 📞 **Hotline** — 1800-XXX-XXXX (Mon–Fri, 9am–5pm)\n"
        "• 📧 **Email** — careers@yourdomain.com\n"
        "• 🏢 **Walk-in** — [Your office address]\n\n"
        "A human advisor can help you explore options beyond what the system recommends."
    ),
    "missing_job": (
        "🤔 **Why wasn't a specific job recommended?**\n\n"
        "Our system ranks careers based on how closely your profile matches patterns "
        "from thousands of real resumes. A job might not appear because:\n\n"
        "• Your current skills/education don't yet align with typical candidates in that field\n"
        "• The role requires specialised qualifications not reflected in your profile\n"
        "• A similar role may appear under a different industry label\n\n"
        "Try updating your profile with more specific skills or experience to see different results."
    ),
}

# ── LLM setup (Gemini) ────────────────────────────────────────────────────────
llm = ChatGoogleGenerativeAI(
    model="models/gemini-2.5-flash",
    google_api_key=GOOGLE_API_KEY,
    temperature=0.3,
    max_output_tokens=2048,
    convert_system_message_to_human=True,  # Required: Gemini doesn't natively support SystemMessage
)

# ── Vector store loader ───────────────────────────────────────────────────────
def _load_single_store(industry: str):
    """Load and embed a single industry PDF into a FAISS vector store."""
    filename = INDUSTRY_PDF_MAP.get(industry.upper())
    if not filename:
        return None
    pdf_path = PDF_FOLDER / filename
    if not pdf_path.exists():
        print(f"[WARN] PDF not found: {pdf_path}")
        return None
    try:
        loader = PyPDFLoader(str(pdf_path))
        docs = loader.load_and_split()
        embeddings = GoogleGenerativeAIEmbeddings(
            model="models/gemini-embedding-001",
            google_api_key=GOOGLE_API_KEY,
        )
        store = FAISS.from_documents(docs, embeddings)
        return store
    except Exception as e:
        print(f"[ERROR] Failed to load {industry}: {e}")
        return None


def load_industry_stores(top_industries: list) -> dict:
    """Load vector stores only for the user's top recommended industries."""
    stores = {}
    for ind in top_industries:
        store = _load_single_store(ind)
        if store:
            stores[ind] = store
    return stores


# ── Dynamic tool factory ───────────────────────────────────────────────────────
def make_explain_career_func(stores: dict):
    """
    Returns a LangChain tool that retrieves relevant knowledge-base content
    for a given career/industry from the pre-loaded vector stores.
    """
    @tool
    def explain_career(career_or_industry: str) -> str:
        """
        Retrieve knowledge base content about a career or industry from the
        uploaded PDF documents. Call this BEFORE explaining any career to the user.

        Args:
            career_or_industry: The job title or industry name to look up.
        """
        target_key = career_or_industry.upper().strip()
        store = stores.get(target_key)

        # Fuzzy fallback
        if store is None:
            for key, s in stores.items():
                if key in target_key or target_key in key:
                    store = s
                    break

        # Final fallback: first available store
        if store is None:
            if not stores:
                return "No industry knowledge base loaded. Please ensure PDFs are available."
            store = next(iter(stores.values()))

        try:
            results = store.similarity_search(career_or_industry, k=4)
            content = "\n\n---\n\n".join([doc.page_content for doc in results])
            return content if content.strip() else "No detailed information found for this career."
        except Exception as e:
            return f"Error retrieving career information: {str(e)}"

    return explain_career


# ── Intent classifier ──────────────────────────────────────────────────────────
def classify_intent(text: str) -> str:
    """Return the matched intent name, or 'unknown'."""
    lowered = text.lower()
    for intent, patterns in INTENT_PATTERNS.items():
        for pat in patterns:
            if re.search(pat, lowered):
                return intent
    return "unknown"


# ── System prompt builder ──────────────────────────────────────────────────────
def build_system_prompt(user_profile: dict, top_industries: list, ranked_careers: dict) -> str:
    careers_summary = []
    for ind in top_industries:
        titles = ranked_careers.get(ind, [])
        careers_summary.append(f"  • {ind}: {', '.join(titles) if titles else 'N/A'}")
    careers_text = "\n".join(careers_summary)

    return f"""You are a concise Career Advisor chatbot. Answer the user's specific question directly — do not give unsolicited full career breakdowns.

USER PROFILE:
- Education: {user_profile.get('education', 'Not provided')}
- Skills: {user_profile.get('skills', 'Not provided')}
- Experience: {user_profile.get('experience', 'Not provided')}

TOP 5 RECOMMENDED INDUSTRIES AND THEIR TOP 5 JOB TITLES:
{careers_text}

INSTRUCTIONS:
1. Always call the explain_career tool first to retrieve knowledge before answering.
2. Read the user's question carefully and answer ONLY what was asked.
   - "Why is X recommended?" → 2–3 sentences on fit, referencing their profile.
   - "What does X do?" → 2–3 sentences describing the role.
   - "What skills do I need for X?" → bullet list of 3–4 key skills.
   - "What is the growth path for X?" → brief 3-step progression.
   - General questions → short direct answer, no full breakdown.
3. Never volunteer all five sections (industry fit, role fit, day-to-day, growth, skill gap) unless the user explicitly asks for a full overview.
4. Keep every response under 120 words unless the user asks for more detail.
5. Be specific to the user's profile. No generic advice.
6. If the user wants more, they will ask — let them drive the depth.
"""


# ── Routing logic ─────────────────────────────────────────────────────────────
def chat(
    user_input: str,
    user_profile: dict,
    top_industries: list,
    ranked_careers: dict,
    stores: dict,
) -> str:
    """
    Route the message:
    - Returns a static string for rule-based intents
    - Returns the sentinel "langchain" to signal the caller should run the LLM pipeline
    """
    intent = classify_intent(user_input)
    if intent in STATIC_RESPONSES:
        return STATIC_RESPONSES[intent]
    return "langchain"


# ── Standalone CLI runner (for testing without Flask) ─────────────────────────
def run_career_chatbot(user_profile: dict, top_industries: list, ranked_careers: dict):
    print("\n" + "=" * 60)
    print("  CAREER ADVISOR CHATBOT  (powered by Gemini)")
    print("=" * 60)
    print(STATIC_RESPONSES["greeting"])
    print()

    stores = load_industry_stores(top_industries)
    explain_tool = make_explain_career_func(stores)
    tools = [explain_tool]
    llm_with_tools = llm.bind_tools(tools)
    tool_map = {t.name: t for t in tools}

    system_prompt = build_system_prompt(user_profile, top_industries, ranked_careers)
    messages = [SystemMessage(content=system_prompt)]

    while True:
        try:
            user_input = input("\nYou: ").strip()
        except (KeyboardInterrupt, EOFError):
            print("\n" + STATIC_RESPONSES["farewell"])
            break

        if not user_input:
            continue

        routed = chat(user_input, user_profile, top_industries, ranked_careers, stores)
        if routed != "langchain":
            print(f"\nAdvisor: {routed}")
            if classify_intent(user_input) == "farewell":
                break
            continue

        print("\nAdvisor: ⏳ Thinking about this for you...")
        messages.append(HumanMessage(content=user_input))

        while True:
            response = llm_with_tools.invoke(messages)
            messages.append(response)

            if not response.tool_calls:
                print(f"\nAdvisor: {response.content}")
                break

            for tc in response.tool_calls:
                print(f"  [Retrieving knowledge for: {tc['args'].get('career_or_industry', '...')}]")
                result = tool_map[tc["name"]].invoke(tc["args"])
                messages.append(ToolMessage(content=result, tool_call_id=tc["id"]))