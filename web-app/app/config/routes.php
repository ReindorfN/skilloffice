<?php
/**
 * Application Routes
 */

// Authentication Routes
$router->add('', ['controller' => 'auth', 'action' => 'splash']);
$router->add('splash', ['controller' => 'auth', 'action' => 'splash']);
$router->add('welcome', ['controller' => 'auth', 'action' => 'welcome']);
$router->add('role-selection', ['controller' => 'auth', 'action' => 'roleSelection']);
$router->add('login', ['controller' => 'auth', 'action' => 'login']);
$router->add('register', ['controller' => 'auth', 'action' => 'register']);
$router->add('logout', ['controller' => 'auth', 'action' => 'logout']);

// Customer Routes
$router->add('customer/home', ['controller' => 'customer', 'action' => 'home']);
$router->add('customer/search', ['controller' => 'customer', 'action' => 'search']);
$router->add('customer/vendor/{id}', ['controller' => 'customer', 'action' => 'viewVendor']);
$router->add('customer/service/{id}', ['controller' => 'customer', 'action' => 'viewService']);
$router->add('customer/book', ['controller' => 'customer', 'action' => 'book']);
$router->add('customer/bookings', ['controller' => 'customer', 'action' => 'bookings']);
$router->add('customer/bookings/{id}', ['controller' => 'customer', 'action' => 'bookingDetails']);
// Note: More specific routes should come first
$router->add('customer/chat/{userId}', ['controller' => 'customer', 'action' => 'chatWith']);
$router->add('customer/chat', ['controller' => 'customer', 'action' => 'chat']);
$router->add('customer/profile', ['controller' => 'customer', 'action' => 'profile']);
$router->add('customer/profile/edit', ['controller' => 'customer', 'action' => 'editProfile']);

// Vendor Routes
$router->add('vendor/dashboard', ['controller' => 'vendor', 'action' => 'dashboard']);
$router->add('vendor/jobs', ['controller' => 'vendor', 'action' => 'jobs']);
$router->add('vendor/jobs/{id}', ['controller' => 'vendor', 'action' => 'jobDetails']);
$router->add('vendor/profile', ['controller' => 'vendor', 'action' => 'profile']);
$router->add('vendor/profile/edit', ['controller' => 'vendor', 'action' => 'editProfile']);
$router->add('vendor/earnings', ['controller' => 'vendor', 'action' => 'earnings']);
$router->add('vendor/portfolio', ['controller' => 'vendor', 'action' => 'portfolio']);
$router->add('vendor/services', ['controller' => 'vendor', 'action' => 'services']);
$router->add('vendor/services/create', ['controller' => 'vendor', 'action' => 'createService']);
$router->add('vendor/services/edit/{id}', ['controller' => 'vendor', 'action' => 'editService']);

// API Routes
$router->add('api/test/connection', ['controller' => 'api', 'action' => 'testConnection']);
$router->add('api/auth/login', ['controller' => 'api', 'action' => 'login']);
$router->add('api/auth/register', ['controller' => 'api', 'action' => 'register']);
$router->add('api/auth/logout', ['controller' => 'api', 'action' => 'logout']);
$router->add('api/user/profile', ['controller' => 'api', 'action' => 'getUserProfile']);
$router->add('api/user/profile/update', ['controller' => 'api', 'action' => 'updateUserProfile']);
$router->add('api/booking/create', ['controller' => 'api', 'action' => 'createBooking']);
$router->add('api/booking/{id}/status', ['controller' => 'api', 'action' => 'updateBookingStatus']);
$router->add('api/booking/{id}/confirm', ['controller' => 'api', 'action' => 'confirmCompletion']);
$router->add('api/message/send', ['controller' => 'api', 'action' => 'sendMessage']);
$router->add('api/message/conversation/{userId}', ['controller' => 'api', 'action' => 'getConversation']);
$router->add('api/payment/initialize', ['controller' => 'api', 'action' => 'initializePayment']);
$router->add('api/payment/callback', ['controller' => 'api', 'action' => 'paymentCallback']);
$router->add('api/payment/verify/{reference}', ['controller' => 'api', 'action' => 'verifyPayment']);
$router->add('api/review/create', ['controller' => 'api', 'action' => 'createReview']);
$router->add('api/review/booking/{bookingId}', ['controller' => 'api', 'action' => 'getReviewByBooking']);

