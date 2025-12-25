# ホテル予約システム - 設計ドキュメント

## 概要

このドキュメントは、Laravel で実装された包括的なホテル予約システムの設計と実装について説明します。

## 主要機能

### 1. 予約ステータス管理システム

#### ステータスの種類
- **provisional（仮予約）**: 初期状態、支払い前
- **confirmed（確定）**: 支払い完了、予約確定
- **checked_in（チェックイン）**: チェックイン済み
- **checked_out（チェックアウト）**: チェックアウト済み
- **cancelled（キャンセル）**: キャンセル済み
- **no_show（ノーショー）**: 予約日に来なかった

#### ステータス遷移ルール

```
provisional → confirmed | cancelled
confirmed → checked_in | cancelled | no_show
checked_in → checked_out
checked_out → (終了状態)
cancelled → (終了状態)
no_show → (終了状態)
```

#### ステータス履歴管理
- `reservation_status_histories` テーブルで全ての遷移を記録
- 変更者、変更時刻、メモを保持
- 監査証跡として利用可能

#### 権限ごとの操作制限

**管理者（admin/manager）**
- 全ての予約の操作が可能

**スタッフ（staff）**
- 確定済み以降の予約のみ操作可能
- チェックイン・チェックアウト処理を実行

**顧客（customer）**
- 自分の仮予約のみキャンセル可能

### 2. 在庫管理システム

#### 設計
- `room_inventories` テーブルで部屋タイプ・日付ごとに在庫を管理
- ユニーク制約: (accommodation_id, room_type, date)

#### フィールド
- `total_rooms`: 総部屋数
- `available_rooms`: 利用可能な部屋数
- `reserved_rooms`: 予約済み部屋数

#### オーバーブッキング防止
- `DB::transaction()` でトランザクション管理
- `lockForUpdate()` で排他ロック
- 在庫チェック後、予約実行前に再度確認

#### 在庫操作
- **初期化**: `InventoryService::initializeInventory()`
- **予約**: `InventoryService::reserveInventory()`
- **解放**: `InventoryService::releaseInventory()`
- **確認**: `InventoryService::checkAvailability()`

### 3. 動的料金計算システム

#### 料金ルールの種類

**曜日別料金（day_of_week）**
- 週末や特定曜日の料金調整
- 条件: `days` 配列で曜日を指定

**シーズン料金（season）**
- ハイシーズン・ローシーズンの料金調整
- 期間指定: `valid_from` ～ `valid_to`

**人数追加料金（extra_guest）**
- 定員を超える場合の追加料金
- 1人あたりの固定額または割合

**連泊割引（consecutive_nights）**
- 指定泊数以上で割引
- 条件: `min_nights`

**早割（early_bird）**
- 指定日数前の予約で割引
- 条件: `min_days_in_advance`

**直前割（last_minute）**
- 直前予約で割引
- 条件: `max_days_in_advance`

#### 計算方式
- **fixed**: 固定額
- **percentage**: パーセンテージ
- **multiplier**: 乗数

#### 料金計算フロー
1. 日ごとの基本料金を計算
2. 曜日別料金を適用
3. シーズン料金を適用
4. 人数追加料金を適用
5. 連泊割引を適用
6. 早割・直前割を適用

#### ルール追加方法
`pricing_rules` テーブルにレコードを追加するだけで新しいルールを適用可能。

```php
PricingRule::create([
    'accommodation_id' => 1,
    'room_type' => 'standard',
    'rule_type' => 'season',
    'name' => 'ゴールデンウィーク料金',
    'conditions' => [],
    'calculation_type' => 'percentage',
    'value' => 50, // 50%増
    'priority' => 100,
    'is_active' => true,
    'valid_from' => '2025-04-29',
    'valid_to' => '2025-05-06',
]);
```

### 4. チェックイン・チェックアウト管理

#### 実績時刻管理
- `actual_check_in_time`: 実際のチェックイン時刻
- `actual_check_out_time`: 実際のチェックアウト時刻

#### 標準時刻
- チェックイン: 15:00
- チェックアウト: 11:00

#### アーリーチェックイン
- 標準時刻より早い場合に判定
- 追加料金を計算
- 料金ルール `early_check_in` で設定可能

#### レイトチェックアウト
- 標準時刻より遅い場合に判定
- 追加料金を計算
- 一定時間を超えたら1泊分請求
- 料金ルール `late_check_out` で設定可能

#### 使用例

```php
$checkInOutService = new CheckInOutService();

// チェックイン
$result = $checkInOutService->checkIn($reservation, $userId);
// $result: ['success', 'actual_time', 'scheduled_time', 'is_early', 'extra_charge']

// チェックアウト
$result = $checkInOutService->checkOut($reservation, $userId);
// $result: ['success', 'actual_time', 'scheduled_time', 'is_late', 'extra_charge']
```

### 5. 宿泊者情報管理システム

#### 個人情報保護

**暗号化フィールド**
- アレルギー情報
- 食事制限
- 特別なリクエスト

`GuestPreference` モデルで自動的に暗号化・復号化。

**GDPR対応**
- `privacy_consent`: プライバシー同意
- `marketing_consent`: マーケティング同意
- `deletion_requested`: 削除リクエスト
- ソフトデリート対応

#### 宿泊履歴
- `total_stays`: 総宿泊回数
- `total_spent`: 総利用金額
- `last_stay_date`: 最終宿泊日

チェックアウト時に自動更新。

#### 嗜好情報
- 喫煙・禁煙
- ベッドタイプ
- フロア希望（低層・高層）
- 静かな部屋希望
- 連絡方法（メール・電話・SMS）
- 言語設定

## データベース構造

### 新規テーブル

#### reservation_status_histories
- 予約ステータスの履歴管理

#### room_inventories
- 部屋タイプ・日付ごとの在庫管理

#### pricing_rules
- 動的料金計算ルール

#### guest_preferences
- 宿泊者の嗜好情報（暗号化対応）

### 拡張テーブル

#### reservations
- ステータス拡張（6種類）
- 実績時刻フィールド追加
- 料金内訳フィールド追加
- 権限管理フィールド追加

#### customers
- 宿泊履歴フィールド追加
- GDPR対応フィールド追加
- ソフトデリート対応

## サービスクラス

### ReservationService
予約の作成、更新、キャンセル、ステータス管理を統合的に処理。

```php
$reservationService->createReservation($data);
$reservationService->confirmReservation($reservation);
$reservationService->cancelReservation($reservation, $reason);
$reservationService->updateReservation($reservation, $data);
```

### InventoryService
在庫管理と排他制御を担当。

```php
$inventoryService->initializeInventory($accommodationId, $roomType, $startDate, $endDate, $totalRooms);
$inventoryService->checkAvailability($accommodationId, $roomType, $checkIn, $checkOut);
$inventoryService->reserveInventory($accommodationId, $roomType, $checkIn, $checkOut);
$inventoryService->releaseInventory($accommodationId, $roomType, $checkIn, $checkOut);
```

### PricingService
動的料金計算を担当。

```php
$pricing = $pricingService->calculateTotalPrice($room, $checkIn, $checkOut, $numberOfGuests);
// 返り値: ['total_amount', 'base_amount', 'nights', 'breakdown', 'applied_discounts']
```

### CheckInOutService
チェックイン・チェックアウト処理と追加料金計算を担当。

```php
$checkInOutService->checkIn($reservation, $userId);
$checkInOutService->checkOut($reservation, $userId);
```

## セキュリティ考慮事項

1. **排他制御**: トランザクションと行ロックでオーバーブッキングを防止
2. **個人情報暗号化**: Laravel の `encrypt()`/`decrypt()` を使用
3. **権限管理**: ロールベースのアクセス制御
4. **監査証跡**: 全てのステータス変更を記録
5. **GDPR対応**: 削除リクエスト、同意管理、ソフトデリート

## テスト

主要な機能のユニットテストを実装：
- ReservationServiceTest
- InventoryServiceTest
- PricingServiceTest
- ReservationTest

## マイグレーション実行

```bash
php artisan migrate
```

## 使用例

### 1. 予約作成

```php
$reservationService = app(ReservationService::class);

$reservation = $reservationService->createReservation([
    'room_id' => 1,
    'customer_id' => 1,
    'check_in_date' => '2025-12-30',
    'check_out_date' => '2026-01-02',
    'number_of_guests' => 2,
    'user_id' => auth()->id(),
]);
```

### 2. 料金ルール設定

```php
// 週末料金
PricingRule::create([
    'accommodation_id' => 1,
    'rule_type' => 'day_of_week',
    'name' => '週末料金',
    'conditions' => ['days' => ['friday', 'saturday']],
    'calculation_type' => 'percentage',
    'value' => 20,
    'is_active' => true,
]);

// 連泊割引
PricingRule::create([
    'accommodation_id' => 1,
    'rule_type' => 'consecutive_nights',
    'name' => '3泊以上割引',
    'conditions' => ['min_nights' => 3],
    'calculation_type' => 'percentage',
    'value' => 10,
    'is_active' => true,
]);
```

### 3. チェックイン処理

```php
$checkInOutService = app(CheckInOutService::class);

$result = $checkInOutService->checkIn($reservation, auth()->id());

if ($result['is_early'] && $result['extra_charge'] > 0) {
    echo "アーリーチェックイン料金: ¥" . number_format($result['extra_charge']);
}
```

## 今後の拡張可能性

- 予約変更履歴の詳細管理
- レビュー・評価システム
- ポイント・リワードプログラム
- AIベースの動的価格最適化
- 複数通貨対応
- 国際化（i18n）
- モバイルアプリ連携
