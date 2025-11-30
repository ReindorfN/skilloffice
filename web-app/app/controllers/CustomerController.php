<?php
require_once 'app/core/Controller.php';
require_once 'app/services/VendorProfileService.php';
require_once 'app/services/ServiceService.php';
require_once 'app/services/BookingService.php';
require_once 'app/services/MessageService.php';

/**
 * Customer Controller
 */
class CustomerController extends Controller {
    
    public function __construct($params = []) {
        parent::__construct($params);
        $this->requireAuth();
        
        // Check if user is customer
        $user = $this->getCurrentUser();
        if ($user && $user->role !== 'customer') {
            $this->redirect('vendor/dashboard');
        }
    }

    /**
     * Customer home
     */
    public function home() {
        $user = $this->getCurrentUser();
        
        // Get featured vendors
        $featuredVendors = VendorProfileService::getFeaturedVendors(6);
        
        // Get popular services
        $popularServices = ServiceService::getPopularServices();
        
        // Get customer bookings for overview
        $allBookings = BookingService::getCustomerBookings($user->id);
        $activeBookings = array_filter($allBookings, function($booking) {
            return in_array($booking->status, ['pending', 'accepted', 'inProgress']);
        });
        $recentBookings = array_slice($allBookings, 0, 3);
        
        // Get service categories
        $serviceCategories = [
            ['name' => 'Plumbing', 'icon' => '🔧', 'color' => '#3B82F6'],
            ['name' => 'Electrical', 'icon' => '⚡', 'color' => '#F59E0B'],
            ['name' => 'Carpentry', 'icon' => '🪚', 'color' => '#10B981'],
            ['name' => 'Painting', 'icon' => '🎨', 'color' => '#EF4444'],
            ['name' => 'Cleaning', 'icon' => '🧹', 'color' => '#8B5CF6'],
            ['name' => 'Masonry', 'icon' => '🧱', 'color' => '#EC4899'],
            ['name' => 'Welding', 'icon' => '🔥', 'color' => '#F97316'],
            ['name' => 'Tiling', 'icon' => '🔲', 'color' => '#06B6D4']
        ];
        
        // Calculate stats
        $totalBookings = count($allBookings);
        $completedBookings = count(array_filter($allBookings, function($b) {
            return $b->status === 'completed';
        }));
        
        $this->render('customer/home', [
            'user' => $user,
            'featuredVendors' => $featuredVendors,
            'popularServices' => $popularServices,
            'activeBookings' => $activeBookings,
            'recentBookings' => $recentBookings,
            'serviceCategories' => $serviceCategories,
            'totalBookings' => $totalBookings,
            'completedBookings' => $completedBookings,
            'currentPage' => 'home'
        ]);
    }

    /**
     * Search
     */
    public function search() {
        $user = $this->getCurrentUser();
        $query = trim($_GET['q'] ?? '');
        
        $vendorResults = [];
        $serviceResults = [];
        
        if (!empty($query)) {
            // Search vendors
            $vendorResults = VendorProfileService::searchVendors($query);
            
            // Search services
            $serviceResults = ServiceService::searchServices($query);
        }
        
        $this->render('customer/search', [
            'user' => $user,
            'query' => $query,
            'vendorResults' => $vendorResults,
            'serviceResults' => $serviceResults,
            'currentPage' => 'search'
        ]);
    }

    /**
     * Bookings
     */
    public function bookings() {
        $user = $this->getCurrentUser();
        $allBookings = BookingService::getCustomerBookings($user->id);
        
        // Sort bookings by status
        $pendingBookings = [];
        $inProgressBookings = [];
        $completedBookings = [];
        
        foreach ($allBookings as $booking) {
            if ($booking->status === 'pending') {
                $pendingBookings[] = $booking;
            } elseif ($booking->status === 'inProgress') {
                $inProgressBookings[] = $booking;
            } elseif ($booking->status === 'completed') {
                $completedBookings[] = $booking;
            }
        }
        
        $this->render('customer/bookings', [
            'user' => $user,
            'pendingBookings' => $pendingBookings,
            'inProgressBookings' => $inProgressBookings,
            'completedBookings' => $completedBookings,
            'currentPage' => 'bookings'
        ]);
    }

    /**
     * View vendor profile
     */
    public function viewVendor() {
        $user = $this->getCurrentUser();
        $vendorId = $this->params['id'] ?? null;
        
        if (!$vendorId) {
            $this->redirect('customer/search');
        }
        
        $vendor = VendorProfileService::getProfile($vendorId);
        if (!$vendor) {
            $this->redirect('customer/search');
        }
        
        // Get vendor services
        $services = ServiceService::getVendorServices($vendorId);
        
        // Get vendor reviews and ratings
        require_once 'app/services/ReviewService.php';
        $reviews = ReviewService::getVendorReviews($vendorId, 10); // Get latest 10 reviews
        $ratingData = ReviewService::getVendorAverageRating($vendorId);
        
        $this->render('customer/vendor-profile', [
            'user' => $user,
            'vendor' => $vendor,
            'services' => $services,
            'reviews' => $reviews,
            'averageRating' => $ratingData['rating'],
            'totalReviews' => $ratingData['count'],
            'currentPage' => 'search'
        ]);
    }

    /**
     * View service details
     */
    public function viewService() {
        $user = $this->getCurrentUser();
        $serviceId = $this->params['id'] ?? null;
        
        if (!$serviceId) {
            $this->redirect('customer/search');
        }
        
        $service = ServiceService::getService($serviceId);
        if (!$service) {
            $this->redirect('customer/search');
        }
        
        // Get vendor profile
        $vendor = VendorProfileService::getProfile($service->vendorId);
        
        // Get service reviews
        require_once 'app/services/ReviewService.php';
        $reviews = ReviewService::getServiceReviews($serviceId);
        
        $this->render('customer/service-details', [
            'user' => $user,
            'service' => $service,
            'vendor' => $vendor,
            'reviews' => $reviews,
            'currentPage' => 'search'
        ]);
    }

    /**
     * Book service
     */
    public function book() {
        $user = $this->getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle booking creation via API
            $this->redirect('customer/bookings');
        }
        
        $vendorId = $_GET['vendor'] ?? null;
        $serviceId = $_GET['service'] ?? null;
        
        $vendor = null;
        $service = null;
        
        if ($vendorId) {
            $vendor = VendorProfileService::getProfile($vendorId);
        }
        
        if ($serviceId) {
            $service = ServiceService::getService($serviceId);
            if ($service && !$vendor) {
                $vendor = VendorProfileService::getProfile($service->vendorId);
            }
        }
        
        $this->render('customer/book', [
            'user' => $user,
            'vendor' => $vendor,
            'service' => $service,
            'currentPage' => 'bookings'
        ]);
    }

    /**
     * Booking details
     */
    public function bookingDetails() {
        $user = $this->getCurrentUser();
        $bookingId = $this->params['id'] ?? null;
        
        if (!$bookingId) {
            $this->redirect('customer/bookings');
        }
        
        $booking = BookingService::getBooking($bookingId);
        
        if (!$booking || $booking->customerId !== $user->id) {
            $this->redirect('customer/bookings');
        }
        
        // Get vendor profile
        $vendor = VendorProfileService::getProfile($booking->vendorId);
        
        // Get payment/earning record for this booking
        require_once 'app/services/EarningsService.php';
        $earnings = EarningsService::getVendorEarnings($booking->vendorId);
        $paymentRecord = null;
        foreach ($earnings as $earning) {
            if ($earning->bookingId === $bookingId && $earning->paymentStatus === 'success') {
                $paymentRecord = $earning;
                break;
            }
        }
        
        $this->render('customer/booking-details', [
            'user' => $user,
            'booking' => $booking,
            'vendor' => $vendor,
            'paymentRecord' => $paymentRecord,
            'currentPage' => 'bookings'
        ]);
    }

    /**
     * Chat
     */
    public function chat() {
        $user = $this->getCurrentUser();
        
        require_once 'app/services/MessageService.php';
        $conversations = MessageService::getUserConversations($user->id);
        
        // Get all vendors for selection
        $vendors = VendorProfileService::getAllVendors();
        
        // Check if vendor parameter is passed (for auto-opening modal)
        $selectedVendorId = $_GET['vendor'] ?? null;
        
        $this->render('customer/chat', [
            'user' => $user,
            'conversations' => $conversations,
            'vendors' => $vendors,
            'selectedVendorId' => $selectedVendorId,
            'currentPage' => 'chat'
        ]);
    }

    /**
     * Chat with specific user
     */
    public function chatWith() {
        $user = $this->getCurrentUser();
        $otherUserId = $this->params['userId'] ?? null;
        
        if (!$otherUserId) {
            $this->redirect('customer/chat');
        }
        
        // Get other user info
        require_once 'app/services/UserService.php';
        $otherUser = UserService::getUser($otherUserId);
        
        if (!$otherUser) {
            $this->redirect('customer/chat');
        }
        
        // Use real-time Firebase chat view
        $this->render('customer/chat-with-realtime', [
            'user' => $user,
            'otherUser' => $otherUser,
            'currentPage' => 'chat'
        ]);
    }

    /**
     * Profile
     */
    public function profile() {
        $user = $this->getCurrentUser();
        
        $this->render('customer/profile', [
            'user' => $user,
            'currentPage' => 'profile'
        ]);
    }

    /**
     * Edit profile
     */
    public function editProfile() {
        $user = $this->getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['fullName'] ?? '';
            $phoneNumber = $_POST['phoneNumber'] ?? '';
            
            // Update user
            require_once 'app/services/UserService.php';
            $userData = [
                'fullName' => $fullName,
                'phoneNumber' => $phoneNumber,
                'updatedAt' => time()
            ];
            
            $updatedUser = UserService::updateUser($user->id, $userData);
            
            if ($updatedUser) {
                $_SESSION['user'] = $updatedUser;
                $this->redirect('customer/profile');
            } else {
                $error = 'Failed to update profile';
                $this->render('customer/edit-profile', ['user' => $user, 'error' => $error]);
            }
        } else {
            $this->render('customer/edit-profile', ['user' => $user]);
        }
    }
}

