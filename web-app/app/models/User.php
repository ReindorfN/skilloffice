<?php
/**
 * User Model
 */
class User {
    public $id;
    public $email;
    public $phoneNumber;
    public $role; // 'customer' or 'artisan'
    public $fullName;
    public $isVerified;
    public $createdAt;
    public $updatedAt;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->email = $data['email'] ?? '';
        $this->phoneNumber = $data['phoneNumber'] ?? null;
        $this->role = $data['role'] ?? 'customer';
        $this->fullName = $data['fullName'] ?? null;
        $this->isVerified = $data['isVerified'] ?? false;
        $this->createdAt = $data['createdAt'] ?? null;
        $this->updatedAt = $data['updatedAt'] ?? null;
    }

    /**
     * Convert to array for Firestore
     */
    public function toArray() {
        return [
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'role' => $this->role,
            'fullName' => $this->fullName,
            'isVerified' => $this->isVerified,
            'createdAt' => $this->createdAt ? date('c', $this->createdAt) : null,
            'updatedAt' => $this->updatedAt ? date('c', $this->updatedAt) : null,
        ];
    }

    /**
     * Create from Firestore document
     */
    public static function fromArray($id, $data) {
        $user = new User();
        $user->id = $id;
        $user->email = $data['email'] ?? '';
        $user->phoneNumber = $data['phoneNumber'] ?? null;
        $user->role = $data['role'] ?? 'customer';
        $user->fullName = $data['fullName'] ?? null;
        $user->isVerified = $data['isVerified'] ?? false;
        
        // Parse timestamps
        $user->createdAt = isset($data['createdAt']) ? 
            (is_numeric($data['createdAt']) ? $data['createdAt'] : strtotime($data['createdAt'])) : null;
        $user->updatedAt = isset($data['updatedAt']) ? 
            (is_numeric($data['updatedAt']) ? $data['updatedAt'] : strtotime($data['updatedAt'])) : null;
        
        return $user;
    }

    /**
     * Check if user is customer
     */
    public function isCustomer() {
        return $this->role === 'customer';
    }

    /**
     * Check if user is artisan/vendor
     */
    public function isArtisan() {
        return $this->role === 'artisan';
    }
}

