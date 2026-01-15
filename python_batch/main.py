#!/usr/bin/env python3
"""
Python Batch System - Main Entry Point
Provides CLI interface for running batch jobs
"""
import click
import asyncio
from rich.console import Console
from rich.table import Table
from src.batch.scraping_batch import ScrapingBatch
from src.batch.analytics_batch import AnalyticsBatch
from src.batch.database_batch import DatabaseBatch
from src.batch.api_batch import ApiBatch
from src.batch.file_batch import FileBatch
from src.scheduler import BatchScheduler
from src.utils.logger import log
from config.settings import settings

console = Console()


@click.group()
@click.version_option(version='1.0.0')
def cli():
    """Python Batch System - Data Processing & Automation"""
    pass


@cli.command()
@click.option('--urls', '-u', multiple=True, help='URLs to scrape')
@click.option('--file', '-f', type=click.Path(exists=True), help='File containing URLs (one per line)')
def scrape(urls, file):
    """Run web scraping batch"""
    console.print("[bold blue]Starting Web Scraping Batch[/bold blue]")

    url_list = list(urls)
    if file:
        with open(file, 'r') as f:
            url_list.extend([line.strip() for line in f if line.strip()])

    if not url_list:
        console.print("[yellow]No URLs provided. Use --urls or --file[/yellow]")
        return

    console.print(f"Scraping {len(url_list)} URLs...")

    batch = ScrapingBatch()
    results = asyncio.run(batch.run(url_list))

    console.print(f"[green]✓ Scraped {len(results)} items[/green]")


@cli.command()
@click.option('--days', '-d', default=30, help='Number of days to analyze')
def analytics(days):
    """Generate analytics report"""
    console.print(f"[bold blue]Generating Analytics Report ({days} days)[/bold blue]")

    batch = AnalyticsBatch()
    result = batch.run(days=days)

    console.print(f"[green]✓ Report generated: {result['report_path']}[/green]")


@cli.command()
@click.option('--optimize', is_flag=True, help='Optimize tables')
@click.option('--cleanup', is_flag=True, help='Cleanup old data')
@click.option('--all', 'run_all', is_flag=True, help='Run all maintenance tasks')
def database(optimize, cleanup, run_all):
    """Run database maintenance"""
    console.print("[bold blue]Database Maintenance[/bold blue]")

    batch = DatabaseBatch()

    if run_all or cleanup:
        console.print("Cleaning up old data...")
        batch.cleanup_old_logs(90)
        batch.archive_old_data(365)

    if run_all or optimize:
        console.print("Optimizing tables...")
        batch.optimize_tables()
        batch.analyze_tables()

    if not run_all and not optimize and not cleanup:
        console.print("[yellow]No options specified. Use --all, --optimize, or --cleanup[/yellow]")
        return

    console.print("[green]✓ Database maintenance completed[/green]")


@cli.command()
def api():
    """Run API synchronization"""
    console.print("[bold blue]API Synchronization[/bold blue]")

    batch = ApiBatch()
    result = asyncio.run(batch.run())

    console.print(f"[green]✓ Synced data - Users: {result['users']}, External: {result['external_data']}[/green]")


@cli.command()
def files():
    """Process files"""
    console.print("[bold blue]File Processing[/bold blue]")

    batch = FileBatch()
    result = batch.run()

    console.print(f"[green]✓ Processed - Images: {result['images']}, PDFs: {result['pdfs']}, Excel: {result['excel']}[/green]")


@cli.command()
def schedule():
    """Start the batch scheduler"""
    console.print("[bold blue]Starting Batch Scheduler[/bold blue]")

    scheduler = BatchScheduler()
    scheduler.start()


@cli.command()
def status():
    """Show system status"""
    table = Table(title="Python Batch System Status")

    table.add_column("Component", style="cyan")
    table.add_column("Status", style="green")
    table.add_column("Details", style="yellow")

    # Check database connection
    try:
        from src.utils.database import engine
        with engine.connect() as conn:
            conn.execute("SELECT 1")
        db_status = "✓ Connected"
    except Exception as e:
        db_status = f"✗ Error: {str(e)[:50]}"

    table.add_row("Database", db_status, settings.db_host)
    table.add_row("Laravel API", "Configured" if settings.laravel_api_token else "No Token", settings.laravel_api_url)
    table.add_row("Scheduler", "Enabled" if settings.enable_scheduler else "Disabled", settings.scheduler_timezone)
    table.add_row("Environment", settings.app_env, f"Debug: {settings.debug}")

    console.print(table)


@cli.command()
def list():
    """List all available batches"""
    table = Table(title="Available Batch Jobs")

    table.add_column("Batch", style="cyan")
    table.add_column("Description", style="yellow")
    table.add_column("Command", style="green")

    batches = [
        ("Scraping", "Web data collection", "python main.py scrape"),
        ("Analytics", "Data analysis & reporting", "python main.py analytics"),
        ("Database", "Database maintenance", "python main.py database --all"),
        ("API", "External API integration", "python main.py api"),
        ("Files", "File processing", "python main.py files"),
        ("Scheduler", "Automated scheduling", "python main.py schedule"),
    ]

    for name, desc, cmd in batches:
        table.add_row(name, desc, cmd)

    console.print(table)


if __name__ == '__main__':
    cli()
