"""
Task Scheduler for Batch Jobs
Uses APScheduler for scheduling recurring tasks
"""
from apscheduler.schedulers.blocking import BlockingScheduler
from apscheduler.triggers.cron import CronTrigger
from datetime import datetime
import pytz
from .batch.scraping_batch import ScrapingBatch
from .batch.analytics_batch import AnalyticsBatch
from .batch.database_batch import DatabaseBatch
from .batch.api_batch import ApiBatch
from .batch.file_batch import FileBatch
from .utils.logger import log
from config.settings import settings


class BatchScheduler:
    """Scheduler for batch jobs"""

    def __init__(self):
        timezone = pytz.timezone(settings.scheduler_timezone)
        self.scheduler = BlockingScheduler(timezone=timezone)
        self.setup_jobs()

    def setup_jobs(self):
        """Setup scheduled jobs"""
        log.info("Setting up scheduled jobs")

        # Scraping: Every hour
        self.scheduler.add_job(
            self.run_scraping,
            CronTrigger(minute=0),  # Every hour at minute 0
            id='scraping_batch',
            name='Data Scraping Batch',
            replace_existing=True
        )

        # Analytics: Daily at 3 AM
        self.scheduler.add_job(
            self.run_analytics,
            CronTrigger(hour=3, minute=0),
            id='analytics_batch',
            name='Analytics Report Generation',
            replace_existing=True
        )

        # Database Maintenance: Weekly on Sunday at 2 AM
        self.scheduler.add_job(
            self.run_database_maintenance,
            CronTrigger(day_of_week='sun', hour=2, minute=0),
            id='database_batch',
            name='Database Maintenance',
            replace_existing=True
        )

        # API Sync: Every 30 minutes
        self.scheduler.add_job(
            self.run_api_sync,
            CronTrigger(minute='*/30'),
            id='api_batch',
            name='API Synchronization',
            replace_existing=True
        )

        # File Processing: Every 2 hours
        self.scheduler.add_job(
            self.run_file_processing,
            CronTrigger(minute=0, hour='*/2'),
            id='file_batch',
            name='File Processing',
            replace_existing=True
        )

        log.info("All jobs configured successfully")

    def run_scraping(self):
        """Run scraping batch"""
        log.info("=" * 50)
        log.info("Starting scheduled scraping batch")
        try:
            batch = ScrapingBatch()
            # Add your URLs here
            # asyncio.run(batch.run(urls=[...]))
            log.info("Scraping batch completed successfully")
        except Exception as e:
            log.error(f"Scraping batch failed: {e}")
        log.info("=" * 50)

    def run_analytics(self):
        """Run analytics batch"""
        log.info("=" * 50)
        log.info("Starting scheduled analytics batch")
        try:
            batch = AnalyticsBatch()
            batch.run(days=30)
            log.info("Analytics batch completed successfully")
        except Exception as e:
            log.error(f"Analytics batch failed: {e}")
        log.info("=" * 50)

    def run_database_maintenance(self):
        """Run database maintenance batch"""
        log.info("=" * 50)
        log.info("Starting scheduled database maintenance")
        try:
            batch = DatabaseBatch()
            batch.run()
            log.info("Database maintenance completed successfully")
        except Exception as e:
            log.error(f"Database maintenance failed: {e}")
        log.info("=" * 50)

    def run_api_sync(self):
        """Run API sync batch"""
        log.info("=" * 50)
        log.info("Starting scheduled API synchronization")
        try:
            batch = ApiBatch()
            import asyncio
            asyncio.run(batch.run())
            log.info("API sync completed successfully")
        except Exception as e:
            log.error(f"API sync failed: {e}")
        log.info("=" * 50)

    def run_file_processing(self):
        """Run file processing batch"""
        log.info("=" * 50)
        log.info("Starting scheduled file processing")
        try:
            batch = FileBatch()
            batch.run()
            log.info("File processing completed successfully")
        except Exception as e:
            log.error(f"File processing failed: {e}")
        log.info("=" * 50)

    def start(self):
        """Start the scheduler"""
        log.info("Starting batch scheduler")
        log.info(f"Timezone: {settings.scheduler_timezone}")
        log.info(f"Current time: {datetime.now()}")

        # Print scheduled jobs
        self.scheduler.print_jobs()

        try:
            self.scheduler.start()
        except (KeyboardInterrupt, SystemExit):
            log.info("Scheduler shutdown requested")
            self.scheduler.shutdown()
            log.info("Scheduler stopped")


def main():
    """Main entry point"""
    if not settings.enable_scheduler:
        log.warning("Scheduler is disabled in settings")
        return

    scheduler = BatchScheduler()
    scheduler.start()


if __name__ == '__main__':
    main()
