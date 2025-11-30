<?php
require_once 'app/services/FirebaseService.php';

/**
 * Message Service
 * Handles messaging between customers and vendors
 */
class MessageService {
    private static $collection = 'messages';

    /**
     * Create a message
     */
    public static function createMessage($messageId, $messageData) {
        FirebaseService::init();
        
        // Generate conversationId for easier querying
        $senderId = $messageData['senderId'] ?? '';
        $receiverId = $messageData['receiverId'] ?? '';
        if ($senderId && $receiverId) {
            $userIds = [$senderId, $receiverId];
            sort($userIds);
            $messageData['conversationId'] = implode('_', $userIds);
        }
        
        $messageData['createdAt'] = time();
        $messageData['updatedAt'] = time();
        
        $result = FirebaseService::createDocument(self::$collection, $messageId, $messageData);
        
        if ($result['code'] === 200 || $result['code'] === 201) {
            return self::getMessage($messageId);
        }
        
        return null;
    }

    /**
     * Get message by ID
     */
    public static function getMessage($messageId) {
        FirebaseService::init();
        $result = FirebaseService::getDocument(self::$collection, $messageId);
        
        if ($result['code'] === 200 && isset($result['data'])) {
            $data = FirebaseService::convertFromFirestoreFormat($result['data']);
            return Message::fromArray($messageId, $data);
        }
        
        return null;
    }

    /**
     * Get conversation between two users
     */
    public static function getConversation($userId1, $userId2) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $messages = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = FirebaseService::convertFromFirestoreFormat($doc);
                
                // Check if message is between these two users
                $senderId = $data['senderId'] ?? '';
                $receiverId = $data['receiverId'] ?? '';
                
                if (($senderId === $userId1 && $receiverId === $userId2) || 
                    ($senderId === $userId2 && $receiverId === $userId1)) {
                    $messages[] = Message::fromArray($id, $data);
                }
            }
        }
        
        // Sort by creation time
        usort($messages, function($a, $b) {
            return ($a->createdAt ?? 0) <=> ($b->createdAt ?? 0);
        });
        
        return $messages;
    }

    /**
     * Get all conversations for a user
     */
    public static function getUserConversations($userId) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $conversations = [];
        $seenUsers = [];
        
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = FirebaseService::convertFromFirestoreFormat($doc);
                
                $senderId = $data['senderId'] ?? '';
                $receiverId = $data['receiverId'] ?? '';
                
                // Find the other user in the conversation
                $otherUserId = null;
                if ($senderId === $userId) {
                    $otherUserId = $receiverId;
                } elseif ($receiverId === $userId) {
                    $otherUserId = $senderId;
                }
                
                if ($otherUserId && !in_array($otherUserId, $seenUsers)) {
                    $seenUsers[] = $otherUserId;
                    $message = Message::fromArray($id, $data);
                    
                    // Get the latest message in this conversation
                    $conversationMessages = self::getConversation($userId, $otherUserId);
                    $latestMessage = end($conversationMessages);
                    
                    $conversations[] = [
                        'otherUserId' => $otherUserId,
                        'latestMessage' => $latestMessage,
                        'unreadCount' => 0 // TODO: Implement unread count
                    ];
                }
            }
        }
        
        // Sort by latest message time
        usort($conversations, function($a, $b) {
            $timeA = $a['latestMessage']->createdAt ?? 0;
            $timeB = $b['latestMessage']->createdAt ?? 0;
            return $timeB <=> $timeA;
        });
        
        return $conversations;
    }

    /**
     * Mark messages as read
     */
    public static function markAsRead($userId, $otherUserId) {
        FirebaseService::init();
        $messages = self::getConversation($userId, $otherUserId);
        
        foreach ($messages as $message) {
            if ($message->receiverId === $userId && !$message->isRead) {
                // Get existing message data and merge
                $existingData = $message->toArray();
                $updateData = array_merge($existingData, [
                    'isRead' => true,
                    'updatedAt' => date('c', time())
                ]);
                
                FirebaseService::updateDocument(self::$collection, $message->id, $updateData);
            }
        }
    }
}

