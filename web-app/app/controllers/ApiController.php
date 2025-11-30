<?php
require_once 'app/core/Controller.php';
require_once 'app/services/FirebaseService.php';
require_once 'app/services/BookingService.php';
require_once 'app/services/MessageService.php';
require_once 'app/services/UserService.php';
require_once 'app/services/EarningsService.php';
require_once 'app/services/ReviewService.php';

/**
 * API Controller
 * Handles API endpoints
 */
class ApiController extends Controller {
    
    /**
     * Test Firebase connection
     */
    public function testConnection() {
        try {
            FirebaseService::init();
            
            // Try to make a simple Firestore request to test connection
            // We'll try to access a non-existent collection (this will return 404 if connection works)
            $testCollection = '_connection_test_' . time();
            $url = "https://firestore.googleapis.com/v1/projects/" . FIREBASE_PROJECT_ID . "/databases/(default)/documents/{$testCollection}";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($curlError || $curlErrno !== 0) {
                $this->json([
                    'success' => false,
                    'message' => 'Connection failed: ' . ($curlError ?: 'Unknown error'),
                    'error' => $curlError,
                    'errno' => $curlErrno
                ], 500);
                return;
            }
            
            // If we get any HTTP response code (including 404), Firebase is reachable
            // 404 is expected for non-existent collection - this confirms connection works
            // 401/403 means auth issue but connection works
            // 200/201 means success
            if ($httpCode > 0) {
                $statusMessage = 'Firebase connection successful';
                if ($httpCode === 404) {
                    $statusMessage = 'Firebase reachable (connection verified)';
                } elseif ($httpCode === 401 || $httpCode === 403) {
                    $statusMessage = 'Firebase reachable (authentication may be required)';
                }
                
                $this->json([
                    'success' => true,
                    'message' => $statusMessage,
                    'httpCode' => $httpCode,
                    'projectId' => FIREBASE_PROJECT_ID,
                    'timestamp' => date('Y-m-d H:i:s')
                ], 200);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Connection timeout or failed - no HTTP response received',
                    'httpCode' => $httpCode
                ], 500);
            }
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error testing connection: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Create booking
     */
    public function createBooking() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $vendorId = $data['vendorId'] ?? '';
        $serviceId = $data['serviceId'] ?? '';
        $description = $data['description'] ?? '';
        $location = $data['location'] ?? '';
        $scheduledDate = isset($data['scheduledDate']) ? strtotime($data['scheduledDate']) : null;
        $scheduledTime = $data['scheduledTime'] ?? '';
        $specialRequirements = $data['specialRequirements'] ?? '';
        $customerNotes = $data['customerNotes'] ?? '';
        
        if (empty($vendorId) || empty($description)) {
            $this->json(['success' => false, 'message' => 'Vendor ID and description are required'], 400);
            return;
        }
        
        // Get service details if serviceId provided
        $serviceTitle = '';
        $quotedPrice = null;
        if ($serviceId) {
            require_once 'app/services/ServiceService.php';
            $service = ServiceService::getService($serviceId);
            if ($service) {
                $serviceTitle = $service->title;
                $quotedPrice = $service->price;
            }
        }
        
        $bookingData = [
            'customerId' => $user->id,
            'vendorId' => $vendorId,
            'serviceId' => $serviceId,
            'serviceTitle' => $serviceTitle,
            'description' => $description,
            'location' => $location,
            'scheduledDate' => $scheduledDate,
            'scheduledTime' => $scheduledTime,
            'quotedPrice' => $quotedPrice,
            'status' => 'pending',
            'paymentStatus' => 'pending',
            'specialRequirements' => $specialRequirements,
            'customerNotes' => $customerNotes
        ];
        
        $bookingId = uniqid('booking_');
        $booking = BookingService::createBooking($bookingId, $bookingData);
        
        if ($booking) {
            $this->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => [
                    'id' => $booking->id,
                    'status' => $booking->status
                ]
            ], 201);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create booking'], 500);
        }
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $bookingId = $this->params['id'] ?? null;
        if (!$bookingId) {
            $this->json(['success' => false, 'message' => 'Booking ID required'], 400);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'] ?? '';
        $vendorNotes = $data['vendorNotes'] ?? '';
        $quotedPrice = isset($data['quotedPrice']) ? (float)$data['quotedPrice'] : null;
        
        $allowedStatuses = ['pending', 'accepted', 'rejected', 'inProgress', 'completed', 'cancelled'];
        if (!in_array($status, $allowedStatuses)) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 400);
            return;
        }
        
        $booking = BookingService::getBooking($bookingId);
        if (!$booking) {
            $this->json(['success' => false, 'message' => 'Booking not found'], 404);
            return;
        }
        
        // Check authorization
        if ($user->role === 'artisan' && $booking->vendorId !== $user->id) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }
        
        if ($user->role === 'customer' && $booking->customerId !== $user->id) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }
        
        $additionalData = [];
        if (isset($data['vendorNotes'])) {
            $additionalData['vendorNotes'] = $data['vendorNotes'];
        }
        if ($quotedPrice !== null) {
            $additionalData['quotedPrice'] = $quotedPrice;
        }
        
        $updated = BookingService::updateBookingStatus($bookingId, $status, $additionalData);
        
        if ($updated) {
            $this->json([
                'success' => true,
                'message' => 'Booking status updated',
                'booking' => [
                    'id' => $updated->id,
                    'status' => $updated->status
                ]
            ], 200);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update booking'], 500);
        }
    }

    /**
     * Confirm completion (customer confirms vendor's completion)
     */
    public function confirmCompletion() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $bookingId = $this->params['id'] ?? null;
        if (!$bookingId) {
            $this->json(['success' => false, 'message' => 'Booking ID required'], 400);
            return;
        }
        
        $booking = BookingService::getBooking($bookingId);
        if (!$booking) {
            $this->json(['success' => false, 'message' => 'Booking not found'], 404);
            return;
        }
        
        // Check authorization - only customer can confirm
        if ($user->role !== 'customer' || $booking->customerId !== $user->id) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }
        
        // Check if booking is already completed by vendor
        if ($booking->status !== 'completed') {
            $this->json(['success' => false, 'message' => 'Vendor has not marked this job as completed yet'], 400);
            return;
        }
        
        // Check if already confirmed
        if ($booking->customerConfirmedCompletion ?? false) {
            $this->json(['success' => false, 'message' => 'Completion already confirmed'], 400);
            return;
        }
        
        // Check if review already exists
        $existingReview = ReviewService::getReviewByBooking($bookingId);
        if ($existingReview) {
            // If review exists, just confirm completion
            $updated = BookingService::updateBookingStatus($bookingId, 'completed', [
                'customerConfirmedCompletion' => true
            ]);
            
            if ($updated) {
                $this->json([
                    'success' => true,
                    'message' => 'Completion confirmed successfully',
                    'booking' => [
                        'id' => $updated->id,
                        'status' => $updated->status,
                        'customerConfirmedCompletion' => $updated->customerConfirmedCompletion
                    ]
                ], 200);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to confirm completion'], 500);
            }
            return;
        }
        
        // Review is mandatory - check if review data is provided
        $input = file_get_contents('php://input');
        $reviewData = json_decode($input, true);
        $rating = isset($reviewData['rating']) ? (int)$reviewData['rating'] : 0;
        $comment = $reviewData['comment'] ?? '';
        
        if ($rating < 1 || $rating > 5) {
            $this->json(['success' => false, 'message' => 'Rating is required (1-5 stars)'], 400);
            return;
        }
        
        // Create review first
        $reviewId = 'review_' . time() . '_' . substr($bookingId, 0, 8);
        $reviewDataToSave = [
            'bookingId' => $bookingId,
            'customerId' => $user->id,
            'vendorId' => $booking->vendorId,
            'serviceId' => $booking->serviceId ?? '',
            'serviceTitle' => $booking->serviceTitle ?? 'Service',
            'rating' => $rating,
            'comment' => $comment
        ];
        
        $review = ReviewService::createReview($reviewId, $reviewDataToSave);
        
        if (!$review) {
            $this->json(['success' => false, 'message' => 'Failed to create review'], 500);
            return;
        }
        
        // Update booking with customer confirmation using merge approach
        $updated = BookingService::updateBookingStatus($bookingId, 'completed', [
            'customerConfirmedCompletion' => true
        ]);
        
        if ($updated) {
            $this->json([
                'success' => true,
                'message' => 'Completion confirmed and review submitted successfully',
                'booking' => [
                    'id' => $updated->id,
                    'status' => $updated->status,
                    'customerConfirmedCompletion' => $updated->customerConfirmedCompletion
                ],
                'review' => [
                    'id' => $review->id,
                    'rating' => $review->rating
                ]
            ], 200);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to confirm completion'], 500);
        }
    }

    /**
     * Send message
     */
    public function sendMessage() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $receiverId = $data['receiverId'] ?? '';
        $content = trim($data['content'] ?? '');
        $bookingId = $data['bookingId'] ?? null;
        
        if (empty($receiverId) || empty($content)) {
            $this->json(['success' => false, 'message' => 'Receiver ID and content are required'], 400);
            return;
        }
        
        $messageData = [
            'senderId' => $user->id,
            'receiverId' => $receiverId,
            'bookingId' => $bookingId,
            'content' => $content,
            'isRead' => false
        ];
        
        $messageId = uniqid('msg_');
        $message = MessageService::createMessage($messageId, $messageData);
        
        if ($message) {
            $this->json([
                'success' => true,
                'message' => 'Message sent',
                'messageData' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'createdAt' => $message->createdAt
                ]
            ], 201);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    /**
     * Get conversation
     */
    public function getConversation() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $otherUserId = $this->params['userId'] ?? null;
        if (!$otherUserId) {
            $this->json(['success' => false, 'message' => 'User ID required'], 400);
            return;
        }
        
        $messages = MessageService::getConversation($user->id, $otherUserId);
        
        // Mark as read
        MessageService::markAsRead($user->id, $otherUserId);
        
        $messagesArray = [];
        foreach ($messages as $message) {
            $messagesArray[] = [
                'id' => $message->id,
                'senderId' => $message->senderId,
                'receiverId' => $message->receiverId,
                'content' => $message->content,
                'isRead' => $message->isRead,
                'createdAt' => $message->createdAt
            ];
        }
        
        $this->json([
            'success' => true,
            'messages' => $messagesArray
        ], 200);
    }

    /**
     * Initialize Paystack payment
     */
    public function initializePayment() {
        $user = $this->getCurrentUser();
        
        if (!$user || $user->role !== 'customer') {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $bookingId = $data['bookingId'] ?? null;
        $amount = $data['amount'] ?? null;
        $email = $data['email'] ?? $user->email ?? null;
        
        if (!$bookingId || !$amount || !$email) {
            $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
            return;
        }
        
        // Get booking details
        $booking = BookingService::getBooking($bookingId);
        if (!$booking || $booking->customerId !== $user->id) {
            $this->json(['success' => false, 'message' => 'Booking not found or unauthorized'], 404);
            return;
        }
        
        // Check if booking is in progress
        if ($booking->status !== 'inProgress') {
            $this->json(['success' => false, 'message' => 'Payment can only be made for in-progress bookings'], 400);
            return;
        }
        
        // Check if already paid
        if ($booking->paymentStatus === 'paid') {
            $this->json(['success' => false, 'message' => 'This booking has already been paid'], 400);
            return;
        }
        
        // Generate unique reference
        $reference = 'SKILL_' . time() . '_' . substr($bookingId, 0, 8) . '_' . rand(1000, 9999);
        
        // Initialize Paystack payment
        $paystackUrl = 'https://api.paystack.co/transaction/initialize';
        $amountInKobo = (int)($amount * 100); // Convert to kobo (Paystack uses smallest currency unit)
        
        $postData = [
            'email' => $email,
            'amount' => $amountInKobo,
            'reference' => $reference,
            'callback_url' => PAYSTACK_CALLBACK_URL . '?booking=' . $bookingId,
            'metadata' => [
                'bookingId' => $bookingId,
                'customerId' => $user->id,
                'vendorId' => $booking->vendorId,
                'serviceTitle' => $booking->serviceTitle ?? 'Service Booking'
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paystackUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === true) {
            // Create earning record with pending status
            $earningId = 'earning_' . time() . '_' . substr($bookingId, 0, 8);
            $earningData = [
                'bookingId' => $bookingId,
                'customerId' => $user->id,
                'vendorId' => $booking->vendorId,
                'serviceTitle' => $booking->serviceTitle ?? 'Service Booking',
                'amount' => (float)$amount,
                'currency' => 'GHS',
                'paymentStatus' => 'pending',
                'paystackReference' => $reference,
                'paymentMethod' => 'card'
            ];
            
            EarningsService::createEarning($earningId, $earningData);
            
            $this->json([
                'success' => true,
                'message' => 'Payment initialized',
                'authorization_url' => $responseData['data']['authorization_url'],
                'access_code' => $responseData['data']['access_code'],
                'reference' => $reference
            ], 200);
        } else {
            $errorMessage = $responseData['message'] ?? 'Failed to initialize payment';
            $this->json(['success' => false, 'message' => $errorMessage], 500);
        }
    }

    /**
     * Payment callback handler (called by Paystack after payment)
     */
    public function paymentCallback() {
        $reference = $_GET['reference'] ?? null;
        $bookingId = $_GET['booking'] ?? null;
        
        if (!$reference) {
            // Redirect to bookings page with error
            header('Location: ' . url('customer/bookings') . '?payment=error');
            exit;
        }
        
        // Verify payment with Paystack
        $paystackUrl = 'https://api.paystack.co/transaction/verify/' . $reference;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paystackUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . PAYSTACK_SECRET_KEY
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === true) {
            $transaction = $responseData['data'];
            
            // Check if payment was successful
            if ($transaction['status'] === 'success') {
                // Find earning record by reference
                $earning = EarningsService::getEarningByReference($reference);
                
                if ($earning) {
                    // Update earning record
                    $updateData = [
                        'paymentStatus' => 'success',
                        'paystackTransactionId' => $transaction['id'] ?? null,
                        'paidAt' => time()
                    ];
                    EarningsService::updateEarning($earning->id, $updateData);
                    
                    // Update booking payment status
                    if ($bookingId) {
                        BookingService::updateBookingStatus($bookingId, 'inProgress', [
                            'paymentStatus' => 'paid'
                        ]);
                    }
                    
                    // Redirect to booking details with success
                    if ($bookingId) {
                        header('Location: ' . url('customer/bookings/' . $bookingId) . '?payment=success');
                    } else {
                        header('Location: ' . url('customer/bookings') . '?payment=success');
                    }
                    exit;
                }
            }
        }
        
        // Payment failed or verification failed
        if ($bookingId) {
            header('Location: ' . url('customer/bookings/' . $bookingId) . '?payment=failed');
        } else {
            header('Location: ' . url('customer/bookings') . '?payment=failed');
        }
        exit;
    }

    /**
     * Verify payment status
     */
    public function verifyPayment() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }
        
        $reference = $this->params['reference'] ?? null;
        if (!$reference) {
            $this->json(['success' => false, 'message' => 'Reference required'], 400);
            return;
        }
        
        // Verify with Paystack
        $paystackUrl = 'https://api.paystack.co/transaction/verify/' . $reference;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paystackUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . PAYSTACK_SECRET_KEY
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === true) {
            $transaction = $responseData['data'];
            
            $this->json([
                'success' => true,
                'status' => $transaction['status'],
                'amount' => $transaction['amount'] / 100, // Convert from kobo
                'reference' => $transaction['reference'],
                'paid_at' => $transaction['paid_at'] ?? null
            ], 200);
        } else {
            $this->json(['success' => false, 'message' => 'Payment verification failed'], 400);
        }
    }
}

