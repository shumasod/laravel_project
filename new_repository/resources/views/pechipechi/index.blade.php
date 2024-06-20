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

<!-- 中略 -->
<div id="roulette">
    <canvas id="rouletteCanvas" width="300" height="300"></canvas>
    <button id="spinButton" disabled>スピン</button>
</div>
<!-- 中略 -->

<script>
    const spinButton = document.getElementById('spinButton');
    const rouletteCanvas = document.getElementById('rouletteCanvas');
    const ctx = rouletteCanvas.getContext('2d');
    const centerX = rouletteCanvas.width / 2;
    const centerY = rouletteCanvas.height / 2;
    const radius = 120;
    const items = ['お仕置き', '許される', 'もう一回', 'やれやれ'];
    let isSpinning = false;

    function drawRouletteWheel() {
        const startAngle = 0;
        const endAngle = 2 * Math.PI;
        const sliceAngle = (endAngle - startAngle) / items.length;

        for (let i = 0; i < items.length; i++) {
            const sliceStartAngle = startAngle + i * sliceAngle;
            const sliceEndAngle = startAngle + (i + 1) * sliceAngle;

            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, sliceStartAngle, sliceEndAngle);
            ctx.closePath();
            ctx.fillStyle = getColor(items[i]);
            ctx.fill();

            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.rotate((sliceStartAngle + sliceEndAngle) / 2);
            ctx.fillStyle = 'white';
            ctx.font = '16px Arial';
            ctx.fillText(items[i], radius / 2, 10);
            ctx.restore();
        }
    }

    function getColor(item) {
        switch (item) {
            case 'お仕置き':
                return '#ff4d4d';
            case '許される':
                return '#4dff4d';
            case 'もう一回':
                return '#ffff4d';
            case 'やれやれ':
                return '#4d94ff';
            default:
                return '#cccccc';
        }
    }

    function spin() {
        spinButton.disabled = true;
        isSpinning = true;
        const randomDegree = Math.floor(Math.random() * 360);
        const spinDuration = 3000;
        const spinInterval = 10;
        let spinAngle = 0;
        let startTime = null;

        function rotate(timestamp) {
            if (!startTime) {
                startTime = timestamp;
            }
            const elapsed = timestamp - startTime;
            spinAngle = (elapsed / spinDuration) * 360 + randomDegree;
            ctx.clearRect(0, 0, rouletteCanvas.width, rouletteCanvas.height);
            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.rotate(spinAngle * Math.PI / 180);
            ctx.translate(-centerX, -centerY);
            drawRouletteWheel();
            ctx.restore();

            if (elapsed < spinDuration) {
                requestAnimationFrame(rotate);
            } else {
                isSpinning = false;
                const selectedItem = items[Math.floor(items.length * ((spinAngle % 360) / 360))];
                handleResult(selectedItem);
                spinButton.disabled = false;
            }
        }

        requestAnimationFrame(rotate);
    }

    function handleResult(result) {
        switch (result) {
            case 'お仕置き':
                window.location.href = '/punishment';
                break;
            case '許される':
                alert('今回は許される！');
                break;
            case 'もう一回':
                alert('もう一回チャンスがある！');
                break;
            case 'やれやれ':
                alert('やれやれ、もう少し頑張れ！');
                break;
        }
    }

    drawRouletteWheel();
    spinButton.addEventListener('click', spin);
</script>
