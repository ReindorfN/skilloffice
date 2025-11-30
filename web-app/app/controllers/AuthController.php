<?php
require_once 'app/core/Controller.php';
require_once 'app/services/FirebaseService.php';
require_once 'app/services/UserService.php';
require_once 'app/models/User.php';

/**
 * Auth Controller
 * Handles authentication-related actions
 */
class AuthController extends Controller {
    
    /**
     * Splash screen
     */
    public function splash() {
        // Check if user is already logged in
        if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
            if ($user->role === 'customer') {
                $this->redirect('customer/home');
            } elseif ($user->role === 'artisan') {
                $this->redirect('vendor/dashboard');
            }
        }
        
        $this->render('auth/splash');
    }

    /**
     * Welcome/Onboarding screen
     */
    public function welcome() {
        $this->render('auth/welcome');
    }

    /**
     * Role selection screen
     */
    public function roleSelection() {
        $this->render('auth/role-selection');
    }

    /**
     * Login screen
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields';
                $this->render('auth/login', ['error' => $error]);
                return;
            }
            
            // Sign in with Firebase
            FirebaseService::init();
            $result = FirebaseService::signIn($email, $password);
            
            if (isset($result['data']['idToken'])) {
                // Get user data
                $idToken = $result['data']['idToken'];
                $localId = $result['data']['localId'];
                $email = $result['data']['email'] ?? $email;
                $displayName = $result['data']['displayName'] ?? '';
                
                // Get user from Firestore (pass idToken for authentication)
                $user = UserService::getUser($localId, $idToken);
                
                // If user document doesn't exist in Firestore, create it
                if (!$user) {
                    // Log for debugging
                    error_log("User document not found in Firestore for userId: {$localId}. Creating new document.");
                    
                    // Create user document in Firestore with basic info from Firebase Auth
                    $userData = [
                        'email' => $email,
                        'fullName' => $displayName ?: 'User',
                        'phoneNumber' => '',
                        'role' => 'customer', // Default role
                        'isVerified' => $result['data']['emailVerified'] ?? false,
                        'createdAt' => time(),
                        'updatedAt' => time()
                    ];
                    
                    $user = UserService::createUser($localId, $userData, $idToken);
                    
                    if (!$user) {
                        $error = 'Failed to create user profile in database. Please check Firestore security rules or try registering again.';
                        error_log("Failed to create user document for userId: {$localId}");
                        $this->render('auth/login', ['error' => $error]);
                        return;
                    }
                    
                    error_log("Successfully created user document for userId: {$localId}");
                }
                
                // Store in session
                $_SESSION['user'] = $user;
                $_SESSION['idToken'] = $idToken;
                
                // Redirect based on role
                if ($user->role === 'customer') {
                    $this->redirect('customer/home');
                } elseif ($user->role === 'artisan') {
                    $this->redirect('vendor/dashboard');
                } else {
                    $this->redirect('role-selection');
                }
            } else {
                $error = $result['data']['error']['message'] ?? 'Login failed';
                error_log("Firebase sign-in failed: " . json_encode($result['data'] ?? []));
                $this->render('auth/login', ['error' => $error]);
            }
        } else {
            $this->render('auth/login');
        }
    }

    /**
     * Registration screen
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $fullName = $_POST['fullName'] ?? '';
            $phoneNumber = $_POST['phoneNumber'] ?? '';
            $role = $_POST['role'] ?? 'customer';
            
            if (empty($email) || empty($password) || empty($fullName)) {
                $error = 'Please fill in all required fields';
                $this->render('auth/register', ['error' => $error, 'role' => $role]);
                return;
            }
            
            // Sign up with Firebase
            FirebaseService::init();
            $result = FirebaseService::signUp($email, $password, $fullName);
            
            if (isset($result['data']['idToken'])) {
                $idToken = $result['data']['idToken'];
                $localId = $result['data']['localId'];
                
                // Create user in Firestore
                $userData = [
                    'email' => $email,
                    'fullName' => $fullName,
                    'phoneNumber' => $phoneNumber,
                    'role' => $role,
                    'isVerified' => false,
                    'createdAt' => time(),
                    'updatedAt' => time()
                ];
                
                // Pass idToken for authentication (though Firestore REST API needs access token)
                // For now, this will work if Firestore rules allow unauthenticated writes
                $user = UserService::createUser($localId, $userData, $idToken);
                
                if ($user) {
                    // Store in session
                    $_SESSION['user'] = $user;
                    $_SESSION['idToken'] = $idToken;
                    
                    // Redirect based on role
                    if ($role === 'customer') {
                        $this->redirect('customer/home');
                    } elseif ($role === 'artisan') {
                        $this->redirect('vendor/profile/edit');
                    }
                } else {
                    // Get more detailed error information
                    $error = 'Failed to create user profile in database. Please check your Firestore security rules or try again.';
                    error_log("Registration failed - User created in Firebase Auth but not in Firestore. UserId: {$localId}");
                    $this->render('auth/register', ['error' => $error, 'role' => $role]);
                }
            } else {
                $error = $result['data']['error']['message'] ?? 'Registration failed';
                $this->render('auth/register', ['error' => $error, 'role' => $role]);
            }
        } else {
            $role = $_GET['role'] ?? 'customer';
            $this->render('auth/register', ['role' => $role]);
        }
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        $this->redirect('login');
    }
}

