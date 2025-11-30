<?php
/**
 * Firebase Service
 * Handles Firebase REST API calls
 */
class FirebaseService {
    private static $apiKey;
    private static $projectId;
    private static $authDomain;
    
    /**
     * Initialize Firebase
     */
    public static function init() {
        self::$apiKey = FIREBASE_API_KEY;
        self::$projectId = FIREBASE_PROJECT_ID;
        self::$authDomain = FIREBASE_AUTH_DOMAIN;
    }

    /**
     * Firebase Auth REST API endpoint
     */
    private static function getAuthUrl($endpoint) {
        return "https://identitytoolkit.googleapis.com/v1/accounts:{$endpoint}?key=" . self::$apiKey;
    }

    /**
     * Firestore REST API endpoint
     */
    private static function getFirestoreUrl($path) {
        // Ensure path doesn't have leading/trailing slashes
        $path = trim($path, '/');
        $url = "https://firestore.googleapis.com/v1/projects/" . self::$projectId . "/databases/(default)/documents/{$path}";
        return $url;
    }

    /**
     * Make HTTP request
     */
    private static function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
        $ch = curl_init();
        
        $defaultHeaders = [
            'Content-Type: application/json',
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Log errors for debugging
        if ($curlError) {
            error_log("FirebaseService::makeRequest - cURL Error: {$curlError} for URL: {$url}");
        }
        
        if ($httpCode >= 400) {
            error_log("FirebaseService::makeRequest - HTTP Error {$httpCode} for URL: {$url}");
            $responsePreview = is_string($response) ? substr($response, 0, 500) : json_encode($response);
            error_log("Response: " . $responsePreview);
        }
        
        return [
            'code' => $httpCode,
            'data' => json_decode($response, true),
            'raw' => $response,
            'error' => $curlError
        ];
    }

    /**
     * Sign up with email and password
     */
    public static function signUp($email, $password, $displayName = null) {
        self::init();
        $url = self::getAuthUrl('signUp');
        
        $data = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ];
        
        if ($displayName) {
            $data['displayName'] = $displayName;
        }
        
        return self::makeRequest($url, 'POST', $data);
    }

    /**
     * Sign in with email and password
     */
    public static function signIn($email, $password) {
        self::init();
        $url = self::getAuthUrl('signInWithPassword');
        
        $data = [
            'email' => $email,
            'password' => $password,
            'returnSecureToken' => true
        ];
        
        return self::makeRequest($url, 'POST', $data);
    }

    /**
     * Get user by ID token
     */
    public static function getUser($idToken) {
        self::init();
        $url = self::getAuthUrl('lookup');
        
        $data = [
            'idToken' => $idToken
        ];
        
        return self::makeRequest($url, 'POST', $data);
    }

    /**
     * Exchange idToken for OAuth2 access token
     * This is needed for Firestore REST API authentication
     */
    public static function getAccessToken($idToken) {
        self::init();
        // Use Google OAuth2 token endpoint
        $url = "https://oauth2.googleapis.com/token";
        
        $data = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $idToken
        ];
        
        // This approach doesn't work directly with Firebase idTokens
        // Instead, we'll use the idToken directly in Firestore requests
        // For production, use Firebase Admin SDK
        return null;
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordResetEmail($email) {
        self::init();
        $url = self::getAuthUrl('sendOobCode');
        
        $data = [
            'requestType' => 'PASSWORD_RESET',
            'email' => $email
        ];
        
        return self::makeRequest($url, 'POST', $data);
    }

    /**
     * Create Firestore document
     * Note: Firestore REST API requires authentication
     * For server-side operations, use Firebase Admin SDK or configure Firestore rules
     * For now, we'll try using the idToken (may not work - requires Admin SDK)
     * 
     * Note: To create a document with a specific ID, Firestore REST API requires PATCH, not POST
     */
    public static function createDocument($collection, $documentId, $data, $idToken = null) {
        self::init();
        // For creating a document with a specific ID, use PATCH to the document path
        $url = self::getFirestoreUrl("{$collection}/{$documentId}");
        
        // Log the URL for debugging
        error_log("FirebaseService::createDocument - URL: {$url}, Collection: {$collection}, DocumentId: {$documentId}");
        
        // Convert data to Firestore format
        $firestoreData = self::convertToFirestoreFormat($data);
        
        $headers = [];
        // Try using idToken - note: Firestore REST API typically requires OAuth2 access tokens
        // For production, use Firebase Admin SDK with service account
        if ($idToken) {
            $headers[] = 'Authorization: Bearer ' . $idToken;
        }
        
        // Use PATCH for creating/updating a document with a specific ID
        // POST is only for creating documents with auto-generated IDs
        $result = self::makeRequest($url, 'PATCH', $firestoreData, $headers);
        
        // Log result for debugging
        if ($result['code'] !== 200 && $result['code'] !== 201) {
            error_log("FirebaseService::createDocument - Failed. Code: {$result['code']}, URL: {$url}");
        }
        
        return $result;
    }

    /**
     * Get Firestore document
     * Note: Firestore REST API requires authentication
     * For server-side operations, use Firebase Admin SDK or configure Firestore rules
     */
    public static function getDocument($collection, $documentId, $idToken = null) {
        self::init();
        $url = self::getFirestoreUrl("{$collection}/{$documentId}");
        
        $headers = [];
        // Try using idToken - note: Firestore REST API typically requires OAuth2 access tokens
        // For production, use Firebase Admin SDK with service account
        if ($idToken) {
            $headers[] = 'Authorization: Bearer ' . $idToken;
        }
        
        return self::makeRequest($url, 'GET', null, $headers);
    }

    /**
     * Update Firestore document
     * Note: Firestore REST API requires authentication
     * For server-side operations, use Firebase Admin SDK or configure Firestore rules
     * 
     * Note: Firestore PATCH merges fields by default, but to be safe, we should
     * ensure all existing fields are preserved. The calling code should merge
     * existing data with new data before calling this method.
     */
    public static function updateDocument($collection, $documentId, $data, $idToken = null) {
        self::init();
        $url = self::getFirestoreUrl("{$collection}/{$documentId}");
        
        // Add updateMask parameter to ensure we're doing a merge update
        // However, Firestore REST API PATCH merges by default, so this is mainly for safety
        // The URL should include ?updateMask=field1,field2,... but for simplicity,
        // we'll rely on PATCH's default merge behavior
        
        // Convert data to Firestore format
        $firestoreData = self::convertToFirestoreFormat($data);
        
        $headers = [];
        // Try using idToken - note: Firestore REST API typically requires OAuth2 access tokens
        // For production, use Firebase Admin SDK with service account
        if ($idToken) {
            $headers[] = 'Authorization: Bearer ' . $idToken;
        }
        
        // Log the update for debugging
        error_log("FirebaseService::updateDocument - Updating: {$collection}/{$documentId}");
        error_log("FirebaseService::updateDocument - Fields being updated: " . implode(', ', array_keys($data)));
        
        $result = self::makeRequest($url, 'PATCH', $firestoreData, $headers);
        
        // Log result for debugging
        if ($result['code'] !== 200) {
            error_log("FirebaseService::updateDocument - Failed. Code: {$result['code']}, URL: {$url}");
            if (isset($result['data'])) {
                error_log("FirebaseService::updateDocument - Error data: " . json_encode($result['data']));
            }
        }
        
        return $result;
    }

    /**
     * Query Firestore collection
     */
    public static function queryCollection($collection, $filters = [], $orderBy = null, $limit = null) {
        self::init();
        // Note: Firestore REST API queries are complex
        // For simplicity, we'll fetch all and filter in PHP
        // In production, use proper Firestore queries
        $url = self::getFirestoreUrl($collection);
        
        return self::makeRequest($url, 'GET');
    }

    /**
     * Convert data to Firestore format
     */
    private static function convertToFirestoreFormat($data) {
        $fields = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $fields[$key] = ['stringValue' => $value];
            } elseif (is_int($value)) {
                $fields[$key] = ['integerValue' => (string)$value];
            } elseif (is_float($value)) {
                $fields[$key] = ['doubleValue' => $value];
            } elseif (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif (is_array($value)) {
                $fields[$key] = ['arrayValue' => ['values' => array_map(function($v) {
                    if (is_string($v)) return ['stringValue' => $v];
                    if (is_int($v)) return ['integerValue' => (string)$v];
                    if (is_float($v)) return ['doubleValue' => $v];
                    return ['stringValue' => (string)$v];
                }, $value)]];
            } elseif ($value === null) {
                $fields[$key] = ['nullValue' => null];
            } else {
                $fields[$key] = ['stringValue' => (string)$value];
            }
        }
        
        return ['fields' => $fields];
    }

    /**
     * Convert Firestore format to PHP array
     */
    public static function convertFromFirestoreFormat($firestoreData) {
        if (!isset($firestoreData['fields'])) {
            return [];
        }
        
        $data = [];
        foreach ($firestoreData['fields'] as $key => $field) {
            if (isset($field['stringValue'])) {
                $data[$key] = $field['stringValue'];
            } elseif (isset($field['integerValue'])) {
                $data[$key] = (int)$field['integerValue'];
            } elseif (isset($field['doubleValue'])) {
                $data[$key] = (float)$field['doubleValue'];
            } elseif (isset($field['booleanValue'])) {
                $data[$key] = $field['booleanValue'];
            } elseif (isset($field['arrayValue']['values'])) {
                $data[$key] = array_map(function($v) {
                    if (isset($v['stringValue'])) return $v['stringValue'];
                    if (isset($v['integerValue'])) return (int)$v['integerValue'];
                    if (isset($v['doubleValue'])) return (float)$v['doubleValue'];
                    return null;
                }, $field['arrayValue']['values']);
            } elseif (isset($field['nullValue'])) {
                $data[$key] = null;
            }
        }
        
        return $data;
    }
}

