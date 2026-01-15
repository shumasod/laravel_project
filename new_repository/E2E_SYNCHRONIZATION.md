# E2Eテスト同期処理システム

このドキュメントでは、E2Eテストシステムに実装された同期処理機能について説明します。

## 概要

同期処理システムは、テストの実行、レポート生成、リソース管理を適切に同期化し、競合状態やデータの不整合を防ぐために実装されています。

## 主要コンポーネント

### 1. FileLock クラス

**場所:** `app/Support/FileLock.php`

ファイルベースのロック機構を提供し、複数のプロセスが同時に同じリソースにアクセスすることを防ぎます。

**機能:**
- 排他的ロックの取得と解放
- タイムアウト付きロック待機
- ロック情報の記録（PID、タイムスタンプ、ホスト名）
- 自動ロック解放（デストラクタ）

**使用例:**

```php
use App\Support\FileLock;

// 基本的な使用
$lock = new FileLock(storage_path('locks/my-task.lock'));
if ($lock->acquire(30)) {
    // クリティカルセクション
    // ...
    $lock->release();
}

// コールバックでの使用
FileLock::executeWithLock('test-execution', function () {
    // テスト実行
    return runTests();
}, 60);

// ロック解放を待つ
if (FileLock::waitForRelease('test-execution', 300)) {
    // ロックが解放された後の処理
}
```

### 2. TestExecutionManager クラス

**場所:** `app/Support/TestExecutionManager.php`

テスト実行の同期管理を行い、並列実行時の制御を提供します。

**機能:**
- 同期的なテスト実行
- 並列テスト実行（最大同時実行数の制御）
- プロセス管理と待機
- 実行サマリーの生成

**使用例:**

```php
use App\Support\TestExecutionManager;

$manager = new TestExecutionManager();

// 同期実行
$results = $manager->executeSync([
    'Unit Tests' => 'vendor/bin/phpunit --testsuite Unit',
    'Feature Tests' => 'vendor/bin/phpunit --testsuite Feature',
]);

// 並列実行（最大4並列）
$results = $manager->executeParallel([
    'Test 1' => 'command1',
    'Test 2' => 'command2',
    'Test 3' => 'command3',
], 4);

// 完了待機
$manager->waitForCompletion(600);

// サマリー取得
$summary = $manager->getSummary();
```

### 3. 同期テスト実行スクリプト

**場所:** `scripts/run-e2e-tests-sync.sh`

シェルスクリプトレベルでの同期処理を提供します。

**機能:**
- ファイルロックによる排他制御
- 古いロックの検出と削除
- タイムアウト付き待機
- プロセスIDトラッキング

**使用例:**

```bash
# 基本実行（同期）
./scripts/run-e2e-tests-sync.sh --all --sync

# 並列実行
./scripts/run-e2e-tests-sync.sh --all --parallel

# 待機モード（他のテストが完了するまで待つ）
./scripts/run-e2e-tests-sync.sh --all --wait --timeout 600

# ブラウザテストのみ（同期）
./scripts/run-e2e-tests-sync.sh --browser --sync
```

### 4. レポート生成の同期化

**場所:** `app/Console/Commands/GenerateTestReport.php`

レポート生成時の同期処理を実装しています。

**機能:**
- レポート生成の排他制御
- テスト完了待機
- アトミックなファイル書き込み
- リトライ機能

**使用例:**

```bash
# テスト完了を待ってからレポート生成
php artisan test:generate-report --wait --timeout=300

# 通常のレポート生成（ロック付き）
php artisan test:generate-report --format=html
```

### 5. Dusk テストケースの同期メソッド

**場所:** `tests/DuskTestCase.php`

ブラウザテストでの同期待機処理を提供します。

**機能:**
- ページロード待機
- AJAX完了待機
- 要素の準備待機
- ネットワークアイドル待機
- リトライ機構（指数バックオフ）

**使用例:**

```php
public function test_example()
{
    $this->browse(function (Browser $browser) {
        // 同期的なページ訪問
        $this->visitSync($browser, '/');

        // 要素の準備を待つ
        $this->waitForElementReady($browser, '#submit-button');

        // 同期的なクリック
        $this->clickSync($browser, '#submit-button');

        // AJAXの完了を待つ
        $this->waitForAjax($browser);

        // ネットワークアイドルを待つ
        $this->waitForNetworkIdle($browser);

        // リトライ付き実行
        $result = $this->retryWithBackoff($browser, function ($b) {
            return $b->text('#result');
        }, 3, 100);
    });
}
```

## 同期処理のパターン

### 1. ロックパターン

```php
// パターン1: 明示的なロック
$lock = new FileLock(storage_path('locks/task.lock'));
try {
    if ($lock->acquire(30)) {
        // クリティカルセクション
    }
} finally {
    $lock->release();
}

// パターン2: コールバックパターン（推奨）
FileLock::executeWithLock('task', function () {
    // クリティカルセクション
}, 30);
```

### 2. 待機パターン

```php
// パターン1: ロック解放を待つ
if (FileLock::waitForRelease('test-execution', 300)) {
    // 続行
}

// パターン2: テストマネージャーで待つ
$manager->waitForCompletion(600);

// パターン3: Duskでページロードを待つ
$this->waitForPageLoad($browser);
```

### 3. リトライパターン

```php
// パターン1: レポート生成でのリトライ
$this->collectTestResultsWithRetry(3);

// パターン2: Duskでのリトライ（指数バックオフ）
$result = $this->retryWithBackoff($browser, function ($b) {
    return $b->element('#dynamic-content');
}, 3, 100);
```

## Artisan コマンド

### test:run-e2e-sync

同期制御付きのテスト実行コマンド

```bash
# 全テストを同期実行
php artisan test:run-e2e-sync --suite=all

# 並列実行（最大4並列）
php artisan test:run-e2e-sync --suite=all --parallel --max-concurrent=4

# レポート生成付き
php artisan test:run-e2e-sync --suite=all --report

# タイムアウト設定
php artisan test:run-e2e-sync --suite=all --timeout=900
```

### test:generate-report（拡張版）

同期機能付きレポート生成

```bash
# テスト完了を待ってレポート生成
php artisan test:generate-report --wait --timeout=300

# 通常のレポート生成
php artisan test:generate-report --format=html
```

## ベストプラクティス

### 1. ロックの使用

- **DO:** 短時間のクリティカルセクションにのみ使用
- **DO:** 適切なタイムアウトを設定
- **DO:** `executeWithLock` を使用して自動解放を保証
- **DON'T:** 長時間のロックは避ける
- **DON'T:** デッドロックを引き起こす入れ子ロックは避ける

### 2. 並列実行

- **DO:** 独立したテストは並列実行
- **DO:** リソースの競合を避ける
- **DO:** 適切な最大同時実行数を設定
- **DON'T:** データベースを変更する複数のテストを同時実行しない

### 3. 待機処理

- **DO:** 明示的な待機条件を使用
- **DO:** 適切なタイムアウトを設定
- **DO:** フレーク防止のためリトライを実装
- **DON'T:** 固定時間の`sleep`や`pause`に依存しない

### 4. Duskテスト

- **DO:** `visitSync` などの同期メソッドを使用
- **DO:** 動的コンテンツには `waitForAjax` を使用
- **DO:** フレーキーなテストには `retryWithBackoff` を使用
- **DON'T:** 任意の`pause`は避ける

## トラブルシューティング

### ロックがリリースされない

```bash
# 古いロックを確認
ls -la storage/locks/

# 古いロックを削除
rm storage/locks/*.lock

# プロセスを確認
ps aux | grep php
```

### テストがタイムアウトする

```bash
# タイムアウトを増やす
./scripts/run-e2e-tests-sync.sh --all --timeout 1200

# または環境変数で設定
export TEST_TIMEOUT=1200
```

### 並列実行時の競合

```bash
# 同期モードで実行
./scripts/run-e2e-tests-sync.sh --all --sync

# または最大並列数を減らす
php artisan test:run-e2e-sync --parallel --max-concurrent=2
```

## パフォーマンス最適化

### 1. ロックの粒度

細かいロックを使用して並列性を向上：

```php
// 悪い例（粗いロック）
FileLock::executeWithLock('all-tests', function () {
    runUnitTests();
    runFeatureTests();
    runBrowserTests();
});

// 良い例（細かいロック）
FileLock::executeWithLock('unit-tests', fn() => runUnitTests());
FileLock::executeWithLock('feature-tests', fn() => runFeatureTests());
FileLock::executeWithLock('browser-tests', fn() => runBrowserTests());
```

### 2. 並列度の調整

```bash
# CPUコア数に基づいて調整
CORES=$(nproc)
php artisan test:run-e2e-sync --parallel --max-concurrent=$CORES
```

### 3. キャッシングの活用

```php
// テスト結果のキャッシング
$results = Cache::remember('test-results', 300, function () {
    return runTests();
});
```

## セキュリティ考慮事項

1. **ロックファイルの権限:** ロックディレクトリは適切な権限を設定
2. **プロセスID検証:** 古いロックを削除する前にプロセスの存在を確認
3. **タイムアウト:** 適切なタイムアウトでリソース枯渇を防ぐ
4. **エラーハンドリング:** 例外時のロック解放を保証

## まとめ

同期処理システムにより以下を実現：

✅ テスト実行の競合防止
✅ リソースの適切な管理
✅ 並列実行の安全性向上
✅ フレーキーテストの削減
✅ レポート生成の信頼性向上

---

**バージョン:** 2.0.0
**作成日:** 2026-01-15
**更新日:** 2026-01-15
