"""
Web Scraping Batch
Collects data from websites and stores in database/files
"""
import asyncio
from typing import List, Dict, Any
from bs4 import BeautifulSoup
import aiohttp
from tenacity import retry, stop_after_attempt, wait_exponential
from ..utils.logger import log
from ..utils.database import db_session
from config.settings import settings


class ScrapingBatch:
    """Web scraping batch processor"""

    def __init__(self):
        self.headers = {"User-Agent": settings.scraping_user_agent}
        self.timeout = settings.scraping_timeout
        self.retry_count = settings.scraping_retry_count

    @retry(
        stop=stop_after_attempt(3),
        wait=wait_exponential(multiplier=1, min=4, max=10)
    )
    async def fetch_url(self, session: aiohttp.ClientSession, url: str) -> str:
        """Fetch URL with retry"""
        async with session.get(url, headers=self.headers, timeout=self.timeout) as response:
            response.raise_for_status()
            return await response.text()

    async def scrape_multiple(self, urls: List[str]) -> List[Dict[str, Any]]:
        """Scrape multiple URLs concurrently"""
        async with aiohttp.ClientSession() as session:
            tasks = [self.scrape_url(session, url) for url in urls]
            return await asyncio.gather(*tasks, return_exceptions=True)

    async def scrape_url(self, session: aiohttp.ClientSession, url: str) -> Dict[str, Any]:
        """Scrape single URL"""
        try:
            html = await self.fetch_url(session, url)
            soup = BeautifulSoup(html, 'lxml')

            # Extract data (customize based on target website)
            data = {
                'url': url,
                'title': soup.title.string if soup.title else None,
                'meta_description': self.extract_meta(soup, 'description'),
                'meta_keywords': self.extract_meta(soup, 'keywords'),
                'headings': [h.text.strip() for h in soup.find_all(['h1', 'h2'])],
                'links': [a['href'] for a in soup.find_all('a', href=True)],
                'images': [img['src'] for img in soup.find_all('img', src=True)],
            }

            log.info(f"Successfully scraped: {url}")
            return data

        except Exception as e:
            log.error(f"Failed to scrape {url}: {e}")
            return {'url': url, 'error': str(e)}

    def extract_meta(self, soup: BeautifulSoup, name: str) -> str:
        """Extract meta tag content"""
        meta = soup.find('meta', attrs={'name': name})
        return meta['content'] if meta and 'content' in meta.attrs else None

    async def run(self, urls: List[str]):
        """Run scraping batch"""
        log.info(f"Starting scraping batch for {len(urls)} URLs")

        results = await self.scrape_multiple(urls)

        # Save results to database
        with db_session() as session:
            for result in results:
                if not isinstance(result, Exception) and 'error' not in result:
                    # Save to database (implement your model)
                    pass

        log.info(f"Scraping batch completed. Scraped {len(results)} items")
        return results


def main():
    """Main entry point"""
    batch = ScrapingBatch()
    urls = [
        'https://example.com',
        # Add more URLs
    ]

    asyncio.run(batch.run(urls))


if __name__ == '__main__':
    main()
