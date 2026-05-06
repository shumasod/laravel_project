# Batch

Laravelアプリケーションとシームレスに連携する、包括的なPythonバッチ処理システムです。

## 機能概要

- 🕷️ **Webスクレイピング** - 非同期処理による高速データ収集
- 📊 **データ分析** - Pandas/Matplotlibによる分析とレポート生成
- 🗄️ **データベース管理** - 定期的なメンテナンスと最適化
- 🔌 **API連携** - Laravel APIおよび外部APIとの統合
- 📁 **ファイル処理** - 画像/PDF/Excelの自動処理
- ⏰ **スケジューラー** - 自動タスク実行

## プロジェクト構造

```
python_batch/
├── src/
│   ├── batch/              # バッチスクリプト
│   │   ├── scraping_batch.py
│   │   ├── analytics_batch.py
│   │   ├── database_batch.py
│   │   ├── api_batch.py
│   │   └── file_batch.py
│   ├── models/             # データモデル
│   ├── services/           # ビジネスロジック
│   ├── utils/              # ユーティリティ
│   │   ├── logger.py
│   │   └── database.py
│   └── scheduler.py        # スケジューラー
├── config/
│   └── settings.py         # 設定管理
├── tests/                  # テストコード
├── logs/                   # ログファイル
├── data/                   # データディレクトリ
├── reports/                # レポート出力
├── main.py                 # メインエントリーポイント
├── requirements.txt        # 依存パッケージ
└── .env                    # 環境変数
```

## セットアップ

### 1. 仮想環境の作成

```bash
cd python_batch
python3 -m venv venv
source venv/bin/activate  # Linux/Mac
# or
venv\Scripts\activate  # Windows
```

### 2. 依存パッケージのインストール

```bash
pip install -r requirements.txt
```

### 3. 環境設定

```bash
cp .env.example .env
# .envファイルを編集して設定を調整
```

### 4. データベース初期化（オプション）

```python
from src.utils.database import init_db
init_db()
```

## 使い方

### CLI コマンド

```bash
# ヘルプ表示
python main.py --help

# 利用可能なバッチ一覧
python main.py list

# システム状態確認
python main.py status
```

### 個別バッチ実行

#### Webスクレイピング

```bash
# URLを直接指定
python main.py scrape --urls https://example.com --urls https://example.org

# ファイルから読み込み
python main.py scrape --file urls.txt
```

#### データ分析

```bash
# 過去30日間のデータを分析
python main.py analytics

# 日数を指定
python main.py analytics --days 60
```

#### データベースメンテナンス

```bash
# すべてのメンテナンスタスクを実行
python main.py database --all

# 最適化のみ
python main.py database --optimize

# クリーンアップのみ
python main.py database --cleanup
```

#### API同期

```bash
python main.py api
```

#### ファイル処理

```bash
python main.py files
```

### スケジューラー実行

```bash
# バックグラウンドで実行
python main.py schedule

# systemdサービスとして実行（推奨）
sudo systemctl start python-batch-scheduler
```

## スケジュール設定

デフォルトのスケジュール（`src/scheduler.py`で変更可能）:

| バッチ | 実行頻度 | 説明 |
|--------|----------|------|
| Scraping | 毎時 | データ収集 |
| Analytics | 毎日3時 | レポート生成 |
| Database | 毎週日曜2時 | メンテナンス |
| API Sync | 30分ごと | API同期 |
| File Processing | 2時間ごと | ファイル処理 |

## 設定

### 環境変数（.env）

```env
# Application
APP_NAME=PythonBatchSystem
APP_ENV=production
DEBUG=false

# Laravel API
LARAVEL_API_URL=http://localhost:8000/api
LARAVEL_API_TOKEN=your-api-token

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

# Logging
LOG_LEVEL=INFO
LOG_FILE=./logs/batch.log
```

## Laravel連携

### API認証設定

1. Laravelでパーソナルアクセストークンを生成:

```php
$token = $user->createToken('python-batch')->plainTextToken;
```

2. `.env`に設定:

```env
LARAVEL_API_TOKEN=your-generated-token
```

### データベース直接アクセス

同じデータベースを参照することで、Laravelのデータに直接アクセス可能：

```python
from src.utils.database import db_session

with db_session() as session:
    result = session.execute("SELECT * FROM users")
```

## 開発

### テスト実行

```bash
# すべてのテストを実行
pytest

# カバレッジ付き
pytest --cov=src --cov-report=html

# 特定のテストファイル
pytest tests/test_scraping.py
```

### コードフォーマット

```bash
# フォーマット
black src/

# リンター
flake8 src/
pylint src/
```

## ログ

ログファイル:
- `logs/batch.log` - すべてのログ
- `logs/error.log` - エラーのみ

ログレベル: DEBUG, INFO, WARNING, ERROR, CRITICAL

## トラブルシューティング

### データベース接続エラー

```bash
# 接続テスト
python -c "from src.utils.database import engine; engine.connect()"
```

### パッケージのインストールエラー

```bash
# pipをアップグレード
pip install --upgrade pip

# 依存関係を再インストール
pip install -r requirements.txt --upgrade
```

### スケジューラーが動作しない

```bash
# ログを確認
tail -f logs/batch.log

# 設定を確認
python main.py status
```

## パフォーマンス最適化

### 並列処理

スクレイピングとAPI呼び出しは自動的に並列処理されます：

```python
# 並列リクエスト数を調整
SCRAPING_CONCURRENT_REQUESTS=10  # .envで設定
```

### データベース接続プール

```python
# settings.pyで調整
engine = create_engine(
    database_url,
    pool_size=10,
    max_overflow=20
)
```

## セキュリティ

- **認証情報**: `.env`ファイルをGitに含めない
- **API トークン**: 定期的にローテーション
- **ログ**: 機密情報をマスク
- **依存関係**: 定期的にアップデート

## ライセンス

MIT License

## サポート

問題や質問は、Issueトラッカーまたはプロジェクト管理者に連絡してください。

---

**バージョン**: 1.0.0
**作成日**: 2026-01-15
**最終更新**: 2026-01-15
