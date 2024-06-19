<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ぺちぺち叩かないで</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="container">
        <h1>おかあさん激怒!</h1>
        <img src="{{ asset('img/angry_mom.png') }}" alt="激怒するおかあさん">
        <div id="counter">0</div>
        <div id="message">もうやめなさい!!</div>
        <div>
            <label for="customMessage">カスタムメッセージ:</label>
            <input type="text" id="customMessage" placeholder="メッセージを入力...">
        </div>
        <button id="clickButton">ここを連打!</button>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
