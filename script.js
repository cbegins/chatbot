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
        newChatButton.disabled = true; // Disable button during request
        newChatButton.textContent = 'Creating...';
        fetch('chat_handler.php?action=new_chat')
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
            fetch(`chat_handler.php?action=send_message&chat_id=${currentChatId}&message=${encodeURIComponent(messageText)}`)
                .then(() => {
                    messageInput.value = '';
                    // Messages will be fetched by the polling mechanism
                })
                .finally(() => {
                    sendButton.disabled = false;
                    sendButton.textContent = 'Send';
                });
        }
    });

    function fetchMessages() {
        if (currentChatId) {
            fetch(`chat_handler.php?action=get_messages&chat_id=${currentChatId}`)
                .then(response => response.json())
                .then(messages => {
                    messagesDiv.innerHTML = '';
                    messages.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message');
                        messageDiv.textContent = message;
                        messagesDiv.appendChild(messageDiv);
                    });
                    messagesDiv.scrollTop = messagesDiv.scrollHeight; // Scroll to bottom
                });
        }
    }

    function startMessagePolling() {
        fetchMessages(); // Initial fetch
        messageCheckInterval = setInterval(fetchMessages, 2000); // Poll every 2 seconds
    }

    function stopMessagePolling() {
        clearInterval(messageCheckInterval);
    }

    leaveChatButton.addEventListener('click', () => {
        if (currentChatId) {
            stopMessagePolling();
            fetch(`chat_handler.php?action=leave_chat&chat_id=${currentChatId}`);
            currentChatId = null;
            chatArea.style.display = 'none';
            joinChatForm.style.display = 'block';
            joinChatIdInput.value = '';
            messagesDiv.innerHTML = '';
        }
    });

    // Initial state: show 'Create New Chat' and the 'Join Chat' form
    newChatButton.style.display = 'block';
    joinChatForm.style.display = 'block';
});
