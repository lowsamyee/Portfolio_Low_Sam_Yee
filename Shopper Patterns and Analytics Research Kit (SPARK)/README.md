# SPARK Dashboard

**Shopper Patterns and Analytics Research Kit** – A Streamlit app for exploring online shopper behavior, predictions, and association rules.

---

## How to Run

### 1. Prerequisites

- Python 3.8 or higher
- pip (Python package manager)

### 2. Install Dependencies

```bash
pip install -r requirements.txt
```

### 3. Export Models and Data

**Run the Jupyter notebook `SPARK_Analysis.ipynb` first** to train models and export:

Open the notebook, run all cells, and ensure the export/save cells complete successfully.

> **Reminder:** Run the export cells in **Section 6.2** of the notebook to save models and association rules before launching the app.

### 4. Run the Application

```bash
streamlit run app.py
```

The app will open in your browser at `http://localhost:8501`.
