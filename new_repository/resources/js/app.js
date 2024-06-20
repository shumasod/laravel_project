import './bootstrap';

const counterElement = document.getElementById('counter');
const messageElement = document.getElementById('message');
const customMessageInput = document.getElementById('customMessage');
const clickButton = document.getElementById('clickButton');

let counter = 0;
const MAX_COUNT = 100;

clickButton.addEventListener('click', () => {
    counter++;
    counterElement.textContent = counter;

    if (counter >= MAX_COUNT) {
        window.location.href = '/punishment';
        return;
    }

    const customMessage = customMessageInput.value.trim();
    if (customMessage) {
        messageElement.textContent = customMessage;
    } else {
        messageElement.textContent = 'もうやめなさい!!';
    }
    messageElement.style.display = 'block';

    axios.post('/check', {
        count: counter
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
