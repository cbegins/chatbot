<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anonymous Chat - CheckmateBegins</title>
    <style>
        :root {
            --primary-color: #007bff;
            --primary-color-darker: #0056b3;
            --secondary-color: #6c757d;
            --background-color: #f8f9fa;
            --foreground-color: #ffffff;
            --text-color: #343a40;
            --border-color: #dee2e6;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            --transition-duration: 0.3s;
            font-size: 16px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .app-container {
            width: 90%;
            max-width: 960px;
            margin: 20px auto;
            background-color: var(--foreground-color);
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        header {
            background-color: var(--primary-color);
            color: var(--foreground-color);
            text-align: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--primary-color-darker);
        }

        main {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        h1, h2 {
            margin-top: 0;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        #chat-controls, #chat-area {
            margin-bottom: 20px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--foreground-color);
            transition: box-shadow var(--transition-duration) ease-in-out;
        }

        #chat-controls:hover, #chat-area:hover {
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.07);
        }

        #join-chat-form {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        input[type="text"] {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
        }

        button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color var(--transition-duration) ease-in-out, color var(--transition-duration) ease-in-out;
        }

        button.primary {
            background-color: var(--primary-color);
            color: var(--foreground-color);
        }

        button.primary:hover {
            background-color: var(--primary-color-darker);
        }

        button.secondary {
            background-color: var(--secondary-color);
            color: var(--foreground-color);
        }

        button.secondary:hover {
            background-color: #545b62; /* Darken variation */
        }

        #chat-area .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        #messages {
            height: 400px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .message {
            padding: 0.75rem 1rem;
            background-color: var(--foreground-color);
            border-radius: 6px;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.03);
            word-wrap: break-word;
        }

        #message-input-area {
            display: flex;
            gap: 0.5rem;
        }

        #message-input-area input[type="text"] {
            flex-grow: 1;
        }

        footer {
            background-color: var(--text-color);
            color: var(--foreground-color);
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }

        #chat-controls button,
        #chat-area button,
        #message-input-area button {
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease-in-out;
        }

        #chat-controls button:active,
        #chat-area button:active,
        #message-input-area button:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header>
            <h1>Anonymous Chat</h1>
        </header>

        <main>
            <?php
            $chatsDir = __DIR__ . '/chats/';

            if (!is_dir($chatsDir)) {
                mkdir($chatsDir, 0777, true);
            }

            function generateUniqueChatId() {
                return uniqid();
            }

            function getChatFilePath($chatId) {
                global $chatsDir;
                return $chatsDir . $chatId . '.txt';
            }

            function deleteChatIfEmpty($chatId) {
                $filePath = getChatFilePath($chatId);
                if (file_exists($filePath) && filesize($filePath) === 0) {
                    unlink($filePath);
                }
            }

            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'new_chat':
                        $newChatId = generateUniqueChatId();
                        echo $newChatId;
                        exit; // Important to stop further HTML rendering for AJAX responses
                        break;

                    case 'send_message':
                        if (isset($_GET['chat_id']) && isset($_GET['message'])) {
                            $chatId = $_GET['chat_id'];
                            $message = date('H:i:s') . ": " . strip_tags($_GET['message']) . "\n";
                            $filePath = getChatFilePath($chatId);
                            file_put_contents($filePath, $message, FILE_APPEND | LOCK_EX);
                        }
                        exit;
                        break;

                    case 'get_messages':
                        if (isset($_GET['chat_id'])) {
                            $chatId = $_GET['chat_id'];
                            $filePath = getChatFilePath($chatId);
                            $messages = [];
                            if (file_exists($filePath)) {
                                $messages = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            }
                            echo json_encode($messages);
                            deleteChatIfEmpty($chatId);
                        }
                        exit;
                        break;

                    case 'leave_chat':
                        if (isset($_GET['chat_id'])) {
                            $chatId = $_GET['chat_id'];
                            deleteChatIfEmpty($chatId);
                        }
                        exit;
                        break;
                }
            }
            ?>
            <section id="chat-controls">
                <button id="new-chat-button">Start New Chat</button>
                <div id="join-chat-form" style="display: none;">
                    <input type="text" id="join-chat-id" placeholder="Enter Chat ID">
                    <button id="join-chat-button">Join</button>
                </div>
            </section>

            <section id="chat-area" style="display: none;">
                <div class="chat-header">
                    <h2>Chat ID: <span id="current-chat-id"></span></h2>
                    <button id="leave-chat-button" class="secondary">Leave Chat</button>
                </div>
                <div id="messages"></div>
                <div id="message-input-area">
                    <input type="text" id="message" placeholder="Type your message...">
                    <button id="send-button" class="primary">Send</button>
                </div>
            </section>
        </main>

        <footer>
            <p>© 2023 CheckmateBegins. All rights reserved.</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const newChatButton = document.getElementById('new-chat-button');
            const joinChatForm = document.getElementById('join-chat-form');
            const joinChatButton = document.getElementById('join-chat-button');
            const joinChatIdInput = document.getElementById('join-chat-id');
            const chatArea = document.getElementById('chat-area');
            const messagesDiv = document.getElementById('messages');
            const messageInput = document.getElementById('message');
            const sendButton = document.getElementById('send-button');
            const currentChatIdSpan = document.getElementById('current-chat-id');
            const leaveChatButton = document.getElementById('leave-chat-button');

            let currentChatId = null;
            let messageCheckInterval;

            newChatButton.addEventListener('click', () => {
                newChatButton.disabled = true;
                newChatButton.textContent = 'Creating...';
                fetch('index.php?action=new_chat')
                    .then(response => response.text())
                    .then(chatId => {
                        currentChatId = chatId;
                        currentChatIdSpan.textContent = chatId;
                        chatArea.style.display = 'block';
                        joinChatForm.style.display = 'none';
                        startMessagePolling();
                    })
                    .finally(() => {
                        newChatButton.disabled = false;
                        newChatButton.textContent = 'Start New Chat';
                    });
            });

            joinChatButton.addEventListener('click', () => {
                const chatId = joinChatIdInput.value.trim();
                if (chatId) {
                    joinChatButton.disabled = true;
                    joinChatButton.textContent = 'Joining...';
                    currentChatId = chatId;
                    currentChatIdSpan.textContent = chatId;
                    chatArea.style.display = 'block';
                    joinChatForm.style.display = 'none';
                    startMessagePolling();
                    joinChatButton.disabled = false;
                    joinChatButton.textContent = 'Join';
                }
            });

            sendButton.addEventListener('click', () => {
                const messageText = messageInput.value.trim();
                if (messageText && currentChatId) {
                    sendButton.disabled = true;
                    sendButton.textContent = 'Sending...';
                    fetch(`index.php?action=send_message&chat_id=${currentChatId}&message=${encodeURIComponent(messageText)}`)
                        .then(() => {
                            messageInput.value = '';
                        })
                        .finally(() => {
                            sendButton.disabled = false;
                            sendButton.textContent = 'Send';
                        });
                }
            });

            function fetchMessages() {
                if (currentChatId) {
                    fetch(`index.php?action=get_messages&chat_id=${currentChatId}`)
                        .then(response => response.json())
                        .then(messages => {
                            messagesDiv.innerHTML = '';
                            messages.forEach(message => {
                                const messageDiv = document.createElement('div');
                                messageDiv.classList.add('message');
                                messageDiv.textContent = message;
                                messagesDiv.appendChild(messageDiv);
                            });
                            messagesDiv.scrollTop = messagesDiv.scrollHeight;
                        });
                }
            }

            function startMessagePolling() {
                fetchMessages();
                messageCheckInterval = setInterval(fetchMessages, 2000);
            }

            function stopMessagePolling() {
                clearInterval(messageCheckInterval);
            }

            leaveChatButton.addEventListener('click', () => {
                if (currentChatId) {
                    stopMessagePolling();
                    fetch(`index.php?action=leave_chat&chat_id=${currentChatId}`);
                    currentChatId = null;
                    chatArea.style.display = 'none';
                    joinChatForm.style.display = 'block';
                    joinChatIdInput.value = '';
                    messagesDiv.innerHTML = '';
                }
            });

            newChatButton.style.display = 'block';
            joinChatForm.style.display = 'block';
        });
    </script>
</body>
</html>
