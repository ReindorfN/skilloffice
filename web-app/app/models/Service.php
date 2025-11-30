<?php
/**
 * Service Model
 */
class Service {
    public $id;
    public $vendorId;
    public $title;
    public $description;
    public $category;
    public $price;
    public $priceType; // 'fixed', 'hourly', 'quote'
    public $images = [];
    public $duration; // Duration in minutes
    public $isActive;
    public $createdAt;
    public $updatedAt;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->vendorId = $data['vendorId'] ?? '';
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->category = $data['category'] ?? '';
        $this->price = $data['price'] ?? 0;
        $this->priceType = $data['priceType'] ?? 'fixed';
        $this->images = $data['images'] ?? [];
        $this->duration = $data['duration'] ?? null;
        $this->isActive = $data['isActive'] ?? true;
        $this->createdAt = $data['createdAt'] ?? null;
        $this->updatedAt = $data['updatedAt'] ?? null;
    }

    /**
     * Convert to array for Firestore
     */
    public function toArray() {
        return [
            'vendorId' => $this->vendorId,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price,
            'priceType' => $this->priceType,
            'images' => $this->images,
            'duration' => $this->duration,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt ? date('c', $this->createdAt) : null,
            'updatedAt' => $this->updatedAt ? date('c', $this->updatedAt) : null,
        ];
    }

    /**
     * Create from Firestore document
     */
    public static function fromArray($id, $data) {
        $service = new Service();
        $service->id = $id;
        $service->vendorId = $data['vendorId'] ?? '';
        $service->title = $data['title'] ?? '';
        $service->description = $data['description'] ?? '';
        $service->category = $data['category'] ?? '';
        $service->price = isset($data['price']) ? (float)$data['price'] : 0;
        $service->priceType = $data['priceType'] ?? 'fixed';
        $service->images = $data['images'] ?? [];
        $service->duration = $data['duration'] ?? null;
        $service->isActive = $data['isActive'] ?? true;
        
        // Parse timestamps
        $service->createdAt = isset($data['createdAt']) ? 
            (is_numeric($data['createdAt']) ? $data['createdAt'] : strtotime($data['createdAt'])) : null;
        $service->updatedAt = isset($data['updatedAt']) ? 
            (is_numeric($data['updatedAt']) ? $data['updatedAt'] : strtotime($data['updatedAt'])) : null;
        
        return $service;
    }
}

