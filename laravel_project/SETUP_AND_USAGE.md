# ホテル予約システム - セットアップと使い方

## 📥 リポジトリの取得

### 初回クローン
```bash
# リポジトリをクローン
git clone https://github.com/shumasod/laravel_project.git
cd laravel_project

# 作業ブランチに切り替え
git checkout claude/review-previous-work-Z5Idg
```

### 既存リポジトリの更新
```bash
# リポジトリのディレクトリに移動
cd laravel_project

# 最新の変更を取得
git pull origin claude/review-previous-work-Z5Idg

# または、すべてのブランチの変更を取得してから切り替え
git fetch origin
git checkout claude/review-previous-work-Z5Idg
git pull
```

### ブランチの確認
```bash
# 現在のブランチを確認
git branch

# リモートのブランチも含めて確認
git branch -a

# ブランチの変更履歴を確認
git log --oneline -10
```

## 🔧 初期セットアップ

### 1. 依存関係のインストール
```bash
cd laravel_project

# Composer依存関係をインストール
composer install

# npm依存関係をインストール（フロントエンド使用時）
npm install
```

### 2. 環境設定
```bash
# .envファイルを作成
cp .env.example .env

# アプリケーションキーを生成
php artisan key:generate

# .envファイルを編集してデータベース設定を行う
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=hotel_booking
# DB_USERNAME=your_username
# DB_PASSWORD=your_password
```

### 3. データベースのセットアップ
```bash
# データベースを作成（MySQLの場合）
mysql -u root -p
CREATE DATABASE hotel_booking;
exit;

# マイグレーションを実行
php artisan migrate

# または、既存データをリセットして再実行
php artisan migrate:fresh
```

### 4. 開発サーバーの起動
```bash
# Laravelサーバーを起動
php artisan serve

# ブラウザで以下にアクセス
# http://localhost:8000
```

## 📋 実装済み機能

### 1. 基本機能
- ✅ 宿泊施設管理（Accommodation）
- ✅ 部屋管理（Room）
- ✅ 顧客管理（Customer）
- ✅ 予約管理（Reservation）
- ✅ 在庫管理（RoomInventory）
- ✅ 動的価格設定（PricingRule）

### 2. 決済機能 🆕
- ✅ 複数の決済方法対応（クレジットカード、銀行振込、現金、デジタルウォレット）
- ✅ 決済処理・トランザクション管理
- ✅ 返金処理（全額・部分返金）
- ✅ 決済キャンセル
- ✅ 決済ステータス追跡

### 3. メール通知機能 🆕
- ✅ 予約確定メール
- ✅ 予約キャンセルメール
- ✅ 支払い確認メール
- ✅ チェックインリマインダー

### 4. レビュー・評価システム 🆕
- ✅ 総合評価 + 5つの詳細評価（清潔さ、サービス、立地、価値、設備）
- ✅ レビューの写真添付
- ✅ 管理者からの返信機能
- ✅ 「役に立った」投票機能
- ✅ レビューの公開/非公開管理

### 5. レポート機能 🆕
- ✅ 予約統計（キャンセル率など）
- ✅ 売上レポート（決済方法別、日別）
- ✅ 占有率レポート
- ✅ レビュー統計
- ✅ 顧客統計（リピーター率など）
- ✅ ダッシュボード総合レポート

## 🧪 テスト用ルート

開発サーバー起動後、以下のURLでテスト可能：

### 基本機能のテスト

#### データベース状態確認
```
GET http://localhost:8000/test-db-status
```

#### サンプルデータ作成
```
GET http://localhost:8000/test-create-sample-data
```
※最初に実行してください。宿泊施設、部屋、顧客、在庫、料金ルールを作成します。

#### 予約作成テスト
```
GET http://localhost:8000/test-create-reservation
```

#### 料金計算テスト
```
GET http://localhost:8000/test-pricing
```

#### 在庫状況テスト
```
GET http://localhost:8000/test-inventory
```

#### ステータス遷移テスト
```
GET http://localhost:8000/test-status-transition
```

### 新機能のテスト 🆕

#### 決済テスト
```
GET http://localhost:8000/test-payment
```
※予約に対する決済を作成・処理します

#### 返金テスト
```
GET http://localhost:8000/test-refund
```
※完了済みの決済に対して返金処理を実行します

#### レビューテスト
```
GET http://localhost:8000/test-review
```
※チェックアウト済みの予約に対してレビューを作成します

#### レポートテスト
```
GET http://localhost:8000/test-report
```
※ダッシュボードレポートを表示します

#### メール通知テスト
```
GET http://localhost:8000/test-notification
```
※実装済みの通知機能一覧を表示します

## 📊 レポート機能の使い方

### ダッシュボード
```
GET http://localhost:8000/reports/dashboard?accommodation_id=1
```

### 予約レポート
```
GET http://localhost:8000/reports/reservations?accommodation_id=1&start_date=2025-01-01&end_date=2025-12-31
```

### 売上レポート
```
GET http://localhost:8000/reports/revenue?accommodation_id=1&start_date=2025-01-01&end_date=2025-12-31
```

### 占有率レポート
```
GET http://localhost:8000/reports/occupancy?accommodation_id=1&start_date=2025-01-01&end_date=2025-12-31
```

### レビューレポート
```
GET http://localhost:8000/reports/reviews?accommodation_id=1
```

### 顧客レポート
```
GET http://localhost:8000/reports/customers?accommodation_id=1
```

### レポートのエクスポート（JSON）
```
GET http://localhost:8000/reports/export?type=dashboard&accommodation_id=1
```

## 🔌 API エンドポイント

### リソースルート（REST API）

#### 宿泊施設
- `GET /accommodations` - 一覧
- `POST /accommodations` - 作成
- `GET /accommodations/{id}` - 詳細
- `PUT /accommodations/{id}` - 更新
- `DELETE /accommodations/{id}` - 削除

#### 部屋
- `GET /rooms` - 一覧
- `POST /rooms` - 作成
- `GET /rooms/{id}` - 詳細
- `PUT /rooms/{id}` - 更新
- `DELETE /rooms/{id}` - 削除

#### 顧客
- `GET /customers` - 一覧
- `POST /customers` - 作成
- `GET /customers/{id}` - 詳細
- `PUT /customers/{id}` - 更新
- `DELETE /customers/{id}` - 削除

#### 予約
- `GET /reservations` - 一覧
- `POST /reservations` - 作成
- `GET /reservations/{id}` - 詳細
- `PUT /reservations/{id}` - 更新
- `DELETE /reservations/{id}` - 削除

#### 決済 🆕
- `GET /payments` - 一覧
- `POST /payments` - 作成
- `GET /payments/{id}` - 詳細
- `POST /payments/{id}/process` - 決済処理
- `POST /payments/{id}/refund` - 返金
- `POST /payments/{id}/cancel` - キャンセル

#### レビュー 🆕
- `GET /reviews` - 一覧
- `POST /reviews` - 作成
- `GET /reviews/{id}` - 詳細
- `PUT /reviews/{id}` - 更新
- `DELETE /reviews/{id}` - 削除
- `POST /reviews/{id}/helpful` - 役立ち投票
- `POST /reviews/{id}/admin-response` - 管理者返信

## 💾 データベース構造

### 新規追加テーブル 🆕

#### payments テーブル
- 決済情報の管理
- 複数の決済方法、ステータス、返金情報を記録

#### reviews テーブル
- レビュー情報の管理
- 総合評価 + 5つの詳細評価
- 写真、管理者返信、投票数を記録

#### review_helpful_votes テーブル
- レビューの「役に立った」投票を記録

## ✉️ メール設定

メール通知機能を使用する場合、`.env`ファイルでメール設定を行ってください：

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hotel-booking.com
MAIL_FROM_NAME="${APP_NAME}"
```

開発環境では、[Mailtrap](https://mailtrap.io/)や`log`ドライバーの使用を推奨します。

## 🛠️ 開発時のヒント

### Gitワークフロー
```bash
# 最新の変更を取得
git pull origin claude/review-previous-work-Z5Idg

# 新しい機能を追加後
git add .
git commit -m "機能の説明"
git push origin claude/review-previous-work-Z5Idg
```

### マイグレーションのリセット
```bash
# データベースを完全にリセット
php artisan migrate:fresh

# シーダーも実行する場合
php artisan migrate:fresh --seed
```

### キャッシュのクリア
```bash
# すべてのキャッシュをクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🚀 本番環境へのデプロイ

1. 環境変数を本番用に設定
2. `APP_DEBUG=false` に設定
3. データベースマイグレーションを実行
4. キャッシュを最適化
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📝 今後の拡張可能性

- 🔐 認証・認可システム（Laravel Sanctum/Passport）
- 💳 実際の決済ゲートウェイ統合（Stripe、PayPal）
- 📱 API認証とトークン管理
- 🖼️ 画像アップロード機能（宿泊施設、レビュー）
- 🌐 多言語対応
- 📅 カレンダーUI
- 🔔 リアルタイム通知（Laravel Echo）
- 📈 より高度な分析・BI機能

## ❓ トラブルシューティング

### マイグレーションエラー
```bash
# マイグレーションをロールバック
php artisan migrate:rollback

# 特定のステップ数だけロールバック
php artisan migrate:rollback --step=3
```

### 依存関係のエラー
```bash
# Composerの依存関係を更新
composer update

# autoloadファイルを再生成
composer dump-autoload
```

### パーミッションエラー
```bash
# storage と bootstrap/cache に書き込み権限を付与
chmod -R 775 storage bootstrap/cache
```

## 📞 サポート

問題が発生した場合は、GitHubのIssuesで報告してください：
https://github.com/shumasod/laravel_project/issues
