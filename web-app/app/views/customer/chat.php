<?php
$title = 'Chat';
require 'app/views/layouts/header.php';
require_once 'app/config/config.php';
?>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-auth-compat.js"></script>

<div class="chat-list-page">
    <div class="chat-header-section">
        <h1>Messages</h1>
        <button class="btn btn-primary" onclick="openVendorSelectionModal()">
            💬 Start New Chat
        </button>
    </div>
    
    <?php if (!empty($conversations)): ?>
        <div class="conversations-list">
            <?php 
            require_once 'app/services/UserService.php';
            foreach ($conversations as $conv): 
                $otherUser = UserService::getUser($conv['otherUserId']);
                $latestMessage = $conv['latestMessage'];
            ?>
                <div class="conversation-item" onclick="loadChatFromConversation('<?php echo $conv['otherUserId']; ?>', '<?php echo htmlspecialchars($otherUser->fullName ?? 'User'); ?>')">
                    <div class="conversation-avatar">
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
                    <div class="conversation-info">
                        <div class="conversation-header">
                            <h3><?php echo htmlspecialchars($otherUser->fullName ?? 'User'); ?></h3>
                            <span class="conversation-time">
                                <?php echo $latestMessage && $latestMessage->createdAt ? date('M j, g:i A', $latestMessage->createdAt) : ''; ?>
                            </span>
                        </div>
                        <p class="conversation-preview">
                            <?php echo $latestMessage ? htmlspecialchars(substr($latestMessage->content, 0, 60)) . (strlen($latestMessage->content) > 60 ? '...' : '') : 'No messages'; ?>
                        </p>
                    </div>
                    <?php if ($conv['unreadCount'] > 0): ?>
                        <span class="unread-badge"><?php echo $conv['unreadCount']; ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p class="empty-icon">💬</p>
            <h3>No Conversations Yet</h3>
            <p class="text-secondary">Start chatting with artisans about your bookings</p>
            <button class="btn btn-primary" onclick="openVendorSelectionModal()" style="margin-top: var(--spacing-lg);">
                Start New Chat
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Vendor Selection Modal -->
<div id="vendorSelectionModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Select an Artisan to Chat With</h2>
            <span class="close" onclick="closeVendorSelectionModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="vendor-search-box">
                <input type="text" id="vendorSearchInput" class="form-control" placeholder="Search artisans by name, location, or skills..." onkeyup="filterVendors()">
            </div>
            <div class="vendors-list" id="vendorsList">
                <?php if (!empty($vendors)): ?>
                    <?php foreach ($vendors as $vendor): ?>
                        <div class="vendor-select-item" data-vendor-id="<?php echo htmlspecialchars($vendor->userId ?? $vendor->id); ?>" onclick="selectVendor('<?php echo htmlspecialchars($vendor->userId ?? $vendor->id); ?>', '<?php echo htmlspecialchars($vendor->businessName ?? 'Artisan'); ?>')">
                            <div class="vendor-select-avatar">
                                <?php 
                                $initials = '';
                                if (isset($vendor->businessName) && $vendor->businessName) {
                                    $nameParts = explode(' ', $vendor->businessName);
                                    $initials = strtoupper(($nameParts[0][0] ?? '') . ($nameParts[1][0] ?? $nameParts[0][1] ?? ''));
                                } else {
                                    $initials = 'VA';
                                }
                                echo htmlspecialchars($initials);
                                ?>
                            </div>
                            <div class="vendor-select-info">
                                <h4><?php echo htmlspecialchars($vendor->businessName ?? 'Artisan'); ?></h4>
                                <?php if ($vendor->location): ?>
                                    <p class="vendor-select-location">📍 <?php echo htmlspecialchars($vendor->location); ?></p>
                                <?php endif; ?>
                                <?php if ($vendor->rating): ?>
                                    <p class="vendor-select-rating">⭐ <?php echo number_format($vendor->rating, 1); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-vendors">
                        <p>No artisans available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function openVendorSelectionModal() {
    document.getElementById('vendorSelectionModal').style.display = 'block';
    document.getElementById('vendorSearchInput').focus();

}

function closeVendorSelectionModal() {
    document.getElementById('vendorSelectionModal').style.display = 'none';
    document.getElementById('vendorSearchInput').value = '';
    filterVendors(); // Reset filter
}

function filterVendors() {
    const input = document.getElementById('vendorSearchInput');
    const filter = input.value.toLowerCase();
    const vendorItems = document.querySelectorAll('.vendor-select-item');
    
    vendorItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(filter)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function selectVendor(vendorId, vendorName) {
    // Close modal
    closeVendorSelectionModal();
    
    // Load chat interface with selected vendor
    loadChatInterface(vendorId, vendorName);
}

function loadChatInterface(vendorId, vendorName) {
    // Hide conversation list
    const chatListPage = document.querySelector('.chat-list-page');
    const conversationsList = document.querySelector('.conversations-list');
    const emptyState = document.querySelector('.empty-state');
    
    if (conversationsList) conversationsList.style.display = 'none';
    if (emptyState) emptyState.style.display = 'none';
    
    // Create or show chat interface
    let chatInterface = document.getElementById('chatInterface');
    if (!chatInterface) {
        chatInterface = document.createElement('div');
        chatInterface.id = 'chatInterface';
        chatInterface.className = 'chat-interface-container';
        chatListPage.appendChild(chatInterface);
    }
    
    // Get vendor info for display
    const vendorItem = document.querySelector(`[data-vendor-id="${vendorId}"]`);
    const vendorLocation = vendorItem ? vendorItem.querySelector('.vendor-select-location')?.textContent : '';
    const vendorRating = vendorItem ? vendorItem.querySelector('.vendor-select-rating')?.textContent : '';
    
    // Get initials
    const nameParts = vendorName.split(' ');
    const initials = (nameParts[0][0] || '') + (nameParts[1] ? nameParts[1][0] : nameParts[0][1] || '');
    
    // Render chat interface
    chatInterface.innerHTML = `
        <div class="chat-interface-header">
            <button class="btn btn-outline btn-sm" onclick="closeChatInterface()" style="margin-right: var(--spacing-md);">← Back</button>
            <div class="chat-user-info">
                <div class="user-avatar-small">${initials.toUpperCase()}</div>
                <div>
                    <h3>${escapeHtml(vendorName)}</h3>
                    <p class="user-status">Online</p>
                </div>
            </div>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="loading-messages"><p>Loading messages...</p></div>
        </div>
        <div class="chat-input-container">
            <form id="messageForm" onsubmit="sendChatMessage(event, '${vendorId}')">
                <div class="chat-input-group">
                    <input type="text" id="messageInput" name="content" class="chat-input" placeholder="Type your message..." required>
                    <button type="submit" class="btn btn-primary send-btn" id="sendBtn">Send</button>
                </div>
            </form>
        </div>
    `;
    
    chatInterface.style.display = 'block';
    
    // Initialize Firebase chat
    initFirebaseChat(vendorId);
}

function closeChatInterface() {
    const chatInterface = document.getElementById('chatInterface');
    if (chatInterface) {
        chatInterface.style.display = 'none';
        chatInterface.innerHTML = '';
    }
    
    // Show conversation list again
    const conversationsList = document.querySelector('.conversations-list');
    const emptyState = document.querySelector('.empty-state');
    if (conversationsList) conversationsList.style.display = 'flex';
    if (emptyState) emptyState.style.display = 'block';
    
    // Stop Firebase listeners
    if (window.messageListener) {
        window.messageListener();
        window.messageListener = null;
    }
}

function loadChatFromConversation(userId, userName) {
    loadChatInterface(userId, userName);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('vendorSelectionModal');
    if (event.target == modal) {
        closeVendorSelectionModal();
    }
}

// Initialize Firebase when page loads
let firebaseInitialized = false;
let db = null;

function initFirebase() {
    if (firebaseInitialized) return;
    
    // Check if Firebase is available (should be loaded in header)
    if (typeof firebase === 'undefined') {
        console.error('Firebase SDK not loaded');
        return;
    }
    
    initializeFirebaseApp();
}

function initializeFirebaseApp() {
    const firebaseConfig = {
        apiKey: '<?php echo FIREBASE_API_KEY; ?>',
        authDomain: '<?php echo FIREBASE_AUTH_DOMAIN; ?>',
        projectId: '<?php echo FIREBASE_PROJECT_ID; ?>',
        storageBucket: '<?php echo FIREBASE_STORAGE_BUCKET; ?>',
        messagingSenderId: '<?php echo FIREBASE_MESSAGING_SENDER_ID; ?>',
        appId: '<?php echo FIREBASE_APP_ID; ?>'
    };
    
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }
    
    db = firebase.firestore();
    firebaseInitialized = true;
}

function initFirebaseChat(receiverId) {
    if (!firebaseInitialized) {
        initFirebase();
        // Wait for Firebase to initialize
        setTimeout(() => initFirebaseChat(receiverId), 500);
        return;
    }
    
    const currentUserId = '<?php echo $user->id; ?>';
    const conversationId = [currentUserId, receiverId].sort().join('_');
    const messagesContainer = document.getElementById('chatMessages');
    let messagesLoaded = false;
    
    // Query messages for this conversation
    const messagesQuery = db.collection('messages')
        .where('conversationId', '==', conversationId)
        .orderBy('createdAt', 'asc');
    
    // Set up real-time listener
    window.messageListener = messagesQuery.onSnapshot((snapshot) => {
        if (!messagesLoaded) {
            messagesContainer.innerHTML = '';
            messagesLoaded = true;
        }
        
        // Clear existing messages
        const existingMessages = messagesContainer.querySelectorAll('.message');
        existingMessages.forEach(msg => msg.remove());
        
        if (snapshot.empty) {
            messagesContainer.innerHTML = '<div class="empty-chat"><p>No messages yet. Start the conversation!</p></div>';
            return;
        }
        
        // Add all messages
        snapshot.forEach((doc) => {
            const data = doc.data();
            addMessageToUI(data.content, data.senderId, data.createdAt?.toDate() || new Date(), currentUserId);
        });
        
        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Mark messages as read
        markMessagesAsRead(conversationId, currentUserId);
    }, (error) => {
        console.error('Error listening to messages:', error);
        messagesContainer.innerHTML = '<div class="error-message"><p>Error loading messages. Please refresh the page.</p></div>';
    });
}

function addMessageToUI(content, senderId, timestamp, currentUserId) {
    const messagesContainer = document.getElementById('chatMessages');
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

async function sendChatMessage(event, receiverId) {
    event.preventDefault();
    
    if (!firebaseInitialized) {
        initFirebase();
        setTimeout(() => sendChatMessage(event, receiverId), 500);
        return;
    }
    
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!content) return;
    
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;
    sendBtn.textContent = 'Sending...';
    
    try {
        const currentUserId = '<?php echo $user->id; ?>';
        const conversationId = [currentUserId, receiverId].sort().join('_');
        
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

async function markMessagesAsRead(conversationId, currentUserId) {
    if (!db) return;
    
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

// Auto-open modal and select vendor if vendor parameter is present
<?php if ($selectedVendorId): ?>
window.addEventListener('load', function() {
    // Find the vendor in the list
    const vendorItem = document.querySelector('[data-vendor-id="<?php echo htmlspecialchars($selectedVendorId); ?>"]');
    if (vendorItem) {
        const vendorName = vendorItem.querySelector('h4').textContent;
        // Auto-select the vendor and load chat
        setTimeout(() => {
            loadChatInterface('<?php echo htmlspecialchars($selectedVendorId); ?>', vendorName);
        }, 300);
    } else {
        // If vendor not found, open modal
        openVendorSelectionModal();
    }
});
<?php endif; ?>

// Initialize Firebase on page load
window.addEventListener('load', function() {
    initFirebase();
});
</script>

<style>
.chat-list-page {
    max-width: 100%;
}

.chat-header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.conversations-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-lg);
}

.conversation-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-lg);
    background-color: var(--surface);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: var(--shadow-sm);
}

.conversation-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.conversation-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-xs);
}

.conversation-header h3 {
    margin: 0;
    font-size: 1.125rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-time {
    font-size: 0.875rem;
    color: var(--text-secondary);
    white-space: nowrap;
    margin-left: var(--spacing-sm);
}

.conversation-preview {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9375rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 var(--spacing-xs);
    background-color: var(--primary);
    color: white;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
}

.empty-state {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
    text-align: center;
    box-shadow: var(--shadow-sm);
    margin-top: var(--spacing-xl);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-md);
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 var(--spacing-sm) 0;
}

/* Chat Interface Styles */
.chat-interface-container {
    display: none;
    flex-direction: column;
    height: calc(100vh - 250px);
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-top: var(--spacing-lg);
}

.chat-interface-header {
    display: flex;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border);
    background-color: white;
}

.chat-user-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    flex: 1;
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

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
}

.modal-content {
    background-color: var(--surface);
    margin: 5% auto;
    padding: 0;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 600px;
    box-shadow: var(--shadow-lg);
}

.modal-large {
    max-width: 700px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border);
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.close {
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    color: var(--text-secondary);
    line-height: 1;
}

.close:hover {
    color: var(--text);
}

.modal-body {
    padding: var(--spacing-lg);
    max-height: 70vh;
    overflow-y: auto;
}

.vendor-search-box {
    margin-bottom: var(--spacing-lg);
}

.vendors-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.vendor-select-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    background-color: white;
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
}

.vendor-select-item:hover {
    border-color: var(--primary);
    background-color: rgba(37, 99, 235, 0.05);
    transform: translateX(4px);
}

.vendor-select-avatar {
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

.vendor-select-info {
    flex: 1;
}

.vendor-select-info h4 {
    margin: 0 0 var(--spacing-xs) 0;
    font-size: 1rem;
}

.vendor-select-location,
.vendor-select-rating {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.empty-vendors {
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--text-secondary);
}

@media (max-width: 768px) {
    .chat-header-section {
        flex-direction: column;
        align-items: stretch;
        gap: var(--spacing-md);
    }
    
    .chat-header-section .btn {
        width: 100%;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>
