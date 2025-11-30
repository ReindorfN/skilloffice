<?php
require_once 'app/services/FirebaseService.php';

/**
 * Vendor Profile Service
 */
class VendorProfileService {
    private static $collection = 'vendor_profiles';

    /**
     * Save vendor profile
     */
    public static function saveProfile($vendorId, $profileData) {
        FirebaseService::init();
        
        // Check if profile exists
        $existing = self::getProfile($vendorId);
        if ($existing) {
            // Merge with existing data
            $existingData = $existing->toArray();
            $mergedData = array_merge($existingData, $profileData);
            $mergedData['updatedAt'] = date('c', time());
            
            $result = FirebaseService::updateDocument(self::$collection, $vendorId, $mergedData);
        } else {
            $profileData['createdAt'] = time();
            $profileData['updatedAt'] = time();
            $result = FirebaseService::createDocument(self::$collection, $vendorId, $profileData);
        }
        
        if ($result['code'] === 200 || $result['code'] === 201) {
            return self::getProfile($vendorId);
        }
        
        return null;
    }

    /**
     * Get vendor profile
     */
    public static function getProfile($vendorId) {
        FirebaseService::init();
        $result = FirebaseService::getDocument(self::$collection, $vendorId);
        
        if ($result['code'] === 200 && isset($result['data'])) {
            $data = FirebaseService::convertFromFirestoreFormat($result['data']);
            return VendorProfile::fromArray($vendorId, $data);
        }
        
        return null;
    }

    /**
     * Get featured vendors
     */
    public static function getFeaturedVendors($limit = 10) {
        // Note: This is a simplified version
        // In production, implement proper Firestore queries
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $vendors = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = FirebaseService::convertFromFirestoreFormat($doc);
                $profile = VendorProfile::fromArray($id, $data);
                if ($profile->isAvailable && $profile->rating >= 4.0) {
                    $vendors[] = $profile;
                }
            }
        }
        
        // Sort by rating and limit
        usort($vendors, function($a, $b) {
            return ($b->rating ?? 0) <=> ($a->rating ?? 0);
        });
        
        return array_slice($vendors, 0, $limit);
    }

    /**
     * Search vendors by query
     * Searches in: businessName, skills, serviceCategories, bio, location
     */
    public static function searchVendors($query) {
        if (empty($query)) {
            return [];
        }

        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $vendors = [];
        $queryLower = strtolower(trim($query));
        $queryTerms = explode(' ', $queryLower);
        
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = FirebaseService::convertFromFirestoreFormat($doc);
                $profile = VendorProfile::fromArray($id, $data);
                
                // Skip if not available
                if (!$profile->isAvailable) {
                    continue;
                }
                
                // Build searchable text from all relevant fields
                $searchableText = '';
                
                // Business name
                if ($profile->businessName) {
                    $searchableText .= ' ' . strtolower($profile->businessName);
                }
                
                // Bio
                if ($profile->bio) {
                    $searchableText .= ' ' . strtolower($profile->bio);
                }
                
                // Location
                if ($profile->location) {
                    $searchableText .= ' ' . strtolower($profile->location);
                }
                
                // Skills (array)
                if (is_array($profile->skills) && !empty($profile->skills)) {
                    $searchableText .= ' ' . strtolower(implode(' ', $profile->skills));
                } elseif (is_string($profile->skills)) {
                    $searchableText .= ' ' . strtolower($profile->skills);
                }
                
                // Service categories (array)
                if (is_array($profile->serviceCategories) && !empty($profile->serviceCategories)) {
                    $searchableText .= ' ' . strtolower(implode(' ', $profile->serviceCategories));
                } elseif (is_string($profile->serviceCategories)) {
                    $searchableText .= ' ' . strtolower($profile->serviceCategories);
                }
                
                // Check if any query term matches
                $matches = false;
                foreach ($queryTerms as $term) {
                    if (strlen($term) > 0 && strpos($searchableText, $term) !== false) {
                        $matches = true;
                        break;
                    }
                }
                
                if ($matches) {
                    $vendors[] = $profile;
                }
            }
        }
        
        // Sort by relevance (rating first, then by number of matches)
        usort($vendors, function($a, $b) use ($queryLower) {
            // First sort by rating
            $ratingDiff = ($b->rating ?? 0) <=> ($a->rating ?? 0);
            if ($ratingDiff !== 0) {
                return $ratingDiff;
            }
            
            // Then by business name match (exact match first)
            $aNameMatch = stripos($a->businessName ?? '', $queryLower) !== false ? 1 : 0;
            $bNameMatch = stripos($b->businessName ?? '', $queryLower) !== false ? 1 : 0;
            return $bNameMatch <=> $aNameMatch;
        });
        
        return $vendors;
    }

    /**
     * Get all available vendors
     */
    public static function getAllVendors() {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $vendors = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = FirebaseService::convertFromFirestoreFormat($doc);
                $profile = VendorProfile::fromArray($id, $data);
                
                // Only include available vendors
                if ($profile->isAvailable) {
                    $vendors[] = $profile;
                }
            }
        }
        
        // Sort by business name
        usort($vendors, function($a, $b) {
            return strcmp($a->businessName ?? '', $b->businessName ?? '');
        });
        
        return $vendors;
    }
}

