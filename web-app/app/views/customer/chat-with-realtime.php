<?php
$title = 'Chat';
require 'app/views/layouts/header.php';
?>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>

<div class="chat-page">
    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="chat-user-info">
                <div class="user-avatar-small">
                    <?php 
                    $initials = '';
                    if (isset($otherUser->fullName) && $otherUser->fullName) {
                        $nameParts = explode(' ', $otherUser->fullName);
                        $initials = strtoupper(($nameParts[0][0] ?? '') . ($nameParts[1][0] ?? $nameParts[0][1] ?? ''));
                    } else {
                        $initials = 'U';
                    }
                    echo htmlspecialchars($initials);
                    ?>
                </div>
                <div>
                    <h3><?php echo htmlspecialchars($otherUser->fullName ?? 'User'); ?></h3>
                    <p class="user-status" id="userStatus">Connecting...</p>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="loading-messages">
                <p>Loading messages...</p>
            </div>
        </div>

        <!-- Message Input -->
        <div class="chat-input-container">
            <form id="messageForm" onsubmit="sendMessage(event)">
                <input type="hidden" name="receiverId" value="<?php echo htmlspecialchars($otherUser->id); ?>">
                <div class="chat-input-group">
                    <input type="text" id="messageInput" name="content" class="chat-input" placeholder="Type your message..." required>
                    <button type="submit" class="btn btn-primary send-btn" id="sendBtn">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Firebase Configuration
const firebaseConfig = {
    apiKey: '<?php echo FIREBASE_API_KEY; ?>',
    authDomain: '<?php echo FIREBASE_AUTH_DOMAIN; ?>',
    projectId: '<?php echo FIREBASE_PROJECT_ID; ?>',
    storageBucket: '<?php echo FIREBASE_STORAGE_BUCKET; ?>',
    messagingSenderId: '<?php echo FIREBASE_MESSAGING_SENDER_ID; ?>',
    appId: '<?php echo FIREBASE_APP_ID; ?>'
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const db = firebase.firestore();

const currentUserId = '<?php echo $user->id; ?>';
const receiverId = '<?php echo $otherUser->id; ?>';

// Get conversation ID (consistent regardless of order)
function getConversationId(userId1, userId2) {
    return [userId1, userId2].sort().join('_');
}

const conversationId = getConversationId(currentUserId, receiverId);
let messagesLoaded = false;

// Load initial messages and set up real-time listener
function initChat() {
    const messagesContainer = document.getElementById('chatMessages');
    messagesContainer.innerHTML = '<div class="loading-messages"><p>Loading messages...</p></div>';

    // Query messages for this conversation
    const messagesQuery = db.collection('messages')
        .where('conversationId', '==', conversationId)
        .orderBy('createdAt', 'asc');

    // Set up real-time listener
    messagesQuery.onSnapshot((snapshot) => {
        if (!messagesLoaded) {
            messagesContainer.innerHTML = '';
            messagesLoaded = true;
        }

        // Clear existing messages (we'll rebuild from snapshot)
        const existingMessages = messagesContainer.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());

        if (snapshot.empty) {
            messagesContainer.innerHTML = '<div class="empty-chat"><p>No messages yet. Start the conversation!</p></div>';
            return;
        }

        // Add all messages
        snapshot.forEach((doc) => {
            const data = doc.data();
            addMessageToUI(data.content, data.senderId, data.createdAt?.toDate() || new Date(), data.isRead);
        });

        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Mark messages as read
        markMessagesAsRead();
    }, (error) => {
        console.error('Error listening to messages:', error);
        messagesContainer.innerHTML = '<div class="error-message"><p>Error loading messages. Please refresh the page.</p></div>';
    });

    // Update status
    document.getElementById('userStatus').textContent = 'Online';
}

// Send message
async function sendMessage(event) {
    event.preventDefault();
    
    const form = event.target;
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!content) return;
    
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending...';
    
    try {
        const messageData = {
            senderId: currentUserId,
            receiverId: receiverId,
            conversationId: conversationId,
            content: content,
            isRead: false,
            createdAt: firebase.firestore.FieldValue.serverTimestamp(),
            updatedAt: firebase.firestore.FieldValue.serverTimestamp()
        };

        await db.collection('messages').add(messageData);
        
        // Clear input
        input.value = '';
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send';
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please try again.');
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send';
    }
}

// Add message to UI
function addMessageToUI(content, senderId, timestamp, isRead) {
    const messagesContainer = document.getElementById('chatMessages');
    
    // Remove empty state if present
    const emptyState = messagesContainer.querySelector('.empty-chat');
    if (emptyState) {
        emptyState.remove();
    }
    
    const isSent = senderId === currentUserId;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message ' + (isSent ? 'message-sent' : 'message-received');
    
    const date = timestamp instanceof Date ? timestamp : new Date(timestamp);
    const timeString = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    
    messageDiv.innerHTML = `
        <div class="message-content">
            <p>${escapeHtml(content)}</p>
            <span class="message-time">${timeString}</span>
        </div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Mark messages as read
async function markMessagesAsRead() {
    try {
        const unreadMessages = await db.collection('messages')
            .where('conversationId', '==', conversationId)
            .where('receiverId', '==', currentUserId)
            .where('isRead', '==', false)
            .get();

        const batch = db.batch();
        unreadMessages.forEach((doc) => {
            batch.update(doc.ref, {
                isRead: true,
                updatedAt: firebase.firestore.FieldValue.serverTimestamp()
            });
        });

        if (!unreadMessages.empty) {
            await batch.commit();
        }
    } catch (error) {
        console.error('Error marking messages as read:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize chat when page loads
window.addEventListener('load', function() {
    initChat();
});
</script>

<style>
.chat-page {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
    max-width: 100%;
}

.chat-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.chat-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border);
    background-color: white;
}

.chat-user-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.user-avatar-small {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.chat-user-info h3 {
    margin: 0 0 var(--spacing-xs) 0;
    font-size: 1.125rem;
}

.user-status {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: var(--spacing-lg);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
    background-color: #f5f5f5;
}

.loading-messages, .error-message {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--text-secondary);
}

.message {
    display: flex;
    max-width: 70%;
    animation: fadeIn 0.3s;
}

.message-sent {
    align-self: flex-end;
}

.message-received {
    align-self: flex-start;
}

.message-content {
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--radius-md);
    background-color: white;
    box-shadow: var(--shadow-sm);
}

.message-sent .message-content {
    background-color: var(--primary);
    color: white;
}

.message-content p {
    margin: 0 0 var(--spacing-xs) 0;
    word-wrap: break-word;
}

.message-time {
    font-size: 0.75rem;
    opacity: 0.7;
    display: block;
    text-align: right;
}

.message-received .message-time {
    text-align: left;
}

.empty-chat {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--text-secondary);
}

.chat-input-container {
    padding: var(--spacing-lg);
    border-top: 1px solid var(--border);
    background-color: white;
}

.chat-input-group {
    display: flex;
    gap: var(--spacing-sm);
}

.chat-input {
    flex: 1;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    font-size: 1rem;
    outline: none;
    transition: border-color 0.2s;
}

.chat-input:focus {
    border-color: var(--primary);
}

.send-btn {
    padding: var(--spacing-sm) var(--spacing-lg);
    white-space: nowrap;
}

.send-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>

