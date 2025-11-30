<?php
/**
 * Booking Model
 */
class Booking {
    public $id;
    public $customerId;
    public $vendorId;
    public $serviceId;
    public $serviceTitle;
    public $description;
    public $location;
    public $latitude;
    public $longitude;
    public $scheduledDate;
    public $scheduledTime;
    public $quotedPrice;
    public $status; // 'pending', 'accepted', 'rejected', 'inProgress', 'completed', 'cancelled'
    public $paymentStatus; // 'pending', 'paid', 'refunded'
    public $specialRequirements;
    public $customerNotes;
    public $vendorNotes;
    public $createdAt;
    public $updatedAt;
    public $completedAt;
    public $customerConfirmedCompletion; // Boolean: customer confirmed vendor's completion

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->customerId = $data['customerId'] ?? '';
        $this->vendorId = $data['vendorId'] ?? '';
        $this->serviceId = $data['serviceId'] ?? '';
        $this->serviceTitle = $data['serviceTitle'] ?? null;
        $this->description = $data['description'] ?? '';
        $this->location = $data['location'] ?? '';
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;
        $this->scheduledDate = $data['scheduledDate'] ?? null;
        $this->scheduledTime = $data['scheduledTime'] ?? null;
        $this->quotedPrice = $data['quotedPrice'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->paymentStatus = $data['paymentStatus'] ?? 'pending';
        $this->specialRequirements = $data['specialRequirements'] ?? null;
        $this->customerNotes = $data['customerNotes'] ?? null;
        $this->vendorNotes = $data['vendorNotes'] ?? null;
        $this->createdAt = $data['createdAt'] ?? null;
        $this->updatedAt = $data['updatedAt'] ?? null;
        $this->completedAt = $data['completedAt'] ?? null;
        $this->customerConfirmedCompletion = isset($data['customerConfirmedCompletion']) ? (bool)$data['customerConfirmedCompletion'] : false;
    }

    /**
     * Convert to array for Firestore
     */
    public function toArray() {
        return [
            'customerId' => $this->customerId,
            'vendorId' => $this->vendorId,
            'serviceId' => $this->serviceId,
            'serviceTitle' => $this->serviceTitle,
            'description' => $this->description,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'scheduledDate' => $this->scheduledDate ? date('c', $this->scheduledDate) : null,
            'scheduledTime' => $this->scheduledTime,
            'quotedPrice' => $this->quotedPrice,
            'status' => $this->status,
            'paymentStatus' => $this->paymentStatus,
            'specialRequirements' => $this->specialRequirements,
            'customerNotes' => $this->customerNotes,
            'vendorNotes' => $this->vendorNotes,
            'createdAt' => $this->createdAt ? date('c', $this->createdAt) : null,
            'updatedAt' => $this->updatedAt ? date('c', $this->updatedAt) : null,
            'completedAt' => $this->completedAt ? date('c', $this->completedAt) : null,
            'customerConfirmedCompletion' => $this->customerConfirmedCompletion,
        ];
    }

    /**
     * Create from Firestore document
     */
    public static function fromArray($id, $data) {
        $booking = new Booking();
        $booking->id = $id;
        $booking->customerId = $data['customerId'] ?? '';
        $booking->vendorId = $data['vendorId'] ?? '';
        $booking->serviceId = $data['serviceId'] ?? '';
        $booking->serviceTitle = $data['serviceTitle'] ?? null;
        $booking->description = $data['description'] ?? '';
        $booking->location = $data['location'] ?? '';
        $booking->latitude = isset($data['latitude']) ? (float)$data['latitude'] : null;
        $booking->longitude = isset($data['longitude']) ? (float)$data['longitude'] : null;
        $booking->scheduledDate = isset($data['scheduledDate']) ? 
            (is_numeric($data['scheduledDate']) ? $data['scheduledDate'] : strtotime($data['scheduledDate'])) : time();
        $booking->scheduledTime = $data['scheduledTime'] ?? null;
        $booking->quotedPrice = isset($data['quotedPrice']) ? (float)$data['quotedPrice'] : null;
        $booking->status = $data['status'] ?? 'pending';
        $booking->paymentStatus = $data['paymentStatus'] ?? 'pending';
        $booking->specialRequirements = $data['specialRequirements'] ?? null;
        $booking->customerNotes = $data['customerNotes'] ?? null;
        $booking->vendorNotes = $data['vendorNotes'] ?? null;
        
        // Parse timestamps
        $booking->createdAt = isset($data['createdAt']) ? 
            (is_numeric($data['createdAt']) ? $data['createdAt'] : strtotime($data['createdAt'])) : null;
        $booking->updatedAt = isset($data['updatedAt']) ? 
            (is_numeric($data['updatedAt']) ? $data['updatedAt'] : strtotime($data['updatedAt'])) : null;
        $booking->completedAt = isset($data['completedAt']) ? 
            (is_numeric($data['completedAt']) ? $data['completedAt'] : strtotime($data['completedAt'])) : null;
        $booking->customerConfirmedCompletion = isset($data['customerConfirmedCompletion']) ? (bool)$data['customerConfirmedCompletion'] : false;
        
        return $booking;
    }

    /**
     * Check if booking is active
     */
    public function isActive() {
        return !in_array($this->status, ['completed', 'cancelled', 'rejected']);
    }
}

