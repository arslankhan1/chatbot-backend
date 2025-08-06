<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Chatbot Test Interface</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .chatbot-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            height: 700px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .chat-header h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .chat-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            justify-content: flex-end;
        }

        .message.bot {
            justify-content: flex-start;
        }

        .message-content {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
        }

        .message.user .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .message.bot .message-content {
            background: white;
            color: #333;
            border: 1px solid #e9ecef;
        }

        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #e9ecef;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        #messageInput {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        #messageInput:focus {
            border-color: #667eea;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .status {
            text-align: center;
            padding: 10px;
            font-size: 0.9rem;
            color: #666;
            font-style: italic;
        }

        .controls {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="chatbot-container">
        <div class="chat-header">
            <h1>🤖 Service Chatbot</h1>
            <p>Find the perfect service for your needs</p>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="status" id="statusMessage">Click "Start Chat" to begin</div>
        </div>

        <div class="chat-input">
            <div class="controls">
                <button class="btn btn-primary" onclick="startChat()" id="startBtn">Start Chat</button>
                <button class="btn btn-secondary" onclick="restartChat()" id="restartBtn"
                    style="display: none;">Restart</button>
            </div>
            <div class="input-group" id="inputGroup" style="display: none;">
                <input type="text" id="messageInput" placeholder="Type your answer..."
                    onkeypress="handleKeyPress(event)">
                <button class="btn btn-primary" onclick="sendMessage()" id="sendBtn">Send</button>
            </div>
        </div>
    </div>

    <script>
        let sessionId = null;
        let currentQuestionKey = null;
        let isWaitingForResponse = false;
        window.startChat = startChat;
        window.restartChat = restartChat;
        window.sendMessage = sendMessage;
        window.handleKeyPress = handleKeyPress;

        const API_BASE = 'http://localhost:8000/api';

        function addMessage(content, isUser = false) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = content;

            messageDiv.appendChild(contentDiv);
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function setStatus(message, showLoading = false) {
            const statusElement = document.getElementById('statusMessage');
            if (!statusElement) return;

            if (showLoading) {
                statusElement.innerHTML = `<span class="loading"></span> ${message}`;
            } else {
                statusElement.textContent = message;
            }
        }

        function toggleInput(show) {
            const inputGroup = document.getElementById('inputGroup');
            const startBtn = document.getElementById('startBtn');
            const restartBtn = document.getElementById('restartBtn');

            inputGroup.style.display = show ? 'flex' : 'none';

            if (sessionId) {
                startBtn.style.display = 'none';
                restartBtn.style.display = 'inline-block';
            } else {
                startBtn.style.display = 'inline-block';
                restartBtn.style.display = 'none';
            }
        }

        async function startChat() {
            setStatus('Starting new chat session...', true);

            try {
                const response = await fetch(`${API_BASE}/start-session`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    sessionId = data.session_id;
                    // document.getElementById('chatMessages').innerHTML = '';
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.querySelectorAll('.message').forEach(el => el.remove());

                    addMessage('Hello! I\'m here to help you find the right service. Let me ask you a few questions.');
                    await getNextQuestion();
                    toggleInput(true);
                    setStatus('');
                } else {
                    setStatus('Failed to start chat session. Please try again.');
                }
            } catch (error) {
                console.error('Error starting chat:', error);
                setStatus('Error connecting to chatbot. Please check if the server is running.');
            }
        }

        async function getNextQuestion() {
            if (!sessionId) return;

            setStatus('Getting next question...', true);

            try {
                const response = await fetch(`${API_BASE}/next-question?session_id=${sessionId}`);
                const data = await response.json();

                if (data.success) {
                    if (data.data.is_completed) {
                        // Chat completed, show services
                        addMessage(data.data.message);
                        if (data.data.services && data.data.services.length > 0) {
                            const servicesList = data.data.next_question.services.map(service =>
                                `• ${service.name} by ${service.provider_name}` +
                                (service.price ? ` - ${service.price}` : '') +
                                (service.provider_contact ? ` (Contact: ${service.provider_contact})` : '')
                            ).join('\n');

                            addMessage(`Available services:\n${servicesList}`);

                        }
                        setStatus('Chat completed! You can restart to search for other services.');
                        toggleInput(false);
                    } else {
                        // Show next question
                        currentQuestionKey = data.data.question_key;
                        addMessage(data.data.question);
                        setStatus('');
                        document.getElementById('messageInput').focus();
                    }
                } else {
                    setStatus('Error getting next question.');
                }
            } catch (error) {
                console.error('Error getting next question:', error);
                setStatus('Error connecting to chatbot.');
            }
        }

        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message || !sessionId || !currentQuestionKey || isWaitingForResponse) {
                return;
            }

            isWaitingForResponse = true;
            addMessage(message, true);
            input.value = '';

            setStatus('Processing your answer...', true);

            try {
                const response = await fetch(`${API_BASE}/answer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        question_key: currentQuestionKey,
                        answer: message
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Add bot response
                    if (data.data.bot_response) {
                        addMessage(data.data.bot_response);
                    }

                    // Handle next question or completion
                    if (data.data.next_question) {
                        if (data.data.next_question.is_completed) {
                            // Chat completed
                            addMessage(data.data.next_question.message);
                            if (data.data.next_question.services && data.data.next_question.services.length > 0) {
                                const servicesList = data.data.next_question.services.map(service =>
                                    `• ${service.name} by ${service.provider_name}` +
                                    (service.price ? ` - ${service.price}` : '') +
                                    (service.provider_contact ? ` (Contact: ${service.provider_contact})` : '')
                                ).join('\n');

                                addMessage(`Available services:\n${servicesList}`);

                            }
                            setStatus('Chat completed! You can restart to search for other services.');
                            toggleInput(false);
                        } else {
                            // Next question
                            currentQuestionKey = data.data.next_question.question_key;
                            addMessage(data.data.next_question.question);
                            setStatus('');
                            input.focus();
                        }
                    } else {
                        await getNextQuestion();
                    }
                } else {
                    addMessage('Sorry, I had trouble processing your answer. Please try again.');
                    setStatus('');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                addMessage('Sorry, I\'m having trouble connecting right now. Please try again.');
                setStatus('');
            }

            isWaitingForResponse = false;
        }

        async function restartChat() {
            if (!sessionId) {
                await startChat();
                return;
            }

            setStatus('Restarting chat...', true);

            try {
                const response = await fetch(`${API_BASE}/restart-session`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        session_id: sessionId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('chatMessages').innerHTML = '';
                    addMessage('Chat restarted! Let\'s begin again.');
                    await getNextQuestion();
                    toggleInput(true);
                    setStatus('');
                } else {
                    setStatus('Failed to restart chat. Starting new session...');
                    sessionId = null;
                    await startChat();
                }
            } catch (error) {
                console.error('Error restarting chat:', error);
                setStatus('Error restarting chat. Starting new session...');
                sessionId = null;
                await startChat();
            }
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleInput(false);
        });
    </script>
</body>

</html>
