<?php
require_once 'app/services/FirebaseService.php';

/**
 * User Service
 * Handles user-related Firestore operations
 */
class UserService {
    private static $collection = 'users';

    /**
     * Create user in Firestore
     * @param string $userId The user ID from Firebase Auth
     * @param array $userData User data to store
     * @param string|null $idToken Optional idToken for authentication
     */
    public static function createUser($userId, $userData, $idToken = null) {
        FirebaseService::init();
        
        // For Firestore REST API, we need to exchange idToken for access token
        // For now, try with idToken (may not work - requires Admin SDK or permissive rules)
        // In production, use Firebase Admin SDK
        $result = FirebaseService::createDocument(self::$collection, $userId, $userData, $idToken);
        
        // Log detailed error information
        if ($result['code'] !== 200 && $result['code'] !== 201) {
            $errorMsg = isset($result['data']['error']['message']) 
                ? $result['data']['error']['message'] 
                : 'Unknown error';
            error_log("UserService::createUser - Failed to create user {$userId}. HTTP Code: {$result['code']}, Error: {$errorMsg}");
            error_log("Full response: " . json_encode($result['data'] ?? []));
            if (isset($result['error']) && $result['error']) {
                error_log("cURL Error: " . $result['error']);
            }
            error_log("Raw response: " . substr($result['raw'] ?? '', 0, 500));
        }
        
        if ($result['code'] === 200 || $result['code'] === 201) {
            return User::fromArray($userId, $userData);
        }
        
        return null;
    }

    /**
     * Get user by ID
     * @param string $userId The user ID from Firebase Auth
     * @param string|null $idToken Optional idToken for authentication
     */
    public static function getUser($userId, $idToken = null) {
        FirebaseService::init();
        $result = FirebaseService::getDocument(self::$collection, $userId, $idToken);
        
        // Log for debugging
        if ($result['code'] !== 200) {
            $errorMsg = isset($result['data']['error']['message']) 
                ? $result['data']['error']['message'] 
                : 'Unknown error';
            error_log("UserService::getUser - Failed to get user {$userId}. HTTP Code: {$result['code']}, Error: {$errorMsg}");
            error_log("Full response: " . json_encode($result['data'] ?? []));
            if (isset($result['error']) && $result['error']) {
                error_log("cURL Error: " . $result['error']);
            }
        }
        
        if ($result['code'] === 200 && isset($result['data'])) {
            $data = FirebaseService::convertFromFirestoreFormat($result['data']);
            return User::fromArray($userId, $data);
        }
        
        // Also check for 404 (document doesn't exist) vs other errors
        if ($result['code'] === 404) {
            error_log("UserService::getUser - User document not found (404) for userId: {$userId}");
        }
        
        return null;
    }

    /**
     * Update user
     */
    public static function updateUser($userId, $userData) {
        FirebaseService::init();
        
        // Get existing user to preserve all data
        $existingUser = self::getUser($userId);
        if (!$existingUser) {
            return null;
        }
        
        // Merge with existing data
        $existingData = $existingUser->toArray();
        $mergedData = array_merge($existingData, $userData);
        $mergedData['updatedAt'] = date('c', time());
        
        $result = FirebaseService::updateDocument(self::$collection, $userId, $mergedData);
        
        if ($result['code'] === 200) {
            return self::getUser($userId);
        }
        
        return null;
    }

    /**
     * Check if user exists
     */
    public static function userExists($userId) {
        $user = self::getUser($userId);
        return $user !== null;
    }
}

