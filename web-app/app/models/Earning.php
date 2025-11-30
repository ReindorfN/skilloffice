<?php
/**
 * Earning Model
 * Represents a payment/earning record in the earnings collection
 */
class Earning {
    public $id;
    public $bookingId;
    public $customerId;
    public $vendorId;
    public $serviceTitle;
    public $amount;
    public $currency;
    public $paymentStatus; // 'pending', 'success', 'failed'
    public $paystackReference;
    public $paystackTransactionId;
    public $paymentMethod;
    public $paidAt;
    public $createdAt;
    public $updatedAt;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->bookingId = $data['bookingId'] ?? '';
        $this->customerId = $data['customerId'] ?? '';
        $this->vendorId = $data['vendorId'] ?? '';
        $this->serviceTitle = $data['serviceTitle'] ?? '';
        $this->amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;
        $this->currency = $data['currency'] ?? 'GHS';
        $this->paymentStatus = $data['paymentStatus'] ?? 'pending';
        $this->paystackReference = $data['paystackReference'] ?? null;
        $this->paystackTransactionId = $data['paystackTransactionId'] ?? null;
        $this->paymentMethod = $data['paymentMethod'] ?? 'card';
        $this->paidAt = isset($data['paidAt']) ? 
            (is_numeric($data['paidAt']) ? $data['paidAt'] : strtotime($data['paidAt'])) : null;
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
            'serviceTitle' => $this->serviceTitle,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'paymentStatus' => $this->paymentStatus,
            'paystackReference' => $this->paystackReference,
            'paystackTransactionId' => $this->paystackTransactionId,
            'paymentMethod' => $this->paymentMethod,
            'paidAt' => $this->paidAt ? date('c', $this->paidAt) : null,
            'createdAt' => $this->createdAt ? date('c', $this->createdAt) : null,
            'updatedAt' => $this->updatedAt ? date('c', $this->updatedAt) : null,
        ];
    }

    /**
     * Create from Firestore document
     */
    public static function fromArray($id, $data) {
        $earning = new Earning();
        $earning->id = $id;
        $earning->bookingId = $data['bookingId'] ?? '';
        $earning->customerId = $data['customerId'] ?? '';
        $earning->vendorId = $data['vendorId'] ?? '';
        $earning->serviceTitle = $data['serviceTitle'] ?? '';
        $earning->amount = isset($data['amount']) ? (float)$data['amount'] : 0.0;
        $earning->currency = $data['currency'] ?? 'GHS';
        $earning->paymentStatus = $data['paymentStatus'] ?? 'pending';
        $earning->paystackReference = $data['paystackReference'] ?? null;
        $earning->paystackTransactionId = $data['paystackTransactionId'] ?? null;
        $earning->paymentMethod = $data['paymentMethod'] ?? 'card';
        $earning->paidAt = isset($data['paidAt']) ? 
            (is_numeric($data['paidAt']) ? $data['paidAt'] : strtotime($data['paidAt'])) : null;
        $earning->createdAt = isset($data['createdAt']) ? 
            (is_numeric($data['createdAt']) ? $data['createdAt'] : strtotime($data['createdAt'])) : null;
        $earning->updatedAt = isset($data['updatedAt']) ? 
            (is_numeric($data['updatedAt']) ? $data['updatedAt'] : strtotime($data['updatedAt'])) : null;
        
        return $earning;
    }
}

