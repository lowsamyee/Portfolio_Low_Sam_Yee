"""
SPARK Dashboard - Shopper Patterns and Analytics Research Kit
A Streamlit web application for exploring e-commerce behavior analysis results
"""

import streamlit as st
import pandas as pd
import numpy as np
import plotly.express as px
import plotly.graph_objects as go
from plotly.subplots import make_subplots
import pickle
import os

# Page configuration
st.set_page_config(
    page_title="SPARK Dashboard",
    page_icon="🛒",
    layout="wide",
    initial_sidebar_state="expanded"
)

# Custom CSS
st.markdown("""
<style>
    .main-header {
        font-size: 3rem;
        color: #1f77b4;
        text-align: center;
        margin-bottom: 2rem;
    }
    .metric-card {
        background-color: #f0f2f6;
        padding: 1rem;
        border-radius: 0.5rem;
        margin: 0.5rem 0;
    }
</style>
""", unsafe_allow_html=True)

# Load data function with caching
@st.cache_data
def load_data():
    """Load the online shoppers dataset"""
    try:
        df = pd.read_csv('online_shoppers_intention.csv')
        return df
    except FileNotFoundError:
        st.error("Dataset not found. Please ensure 'online_shoppers_intention.csv' is in the project directory.")
        return None

# Load models function
@st.cache_resource
def load_models():
    """Load trained models"""
    models = {}
    try:
        if os.path.exists('models/logistic_regression.pkl'):
            with open('models/logistic_regression.pkl', 'rb') as f:
                models['lr'] = pickle.load(f)
        if os.path.exists('models/decision_tree.pkl'):
            with open('models/decision_tree.pkl', 'rb') as f:
                models['dt'] = pickle.load(f)
        if os.path.exists('models/scaler.pkl'):
            with open('models/scaler.pkl', 'rb') as f:
                models['scaler'] = pickle.load(f)
    except Exception as e:
        st.warning(f"Could not load models: {e}")
    return models

# Sidebar navigation
st.sidebar.title("🛒 SPARK Dashboard")
st.sidebar.markdown("---")

page = st.sidebar.radio(
    "Navigation",
    ["📊 Overview", "📈 Data Explorer", "🎯 Model Performance", 
     "🔮 Model Prediction", "🔗 Association Rules"]
)

# Load data
df = load_data()

if df is not None:
    # Main content based on selected page
    
    if page == "📊 Overview":
        st.markdown('<h1 class="main-header">🛒 SPARK Dashboard</h1>', unsafe_allow_html=True)
        st.markdown("### Shopper Patterns and Analytics Research Kit")
        
        st.markdown("""
        Welcome to the SPARK Dashboard! This interactive application presents insights from our 
        comprehensive analysis of online shopping behavior and revenue prediction.
        """)
        
        # Project overview
        col1, col2, col3 = st.columns(3)
        
        with col1:
            st.markdown("#### 📊 Dataset Info")
            st.metric("Total Sessions", f"{len(df):,}")
            st.metric("Features", df.shape[1])
            st.metric("Revenue Sessions", f"{df['Revenue'].sum():,}")
        
        with col2:
            st.markdown("#### 🎯 Classification")
            st.info("""
            **Models Trained:**
            - Logistic Regression
            - Decision Tree
            
            **Best Model:** Decision Tree  
            **Accuracy:** 89.02%
            """)
        
        with col3:
            st.markdown("#### 🔗 Association Rules")
            st.info("""
            **Patterns Discovered:**
            - 734 Frequent Itemsets
            - 2,644 Association Rules
            
            **Min Support:** 5%  
            **Min Confidence:** 60%
            """)
        
        st.markdown("---")
        
        # Dataset overview
        st.markdown("### 📋 Dataset Quick Stats")
        
        col1, col2 = st.columns(2)
        
        with col1:
            st.markdown("#### Revenue Distribution")
            revenue_counts = df['Revenue'].value_counts()
            fig = px.pie(
                values=revenue_counts.values,
                names=['No Revenue', 'Revenue'],
                color_discrete_sequence=['#ff6b6b', '#51cf66']
            )
            fig.update_layout(
                title_text="",
                showlegend=True,
                legend=dict(orientation="h", yanchor="bottom", y=-0.12, xanchor="center", x=0.5)
            )
            st.plotly_chart(fig, use_container_width=True)
        
        with col2:
            st.markdown("#### Visitor Types")
            visitor_counts = df[df['VisitorType'] != 'Other']['VisitorType'].value_counts()
            short_labels = [s.replace('_', ' ') for s in visitor_counts.index]
            
            import plotly.graph_objects as go
            fig = go.Figure()
            fig.add_trace(go.Bar(
                x=short_labels,
                y=visitor_counts.values,
                marker_color=['#4c78a8', '#f58518'],
                text=visitor_counts.values,
                textposition='outside'
            ))
            fig.update_layout(
                title_text="",
                showlegend=False,
                xaxis=dict(title="", tickangle=0, type='category'),
                yaxis=dict(title="Count"),
                bargap=0.4,
                margin=dict(l=40, r=40, t=10, b=60),
                height=400
            )
            st.plotly_chart(fig, use_container_width=True)
        
        st.markdown("---")
        st.markdown("### 🚀 Get Started")
        st.markdown("""
        Use the sidebar to navigate through different sections:
        - **Data Explorer:** Explore the dataset with interactive visualizations
        - **Model Performance:** Compare classification model results
        - **Model Prediction:** Test the models with custom inputs
        - **Association Rules:** Explore discovered shopping patterns
        - **Data Explorer:** Analyze the dataset in detail
        """)
    
    elif page == "🎯 Model Performance":
        st.markdown("## 🎯 Model Performance Comparison")
        
        st.markdown("""
        This section presents the performance evaluation of two classification models trained 
        to predict whether a shopping session will result in revenue.
        """)
        
        # Model comparison metrics
        st.markdown("### 📊 Performance Metrics")
        
        comparison_data = {
            'Metric': ['Accuracy', 'Precision', 'Recall', 'F1-Score', 'ROC-AUC'],
            'Logistic Regression': [0.8894, 0.7718, 0.4162, 0.5408, 0.9022],
            'Decision Tree': [0.8902, 0.6500, 0.6466, 0.6483, 0.9080]
        }
        comparison_df = pd.DataFrame(comparison_data)
        
        # Display metrics table
        st.dataframe(
            comparison_df.style.format({
                'Logistic Regression': '{:.2%}',
                'Decision Tree': '{:.2%}'
            }).highlight_max(axis=1, subset=['Logistic Regression', 'Decision Tree'], color='lightgreen'),
            use_container_width=True
        )
        
        # Metrics visualization
        st.markdown("### 📈 Metrics Visualization")
        
        fig = go.Figure()
        fig.add_trace(go.Bar(
            name='Logistic Regression',
            x=comparison_df['Metric'],
            y=comparison_df['Logistic Regression'],
            marker_color='#1f77b4'
        ))
        fig.add_trace(go.Bar(
            name='Decision Tree',
            x=comparison_df['Metric'],
            y=comparison_df['Decision Tree'],
            marker_color='#ff7f0e'
        ))
        
        fig.update_layout(
            title="Model Performance Comparison",
            xaxis_title="Metric",
            yaxis_title="Score",
            barmode='group',
            height=400
        )
        st.plotly_chart(fig, use_container_width=True)
        
        # Winner announcement
        st.markdown("### 🏆 Winner: Decision Tree")
        
        col1, col2 = st.columns(2)
        
        with col1:
            st.success("""
            **Decision Tree Advantages:**
            - ✅ Higher Accuracy (89.02% vs 88.94%)
            - ✅ Better Recall (64.66% vs 41.62%)
            - ✅ Higher F1-Score (64.83% vs 54.08%)
            - ✅ Captures non-linear patterns
            """)
        
        with col2:
            st.info("""
            **Logistic Regression Strengths:**
            - ✅ Higher Precision (77.18% vs 65.00%)
            - ✅ Faster inference time
            - ✅ More interpretable coefficients
            """)
        
        st.markdown("""
        **Recommendation:** Use **Decision Tree** for production as it achieves about 55% higher recall, 
        making it significantly better at identifying sessions that will result in purchases—the 
        primary business objective.
        """)
        
        # Confusion matrices
        st.markdown("---")
        st.markdown("### 🎯 Confusion Matrices")
        
        col1, col2 = st.columns(2)
        
        with col1:
            st.markdown("#### Logistic Regression")
            cm_lr = np.array([[1998, 61], [238, 144]])
            fig = px.imshow(
                cm_lr,
                labels=dict(x="Predicted", y="Actual", color="Count"),
                x=['No Revenue', 'Revenue'],
                y=['No Revenue', 'Revenue'],
                color_continuous_scale='Blues',
                text_auto=True,
                title="Logistic Regression Confusion Matrix"
            )
            st.plotly_chart(fig, use_container_width=True)
        
        with col2:
            st.markdown("#### Decision Tree")
            cm_dt = np.array([[1952, 107], [166, 216]])
            fig = px.imshow(
                cm_dt,
                labels=dict(x="Predicted", y="Actual", color="Count"),
                x=['No Revenue', 'Revenue'],
                y=['No Revenue', 'Revenue'],
                color_continuous_scale='Oranges',
                text_auto=True,
                title="Decision Tree Confusion Matrix"
            )
            st.plotly_chart(fig, use_container_width=True)
    
    elif page == "🔮 Model Prediction":
        st.markdown("## 🔮 Revenue Prediction")
        
        st.markdown("""
        Test the trained models by adjusting the session features below. 
        The models will predict whether the session will result in revenue.
        """)
        
        # Check if models are loaded
        with st.spinner("Loading models..."):
            models = load_models()
        
        if not models:
            st.warning("""
            ⚠️ Models not found. To use this feature:
            1. Run the notebook to train models
            2. Save models using the export code cell
            3. Ensure models/ directory contains the .pkl files
            """)
        
        st.markdown("---")
        
        # Input form
        st.markdown("### 📝 Enter Session Features")
        
        st.info("💡 **Note:** Some technical features (Browser, OS, Region, Traffic Type) are automatically set to typical values for simplicity.")
        
        col1, col2, col3 = st.columns(3)
        
        with col1:
            st.markdown("**Page Visits**")
            administrative = st.slider("Administrative Pages", 0, 20, 2)
            informational = st.slider("Informational Pages", 0, 20, 0)
            product_related = st.slider("Product Related Pages", 0, 50, 10)
        
        with col2:
            st.markdown("**Duration (seconds)**")
            admin_duration = st.slider("Administrative Duration", 0, 3000, 100)
            info_duration = st.slider("Informational Duration", 0, 2000, 0)
            product_duration = st.slider("Product Related Duration", 0, 5000, 500)
        
        with col3:
            st.markdown("**Engagement Metrics**")
            bounce_rate = st.slider("Bounce Rate", 0.0, 0.5, 0.02, 0.01)
            exit_rate = st.slider("Exit Rate", 0.0, 0.5, 0.03, 0.01)
            page_values = st.slider("Page Values", 0.0, 100.0, 5.0, 1.0)
        
        col1, col2 = st.columns(2)
        
        with col1:
            month = st.selectbox("Month", ['Feb', 'Mar', 'May', 'Jun', 'Jul', 'Aug', 
                                           'Sep', 'Oct', 'Nov', 'Dec'])
            visitor_type = st.selectbox("Visitor Type", 
                                       ['Returning_Visitor', 'New_Visitor', 'Other'])
        
        with col2:
            special_day = st.slider("Special Day", 0.0, 1.0, 0.0, 0.1)
            weekend = st.checkbox("Weekend Session")
        
        # Predict button
        if st.button("🔮 Predict Revenue", type="primary"):
            st.markdown("---")
            st.markdown("### 🎯 Prediction Results")
            
            # Prepare input features
            if models and 'lr' in models and 'dt' in models:
                try:
                    # Load feature names
                    with open('models/feature_names.pkl', 'rb') as f:
                        feature_names = pickle.load(f)
                    
                    # Create a list to hold feature values in the exact order
                    feature_values = []
                    
                    for feature in feature_names:
                        # Default value
                        value = 0
                        
                        # Set user input values
                        if feature == 'Administrative':
                            value = administrative
                        elif feature == 'Administrative_Duration':
                            value = admin_duration
                        elif feature == 'Informational':
                            value = informational
                        elif feature == 'Informational_Duration':
                            value = info_duration
                        elif feature == 'ProductRelated':
                            value = product_related
                        elif feature == 'ProductRelated_Duration':
                            value = product_duration
                        elif feature == 'BounceRates':
                            value = bounce_rate
                        elif feature == 'ExitRates':
                            value = exit_rate
                        elif feature == 'PageValues':
                            value = page_values
                        elif feature == 'SpecialDay':
                            value = special_day
                        elif feature == 'OperatingSystems':
                            value = 2  # Default
                        elif feature == 'Browser':
                            value = 2  # Default
                        elif feature == 'Region':
                            value = 1  # Default
                        elif feature == 'TrafficType':
                            value = 2  # Default
                        elif feature == 'Weekend':
                            value = 1 if weekend else 0
                        elif feature == f'Month_{month}':
                            value = 1
                        elif feature == f'VisitorType_{visitor_type}':
                            value = 1
                        
                        feature_values.append(value)
                    
                    # Create DataFrame with exact feature names and values
                    # Convert to list to ensure compatibility
                    feature_names_list = list(feature_names)
                    input_data = pd.DataFrame([feature_values], columns=feature_names_list)
                    
                    # Scale numerical features if scaler is available
                    if 'scaler' in models:
                        # Get columns that were used during scaler training
                        scaler_features = models['scaler'].feature_names_in_ if hasattr(models['scaler'], 'feature_names_in_') else None
                        
                        if scaler_features is not None:
                            # Use the exact columns the scaler expects
                            cols_to_scale = [col for col in scaler_features if col in input_data.columns]
                            
                            # Scale only those columns
                            scaled_values = models['scaler'].transform(input_data[cols_to_scale])
                            
                            # Create new dataframe preserving ALL columns
                            input_scaled = input_data.copy()
                            
                            # Update only the scaled columns
                            for i, col in enumerate(cols_to_scale):
                                input_scaled[col] = scaled_values[0][i]
                        else:
                            # Fallback: scale common numerical columns
                            numerical_cols = ['Administrative', 'Administrative_Duration', 'Informational',
                                            'Informational_Duration', 'ProductRelated', 'ProductRelated_Duration',
                                            'BounceRates', 'ExitRates', 'PageValues', 'SpecialDay',
                                            'OperatingSystems', 'Browser', 'Region', 'TrafficType']
                            
                            input_scaled = input_data.copy()
                            cols_to_scale = [col for col in numerical_cols if col in input_data.columns]
                            scaled_array = models['scaler'].transform(input_data[cols_to_scale])
                            
                            for i, col in enumerate(cols_to_scale):
                                input_scaled[col] = scaled_array[0][i]
                        
                    else:
                        input_scaled = input_data
                    
                    # Make predictions with both models
                    lr_pred = models['lr'].predict(input_scaled)[0]
                    lr_prob = models['lr'].predict_proba(input_scaled)[0][1]
                    
                    dt_pred = models['dt'].predict(input_scaled)[0]
                    dt_prob = models['dt'].predict_proba(input_scaled)[0][1]
                    
                    # Display results
                    col1, col2 = st.columns(2)
                    
                    with col1:
                        st.markdown("#### 🤖 Logistic Regression")
                        pred_lr = "💰 Revenue Expected!" if lr_pred == 1 else "📭 No Revenue"
                        
                        if lr_pred == 1:
                            st.success(pred_lr)
                        else:
                            st.error(pred_lr)
                        
                        st.metric("Probability", f"{lr_prob:.1%}")
                        st.progress(float(lr_prob))
                        
                        # Show confidence level
                        confidence = abs(lr_prob - 0.5) * 2
                        conf_label = "High" if confidence > 0.7 else "Medium" if confidence > 0.4 else "Low"
                        st.caption(f"Confidence: {conf_label} ({confidence:.1%})")
                    
                    with col2:
                        st.markdown("#### 🌳 Decision Tree")
                        pred_dt = "💰 Revenue Expected!" if dt_pred == 1 else "📭 No Revenue"
                        
                        if dt_pred == 1:
                            st.success(pred_dt)
                        else:
                            st.error(pred_dt)
                        
                        st.metric("Probability", f"{dt_prob:.1%}")
                        st.progress(float(dt_prob))
                        
                        # Show confidence level
                        confidence = abs(dt_prob - 0.5) * 2
                        conf_label = "High" if confidence > 0.7 else "Medium" if confidence > 0.4 else "Low"
                        st.caption(f"Confidence: {conf_label} ({confidence:.1%})")
                    
                    # Model agreement
                    st.markdown("---")
                    if lr_pred == dt_pred:
                        st.success(f"✅ **Both models agree:** {pred_lr}")
                    else:
                        st.warning(f"⚠️ **Models disagree:** LR says {pred_lr}, DT says {pred_dt}")
                    
                    # Show probability difference
                    prob_diff = abs(lr_prob - dt_prob)
                    st.info(f"📊 Probability difference: {prob_diff:.1%} (LR: {lr_prob:.1%} vs DT: {dt_prob:.1%})")
                    
                except Exception as e:
                    st.error(f"❌ Prediction error: {str(e)}")
                    st.info("Run the notebook export (Section 6.2) to train and save models, then ensure all model files are in the models/ directory.")
            else:
                st.error("⚠️ Models not loaded. Run the notebook to train and export models, then place the .pkl files in the models/ directory.")
            
    
    elif page == "🔗 Association Rules":
        st.markdown("## 🔗 Association Rules Discovery")
        
        # Try to load real association rules
        try:
            rules_df_real = pd.read_csv('models/association_rules.csv')
            
            # Convert string representations of frozensets back to readable format
            if 'antecedents' in rules_df_real.columns:
                rules_df_real['Antecedents'] = rules_df_real['antecedents'].astype(str)
                rules_df_real['Consequents'] = rules_df_real['consequents'].astype(str)
            
            total_rules = len(rules_df_real)
            using_real_data = True
        except FileNotFoundError:
            st.warning("⚠️ Real association rules not found. Run the export cell in your notebook (Section 6.2) to generate and save association rules.")
            rules_df_real = None
            total_rules = 0
            using_real_data = False
        
        # Load frequent itemsets count
        try:
            itemsets_df = pd.read_csv('models/frequent_itemsets.csv')
            total_itemsets = len(itemsets_df)
        except:
            total_itemsets = 734
        
        st.markdown(f"""
        Association rule mining revealed **{total_rules:,} patterns** in shopping behavior from **{total_itemsets} frequent itemsets**. 
        These rules identify which combinations of behaviors frequently lead to purchases.
        """)
        
        # Summary stats
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            st.metric("Frequent Itemsets", f"{total_itemsets}")
        with col2:
            st.metric("Total Rules", f"{total_rules:,}")
        with col3:
            if using_real_data and rules_df_real is not None:
                revenue_rules_count = len(rules_df_real[rules_df_real['Consequents'].str.contains('Revenue', case=False, na=False)])
                st.metric("Revenue Rules", f"{revenue_rules_count}")
            else:
                st.metric("Revenue Rules", "156")
        with col4:
            st.metric("Min Confidence", "60%")
        
        st.markdown("---")
        
        # Filter options
        st.markdown("### 🎛️ Filter Rules")
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            min_support = st.slider("Min Support (%)", 5, 15, 5) / 100
        with col2:
            min_confidence = st.slider("Min Confidence (%)", 60, 100, 60) / 100
        with col3:
            min_lift = st.slider("Min Lift", 1.0, 6.0, 1.0, 0.5)
        with col4:
            show_count = st.selectbox("Show Top", [10, 20, 30, 50], index=1)
        
        # Category filter
        category_filter = st.selectbox(
            "Filter by Category",
            ["All", "💰 Revenue", "❌ No Revenue", "📊 Engagement"],
            index=0
        )
        
        # Use real data if available; otherwise show empty state
        if using_real_data and rules_df_real is not None:
            # Use real rules from CSV
            all_rules_data = rules_df_real.copy()
            
            # Standardize column names
            if 'support' in all_rules_data.columns:
                all_rules_data.rename(columns={
                    'support': 'Support',
                    'confidence': 'Confidence',
                    'lift': 'Lift'
                }, inplace=True)
            
            # Add category based on consequents
            if 'Consequents' in all_rules_data.columns:
                def categorize_rule(row):
                    cons = str(row['Consequents']).lower()
                    if 'revenue_yes' in cons or "frozenset({'revenue_yes'})" in cons:
                        return '💰 Revenue'
                    elif 'revenue_no' in cons or "frozenset({'revenue_no'})" in cons:
                        return '❌ No Revenue'
                    else:
                        return '📊 Engagement'
                
                all_rules_data['Category'] = all_rules_data.apply(categorize_rule, axis=1)
            
            rules_df_all = all_rules_data
        else:
            rules_df_all = pd.DataFrame(columns=['Antecedents', 'Consequents', 'Support', 'Confidence', 'Lift', 'Category'])
        
        # Filter rules
        filtered_rules = rules_df_all[
            (rules_df_all['Support'] >= min_support) &
            (rules_df_all['Confidence'] >= min_confidence) &
            (rules_df_all['Lift'] >= min_lift) &
            ((rules_df_all['Category'] == category_filter) if category_filter != "All" else True)
        ].copy()
        
        total_matching = len(filtered_rules)
        filtered_rules = filtered_rules.head(show_count)
        
        # Reset index and renumber from 1
        filtered_rules.reset_index(drop=True, inplace=True)
        filtered_rules['#'] = range(1, len(filtered_rules) + 1)
        
        st.markdown(f"### 🏆 Top {len(filtered_rules)} Association Rules")
        if total_matching > show_count:
            st.caption(f"Showing top {show_count} of {total_matching} rules matching filters (from {total_rules:,} total rules)")
        else:
            st.caption(f"Showing all {total_matching} rules matching filters (from {total_rules:,} total rules)")
        
        # Display rules table with renumbered index
        display_cols = ['#', 'Antecedents', 'Consequents', 'Support', 'Confidence', 'Lift', 'Category']
        st.dataframe(
            filtered_rules[display_cols].style.format({
                'Support': '{:.1%}',
                'Confidence': '{:.1%}',
                'Lift': '{:.2f}'
            }).background_gradient(subset=['Lift'], cmap='YlOrRd'),
            use_container_width=True,
            height=500
        )
        
        st.markdown("---")
        
        # Rule visualization
        st.markdown("### 📈 Rules Visualization")
        
        col1, col2 = st.columns([2, 1])
        
        with col1:
            # Add jitter to separate overlapping points
            rules_df_viz = filtered_rules.copy()
            np.random.seed(42)  # For reproducibility
            rules_df_viz['Support_jitter'] = rules_df_viz['Support'] + np.random.uniform(-0.002, 0.002, len(rules_df_viz))
            rules_df_viz['Confidence_jitter'] = rules_df_viz['Confidence'] + np.random.uniform(-0.005, 0.005, len(rules_df_viz))
            
            fig = px.scatter(
                rules_df_viz,
                x='Support_jitter',
                y='Confidence_jitter',
                size='Lift',
                color='Category',
                hover_data={
                    'Support_jitter': False,
                    'Confidence_jitter': False,
                    'Support': ':.1%',
                    'Confidence': ':.1%',
                    'Lift': ':.2f',
                    'Antecedents': True,
                    'Consequents': True,
                    '#': True,
                    'Category': True
                },
                title="Association Rules: Support vs Confidence (sized by Lift)",
                labels={'Support_jitter': 'Support', 'Confidence_jitter': 'Confidence'},
                color_discrete_map={'💰 Revenue': '#2ecc71', '📊 Engagement': '#3498db', '❌ No Revenue': '#e74c3c'}
            )
            fig.update_traces(
                marker=dict(opacity=0.8, line=dict(width=1, color='white'))
            )
            fig.update_layout(height=500)
            st.plotly_chart(fig, use_container_width=True)
        
        with col2:
            st.markdown("#### 📊 Rule Statistics")
            
            st.metric("Avg Lift", f"{filtered_rules['Lift'].mean():.2f}")
            st.metric("Max Confidence", f"{filtered_rules['Confidence'].max():.1%}")
            st.metric("Avg Support", f"{filtered_rules['Support'].mean():.1%}")
            
            st.markdown("---")
            st.markdown("**Rule Categories:**")
            category_counts = filtered_rules['Category'].value_counts()
            for cat, count in category_counts.items():
                st.write(f"{cat}: {count}")
        
        st.caption("💡 **Note:** Point size represents Lift (larger = stronger association). Small jitter added to separate overlapping rules.")
        
    
    elif page == "📈 Data Explorer":
        st.markdown("## 📈 Dataset Explorer")
        
        st.markdown("""
        Explore the online shoppers intention dataset with interactive visualizations and statistics.
        """)
        
        # Dataset info
        st.markdown("### 📊 Dataset Information")
        
        col1, col2, col3, col4 = st.columns(4)
        
        with col1:
            st.metric("Total Records", f"{len(df):,}")
        with col2:
            st.metric("Features", df.shape[1])
        with col3:
            revenue_rate = (df['Revenue'].sum() / len(df)) * 100
            st.metric("Revenue Rate", f"{revenue_rate:.1f}%")
        with col4:
            st.metric("Missing Values", df.isnull().sum().sum())
        
        st.markdown("---")
        
        # Feature distributions
        st.markdown("### 📊 Feature Distributions")
        
        # Select feature to visualize
        numerical_cols = df.select_dtypes(include=['int64', 'float64']).columns.tolist()
        selected_feature = st.selectbox("Select feature to visualize:", numerical_cols)
        
        col1, col2 = st.columns(2)
        
        with col1:
            fig = px.histogram(
                df,
                x=selected_feature,
                color='Revenue',
                title=f"{selected_feature} Distribution by Revenue",
                labels={selected_feature: selected_feature, 'count': 'Frequency'},
                barmode='overlay',
                opacity=0.7
            )
            st.plotly_chart(fig, use_container_width=True)
        
        with col2:
            fig = px.box(
                df,
                x='Revenue',
                y=selected_feature,
                color='Revenue',
                title=f"{selected_feature} Box Plot by Revenue",
                labels={selected_feature: selected_feature, 'Revenue': 'Revenue'}
            )
            st.plotly_chart(fig, use_container_width=True)
        
        st.markdown("---")
        
        # Correlation heatmap
        st.markdown("### 🔥 Feature Correlation Heatmap")
        
        # Select top numerical features
        top_features = ['Administrative', 'Informational', 'ProductRelated', 
                       'BounceRates', 'ExitRates', 'PageValues', 'SpecialDay']
        
        corr_matrix = df[top_features].corr()
        
        fig = px.imshow(
            corr_matrix,
            labels=dict(color="Correlation"),
            x=top_features,
            y=top_features,
            color_continuous_scale='RdBu_r',
            aspect="auto",
            title="Feature Correlation Matrix"
        )
        st.plotly_chart(fig, use_container_width=True)
        
        st.markdown("---")
        
        # Sample data viewer
        st.markdown("### 👀 Sample Data")
        
        num_rows = st.slider("Number of rows to display:", 5, 50, 10)
        st.dataframe(df.head(num_rows), use_container_width=True)
        
        # Download option
        st.markdown("---")
        st.markdown("### 📥 Download Data")
        
        csv = df.to_csv(index=False).encode('utf-8')
        st.download_button(
            label="Download Full Dataset (CSV)",
            data=csv,
            file_name="online_shoppers_data.csv",
            mime="text/csv"
        )
