"""
Preprocessing utilities for SPARK Dashboard
Helper functions for data transformation and feature engineering
"""

import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler

def preprocess_input(data_dict, feature_names):
    """
    Preprocess user input data for model prediction
    
    Parameters:
    -----------
    data_dict : dict
        Dictionary containing feature names and values from user input
    feature_names : list
        List of all expected feature names in correct order
    
    Returns:
    --------
    DataFrame with preprocessed features ready for prediction
    """
    # Create dataframe from input
    df = pd.DataFrame([data_dict])
    
    # Ensure all required features are present
    for feature in feature_names:
        if feature not in df.columns:
            df[feature] = 0
    
    # Reorder columns to match training data
    df = df[feature_names]
    
    return df


def encode_categorical(df, categorical_cols):
    """
    One-hot encode categorical variables
    
    Parameters:
    -----------
    df : DataFrame
        Input dataframe
    categorical_cols : list
        List of categorical column names to encode
    
    Returns:
    --------
    DataFrame with encoded categorical variables
    """
    df_encoded = pd.get_dummies(df, columns=categorical_cols, prefix=categorical_cols)
    return df_encoded


def scale_features(df, numerical_cols, scaler=None):
    """
    Scale numerical features using StandardScaler
    
    Parameters:
    -----------
    df : DataFrame
        Input dataframe
    numerical_cols : list
        List of numerical column names to scale
    scaler : StandardScaler, optional
        Pre-fitted scaler. If None, a new scaler will be created
    
    Returns:
    --------
    DataFrame with scaled numerical features, fitted scaler
    """
    if scaler is None:
        scaler = StandardScaler()
        scaled_array = scaler.fit_transform(df[numerical_cols])
    else:
        scaled_array = scaler.transform(df[numerical_cols])
    
    # Create scaled dataframe
    df_scaled = df.copy()
    df_scaled[numerical_cols] = scaled_array
    
    return df_scaled, scaler


def get_feature_importance_dict(model, feature_names, top_n=10):
    """
    Extract feature importance from model
    
    Parameters:
    -----------
    model : sklearn model
        Trained model with feature_importances_ or coef_ attribute
    feature_names : list
        List of feature names
    top_n : int
        Number of top features to return
    
    Returns:
    --------
    Dictionary with feature names and importance scores
    """
    if hasattr(model, 'feature_importances_'):
        # Decision Tree, Random Forest, etc.
        importances = model.feature_importances_
    elif hasattr(model, 'coef_'):
        # Logistic Regression, Linear models
        importances = np.abs(model.coef_[0])
    else:
        return {}
    
    # Create importance dataframe
    importance_df = pd.DataFrame({
        'feature': feature_names,
        'importance': importances
    }).sort_values('importance', ascending=False).head(top_n)
    
    return importance_df.to_dict('records')


def calculate_metrics(y_true, y_pred, y_pred_proba=None):
    """
    Calculate classification metrics
    
    Parameters:
    -----------
    y_true : array-like
        True labels
    y_pred : array-like
        Predicted labels
    y_pred_proba : array-like, optional
        Predicted probabilities for positive class
    
    Returns:
    --------
    Dictionary with metric names and values
    """
    from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, roc_auc_score
    
    metrics = {
        'accuracy': accuracy_score(y_true, y_pred),
        'precision': precision_score(y_true, y_pred),
        'recall': recall_score(y_true, y_pred),
        'f1_score': f1_score(y_true, y_pred)
    }
    
    if y_pred_proba is not None:
        metrics['roc_auc'] = roc_auc_score(y_true, y_pred_proba)
    
    return metrics


def format_association_rule(rule_series):
    """
    Format association rule for display
    
    Parameters:
    -----------
    rule_series : Series
        Row from association rules dataframe
    
    Returns:
    --------
    Formatted string representation of the rule
    """
    antecedents = ', '.join(list(rule_series['antecedents']))
    consequents = ', '.join(list(rule_series['consequents']))
    
    return f"{antecedents} → {consequents} (Support: {rule_series['support']:.2%}, Confidence: {rule_series['confidence']:.2%}, Lift: {rule_series['lift']:.2f})"


def bin_numerical_feature(series, bins=3, labels=None):
    """
    Bin a numerical feature into categories
    
    Parameters:
    -----------
    series : Series
        Numerical feature to bin
    bins : int or list
        Number of bins or bin edges
    labels : list, optional
        Labels for bins
    
    Returns:
    --------
    Series with binned values
    """
    if labels is None:
        labels = ['Low', 'Medium', 'High'] if bins == 3 else [f'Bin_{i}' for i in range(bins)]
    
    return pd.cut(series, bins=bins, labels=labels)
