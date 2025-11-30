<?php
require_once 'app/services/FirebaseService.php';

/**
 * Earnings Service
 * Handles CRUD operations for earnings/payments
 */
class EarningsService {
    private static $collection = 'earnings';

    /**
     * Create earning record
     */
    public static function createEarning($earningId, $earningData) {
        FirebaseService::init();
        $earningData['createdAt'] = time();
        $earningData['updatedAt'] = time();
        
        $result = FirebaseService::createDocument(self::$collection, $earningId, $earningData);
        
        if ($result['code'] === 200 || $result['code'] === 201) {
            return self::getEarning($earningId);
        }
        
        return null;
    }

    /**
     * Get earning by ID
     */
    public static function getEarning($earningId) {
        FirebaseService::init();
        $result = FirebaseService::getDocument(self::$collection, $earningId);
        
        if ($result['code'] === 200 && isset($result['data'])) {
            $data = FirebaseService::convertFromFirestoreFormat($result['data']);
            return Earning::fromArray($earningId, $data);
        }
        
        return null;
    }

    /**
     * Get earning by Paystack reference
     */
    public static function getEarningByReference($reference) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = isset($doc['fields']) ? FirebaseService::convertFromFirestoreFormat($doc) : [];
                if (isset($data['paystackReference']) && $data['paystackReference'] === $reference) {
                    return Earning::fromArray($id, $data);
                }
            }
        }
        
        return null;
    }

    /**
     * Get vendor earnings
     */
    public static function getVendorEarnings($vendorId, $status = null) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $earnings = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = isset($doc['fields']) ? FirebaseService::convertFromFirestoreFormat($doc) : [];
                if (isset($data['vendorId']) && $data['vendorId'] === $vendorId) {
                    if ($status === null || (isset($data['paymentStatus']) && $data['paymentStatus'] === $status)) {
                        $earnings[] = Earning::fromArray($id, $data);
                    }
                }
            }
        }
        
        // Sort by createdAt descending (newest first)
        usort($earnings, function($a, $b) {
            return ($b->createdAt ?? 0) <=> ($a->createdAt ?? 0);
        });
        
        return $earnings;
    }

    /**
     * Get customer payments
     */
    public static function getCustomerPayments($customerId) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $payments = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = isset($doc['fields']) ? FirebaseService::convertFromFirestoreFormat($doc) : [];
                if (isset($data['customerId']) && $data['customerId'] === $customerId) {
                    $payments[] = Earning::fromArray($id, $data);
                }
            }
        }
        
        // Sort by createdAt descending (newest first)
        usort($payments, function($a, $b) {
            return ($b->createdAt ?? 0) <=> ($a->createdAt ?? 0);
        });
        
        return $payments;
    }

    /**
     * Update earning record
     */
    public static function updateEarning($earningId, $updateData) {
        FirebaseService::init();
        
        // Get existing earning
        $existingEarning = self::getEarning($earningId);
        if (!$existingEarning) {
            return null;
        }
        
        // Merge with existing data
        $existingData = $existingEarning->toArray();
        $mergedData = array_merge($existingData, $updateData);
        $mergedData['updatedAt'] = date('c', time());
        
        $result = FirebaseService::updateDocument(self::$collection, $earningId, $mergedData);
        
        if ($result['code'] === 200) {
            return self::getEarning($earningId);
        }
        
        return null;
    }

    /**
     * Calculate total earnings for a vendor
     */
    public static function getTotalEarnings($vendorId) {
        $earnings = self::getVendorEarnings($vendorId, 'success');
        $total = 0.0;
        foreach ($earnings as $earning) {
            $total += $earning->amount;
        }
        return $total;
    }

    /**
     * Calculate monthly earnings for a vendor
     */
    public static function getMonthlyEarnings($vendorId, $month = null, $year = null) {
        if ($month === null) {
            $month = (int)date('m');
        }
        if ($year === null) {
            $year = (int)date('Y');
        }
        
        $earnings = self::getVendorEarnings($vendorId, 'success');
        $total = 0.0;
        
        foreach ($earnings as $earning) {
            if ($earning->paidAt) {
                $earningMonth = (int)date('m', $earning->paidAt);
                $earningYear = (int)date('Y', $earning->paidAt);
                if ($earningMonth === $month && $earningYear === $year) {
                    $total += $earning->amount;
                }
            }
        }
        
        return $total;
    }
}

