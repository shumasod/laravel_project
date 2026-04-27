"""
External API Integration Batch
Integrates with external APIs and Laravel API
"""
import asyncio
import httpx
from typing import List, Dict, Any
from tenacity import retry, stop_after_attempt, wait_exponential
from ..utils.logger import log
from config.settings import settings


class ApiBatch:
    """External API integration"""

    def __init__(self):
        self.laravel_api_url = settings.laravel_api_url
        self.api_token = settings.laravel_api_token
        self.timeout = settings.external_api_timeout

    def get_headers(self) -> Dict[str, str]:
        """Get API request headers"""
        headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
        if self.api_token:
            headers['Authorization'] = f'Bearer {self.api_token}'
        return headers

    @retry(stop=stop_after_attempt(3), wait=wait_exponential(multiplier=1, min=4, max=10))
    async def call_laravel_api(self, endpoint: str, method: str = 'GET', data: Dict = None) -> Dict[str, Any]:
        """Call Laravel API"""
        url = f"{self.laravel_api_url}/{endpoint}"

        async with httpx.AsyncClient(timeout=self.timeout) as client:
            if method == 'GET':
                response = await client.get(url, headers=self.get_headers())
            elif method == 'POST':
                response = await client.post(url, headers=self.get_headers(), json=data)
            elif method == 'PUT':
                response = await client.put(url, headers=self.get_headers(), json=data)
            elif method == 'DELETE':
                response = await client.delete(url, headers=self.get_headers())
            else:
                raise ValueError(f"Unsupported method: {method}")

            response.raise_for_status()
            return response.json()

    async def fetch_users(self) -> List[Dict]:
        """Fetch users from Laravel API"""
        try:
            result = await self.call_laravel_api('users', 'GET')
            log.info(f"Fetched {len(result.get('data', []))} users")
            return result.get('data', [])
        except Exception as e:
            log.error(f"Failed to fetch users: {e}")
            return []

    async def sync_data_to_laravel(self, data: List[Dict]):
        """Sync data to Laravel API"""
        results = []

        for item in data:
            try:
                result = await self.call_laravel_api('data/sync', 'POST', item)
                results.append({'status': 'success', 'data': result})
                log.info(f"Synced item: {item.get('id', 'N/A')}")
            except Exception as e:
                results.append({'status': 'error', 'error': str(e), 'data': item})
                log.error(f"Failed to sync item: {e}")

        return results

    @retry(stop=stop_after_attempt(3), wait=wait_exponential(multiplier=1, min=4, max=10))
    async def call_external_api(self, url: str, params: Dict = None) -> Dict[str, Any]:
        """Call external third-party API"""
        headers = {
            'User-Agent': settings.scraping_user_agent,
        }

        if settings.external_api_key:
            headers['X-API-Key'] = settings.external_api_key

        async with httpx.AsyncClient(timeout=self.timeout) as client:
            response = await client.get(url, headers=headers, params=params)
            response.raise_for_status()
            return response.json()

    async def fetch_external_data(self) -> List[Dict]:
        """Fetch data from external API"""
        try:
            # Example: Fetch weather data, stock prices, etc.
            # url = 'https://api.example.com/data'
            # result = await self.call_external_api(url)
            result = {'data': []}  # Placeholder

            log.info(f"Fetched {len(result.get('data', []))} items from external API")
            return result.get('data', [])

        except Exception as e:
            log.error(f"Failed to fetch external data: {e}")
            return []

    async def run(self):
        """Run API integration batch"""
        log.info("Starting API integration batch")

        # Fetch from Laravel API
        users = await self.fetch_users()

        # Fetch from external API
        external_data = await self.fetch_external_data()

        # Sync data back to Laravel
        if external_data:
            sync_results = await self.sync_data_to_laravel(external_data)
            log.info(f"Synced {len(sync_results)} items")

        log.info("API integration batch completed")
        return {'users': len(users), 'external_data': len(external_data)}


def main():
    """Main entry point"""
    batch = ApiBatch()
    asyncio.run(batch.run())


if __name__ == '__main__':
    main()
