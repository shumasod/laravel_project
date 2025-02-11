# Laravel 10 プロジェクトセットアップガイド

## 目次
- [インストール手順](#インストール手順)
- [SEO対策](#seo対策)
- [データベースセットアップ](#データベースセットアップ)
- [テンプレート作成](#テンプレート作成)

## インストール手順

### 1. 新規プロジェクト作成
```bash
composer create-project --prefer-dist laravel/laravel new_repository "10.*"
```

### 2. 必要なパッケージのインストール
```bash
# npmパッケージ
npm install
npm run dev

# Vite
npm install vite

# Tailwind CSS
npm install -D tailwindcss postcss autoprefixer

# Laravel UI
composer require laravel/ui
```

## SEO対策

### 1. メタタグの設定
`resources/views/layouts/app.blade.php`に以下のメタタグを追加：

```html
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO基本設定 -->
    <title>@yield('title', 'デフォルトタイトル') | サイト名</title>
    <meta name="description" content="@yield('description', 'デフォルトの説明文')">
    
    <!-- OGP設定 -->
    <meta property="og:title" content="@yield('title', 'デフォルトタイトル')">
    <meta property="og:description" content="@yield('description', 'デフォルトの説明文')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="サイト名">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'デフォルトタイトル')">
    <meta name="twitter:description" content="@yield('description', 'デフォルトの説明文')">
    
    <!-- canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
</head>
```

### 2. ルーティング設定
`routes/web.php`に以下を追加：

```php
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::resource('users', 'UserController');

// サイトマップ生成用ルート
Route::get('sitemap.xml', 'SitemapController@index');
```

## データベースセットアップ

```bash
php artisan migrate
```
## データベースにテストデータ流す場合

```bash
php artisan db:seed
```


## テンプレート作成

### ユーザー一覧ページ (`resources/views/users/index.blade.php`)

```html
@extends('layouts.app')

@section('title', 'ユーザー一覧')
@section('description', 'ユーザー一覧ページです。登録ユーザーの情報を確認できます。')

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">ユーザー一覧</h1>
    
    <!-- 構造化データ -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Table",
        "about": "ユーザー一覧"
    }
    </script>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <!-- テーブルヘッダー -->
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名前</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メール</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">電話番号</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">住所</th>
                </tr>
            </thead>
            <!-- テーブルボディ -->
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->phone }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->address }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ページネーション -->
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
```

## SEO最適化のポイント

1. **メタタグの最適化**
   - タイトルタグ
   - メタディスクリプション
   - OGPタグ
   - Twitter Card

2. **構造化データの実装**
   - JSON-LDフォーマット
   - スキーママークアップ

3. **パフォーマンス最適化**
   - 画像の最適化
   - レスポンシブデザイン
   - ページ読み込み速度の改善

4. **URL構造**
   - SEOフレンドリーなURL
   - 正しいcanonical URL

5. **その他の推奨事項**
   - サイトマップの生成
   - robots.txtの設定
   - 適切なヘッディング構造
   - モバイルフレンドリー対応
