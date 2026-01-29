"""
Data Analytics and Report Generation Batch
Analyzes data and generates comprehensive reports
"""
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from pathlib import Path
from datetime import datetime, timedelta
from typing import Dict, Any
from ..utils.logger import log
from ..utils.database import db_session
from config.settings import settings


class AnalyticsBatch:
    """Data analytics and report generation"""

    def __init__(self):
        self.output_dir = Path(settings.report_output_dir)
        self.output_dir.mkdir(parents=True, exist_ok=True)
        sns.set_theme(style="whitegrid")

    def fetch_data(self, days: int = 30) -> pd.DataFrame:
        """Fetch data from database"""
        with db_session() as session:
            # Example: Fetch data from last N days
            # Customize based on your schema
            query = """
                SELECT
                    DATE(created_at) as date,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                FROM transactions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL %s DAY)
                GROUP BY DATE(created_at)
                ORDER BY date
            """
            # df = pd.read_sql(query, session.bind, params=(days,))

            # Sample data for demonstration
            dates = pd.date_range(end=datetime.now(), periods=days, freq='D')
            df = pd.DataFrame({
                'date': dates,
                'count': pd.np.random.randint(10, 100, size=days),
                'total_amount': pd.np.random.uniform(1000, 10000, size=days)
            })

        return df

    def analyze_data(self, df: pd.DataFrame) -> Dict[str, Any]:
        """Perform data analysis"""
        analytics = {
            'summary': {
                'total_transactions': int(df['count'].sum()),
                'total_amount': float(df['total_amount'].sum()),
                'average_daily_transactions': float(df['count'].mean()),
                'average_daily_amount': float(df['total_amount'].mean()),
                'max_transactions_day': df.loc[df['count'].idxmax(), 'date'].strftime('%Y-%m-%d'),
                'max_amount_day': df.loc[df['total_amount'].idxmax(), 'date'].strftime('%Y-%m-%d'),
            },
            'trends': {
                'transaction_growth': float((df['count'].iloc[-1] - df['count'].iloc[0]) / df['count'].iloc[0] * 100),
                'amount_growth': float((df['total_amount'].iloc[-1] - df['total_amount'].iloc[0]) / df['total_amount'].iloc[0] * 100),
            },
            'statistics': {
                'count_std': float(df['count'].std()),
                'amount_std': float(df['total_amount'].std()),
                'count_median': float(df['count'].median()),
                'amount_median': float(df['total_amount'].median()),
            }
        }

        return analytics

    def generate_visualizations(self, df: pd.DataFrame, timestamp: str):
        """Generate charts and graphs"""
        fig, axes = plt.subplots(2, 2, figsize=(15, 10))
        fig.suptitle('Data Analytics Report', fontsize=16, fontweight='bold')

        # Transaction count over time
        axes[0, 0].plot(df['date'], df['count'], marker='o', linewidth=2)
        axes[0, 0].set_title('Daily Transactions')
        axes[0, 0].set_xlabel('Date')
        axes[0, 0].set_ylabel('Count')
        axes[0, 0].grid(True, alpha=0.3)

        # Amount over time
        axes[0, 1].plot(df['date'], df['total_amount'], marker='s', color='green', linewidth=2)
        axes[0, 1].set_title('Daily Total Amount')
        axes[0, 1].set_xlabel('Date')
        axes[0, 1].set_ylabel('Amount')
        axes[0, 1].grid(True, alpha=0.3)

        # Distribution histogram
        axes[1, 0].hist(df['count'], bins=20, color='skyblue', edgecolor='black')
        axes[1, 0].set_title('Transaction Count Distribution')
        axes[1, 0].set_xlabel('Count')
        axes[1, 0].set_ylabel('Frequency')

        # Box plot
        axes[1, 1].boxplot([df['count'], df['total_amount']], labels=['Count', 'Amount'])
        axes[1, 1].set_title('Data Distribution')
        axes[1, 1].set_ylabel('Value')

        plt.tight_layout()
        chart_path = self.output_dir / f'analytics_chart_{timestamp}.png'
        plt.savefig(chart_path, dpi=300, bbox_inches='tight')
        plt.close()

        log.info(f"Charts saved to {chart_path}")
        return chart_path

    def generate_html_report(self, analytics: Dict[str, Any], chart_path: Path, timestamp: str):
        """Generate HTML report"""
        html_content = f"""
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Report - {timestamp}</title>
    <style>
        body {{ font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; background: #f5f5f5; }}
        .container {{ max-width: 1200px; margin: 0 auto; background: white; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }}
        h1 {{ color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }}
        h2 {{ color: #555; margin-top: 30px; }}
        .summary {{ display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }}
        .card {{ background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }}
        .card h3 {{ margin: 0 0 10px 0; font-size: 14px; opacity: 0.9; }}
        .card .value {{ font-size: 32px; font-weight: bold; }}
        .chart {{ text-align: center; margin: 30px 0; }}
        .chart img {{ max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; }}
        table {{ width: 100%; border-collapse: collapse; margin: 20px 0; }}
        th, td {{ padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }}
        th {{ background-color: #4CAF50; color: white; }}
        tr:hover {{ background-color: #f5f5f5; }}
        .footer {{ text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #777; }}
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Data Analytics Report</h1>
        <p>Generated: {timestamp}</p>

        <h2>Summary</h2>
        <div class="summary">
            <div class="card">
                <h3>Total Transactions</h3>
                <div class="value">{analytics['summary']['total_transactions']:,}</div>
            </div>
            <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>Total Amount</h3>
                <div class="value">¥{analytics['summary']['total_amount']:,.0f}</div>
            </div>
            <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>Avg Daily Transactions</h3>
                <div class="value">{analytics['summary']['average_daily_transactions']:.1f}</div>
            </div>
            <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <h3>Avg Daily Amount</h3>
                <div class="value">¥{analytics['summary']['average_daily_amount']:,.0f}</div>
            </div>
        </div>

        <h2>Growth Trends</h2>
        <table>
            <tr>
                <th>Metric</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Transaction Growth</td>
                <td>{analytics['trends']['transaction_growth']:+.2f}%</td>
            </tr>
            <tr>
                <td>Amount Growth</td>
                <td>{analytics['trends']['amount_growth']:+.2f}%</td>
            </tr>
        </table>

        <h2>Visualizations</h2>
        <div class="chart">
            <img src="{chart_path.name}" alt="Analytics Charts">
        </div>

        <div class="footer">
            <p>Python Batch System - Analytics Report</p>
        </div>
    </div>
</body>
</html>
"""

        report_path = self.output_dir / f'analytics_report_{timestamp}.html'
        report_path.write_text(html_content, encoding='utf-8')

        log.info(f"HTML report saved to {report_path}")
        return report_path

    def run(self, days: int = 30):
        """Run analytics batch"""
        log.info(f"Starting analytics batch for last {days} days")

        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')

        # Fetch data
        df = self.fetch_data(days)
        log.info(f"Fetched {len(df)} records")

        # Analyze data
        analytics = self.analyze_data(df)
        log.info("Data analysis completed")

        # Generate visualizations
        chart_path = self.generate_visualizations(df, timestamp)

        # Generate HTML report
        report_path = self.generate_html_report(analytics, chart_path, timestamp)

        log.info(f"Analytics batch completed. Report: {report_path}")
        return {'analytics': analytics, 'report_path': str(report_path)}


def main():
    """Main entry point"""
    batch = AnalyticsBatch()
    batch.run(days=30)


if __name__ == '__main__':
    main()
