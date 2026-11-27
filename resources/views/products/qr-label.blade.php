<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QRラベル - {{ $product->sku }}</title>
    <style>
        body { margin: 0; padding: 20px; font-family: sans-serif; background: #fff; }
        .label {
            display: inline-block;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 16px 20px;
            text-align: center;
            min-width: 220px;
        }
        .label img { display: block; margin: 0 auto; }
        .label .name { font-size: 14px; font-weight: bold; margin-top: 8px; }
        .label .sku { font-size: 12px; color: #555; margin-top: 4px; font-family: monospace; }
        .no-print { margin-bottom: 20px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 16px;cursor:pointer;">印刷する</button>
        <a href="{{ route('products.show', $product) }}" style="margin-left:12px;">戻る</a>
    </div>
    <div class="label">
        <img src="{{ route('products.qrcode', $product) }}" width="180" height="180" alt="QR">
        <div class="name">{{ $product->name }}</div>
        <div class="sku">{{ $product->sku }}</div>
    </div>
</body>
</html>
