<?php
/**
 * Message Model
 */
class Message {
    public $id;
    public $senderId;
    public $receiverId;
    public $bookingId; // Optional: link to booking
    public $content;
    public $isRead;
    public $createdAt;
    public $updatedAt;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->senderId = $data['senderId'] ?? '';
        $this->receiverId = $data['receiverId'] ?? '';
        $this->bookingId = $data['bookingId'] ?? null;
        $this->content = $data['content'] ?? '';
        $this->isRead = $data['isRead'] ?? false;
        $this->createdAt = $data['createdAt'] ?? null;
        $this->updatedAt = $data['updatedAt'] ?? null;
    }

    /**
     * Convert to array for Firestore
     */
    public function toArray() {
        return [
            'senderId' => $this->senderId,
            'receiverId' => $this->receiverId,
            'bookingId' => $this->bookingId,
            'content' => $this->content,
            'isRead' => $this->isRead,
            'createdAt' => $this->createdAt ? date('c', $this->createdAt) : null,
            'updatedAt' => $this->updatedAt ? date('c', $this->updatedAt) : null,
        ];
    }

    /**
     * Create from Firestore document
     */
    public static function fromArray($id, $data) {
        $message = new Message();
        $message->id = $id;
        $message->senderId = $data['senderId'] ?? '';
        $message->receiverId = $data['receiverId'] ?? '';
        $message->bookingId = $data['bookingId'] ?? null;
        $message->content = $data['content'] ?? '';
        $message->isRead = isset($data['isRead']) ? (bool)$data['isRead'] : false;
        
        // Parse timestamps
        $message->createdAt = isset($data['createdAt']) ? 
            (is_numeric($data['createdAt']) ? $data['createdAt'] : strtotime($data['createdAt'])) : null;
        $message->updatedAt = isset($data['updatedAt']) ? 
            (is_numeric($data['updatedAt']) ? $data['updatedAt'] : strtotime($data['updatedAt'])) : null;
        
        return $message;
    }
}

