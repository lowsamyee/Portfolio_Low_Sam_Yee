import sqlite3
from datetime import datetime, timedelta

DB_PATH = "career_system.db"

def init_db():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("""
        CREATE TABLE IF NOT EXISTS user_context (
            session_id TEXT PRIMARY KEY,
            education TEXT,
            skills TEXT,
            experience TEXT,
            top_industries TEXT,
            top_careers TEXT,
            last_viewed_industry TEXT,
            last_viewed_title TEXT,
            expires_at TEXT
        )
    """)
    conn.commit()
    conn.close()

def save_context(session_id, education, skills, experience, top_industries, top_careers):
    import json
    expires_at = (datetime.utcnow() + timedelta(hours=24)).isoformat()
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("""
        INSERT OR REPLACE INTO user_context
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    """, (
        session_id, education, skills, experience,
        json.dumps(top_industries), json.dumps(top_careers),
        None, None, expires_at
    ))
    conn.commit()
    conn.close()

def get_context(session_id):
    import json
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    c.execute("SELECT * FROM user_context WHERE session_id = ?", (session_id,))
    row = c.fetchone()
    conn.close()
    if not row:
        return None
    expires_at = datetime.fromisoformat(row[8])
    if datetime.utcnow() > expires_at:
        delete_context(session_id)
        return None
    return {
        "session_id": row[0],
        "education": row[1],
        "skills": row[2],
        "experience": row[3],
        "top_industries": json.loads(row[4]),
        "top_careers": json.loads(row[5]),
        "last_viewed_industry": row[6],
        "last_viewed_title": row[7],
    }

def update_last_viewed(session_id, industry=None, title=None):
    expires_at = (datetime.utcnow() + timedelta(hours=24)).isoformat()
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()
    if industry:
        c.execute("UPDATE user_context SET last_viewed_industry=?, expires_at=? WHERE session_id=?",
                  (industry, expires_at, session_id))
    if title:
        c.execute("UPDATE user_context SET last_viewed_title=?, expires_at=? WHERE session_id=?",
                  (title, expires_at, session_id))
    conn.commit()
    conn.close()

def delete_context(session_id):
    conn = sqlite3.connect(DB_PATH)
    sqlite3.connect(DB_PATH).cursor().execute(
        "DELETE FROM user_context WHERE session_id=?", (session_id,)
    )
    conn.commit()
    conn.close()


