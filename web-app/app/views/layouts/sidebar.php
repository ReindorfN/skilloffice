<?php
/**
 * Sidebar Navigation Component
 * Displays navigation menu based on user role
 */
if (!isset($user) || !$user) {
    return;
}
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo url($user->role === 'customer' ? 'customer/home' : 'vendor/dashboard'); ?>" class="sidebar-logo">
            <h2 style="margin: 0; color: var(--primary);">SkillOffice</h2>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <?php if ($user->role === 'customer'): ?>
            <!-- Customer Navigation -->
            <a href="<?php echo url('customer/home'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'home') ? 'active' : ''; ?>">
                <span class="nav-icon">🏠</span>
                <span class="nav-label">Home</span>
            </a>
            <a href="<?php echo url('customer/search'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'search') ? 'active' : ''; ?>">
                <span class="nav-icon">🔍</span>
                <span class="nav-label">Search</span>
            </a>
            <a href="<?php echo url('customer/bookings'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'bookings') ? 'active' : ''; ?>">
                <span class="nav-icon">📋</span>
                <span class="nav-label">Bookings</span>
            </a>
            <a href="<?php echo url('customer/chat'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'chat') ? 'active' : ''; ?>">
                <span class="nav-icon">💬</span>
                <span class="nav-label">Chat</span>
            </a>
            <a href="<?php echo url('customer/profile'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'profile') ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-label">Profile</span>
            </a>
        <?php else: ?>
            <!-- Vendor Navigation -->
            <a href="<?php echo url('vendor/dashboard'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="<?php echo url('vendor/jobs'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'jobs') ? 'active' : ''; ?>">
                <span class="nav-icon">💼</span>
                <span class="nav-label">Jobs</span>
            </a>
            <a href="<?php echo url('vendor/services'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'services') ? 'active' : ''; ?>">
                <span class="nav-icon">🛠️</span>
                <span class="nav-label">Services</span>
            </a>
            <a href="<?php echo url('vendor/earnings'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'earnings') ? 'active' : ''; ?>">
                <span class="nav-icon">💰</span>
                <span class="nav-label">Earnings</span>
            </a>
            <a href="<?php echo url('vendor/profile'); ?>" class="nav-item <?php echo (isset($currentPage) && $currentPage === 'profile') ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span>
                <span class="nav-label">Profile</span>
            </a>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php 
                $initials = '';
                if (isset($user->fullName) && $user->fullName) {
                    $names = explode(' ', $user->fullName);
                    $initials = strtoupper(($names[0][0] ?? '') . ($names[1][0] ?? ''));
                } else {
                    $initials = 'U';
                }
                echo htmlspecialchars($initials);
                ?>
            </div>
            <div class="user-details">
                <p class="user-name"><?php echo htmlspecialchars($user->fullName ?? 'User'); ?></p>
                <p class="user-role"><?php echo ucfirst($user->role ?? 'user'); ?></p>
            </div>
        </div>
        <a href="<?php echo url('logout'); ?>" class="nav-item logout">
            <span class="nav-icon">🚪</span>
            <span class="nav-label">Logout</span>
        </a>
    </div>
</aside>

