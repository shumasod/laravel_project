"""
Configuration settings for Python Batch System
"""
from pathlib import Path
from typing import Optional
from pydantic_settings import BaseSettings
from pydantic import Field


class Settings(BaseSettings):
    """Application settings"""

    # Application
    app_name: str = Field(default="PythonBatchSystem", env="APP_NAME")
    app_env: str = Field(default="development", env="APP_ENV")
    debug: bool = Field(default=False, env="DEBUG")

    # Laravel API
    laravel_api_url: str = Field(default="http://localhost:8000/api", env="LARAVEL_API_URL")
    laravel_api_token: Optional[str] = Field(default=None, env="LARAVEL_API_TOKEN")

    # Database
    db_connection: str = Field(default="mysql", env="DB_CONNECTION")
    db_host: str = Field(default="localhost", env="DB_HOST")
    db_port: int = Field(default=3306, env="DB_PORT")
    db_database: str = Field(default="laravel", env="DB_DATABASE")
    db_username: str = Field(default="root", env="DB_USERNAME")
    db_password: str = Field(default="", env="DB_PASSWORD")

    # Redis
    redis_host: str = Field(default="localhost", env="REDIS_HOST")
    redis_port: int = Field(default=6379, env="REDIS_PORT")
    redis_password: Optional[str] = Field(default=None, env="REDIS_PASSWORD")
    redis_db: int = Field(default=0, env="REDIS_DB")

    # Scraping
    scraping_user_agent: str = Field(
        default="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
        env="SCRAPING_USER_AGENT"
    )
    scraping_timeout: int = Field(default=30, env="SCRAPING_TIMEOUT")
    scraping_retry_count: int = Field(default=3, env="SCRAPING_RETRY_COUNT")
    scraping_concurrent_requests: int = Field(default=5, env="SCRAPING_CONCURRENT_REQUESTS")

    # File Processing
    upload_dir: str = Field(default="../new_repository/storage/app/uploads", env="UPLOAD_DIR")
    temp_dir: str = Field(default="./data/temp", env="TEMP_DIR")
    max_file_size: int = Field(default=10485760, env="MAX_FILE_SIZE")

    # Reporting
    report_output_dir: str = Field(default="./reports", env="REPORT_OUTPUT_DIR")
    report_format: str = Field(default="html,pdf,excel", env="REPORT_FORMAT")

    # Logging
    log_level: str = Field(default="INFO", env="LOG_LEVEL")
    log_file: str = Field(default="./logs/batch.log", env="LOG_FILE")
    log_rotation: str = Field(default="1 day", env="LOG_ROTATION")
    log_retention: str = Field(default="30 days", env="LOG_RETENTION")

    # External APIs
    external_api_key: Optional[str] = Field(default=None, env="EXTERNAL_API_KEY")
    external_api_timeout: int = Field(default=60, env="EXTERNAL_API_TIMEOUT")

    # Monitoring
    sentry_dsn: Optional[str] = Field(default=None, env="SENTRY_DSN")
    enable_monitoring: bool = Field(default=False, env="ENABLE_MONITORING")

    # Scheduler
    scheduler_timezone: str = Field(default="Asia/Tokyo", env="SCHEDULER_TIMEZONE")
    enable_scheduler: bool = Field(default=True, env="ENABLE_SCHEDULER")

    @property
    def database_url(self) -> str:
        """Get database URL"""
        return f"{self.db_connection}://{self.db_username}:{self.db_password}@{self.db_host}:{self.db_port}/{self.db_database}"

    @property
    def redis_url(self) -> str:
        """Get Redis URL"""
        password = f":{self.redis_password}@" if self.redis_password else ""
        return f"redis://{password}{self.redis_host}:{self.redis_port}/{self.redis_db}"

    @property
    def base_dir(self) -> Path:
        """Get base directory"""
        return Path(__file__).parent.parent

    def get_report_formats(self) -> list[str]:
        """Get list of report formats"""
        return [fmt.strip() for fmt in self.report_format.split(',')]

    class Config:
        env_file = ".env"
        env_file_encoding = "utf-8"


# Global settings instance
settings = Settings()
