"""
File Processing Batch
Processes various file types (images, PDFs, Excel, etc.)
"""
from pathlib import Path
from typing import List, Dict, Any
from PIL import Image
import PyPDF2
import pandas as pd
from ..utils.logger import log
from config.settings import settings


class FileBatch:
    """File processing operations"""

    def __init__(self):
        self.upload_dir = Path(settings.upload_dir)
        self.temp_dir = Path(settings.temp_dir)
        self.temp_dir.mkdir(parents=True, exist_ok=True)

    def process_images(self, image_paths: List[Path]) -> List[Dict[str, Any]]:
        """Process image files"""
        log.info(f"Processing {len(image_paths)} images")
        results = []

        for image_path in image_paths:
            try:
                with Image.open(image_path) as img:
                    # Get image info
                    info = {
                        'path': str(image_path),
                        'format': img.format,
                        'mode': img.mode,
                        'size': img.size,
                        'width': img.width,
                        'height': img.height,
                    }

                    # Resize if needed
                    if img.width > 1920 or img.height > 1080:
                        img.thumbnail((1920, 1080), Image.LANCZOS)
                        resized_path = self.temp_dir / f"resized_{image_path.name}"
                        img.save(resized_path)
                        info['resized_path'] = str(resized_path)

                    # Generate thumbnail
                    img.thumbnail((300, 300), Image.LANCZOS)
                    thumb_path = self.temp_dir / f"thumb_{image_path.name}"
                    img.save(thumb_path)
                    info['thumbnail_path'] = str(thumb_path)

                    results.append({'status': 'success', **info})
                    log.info(f"Processed image: {image_path.name}")

            except Exception as e:
                results.append({'status': 'error', 'path': str(image_path), 'error': str(e)})
                log.error(f"Failed to process image {image_path}: {e}")

        return results

    def process_pdfs(self, pdf_paths: List[Path]) -> List[Dict[str, Any]]:
        """Process PDF files"""
        log.info(f"Processing {len(pdf_paths)} PDFs")
        results = []

        for pdf_path in pdf_paths:
            try:
                with open(pdf_path, 'rb') as file:
                    pdf_reader = PyPDF2.PdfReader(file)

                    # Extract text from all pages
                    text = ''
                    for page in pdf_reader.pages:
                        text += page.extract_text()

                    info = {
                        'path': str(pdf_path),
                        'num_pages': len(pdf_reader.pages),
                        'text_length': len(text),
                        'text_preview': text[:500],  # First 500 chars
                    }

                    # Save extracted text
                    text_path = self.temp_dir / f"{pdf_path.stem}.txt"
                    text_path.write_text(text, encoding='utf-8')
                    info['text_file'] = str(text_path)

                    results.append({'status': 'success', **info})
                    log.info(f"Processed PDF: {pdf_path.name}")

            except Exception as e:
                results.append({'status': 'error', 'path': str(pdf_path), 'error': str(e)})
                log.error(f"Failed to process PDF {pdf_path}: {e}")

        return results

    def process_excel(self, excel_paths: List[Path]) -> List[Dict[str, Any]]:
        """Process Excel files"""
        log.info(f"Processing {len(excel_paths)} Excel files")
        results = []

        for excel_path in excel_paths:
            try:
                # Read Excel file
                df = pd.read_excel(excel_path)

                info = {
                    'path': str(excel_path),
                    'rows': len(df),
                    'columns': len(df.columns),
                    'column_names': list(df.columns),
                    'summary': df.describe().to_dict(),
                }

                # Convert to CSV
                csv_path = self.temp_dir / f"{excel_path.stem}.csv"
                df.to_csv(csv_path, index=False)
                info['csv_file'] = str(csv_path)

                # Convert to JSON
                json_path = self.temp_dir / f"{excel_path.stem}.json"
                df.to_json(json_path, orient='records', indent=2)
                info['json_file'] = str(json_path)

                results.append({'status': 'success', **info})
                log.info(f"Processed Excel: {excel_path.name}")

            except Exception as e:
                results.append({'status': 'error', 'path': str(excel_path), 'error': str(e)})
                log.error(f"Failed to process Excel {excel_path}: {e}")

        return results

    def cleanup_old_files(self, days: int = 7):
        """Cleanup old temporary files"""
        log.info(f"Cleaning up files older than {days} days")

        import time
        cutoff_time = time.time() - (days * 24 * 60 * 60)
        deleted_count = 0

        for file_path in self.temp_dir.rglob('*'):
            if file_path.is_file() and file_path.stat().st_mtime < cutoff_time:
                try:
                    file_path.unlink()
                    deleted_count += 1
                except Exception as e:
                    log.error(f"Failed to delete {file_path}: {e}")

        log.info(f"Deleted {deleted_count} old files")
        return deleted_count

    def run(self):
        """Run file processing batch"""
        log.info("Starting file processing batch")

        # Find files to process
        image_paths = list(self.upload_dir.glob('*.{jpg,jpeg,png,gif}'))
        pdf_paths = list(self.upload_dir.glob('*.pdf'))
        excel_paths = list(self.upload_dir.glob('*.{xlsx,xls}'))

        # Process files
        image_results = self.process_images(image_paths) if image_paths else []
        pdf_results = self.process_pdfs(pdf_paths) if pdf_paths else []
        excel_results = self.process_excel(excel_paths) if excel_paths else []

        # Cleanup old files
        self.cleanup_old_files(days=7)

        log.info("File processing batch completed")
        return {
            'images': len(image_results),
            'pdfs': len(pdf_results),
            'excel': len(excel_results),
        }


def main():
    """Main entry point"""
    batch = FileBatch()
    batch.run()


if __name__ == '__main__':
    main()
