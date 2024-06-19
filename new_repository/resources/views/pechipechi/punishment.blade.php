<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スロークエリ発動</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>スロークエリ発動</h1>
        <p>連打しすぎてシステムに負荷がかかりました。</p>
        <p>処理を続行するにはキャプチャを入力してください。</p>
        <div class="captcha-container">
            <canvas id="captchaCanvas"></canvas>
            <input type="text" id="captchaInput" placeholder="キャプチャを入力">
            <button id="submitButton">送信</button>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
