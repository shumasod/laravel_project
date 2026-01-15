# E2E テスト自動化システム

このドキュメントでは、Laravel プロジェクトに統合されたE2E（End-to-End）テスト自動化システムの使用方法について説明します。

## 目次

1. [概要](#概要)
2. [セットアップ](#セットアップ)
3. [テストコードの自動生成](#テストコードの自動生成)
4. [テストの実行](#テストの実行)
5. [テスト結果の表示](#テスト結果の表示)
6. [CI/CD統合](#cicd統合)
7. [設定オプション](#設定オプション)

## 概要

このE2Eテスト自動化システムは以下の機能を提供します：

- ✅ テストコードの自動生成
- ✅ ブラウザテスト（Laravel Dusk）
- ✅ APIテスト
- ✅ フィーチャーテスト
- ✅ 美しいHTMLレポート生成
- ✅ GitHub Actions統合
- ✅ 並列テスト実行サポート

## セットアップ

### 1. Laravel Duskのインストール

```bash
# スクリプトを実行可能にする
chmod +x scripts/install-dusk.sh

# Duskをインストール
./scripts/install-dusk.sh
```

または手動でインストール：

```bash
cd new_repository
composer require --dev laravel/dusk
php artisan dusk:install
php artisan dusk:chrome-driver --detect
```

### 2. 環境変数の設定

`.env` ファイルに以下を追加：

```env
# E2E Testing Configuration
DUSK_HEADLESS=true
DUSK_NO_SANDBOX=true
DUSK_DISABLE_GPU=true
DUSK_WINDOW_SIZE=1920,1080

# Test Report Configuration
TEST_REPORT_FORMAT=html
TEST_REPORT_KEEP_HISTORY=true
TEST_REPORT_MAX_REPORTS=10

# Test Execution
E2E_PARALLEL=false
E2E_TIMEOUT=60
E2E_RETRY_FAILED=false
```

### 3. ディレクトリ作成

```bash
mkdir -p storage/test-reports
mkdir -p tests/Browser
mkdir -p tests/Feature/Api
chmod -R 775 storage/test-reports
```

## テストコードの自動生成

### 個別のテストを生成

```bash
# ブラウザテストを生成
php artisan test:generate-e2e HomePageTest --url=/ --type=browser

# APIテストを生成
php artisan test:generate-e2e UserApiTest --url=/api/users --type=api

# フィーチャーテストを生成
php artisan test:generate-e2e LoginFeatureTest --url=/login --type=feature
```

### テストスイート全体を生成

すべてのルートに対してテストを自動生成：

```bash
# すべてのテストを生成
php artisan test:generate-suite --type=all

# ブラウザテストのみ
php artisan test:generate-suite --type=browser

# APIテストのみ
php artisan test:generate-suite --type=api

# 既存のテストを上書き
php artisan test:generate-suite --force
```

## テストの実行

### スクリプトを使用した実行

```bash
# スクリプトを実行可能にする
chmod +x scripts/run-e2e-tests.sh

# すべてのテストを実行
./scripts/run-e2e-tests.sh --all

# ブラウザテストのみ
./scripts/run-e2e-tests.sh --browser

# フィーチャーテストのみ
./scripts/run-e2e-tests.sh --feature

# カバレッジレポート付き
./scripts/run-e2e-tests.sh --all --coverage

# 並列実行
./scripts/run-e2e-tests.sh --all --parallel
```

### 直接実行

```bash
# PHPUnit テスト
vendor/bin/phpunit

# フィーチャーテストのみ
vendor/bin/phpunit --testsuite Feature

# ユニットテストのみ
vendor/bin/phpunit --testsuite Unit

# Dusk ブラウザテスト
php artisan dusk

# 特定のテストファイル
php artisan dusk tests/Browser/ExampleBrowserTest.php

# カバレッジレポート生成
vendor/bin/phpunit --coverage-html storage/test-reports/coverage
```

## テスト結果の表示

### レポート生成

テストレポートは自動的に生成されますが、手動でも生成できます：

```bash
# HTMLレポート生成
php artisan test:generate-report --format=html --output=storage/test-reports/report.html

# JSONレポート生成
php artisan test:generate-report --format=json --output=storage/test-reports/report.json

# Markdownレポート生成
php artisan test:generate-report --format=markdown --output=storage/test-reports/report.md
```

### レポートの確認

生成されたレポートは以下の場所に保存されます：

```
storage/test-reports/
├── test-report-20260114_120000.html  # タイムスタンプ付きレポート
├── latest.html                       # 最新レポートへのシンボリックリンク
├── test-report-20260114_120000.json
└── coverage/                         # カバレッジレポート（オプション）
```

ブラウザでHTMLレポートを開く：

```bash
# ローカルサーバーで表示
php -S localhost:8080 -t storage/test-reports

# ブラウザで開く
open storage/test-reports/latest.html
```

## CI/CD統合

### GitHub Actions

GitHub Actions ワークフローは `.github/workflows/e2e-tests.yml` に設定されています。

**自動実行タイミング：**
- `main`、`develop` ブランチへのプッシュ時
- プルリクエスト作成時
- 手動実行（workflow_dispatch）

**実行内容：**
1. 環境セットアップ
2. 依存関係のインストール
3. データベースマイグレーション
4. ユニットテスト実行
5. フィーチャーテスト実行
6. ブラウザテスト実行
7. レポート生成
8. アーティファクトのアップロード

**手動実行：**

GitHub のリポジトリページから：
1. "Actions" タブを開く
2. "E2E Tests" ワークフローを選択
3. "Run workflow" をクリック

### テスト結果の確認

- GitHub Actions の実行ログで結果を確認
- アーティファクトとしてレポートをダウンロード可能
- プルリクエストにコメントとして結果が投稿される

## 設定オプション

### E2Eテスト設定

設定ファイル: `config/e2e-testing.php`

```php
return [
    'report' => [
        'directory' => storage_path('test-reports'),
        'format' => 'html',
        'keep_history' => true,
        'max_reports' => 10,
    ],

    'test_generation' => [
        'auto_generate' => false,
        'default_type' => 'browser',
        'skip_existing' => true,
    ],

    'execution' => [
        'parallel' => false,
        'timeout' => 60,
        'retry_failed' => false,
        'retry_count' => 3,
    ],

    'browser' => [
        'headless' => true,
        'no_sandbox' => true,
        'disable_gpu' => true,
        'window_size' => '1920,1080',
    ],
];
```

### PHPUnit設定

設定ファイル: `phpunit.xml`

```xml
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

## テストの書き方

### ブラウザテストの例

```php
<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomePageTest extends DuskTestCase
{
    public function test_homepage_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Laravel')
                    ->assertVisible('#app');
        });
    }
}
```

### APIテストの例

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class UserApiTest extends TestCase
{
    public function test_can_get_users()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'email']
                     ]
                 ]);
    }
}
```

## トラブルシューティング

### Chrome Driverの問題

```bash
# Chrome Driverを再インストール
php artisan dusk:chrome-driver --detect

# Chrome Driverのバージョンを確認
./vendor/laravel/dusk/bin/chromedriver-linux --version
```

### 権限の問題

```bash
# ディレクトリに権限を付与
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod +x scripts/*.sh
```

### スクリーンショットの確認

テスト失敗時のスクリーンショットは以下に保存されます：

```
tests/Browser/screenshots/
tests/Browser/console/
```

## ベストプラクティス

1. **テストは独立させる**: 各テストは他のテストに依存しない
2. **明確な命名**: テストメソッド名は何をテストしているか明確にする
3. **適切なアサーション**: 必要最小限のアサーションを使用
4. **データのクリーンアップ**: `RefreshDatabase` トレイトを使用
5. **待機時間の管理**: 必要に応じて `pause()` や `waitFor()` を使用

## リソース

- [Laravel Dusk Documentation](https://laravel.com/docs/dusk)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [GitHub Actions Documentation](https://docs.github.com/actions)

## サポート

問題が発生した場合は、以下を確認してください：

1. エラーログ: `storage/logs/laravel.log`
2. テストログ: `storage/test-reports/`
3. スクリーンショット: `tests/Browser/screenshots/`

---

**システムバージョン:** 1.0.0
**最終更新:** 2026-01-14
