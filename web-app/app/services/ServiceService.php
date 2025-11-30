<?php
require_once 'app/services/FirebaseService.php';

/**
 * Service Service
 * Handles service listings
 */
class ServiceService {
    private static $collection = 'services';

    /**
     * Create service
     */
    public static function createService($serviceId, $serviceData) {
        FirebaseService::init();
        $serviceData['createdAt'] = time();
        $serviceData['updatedAt'] = time();
        
        $result = FirebaseService::createDocument(self::$collection, $serviceId, $serviceData);
        
        if ($result['code'] === 200 || $result['code'] === 201) {
            return self::getService($serviceId);
        }
        
        return null;
    }

    /**
     * Get service by ID
     */
    public static function getService($serviceId) {
        FirebaseService::init();
        $result = FirebaseService::getDocument(self::$collection, $serviceId);
        
        if ($result['code'] === 200 && isset($result['data'])) {
            $data = FirebaseService::convertFromFirestoreFormat($result['data']);
            return Service::fromArray($serviceId, $data);
        }
        
        return null;
    }

    /**
     * Get vendor services
     */
    public static function getVendorServices($vendorId) {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $services = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = FirebaseService::convertFromFirestoreFormat($doc);
                if (isset($data['vendorId']) && $data['vendorId'] === $vendorId) {
                    $services[] = Service::fromArray($id, $data);
                }
            }
        }
        
        return $services;
    }

    /**
     * Get popular services
     */
    public static function getPopularServices() {
        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $services = [];
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = FirebaseService::convertFromFirestoreFormat($doc);
                if (isset($data['isActive']) && $data['isActive']) {
                    $services[] = Service::fromArray($id, $data);
                }
            }
        }
        
        // Group by category
        $grouped = [];
        foreach ($services as $service) {
            $category = $service->category;
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $service;
        }
        
        return $grouped;
    }

    /**
     * Update service
     */
    public static function updateService($serviceId, $serviceData) {
        FirebaseService::init();
        
        // Get existing service to preserve all data
        $existingService = self::getService($serviceId);
        if (!$existingService) {
            return null;
        }
        
        // Merge with existing data
        $existingData = $existingService->toArray();
        $mergedData = array_merge($existingData, $serviceData);
        $mergedData['updatedAt'] = date('c', time());
        
        $result = FirebaseService::updateDocument(self::$collection, $serviceId, $mergedData);
        
        if ($result['code'] === 200) {
            return self::getService($serviceId);
        }
        
        return null;
    }

    /**
     * Search services by query
     * Searches in: title, description, category
     */
    public static function searchServices($query) {
        if (empty($query)) {
            return [];
        }

        FirebaseService::init();
        $result = FirebaseService::queryCollection(self::$collection);
        
        $services = [];
        $queryLower = strtolower(trim($query));
        $queryTerms = explode(' ', $queryLower);
        
        if ($result['code'] === 200 && isset($result['data']['documents'])) {
            foreach ($result['data']['documents'] as $doc) {
                $id = basename($doc['name']);
                $data = FirebaseService::convertFromFirestoreFormat($doc);
                $service = Service::fromArray($id, $data);
                
                // Skip if not active
                if (!$service->isActive) {
                    continue;
                }
                
                // Build searchable text from all relevant fields
                $searchableText = '';
                
                // Title
                if ($service->title) {
                    $searchableText .= ' ' . strtolower($service->title);
                }
                
                // Description
                if ($service->description) {
                    $searchableText .= ' ' . strtolower($service->description);
                }
                
                // Category
                if ($service->category) {
                    $searchableText .= ' ' . strtolower($service->category);
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
                    $services[] = $service;
                }
            }
        }
        
        // Sort by relevance (title match first, then category match)
        usort($services, function($a, $b) use ($queryLower) {
            // Exact title match first
            $aTitleMatch = stripos($a->title ?? '', $queryLower) !== false ? 1 : 0;
            $bTitleMatch = stripos($b->title ?? '', $queryLower) !== false ? 1 : 0;
            if ($aTitleMatch !== $bTitleMatch) {
                return $bTitleMatch <=> $aTitleMatch;
            }
            
            // Then by category match
            $aCategoryMatch = stripos($a->category ?? '', $queryLower) !== false ? 1 : 0;
            $bCategoryMatch = stripos($b->category ?? '', $queryLower) !== false ? 1 : 0;
            return $bCategoryMatch <=> $aCategoryMatch;
        });
        
        return $services;
    }
}

