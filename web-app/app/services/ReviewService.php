<?php
require_once 'app/services/FirebaseService.php';

/**
 * Review Service
 * Handles CRUD operations for reviews and ratings
 */
class ReviewService {
    private static $collection = 'reviews';

    /**
     * Create review
     */
    public static function createReview($reviewId, $reviewData) {
        FirebaseService::init();
        $reviewData['createdAt'] = time();
        $reviewData['updatedAt'] = time();
        
        $result = FirebaseService::createDocument(self::$collection, $reviewId, $reviewData);
        
        if ($result['code'] === 200 || $result['code'] === 201) {
            // Update vendor rating after creating review
            self::updateVendorRating($reviewData['vendorId']);
            return self::getReview($reviewId);
        }
        
        return null;
    }

    /**
     * Get review by ID
     */
    public static function getReview($reviewId) {
        FirebaseService::init();
        $result = FirebaseService::getDocument(self::$collection, $reviewId);
        
        if ($result['code'] === 200 && isset($result['data'])) {
            $data = FirebaseService::convertFromFirestoreFormat($result['data']);
            return Review::fromArray($reviewId, $data);
        }
        
        return null;
    }

    /**
     * Get review by booking ID
     */
    public static function getReviewByBooking($bookingId) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = isset($doc['fields']) ? FirebaseService::convertFromFirestoreFormat($doc) : [];
                if (isset($data['bookingId']) && $data['bookingId'] === $bookingId) {
                    return Review::fromArray($id, $data);
                }
            }
        }
        
        return null;
    }

    /**
     * Get vendor reviews
     */
    public static function getVendorReviews($vendorId, $limit = null) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $reviews = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = isset($doc['fields']) ? FirebaseService::convertFromFirestoreFormat($doc) : [];
                if (isset($data['vendorId']) && $data['vendorId'] === $vendorId) {
                    $reviews[] = Review::fromArray($id, $data);
                }
            }
        }
        
        // Sort by createdAt descending (newest first)
        usort($reviews, function($a, $b) {
            return ($b->createdAt ?? 0) <=> ($a->createdAt ?? 0);
        });
        
        if ($limit) {
            $reviews = array_slice($reviews, 0, $limit);
        }
        
        return $reviews;
    }

    /**
     * Get service reviews
     */
    public static function getServiceReviews($serviceId, $limit = null) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $reviews = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = isset($doc['fields']) ? FirebaseService::convertFromFirestoreFormat($doc) : [];
                if (isset($data['serviceId']) && $data['serviceId'] === $serviceId) {
                    $reviews[] = Review::fromArray($id, $data);
                }
            }
        }
        
        // Sort by createdAt descending (newest first)
        usort($reviews, function($a, $b) {
            return ($b->createdAt ?? 0) <=> ($a->createdAt ?? 0);
        });
        
        if ($limit) {
            $reviews = array_slice($reviews, 0, $limit);
        }
        
        return $reviews;
    }

    /**
     * Calculate and update vendor rating
     */
    public static function updateVendorRating($vendorId) {
        require_once 'app/services/VendorProfileService.php';
        
        $reviews = self::getVendorReviews($vendorId);
        
        if (empty($reviews)) {
            return;
        }
        
        $totalRating = 0;
        $count = 0;
        
        foreach ($reviews as $review) {
            if ($review->rating > 0) {
                $totalRating += $review->rating;
                $count++;
            }
        }
        
        if ($count > 0) {
            $averageRating = round($totalRating / $count, 1);
            
            // Update vendor profile with new rating
            $profile = VendorProfileService::getProfile($vendorId);
            if ($profile) {
                $existingData = $profile->toArray();
                $updateData = array_merge($existingData, [
                    'rating' => $averageRating,
                    'totalReviews' => $count
                ]);
                
                FirebaseService::init();
                FirebaseService::updateDocument('vendor_profiles', $vendorId, $updateData);
            }
        }
    }

    /**
     * Get average rating for vendor
     */
    public static function getVendorAverageRating($vendorId) {
        $reviews = self::getVendorReviews($vendorId);
        
        if (empty($reviews)) {
            return ['rating' => 0, 'count' => 0];
        }
        
        $totalRating = 0;
        $count = 0;
        
        foreach ($reviews as $review) {
            if ($review->rating > 0) {
                $totalRating += $review->rating;
                $count++;
            }
        }
        
        if ($count > 0) {
            return [
                'rating' => round($totalRating / $count, 1),
                'count' => $count
            ];
        }
        
        return ['rating' => 0, 'count' => 0];
    }
}

