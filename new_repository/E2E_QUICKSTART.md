# E2Eテスト自動化 - クイックスタートガイド

このガイドでは、E2Eテスト自動化システムを5分で始められます。

## 🚀 クイックスタート（3ステップ）

### ステップ 1: セットアップ

```bash
cd new_repository

# 依存関係のインストール（まだの場合）
composer install

# Duskのインストール
chmod +x scripts/install-dusk.sh
./scripts/install-dusk.sh
```

### ステップ 2: テストの自動生成

```bash
# 全ルートに対してテストを自動生成
php artisan test:generate-suite --type=all

# または、特定のテストのみ生成
php artisan test:generate-e2e HomePageTest --url=/ --type=browser
```

### ステップ 3: テストの実行

```bash
# スクリプトを実行可能にする
chmod +x scripts/run-e2e-tests.sh

# すべてのテストを実行
./scripts/run-e2e-tests.sh --all

# ブラウザでレポートを開く
open storage/test-reports/latest.html
```

## 🎯 よく使うコマンド

### テスト生成

```bash
# ブラウザテスト
php artisan test:generate-e2e LoginTest --url=/login --type=browser

# APIテスト
php artisan test:generate-e2e UserApiTest --url=/api/users --type=api

# 全テスト自動生成
php artisan test:generate-suite --type=all
```

### テスト実行

```bash
# 全テスト
./scripts/run-e2e-tests.sh --all

# ブラウザテストのみ
./scripts/run-e2e-tests.sh --browser

# カバレッジ付き
./scripts/run-e2e-tests.sh --all --coverage

# 並列実行（高速）
./scripts/run-e2e-tests.sh --all --parallel
```

### レポート生成

```bash
# HTMLレポート
php artisan test:generate-report --format=html

# JSONレポート
php artisan test:generate-report --format=json

# Markdownレポート
php artisan test:generate-report --format=markdown
```

## 📊 レポートの確認方法

### 方法1: ブラウザで直接開く

```bash
# Mac
open storage/test-reports/latest.html

# Linux
xdg-open storage/test-reports/latest.html

# Windows
start storage/test-reports/latest.html
```

### 方法2: ローカルサーバーで表示

```bash
php -S localhost:8080 -t storage/test-reports
# ブラウザで http://localhost:8080/latest.html を開く
```

## 🎨 サンプルテストコード

### ブラウザテスト

```php
// tests/Browser/LoginTest.php
public function test_user_can_login()
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->type('email', 'user@example.com')
                ->type('password', 'password')
                ->press('Login')
                ->assertPathIs('/dashboard')
                ->assertSee('Welcome');
    });
}
```

### APIテスト

```php
// tests/Feature/Api/UserApiTest.php
public function test_can_create_user()
{
    $response = $this->postJson('/api/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(201)
             ->assertJson(['success' => true]);
}
```

## 🔧 環境設定

`.env` に追加：

```env
# 必須設定
DUSK_HEADLESS=true
DUSK_NO_SANDBOX=true

# オプション設定
TEST_REPORT_FORMAT=html
E2E_PARALLEL=false
```

## 📁 ディレクトリ構造

```
new_repository/
├── tests/
│   ├── Browser/              # ブラウザテスト
│   │   └── ExampleBrowserTest.php
│   ├── Feature/              # フィーチャーテスト
│   │   ├── Api/              # APIテスト
│   │   └── ExampleTest.php
│   └── Unit/                 # ユニットテスト
├── storage/
│   └── test-reports/         # テストレポート
│       ├── latest.html       # 最新レポート
│       └── coverage/         # カバレッジレポート
├── scripts/
│   ├── install-dusk.sh       # Duskインストール
│   └── run-e2e-tests.sh      # テスト実行
└── .github/
    └── workflows/
        └── e2e-tests.yml     # CI/CD設定
```

## 💡 Tips

### 1. ヘッドレスモードの切り替え

デバッグ時はブラウザを表示：

```bash
# .env
DUSK_HEADLESS=false

# または環境変数で
DUSK_HEADLESS_DISABLED=1 php artisan dusk
```

### 2. 特定のテストのみ実行

```bash
# メソッド名で指定
php artisan dusk --filter test_homepage_loads

# ファイルで指定
php artisan dusk tests/Browser/LoginTest.php
```

### 3. 失敗時のスクリーンショット

```bash
# 失敗時に自動保存される
tests/Browser/screenshots/
tests/Browser/console/
```

### 4. 並列実行で高速化

```bash
./scripts/run-e2e-tests.sh --all --parallel
```

## 🐛 トラブルシューティング

### Chrome Driverエラー

```bash
php artisan dusk:chrome-driver --detect
```

### 権限エラー

```bash
chmod -R 775 storage
chmod +x scripts/*.sh
```

### ポート競合

```bash
# .env
APP_URL=http://localhost:8001
```

## 📚 次のステップ

1. [完全なドキュメント](./E2E_TESTING_README.md) を読む
2. カスタムテストを作成する
3. CI/CDパイプラインに統合する
4. カバレッジレポートを確認する

## 🎓 学習リソース

- [Laravel Dusk公式ドキュメント](https://laravel.com/docs/dusk)
- [PHPUnit公式ドキュメント](https://phpunit.de)
- プロジェクト内の `E2E_TESTING_README.md`

---

質問やサポートが必要な場合は、プロジェクトのIssueトラッカーをご確認ください。
