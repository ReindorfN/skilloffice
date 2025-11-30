<?php
require_once 'app/core/Controller.php';
require_once 'app/services/VendorProfileService.php';
require_once 'app/services/ServiceService.php';
require_once 'app/services/BookingService.php';
require_once 'app/services/EarningsService.php';

/**
 * Vendor Controller
 */
class VendorController extends Controller {
    
    public function __construct($params = []) {
        parent::__construct($params);
        $this->requireAuth();
        
        // Check if user is vendor
        $user = $this->getCurrentUser();
        if ($user && $user->role !== 'artisan') {
            $this->redirect('customer/home');
        }
    }

    /**
     * Dashboard
     */
    public function dashboard() {
        $user = $this->getCurrentUser();
        
        // Get bookings by status
        $pendingBookings = BookingService::getVendorBookings($user->id, 'pending');
        $acceptedBookings = BookingService::getVendorBookings($user->id, 'accepted');
        $completedBookings = BookingService::getVendorBookings($user->id, 'completed');
        $allBookings = BookingService::getVendorBookings($user->id);
        
        // Get earnings from earnings collection
        $totalEarnings = EarningsService::getTotalEarnings($user->id);
        $monthlyEarnings = EarningsService::getMonthlyEarnings($user->id);
        
        // Calculate pending earnings (from bookings that are in progress but not yet paid)
        $pendingEarnings = 0;
        $inProgressBookings = BookingService::getVendorBookings($user->id, 'inProgress');
        foreach ($inProgressBookings as $booking) {
            if (($booking->paymentStatus ?? 'pending') === 'pending') {
                $pendingEarnings += $booking->quotedPrice ?? 0;
            }
        }
        
        // Get upcoming appointments (accepted bookings sorted by date)
        $upcomingAppointments = $acceptedBookings;
        usort($upcomingAppointments, function($a, $b) {
            $dateA = $a->scheduledDate ?? $a->createdAt ?? 0;
            $dateB = $b->scheduledDate ?? $b->createdAt ?? 0;
            return $dateA - $dateB;
        });
        // Get only future appointments
        $upcomingAppointments = array_filter($upcomingAppointments, function($booking) {
            $scheduledDate = $booking->scheduledDate ?? $booking->createdAt ?? 0;
            return $scheduledDate >= time();
        });
        $upcomingAppointments = array_slice($upcomingAppointments, 0, 5); // Limit to 5
        
        // Calculate performance metrics
        $totalJobs = count($allBookings);
        
        // Active jobs = inProgress bookings (approved but not completed)
        $activeJobs = count($inProgressBookings);
        
        // Completed jobs = completed bookings that have been confirmed by both parties
        $confirmedCompletedBookings = array_filter($completedBookings, function($booking) {
            return ($booking->customerConfirmedCompletion ?? false);
        });
        $completedCount = count($confirmedCompletedBookings);
        $completionRate = $totalJobs > 0 ? round(($completedCount / $totalJobs) * 100) : 0;
        
        // Get ratings from reviews
        require_once 'app/services/ReviewService.php';
        $ratingData = ReviewService::getVendorAverageRating($user->id);
        $averageRating = $ratingData['rating'];
        $totalReviews = $ratingData['count'];
        
        $this->render('vendor/dashboard', [
            'user' => $user,
            'pendingBookings' => $pendingBookings,
            'acceptedBookings' => $acceptedBookings,
            'completedBookings' => $completedBookings,
            'upcomingAppointments' => $upcomingAppointments,
            'totalEarnings' => $totalEarnings,
            'monthlyEarnings' => $monthlyEarnings,
            'pendingEarnings' => $pendingEarnings,
            'activeJobs' => $activeJobs,
            'completionRate' => $completionRate,
            'averageRating' => $averageRating,
            'totalReviews' => $totalReviews,
            'currentPage' => 'dashboard'
        ]);
    }

    /**
     * Jobs
     */
    public function jobs() {
        $user = $this->getCurrentUser();
        $pendingBookings = BookingService::getVendorBookings($user->id, 'pending');
        $inProgressBookings = BookingService::getVendorBookings($user->id, 'inProgress');
        $completedBookings = BookingService::getVendorBookings($user->id, 'completed');
        $services = ServiceService::getVendorServices($user->id);
        
        $this->render('vendor/jobs', [
            'user' => $user,
            'pendingBookings' => $pendingBookings,
            'inProgressBookings' => $inProgressBookings,
            'completedBookings' => $completedBookings,
            'services' => $services,
            'currentPage' => 'jobs'
        ]);
    }

    /**
     * Job details
     */
    public function jobDetails() {
        $user = $this->getCurrentUser();
        $jobId = $this->params['id'] ?? null;
        
        if (!$jobId) {
            $this->redirect('vendor/jobs');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $booking = BookingService::getBooking($jobId);
            
            if ($booking && $booking->vendorId === $user->id) {
                if ($action === 'accept') {
                    // When accepting, mark as inProgress instead of accepted
                    $updated = BookingService::updateBookingStatus($jobId, 'inProgress');
                    if ($updated) {
                        $this->redirect('vendor/jobs');
                        return;
                    }
                } elseif ($action === 'reject') {
                    $updated = BookingService::updateBookingStatus($jobId, 'rejected');
                    if ($updated) {
                        $this->redirect('vendor/jobs');
                        return;
                    }
                }
            }
        }
        
        $booking = BookingService::getBooking($jobId);
        
        if (!$booking || $booking->vendorId !== $user->id) {
            $this->redirect('vendor/jobs');
        }
        
        $this->render('vendor/job-details', [
            'user' => $user,
            'booking' => $booking
        ]);
    }

    /**
     * Profile
     */
    public function profile() {
        $user = $this->getCurrentUser();
        $profile = VendorProfileService::getProfile($user->id);
        
        $this->render('vendor/profile', [
            'user' => $user,
            'profile' => $profile,
            'currentPage' => 'profile'
        ]);
    }

    /**
     * Edit profile
     */
    public function editProfile() {
        $user = $this->getCurrentUser();
        $profile = VendorProfileService::getProfile($user->id);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profileData = [
                'userId' => $user->id,
                'businessName' => $_POST['businessName'] ?? '',
                'bio' => $_POST['bio'] ?? '',
                'location' => $_POST['location'] ?? '',
                'hourlyRate' => isset($_POST['hourlyRate']) ? (float)$_POST['hourlyRate'] : null,
                'skills' => isset($_POST['skills']) ? explode(',', $_POST['skills']) : [],
                'serviceCategories' => isset($_POST['serviceCategories']) ? explode(',', $_POST['serviceCategories']) : [],
                'updatedAt' => time()
            ];
            
            if (!$profile) {
                $profileData['createdAt'] = time();
            }
            
            $updatedProfile = VendorProfileService::saveProfile($user->id, $profileData);
            
            if ($updatedProfile) {
                $this->redirect('vendor/profile');
            } else {
                $error = 'Failed to save profile';
                $this->render('vendor/edit-profile', ['user' => $user, 'profile' => $profile, 'error' => $error]);
            }
        } else {
            $this->render('vendor/edit-profile', ['user' => $user, 'profile' => $profile]);
        }
    }

    /**
     * Earnings
     */
    public function earnings() {
        $user = $this->getCurrentUser();
        
        // Get all earnings
        $allEarnings = EarningsService::getVendorEarnings($user->id);
        $successfulEarnings = EarningsService::getVendorEarnings($user->id, 'success');
        $pendingEarnings = EarningsService::getVendorEarnings($user->id, 'pending');
        
        // Calculate totals
        $totalEarnings = EarningsService::getTotalEarnings($user->id);
        $monthlyEarnings = EarningsService::getMonthlyEarnings($user->id);
        
        // Calculate pending total
        $pendingTotal = 0.0;
        foreach ($pendingEarnings as $earning) {
            $pendingTotal += $earning->amount;
        }
        
        // Get current month and year for filtering
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');
        
        // Filter earnings by month if requested
        $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : $currentMonth;
        $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;
        
        $monthlyEarningsList = [];
        foreach ($successfulEarnings as $earning) {
            if ($earning->paidAt) {
                $earningMonth = (int)date('m', $earning->paidAt);
                $earningYear = (int)date('Y', $earning->paidAt);
                if ($earningMonth === $selectedMonth && $earningYear === $selectedYear) {
                    $monthlyEarningsList[] = $earning;
                }
            }
        }
        
        $this->render('vendor/earnings', [
            'user' => $user,
            'allEarnings' => $allEarnings,
            'successfulEarnings' => $successfulEarnings,
            'pendingEarnings' => $pendingEarnings,
            'monthlyEarningsList' => $monthlyEarningsList,
            'totalEarnings' => $totalEarnings,
            'monthlyEarnings' => $monthlyEarnings,
            'pendingTotal' => $pendingTotal,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'currentPage' => 'earnings'
        ]);
    }

    /**
     * Portfolio
     */
    public function portfolio() {
        $user = $this->getCurrentUser();
        
        $this->render('vendor/portfolio', [
            'user' => $user
        ]);
    }

    /**
     * Services
     */
    public function services() {
        $user = $this->getCurrentUser();
        $services = ServiceService::getVendorServices($user->id);
        
        $this->render('vendor/services', [
            'user' => $user,
            'services' => $services,
            'currentPage' => 'services'
        ]);
    }

    /**
     * Create service
     */
    public function createService() {
        $user = $this->getCurrentUser();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceData = [
                'vendorId' => $user->id,
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'category' => $_POST['category'] ?? '',
                'price' => isset($_POST['price']) ? (float)$_POST['price'] : 0,
                'priceType' => $_POST['priceType'] ?? 'fixed',
                'duration' => isset($_POST['duration']) ? (int)$_POST['duration'] : null,
                'isActive' => isset($_POST['isActive']) ? (bool)$_POST['isActive'] : true
            ];
            
            $serviceId = uniqid('service_');
            $service = ServiceService::createService($serviceId, $serviceData);
            
            if ($service) {
                $this->redirect('vendor/services');
            } else {
                $error = 'Failed to create service';
                $this->render('vendor/create-service', ['user' => $user, 'error' => $error]);
            }
        } else {
            $this->render('vendor/create-service', ['user' => $user]);
        }
    }

    /**
     * Edit service
     */
    public function editService() {
        $user = $this->getCurrentUser();
        $serviceId = $this->params['id'] ?? null;
        
        if (!$serviceId) {
            $this->redirect('vendor/services');
        }
        
        $service = ServiceService::getService($serviceId);
        
        if (!$service || $service->vendorId !== $user->id) {
            $this->redirect('vendor/services');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $serviceData = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'category' => $_POST['category'] ?? '',
                'price' => isset($_POST['price']) ? (float)$_POST['price'] : 0,
                'priceType' => $_POST['priceType'] ?? 'fixed',
                'duration' => isset($_POST['duration']) ? (int)$_POST['duration'] : null,
                'isActive' => isset($_POST['isActive']) ? (bool)$_POST['isActive'] : true
            ];
            
            $updatedService = ServiceService::updateService($serviceId, $serviceData);
            
            if ($updatedService) {
                $this->redirect('vendor/services');
            } else {
                $error = 'Failed to update service';
                $this->render('vendor/edit-service', ['user' => $user, 'service' => $service, 'error' => $error]);
            }
        } else {
            $this->render('vendor/edit-service', ['user' => $user, 'service' => $service]);
        }
    }
}

