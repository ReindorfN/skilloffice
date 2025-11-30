<?php
/**
 * Review Model
 * Represents a review and rating for a service/booking
 */
class Review {
    public $id;
    public $bookingId;
    public $customerId;
    public $vendorId;
    public $serviceId;
    public $serviceTitle;
    public $rating; // 1-5
    public $comment;
    public $createdAt;
    public $updatedAt;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->bookingId = $data['bookingId'] ?? '';
        $this->customerId = $data['customerId'] ?? '';
        $this->vendorId = $data['vendorId'] ?? '';
        $this->serviceId = $data['serviceId'] ?? '';
        $this->serviceTitle = $data['serviceTitle'] ?? '';
        $this->rating = isset($data['rating']) ? (int)$data['rating'] : 0;
        $this->comment = $data['comment'] ?? '';
        $this->createdAt = isset($data['createdAt']) ? 
            (is_numeric($data['createdAt']) ? $data['createdAt'] : strtotime($data['createdAt'])) : time();
        $this->updatedAt = isset($data['updatedAt']) ? 
            (is_numeric($data['updatedAt']) ? $data['updatedAt'] : strtotime($data['updatedAt'])) : time();
    }

    /**
     * Convert to array for Firestore
     */
    public function toArray() {
        return [
            'bookingId' => $this->bookingId,
            'customerId' => $this->customerId,
            'vendorId' => $this->vendorId,
            'serviceId' => $this->serviceId,
            'serviceTitle' => $this->serviceTitle,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'createdAt' => $this->createdAt ? date('c', $this->createdAt) : null,
            'updatedAt' => $this->updatedAt ? date('c', $this->updatedAt) : null,
        ];
    }

    /**
     * Create from Firestore document
     */
    public static function fromArray($id, $data) {
        $review = new Review();
        $review->id = $id;
        $review->bookingId = $data['bookingId'] ?? '';
        $review->customerId = $data['customerId'] ?? '';
        $review->vendorId = $data['vendorId'] ?? '';
        $review->serviceId = $data['serviceId'] ?? '';
        $review->serviceTitle = $data['serviceTitle'] ?? '';
        $review->rating = isset($data['rating']) ? (int)$data['rating'] : 0;
        $review->comment = $data['comment'] ?? '';
        $review->createdAt = isset($data['createdAt']) ? 
            (is_numeric($data['createdAt']) ? $data['createdAt'] : strtotime($data['createdAt'])) : null;
        $review->updatedAt = isset($data['updatedAt']) ? 
            (is_numeric($data['updatedAt']) ? $data['updatedAt'] : strtotime($data['updatedAt'])) : null;
        
        return $review;
    }
}

