"""
SPARK Dashboard Utilities Package
"""

from .preprocessing import (
    preprocess_input,
    encode_categorical,
    scale_features,
    get_feature_importance_dict,
    calculate_metrics,
    format_association_rule,
    bin_numerical_feature
)

__all__ = [
    'preprocess_input',
    'encode_categorical',
    'scale_features',
    'get_feature_importance_dict',
    'calculate_metrics',
    'format_association_rule',
    'bin_numerical_feature'
]
