<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>全商品QRコード一覧</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 16px; }
        h1 { font-size: 18px; margin-bottom: 16px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 16px;
        }
        .card {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .card img { width: 120px; height: 120px; }
        .card .name { font-size: 12px; font-weight: bold; margin-top: 6px; word-break: break-all; }
        .card .sku  { font-size: 11px; color: #666; }
        .no-print { margin-bottom: 16px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 8px; }
        }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()" style="padding:8px 16px;cursor:pointer;">印刷</button>
    <a href="{{ route('products.index') }}" style="margin-left:12px;">← 商品一覧に戻る</a>
</div>
<h1>全商品 QRコード一覧</h1>
<div class="grid">
    @foreach($products as $product)
    <div class="card">
        <img src="{{ route('products.qrcode', $product) }}" alt="QR">
        <div class="name">{{ $product->name }}</div>
        <div class="sku">{{ $product->sku }}</div>
    </div>
    @endforeach
</div>
</body>
</html>
