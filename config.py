"""
Flask Application Configuration
Handles development, testing, and production configurations
"""

import os
from datetime import timedelta


class Config:
    """Base configuration - shared across all environments"""
    
    # Flask
    FLASK_APP = os.getenv('FLASK_APP', 'app.py')
    SECRET_KEY = os.getenv('SECRET_KEY', 'dev-secret-key-change-in-production')
    PERMANENT_SESSION_LIFETIME = timedelta(days=7)
    SESSION_COOKIE_SECURE = True
    SESSION_COOKIE_HTTPONLY = True
    SESSION_COOKIE_SAMESITE = 'Lax'
    
    # Database
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    SQLALCHEMY_ECHO = False
    
    # Security
    PREFERRED_URL_SCHEME = 'https'
    
    # Logging
    LOG_LEVEL = os.getenv('LOG_LEVEL', 'INFO')
    LOG_FILE = os.getenv('LOG_FILE', 'logs/app.log')
    
    # Email
    MAIL_SERVER = 'smtp.gmail.com'
    MAIL_PORT = 587
    MAIL_USE_TLS = True
    MAIL_USERNAME = os.getenv('SENDER_EMAIL', '')
    MAIL_PASSWORD = os.getenv('SENDER_PASSWORD', '')


class DevelopmentConfig(Config):
    """Development environment configuration"""
    
    DEBUG = True
    TESTING = False
    SQLALCHEMY_DATABASE_URI = os.getenv(
        'DATABASE_URL',
        'sqlite:///coding_science.db'
    )
    SQLALCHEMY_ECHO = True
    SESSION_COOKIE_SECURE = False
    PREFERRED_URL_SCHEME = 'http'


class TestingConfig(Config):
    """Testing environment configuration"""
    
    DEBUG = True
    TESTING = True
    SQLALCHEMY_DATABASE_URI = 'sqlite:///:memory:'
    WTF_CSRF_ENABLED = False
    SESSION_COOKIE_SECURE = False
    PREFERRED_URL_SCHEME = 'http'


class ProductionConfig(Config):
    """Production environment configuration"""
    
    DEBUG = False
    TESTING = False
    SQLALCHEMY_DATABASE_URI = os.getenv(
        'DATABASE_URL',
        'sqlite:////tmp/coding_science.db'
    )
    
    # Enforce production security
    SESSION_COOKIE_SECURE = True
    PERMANENT_SESSION_LIFETIME = timedelta(days=7)
    
    # Require SECRET_KEY in production
    @staticmethod
    def validate_config():
        """Validate that all required production variables are set"""
        required_vars = ['SECRET_KEY', 'DATABASE_URL']
        missing = [var for var in required_vars if not os.getenv(var)]
        
        if missing:
            raise ValueError(f"Missing required environment variables: {', '.join(missing)}")


def get_config(env=None):
    """Get configuration based on environment"""
    if env is None:
        env = os.getenv('FLASK_ENV', 'development')
    
    config_map = {
        'development': DevelopmentConfig,
        'testing': TestingConfig,
        'production': ProductionConfig
    }
    
    config = config_map.get(env, DevelopmentConfig)
    
    # Validate production config
    if env == 'production':
        config.validate_config()
    
    return config
