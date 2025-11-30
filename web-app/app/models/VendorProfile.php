<?php
/**
 * Vendor Profile Model
 */
class VendorProfile {
    public $id;
    public $userId;
    public $businessName;
    public $bio;
    public $location;
    public $latitude;
    public $longitude;
    public $skills = [];
    public $serviceCategories = [];
    public $hourlyRate;
    public $rating;
    public $totalReviews;
    public $completedJobs;
    public $profileImageUrl;
    public $portfolioImageUrls = [];
    public $isAvailable;
    public $availability;
    public $verificationStatus;
    public $createdAt;
    public $updatedAt;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->userId = $data['userId'] ?? '';
        $this->businessName = $data['businessName'] ?? null;
        $this->bio = $data['bio'] ?? null;
        $this->location = $data['location'] ?? null;
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;
        $this->skills = $data['skills'] ?? [];
        $this->serviceCategories = $data['serviceCategories'] ?? [];
        $this->hourlyRate = $data['hourlyRate'] ?? null;
        $this->rating = $data['rating'] ?? null;
        $this->totalReviews = $data['totalReviews'] ?? null;
        $this->completedJobs = $data['completedJobs'] ?? null;
        $this->profileImageUrl = $data['profileImageUrl'] ?? null;
        $this->portfolioImageUrls = $data['portfolioImageUrls'] ?? [];
        $this->isAvailable = $data['isAvailable'] ?? true;
        $this->availability = $data['availability'] ?? null;
        $this->verificationStatus = $data['verificationStatus'] ?? 'pending';
        $this->createdAt = $data['createdAt'] ?? null;
        $this->updatedAt = $data['updatedAt'] ?? null;
    }

    /**
     * Convert to array for Firestore
     */
    public function toArray() {
        return [
            'userId' => $this->userId,
            'businessName' => $this->businessName,
            'bio' => $this->bio,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'skills' => $this->skills,
            'serviceCategories' => $this->serviceCategories,
            'hourlyRate' => $this->hourlyRate,
            'rating' => $this->rating,
            'totalReviews' => $this->totalReviews,
            'completedJobs' => $this->completedJobs,
            'profileImageUrl' => $this->profileImageUrl,
            'portfolioImageUrls' => $this->portfolioImageUrls,
            'isAvailable' => $this->isAvailable,
            'availability' => $this->availability,
            'verificationStatus' => $this->verificationStatus,
            'createdAt' => $this->createdAt ? date('c', $this->createdAt) : null,
            'updatedAt' => $this->updatedAt ? date('c', $this->updatedAt) : null,
        ];
    }

    /**
     * Create from Firestore document
     */
    public static function fromArray($id, $data) {
        $profile = new VendorProfile();
        $profile->id = $id;
        $profile->userId = $data['userId'] ?? $id;
        $profile->businessName = $data['businessName'] ?? null;
        $profile->bio = $data['bio'] ?? null;
        $profile->location = $data['location'] ?? null;
        $profile->latitude = isset($data['latitude']) ? (float)$data['latitude'] : null;
        $profile->longitude = isset($data['longitude']) ? (float)$data['longitude'] : null;
        $profile->skills = $data['skills'] ?? [];
        $profile->serviceCategories = $data['serviceCategories'] ?? [];
        $profile->hourlyRate = isset($data['hourlyRate']) ? (float)$data['hourlyRate'] : null;
        $profile->rating = isset($data['rating']) ? (float)$data['rating'] : null;
        $profile->totalReviews = $data['totalReviews'] ?? null;
        $profile->completedJobs = $data['completedJobs'] ?? null;
        $profile->profileImageUrl = $data['profileImageUrl'] ?? null;
        $profile->portfolioImageUrls = $data['portfolioImageUrls'] ?? [];
        $profile->isAvailable = $data['isAvailable'] ?? true;
        $profile->availability = $data['availability'] ?? null;
        $profile->verificationStatus = $data['verificationStatus'] ?? 'pending';
        
        // Parse timestamps
        $profile->createdAt = isset($data['createdAt']) ? 
            (is_numeric($data['createdAt']) ? $data['createdAt'] : strtotime($data['createdAt'])) : null;
        $profile->updatedAt = isset($data['updatedAt']) ? 
            (is_numeric($data['updatedAt']) ? $data['updatedAt'] : strtotime($data['updatedAt'])) : null;
        
        return $profile;
    }

    /**
     * Check if profile is complete
     */
    public function isComplete() {
        return !empty($this->businessName) &&
               !empty($this->bio) &&
               !empty($this->location) &&
               !empty($this->skills) &&
               isset($this->hourlyRate);
    }
}

