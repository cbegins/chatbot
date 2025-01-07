<?php
$chatsDir = __DIR__ . '/chats/';

if (!is_dir($chatsDir)) {
    mkdir($chatsDir, 0777, true); // Create the directory if it doesn't exist
}

function generateUniqueChatId() {
    return uniqid(); // Simple way to generate a reasonably unique ID
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
            break;

        case 'send_message':
            if (isset($_GET['chat_id']) && isset($_GET['message'])) {
                $chatId = $_GET['chat_id'];
                $message = date('H:i:s') . ": " . strip_tags($_GET['message']) . "\n";
                $filePath = getChatFilePath($chatId);
                file_put_contents($filePath, $message, FILE_APPEND | LOCK_EX);
            }
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
                deleteChatIfEmpty($chatId); // Check if chat is empty after getting messages
            }
            break;

        case 'leave_chat':
            if (isset($_GET['chat_id'])) {
                $chatId = $_GET['chat_id'];
                deleteChatIfEmpty($chatId);
            }
            break;
    }
}
?>
