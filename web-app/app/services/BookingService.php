<?php
require_once 'app/services/FirebaseService.php';

/**
 * Booking Service
 */
class BookingService {
    private static $collection = 'bookings';

    /**
     * Create booking
     */
    public static function createBooking($bookingId, $bookingData) {
        FirebaseService::init();
        $bookingData['createdAt'] = time();
        $bookingData['updatedAt'] = time();
        
        $result = FirebaseService::createDocument(self::$collection, $bookingId, $bookingData);
        
        if ($result['code'] === 200 || $result['code'] === 201) {
            return self::getBooking($bookingId);
        }
        
        return null;
    }

    /**
     * Get booking by ID
     */
    public static function getBooking($bookingId) {
        FirebaseService::init();
        $result = FirebaseService::getDocument(self::$collection, $bookingId);
        
        if ($result['code'] === 200 && isset($result['data'])) {
            $data = FirebaseService::convertFromFirestoreFormat($result['data']);
            return Booking::fromArray($bookingId, $data);
        }
        
        return null;
    }

    /**
     * Get vendor bookings
     */
    public static function getVendorBookings($vendorId, $status = null) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $bookings = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                // Extract fields from Firestore document format
                $data = isset($doc['fields']) ? FirebaseService::convertFromFirestoreFormat($doc) : [];
                if (isset($data['vendorId']) && $data['vendorId'] === $vendorId) {
                    if ($status === null || (isset($data['status']) && $data['status'] === $status)) {
                        $bookings[] = Booking::fromArray($id, $data);
                    }
                }
            }
        }
        
        return $bookings;
    }

    /**
     * Get customer bookings
     */
    public static function getCustomerBookings($customerId, $status = null) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $bookings = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                // Extract fields from Firestore document format
                $data = isset($doc['fields']) ? FirebaseService::convertFromFirestoreFormat($doc) : [];
                if (isset($data['customerId']) && $data['customerId'] === $customerId) {
                    if ($status === null || (isset($data['status']) && $data['status'] === $status)) {
                        $bookings[] = Booking::fromArray($id, $data);
                    }
                }
            }
        }
        
        return $bookings;
    }

    /**
     * Update booking status
     * This method preserves all existing booking data and only updates specified fields
     */
    public static function updateBookingStatus($bookingId, $status, $additionalData = []) {
        FirebaseService::init();
        
        // First, get the existing booking to preserve all data
        $existingBooking = self::getBooking($bookingId);
        if (!$existingBooking) {
            error_log("BookingService::updateBookingStatus - Booking not found: {$bookingId}");
            return null;
        }
        
        // Log existing booking data for debugging
        error_log("BookingService::updateBookingStatus - Existing booking fields: " . implode(', ', array_keys($existingBooking->toArray())));
        
        // Convert existing booking to array, preserving all fields
        $existingData = $existingBooking->toArray();
        
        // Prepare update data - merge existing with new, ensuring new data takes precedence
        $updateData = $existingData;
        
        // Apply additional data (vendor notes, quoted price, etc.)
        foreach ($additionalData as $key => $value) {
            $updateData[$key] = $value;
        }
        
        // Update status and timestamp
        $updateData['status'] = $status;
        $updateData['updatedAt'] = date('c', time()); // Use ISO 8601 format to match toArray()
        
        // Handle completed status
        if ($status === 'completed' && !isset($updateData['completedAt'])) {
            $updateData['completedAt'] = date('c', time()); // Use ISO 8601 format
        }
        
        // Log what we're updating
        error_log("BookingService::updateBookingStatus - Updating booking {$bookingId} with fields: " . implode(', ', array_keys($updateData)));
        
        // Update the document with merged data
        $result = FirebaseService::updateDocument(self::$collection, $bookingId, $updateData);
        
        if ($result['code'] === 200) {
            return self::getBooking($bookingId);
        } else {
            error_log("BookingService::updateBookingStatus - Update failed. Code: {$result['code']}");
            if (isset($result['data'])) {
                error_log("BookingService::updateBookingStatus - Error: " . json_encode($result['data']));
            }
        }
        
        return null;
    }
}

