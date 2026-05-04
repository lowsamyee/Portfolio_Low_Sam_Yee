import pandas as pd
import numpy as np
import re
import nltk

nltk.download('stopwords', quiet=True)
from collections import Counter
from nltk.corpus import stopwords
from sklearn.model_selection import train_test_split
from sklearn.base import clone
from sklearn.preprocessing import LabelEncoder
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.metrics.pairwise import cosine_similarity

try:
    stop_words = stopwords.words("english")
except:
    nltk.download('stopwords')
    stop_words = stopwords.words("english")

df = pd.read_csv("Resume_for_model.csv")

def clean_text(text, lowercase=True):
    if not text:
        return ""
    text = re.sub(r"<.*?>", " ", text)
    text = re.sub(r"[\r\n\t]", " ", text)
    text = re.sub(r"[^a-zA-Z0-9\s,.\-&/]", " ", text)
    text = re.sub(r"\s+", " ", text).strip()
    if lowercase:
        text = text.lower()
    return text

def build_clean_skill_vocab(df, top_k=2000):
    all_skills = " ".join(df["skills"].dropna().astype(str)).lower()
    tokens = re.findall(r"[a-zA-Z0-9\+\#\.]+", all_skills)
    freq = Counter(tokens)
    generic_words = {"skills","skill","strong","excellent","knowledge","ability",
                     "experience","work","including","various","using","good","tasks","job","project"}
    max_freq_threshold = int(len(df) * 0.4)
    clean_vocab = [
        word for word, count in freq.most_common()
        if word not in generic_words and count > 3 and count < max_freq_threshold
    ]
    return set(clean_vocab[:top_k])

def filter_skills(text, vocab):
    tokens = re.findall(r"[a-zA-Z0-9\+\#\.]+", str(text).lower())
    return " ".join([t for t in tokens if t in vocab])

def softmax(x, temperature=1.0):
    x = np.array(x, dtype=np.float64) / temperature
    e = np.exp(x - np.max(x))
    return e / e.sum()

clean_skill_vocab = build_clean_skill_vocab(df)
df["skills_c"] = df["skills"].apply(lambda x: filter_skills(x, clean_skill_vocab))
df["edu_c"] = df["education"].apply(clean_text)
df["exp_c"] = df["experience"].apply(clean_text)
df["text_with_exp"] = df["edu_c"] + " " + df["skills_c"] + " " + df["exp_c"]

le = LabelEncoder()
y = le.fit_transform(df["job industry"])

tfidf_global = TfidfVectorizer(
    stop_words=stop_words, ngram_range=(1,2),
    max_features=8000, min_df=3, max_df=0.7, sublinear_tf=True
)
tfidf_global.fit(df["text_with_exp"])
X_full = tfidf_global.transform(df["text_with_exp"])

lr_model = LogisticRegression(max_iter=4000, C=0.2, n_jobs=-1, class_weight='balanced', random_state=42)
lr_model.fit(X_full, y)

X_train, X_test, y_train, y_test = train_test_split(
    df["text_with_exp"], y,
    test_size=0.2,
    random_state=42,
    stratify=y
)

train_df = df.loc[X_train.index]

job_title_index = {}
for industry in train_df["job industry"].unique():
    subset = train_df[train_df["job industry"] == industry].reset_index(drop=True)
    job_title_index[industry] = {
        "titles": subset["job title"].tolist(),
        "texts": subset["text_with_exp"].tolist()
    }

def get_recommendations(education, skills, experience):
    skills_filtered = filter_skills(skills, clean_skill_vocab)
    edu_clean = clean_text(education)
    exp_clean = clean_text(experience)
    text = edu_clean + " " + skills_filtered + " " + exp_clean

    user_vec = tfidf_global.transform([text])

    probs = lr_model.predict_proba(user_vec)[0]
    top5_idx = np.argsort(probs)[::-1][:5]
    top5_industries = le.inverse_transform(top5_idx).tolist()

    ranked_careers = {}
    for industry in top5_industries:
        if industry not in job_title_index:
            ranked_careers[industry] = []
            continue
        titles = job_title_index[industry]["titles"]
        texts = job_title_index[industry]["texts"]
        title_vecs = tfidf_global.transform(texts)
        sims = cosine_similarity(user_vec, title_vecs)[0]
        title_scores = {}
        for title, sim in zip(titles, sims):
            title_scores.setdefault(title, []).append(sim)
        aggregated = {t: float(np.mean(s)) for t, s in title_scores.items()}
        sorted_titles = sorted(aggregated.items(), key=lambda x: x[1], reverse=True)[:5]
        ranked_careers[industry] = [t for t, _ in sorted_titles]

    return top5_industries, ranked_careers

