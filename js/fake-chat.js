document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendMessage');
    const chatButton = document.querySelector('.chat-button');
    const chatModal = document.getElementById('chatModal');
    
    const autoResponses = [
        "Доброго дня! Чим я можу вам допомогти?",
        "Дякуємо за ваш запит. Наразі всі наші оператори зайняті. Я постараюся вам допомогти.",
        "Це чудове запитання! Дозвольте мені уточнити деякі деталі і я зв'яжуся з вами найближчим часом.",
        "Для отримання більш детальної інформації, будь ласка, вкажіть модель автомобіля, яка вас цікавить.",
        "Ми отримали ваше повідомлення і скоро зв'яжемося з вами за вказаним у профілі номером телефону.",
        "Ви завжди можете зателефонувати нам за номером +380 44 123 4567 для отримання швидкої консультації.",
        "Хочете залишити заявку на тест-драйв цього автомобіля?",
        "Дякуємо за звернення! Ваша заявка успішно зареєстрована.",
        "Вибачте за затримку з відповіддю. Ми цінуємо ваше терпіння!"
    ];

    function addWelcomeMessage() {
        if (!chatMessages.querySelector('.message')) {
            addMessage("Доброго дня! Я віртуальний помічник MyWheels. Чим можу вам допомогти?", false);
        }
    }

    function addMessage(text, isOwn) {
        const messageDiv = document.createElement('div');
        messageDiv.className = isOwn ? 'message own' : 'message';
        
        const timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        messageDiv.innerHTML = `
            <div class="message-content ${isOwn ? 'bg-primary text-white' : 'bg-light'}">
                ${text}
            </div>
            <div class="message-time small text-muted ${isOwn ? 'text-end' : ''}">${timestamp}</div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function getRandomResponse() {
        const randomIndex = Math.floor(Math.random() * autoResponses.length);
        return autoResponses[randomIndex];
    }

    function simulateTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message typing';
        typingDiv.innerHTML = '<div class="message-content bg-light"><div class="typing-dots"><span>.</span><span>.</span><span>.</span></div></div>';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        return typingDiv;
    }
    
    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        addMessage(message, true);
        
        messageInput.value = '';

        const typingDiv = simulateTyping();
        
        const responseDelay = 1000 + Math.random() * 2000;
        
        setTimeout(() => {
            chatMessages.removeChild(typingDiv);
            
            addMessage(getRandomResponse(), false);
        }, responseDelay);
    }

    if (sendButton && messageInput) {
        sendButton.addEventListener('click', sendMessage);
        
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    if (chatModal) {
        chatModal.addEventListener('shown.bs.modal', addWelcomeMessage);
    }
    
    if (!document.getElementById('chat-styles')) {
        const style = document.createElement('style');
        style.id = 'chat-styles';
        style.textContent = `
            .chat-messages {
                height: 300px;
                overflow-y: auto;
                padding: 15px;
                background-color: #f8f9fa;
                border-radius: 5px;
            }
            .message {
                margin-bottom: 15px;
                max-width: 80%;
                clear: both;
            }
            .message.own {
                float: right;
            }
            .message-content {
                padding: 10px 15px;
                border-radius: 18px;
                display: inline-block;
                word-break: break-word;
            }
            .message.own .message-content {
                border-bottom-right-radius: 5px;
            }
            .message:not(.own) .message-content {
                border-bottom-left-radius: 5px;
            }
            .typing-dots {
                display: flex;
                justify-content: center;
                min-width: 30px;
            }
            .typing-dots span {
                animation: dotFade 1.4s infinite;
                opacity: 0;
            }
            .typing-dots span:nth-child(2) {
                animation-delay: 0.2s;
            }
            .typing-dots span:nth-child(3) {
                animation-delay: 0.4s;
            }
            @keyframes dotFade {
                0%, 100% { opacity: 0; }
                50% { opacity: 1; }
            }
            .chat-button {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background-color: #007bff;
                color: white;
                border: none;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                z-index: 1000;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }
            .chat-button i {
                font-size: 24px;
            }
        `;
        document.head.appendChild(style);
    }
}); 