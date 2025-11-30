<?php
$title = 'Home';
require 'app/views/layouts/header.php';
?>

<div class="customer-home">
    <!-- Hero Section with Welcome -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Welcome back, <?php 
                $userName = isset($user) && $user ? ($user->fullName ?? 'Customer') : 'Customer';
                $nameParts = explode(' ', $userName);
                $firstName = $nameParts[0] ?? 'Customer';
                echo htmlspecialchars($firstName);
            ?>! 👋</h1>
            <p class="hero-subtitle">Find skilled artisans near you for all your service needs</p>
        </div>
        <div class="hero-stats">
            <div class="stat-item">
                <div class="stat-value"><?php echo count($activeBookings ?? []); ?></div>
                <div class="stat-label">Active Bookings</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $completedBookings ?? 0; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
    </div>

    <!-- Enhanced Search Bar -->
    <div class="search-section">
        <div class="search-card">
            <form method="GET" action="<?php echo url('customer/search'); ?>" class="search-form">
                <div class="search-input-group">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="q" class="search-input" placeholder="Search for services, artisans, or skills...">
                    <button type="submit" class="btn btn-primary search-btn">Search</button>
                </div>
            </form>
            <div class="quick-filters">
                <span class="quick-filters-label">Quick filters:</span>
                <a href="<?php echo url('customer/search?q=plumbing'); ?>" class="quick-filter-tag">Plumbing</a>
                <a href="<?php echo url('customer/search?q=electrical'); ?>" class="quick-filter-tag">Electrical</a>
                <a href="<?php echo url('customer/search?q=carpentry'); ?>" class="quick-filter-tag">Carpentry</a>
                <a href="<?php echo url('customer/search?q=cleaning'); ?>" class="quick-filter-tag">Cleaning</a>
            </div>
        </div>
    </div>

    <!-- Service Categories -->
    <section class="section">
        <div class="section-header">
            <h2>Browse by Category</h2>
            <a href="<?php echo url('customer/search'); ?>" class="view-all-link">View All</a>
        </div>
        <div class="categories-grid">
            <?php foreach ($serviceCategories ?? [] as $category): ?>
                <a href="<?php echo url('customer/search?q=' . urlencode(strtolower($category['name']))); ?>" class="category-card">
                    <div class="category-icon" style="background: linear-gradient(135deg, <?php echo $category['color']; ?>, <?php echo $category['color']; ?>dd);">
                        <?php echo $category['icon']; ?>
                    </div>
                    <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Active Bookings Overview -->
    <?php if (!empty($activeBookings)): ?>
    <section class="section">
        <div class="section-header">
            <h2>Active Bookings</h2>
            <a href="<?php echo url('customer/bookings'); ?>" class="view-all-link">View All</a>
        </div>
        <div class="bookings-preview">
            <?php foreach (array_slice($activeBookings, 0, 3) as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <h3><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service'); ?></h3>
                        <span class="status-badge status-<?php echo htmlspecialchars($booking->status ?? 'pending'); ?>">
                            <?php echo ucfirst($booking->status ?? 'Pending'); ?>
                        </span>
                    </div>
                    <div class="booking-details">
                        <p class="booking-detail">
                            <span class="booking-icon">📅</span>
                            <?php 
                            $scheduledDate = $booking->scheduledDate ?? $booking->createdAt ?? time();
                            echo date('M d, Y', $scheduledDate);
                            ?>
                        </p>
                        <p class="booking-detail">
                            <span class="booking-icon">📍</span>
                            <?php echo htmlspecialchars($booking->location ?? 'Location not specified'); ?>
                        </p>
                        <?php if ($booking->quotedPrice): ?>
                            <p class="booking-detail">
                                <span class="booking-icon">💰</span>
                                <strong>₵<?php echo number_format($booking->quotedPrice); ?></strong>
                            </p>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo url('customer/bookings'); ?>" class="btn btn-outline btn-sm">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Artisans -->
    <?php if (!empty($featuredVendors)): ?>
    <section class="section">
        <div class="section-header">
            <h2>⭐ Featured Artisans</h2>
            <a href="<?php echo url('customer/search'); ?>" class="view-all-link">View All</a>
        </div>
        <div class="vendors-grid">
            <?php foreach ($featuredVendors as $vendor): ?>
                <div class="vendor-card">
                    <div class="vendor-header">
                        <div class="vendor-avatar">
                            <?php 
                            $initials = '';
                            if (isset($vendor->businessName) && $vendor->businessName) {
                                $initials = strtoupper(substr($vendor->businessName, 0, 2));
                            } else {
                                $initials = 'VA';
                            }
                            echo htmlspecialchars($initials);
                            ?>
                        </div>
                        <div class="vendor-info">
                            <h3><?php echo htmlspecialchars($vendor->businessName ?? 'Artisan'); ?></h3>
                            <?php if ($vendor->rating): ?>
                                <div class="vendor-rating">
                                    <span class="stars">⭐</span>
                                    <span><?php echo number_format($vendor->rating, 1); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($vendor->location): ?>
                        <p class="vendor-location">
                            <span class="location-icon">📍</span>
                            <?php echo htmlspecialchars($vendor->location); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ($vendor->specializations): ?>
                        <div class="vendor-specializations">
                            <?php 
                            $specs = is_array($vendor->specializations) 
                                ? $vendor->specializations 
                                : explode(',', $vendor->specializations);
                            foreach (array_slice($specs, 0, 3) as $spec): 
                            ?>
                                <span class="specialization-tag"><?php echo htmlspecialchars(trim($spec)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($vendor->hourlyRate): ?>
                        <p class="vendor-rate">
                            <strong class="text-primary">₵<?php echo number_format($vendor->hourlyRate); ?>/hr</strong>
                        </p>
                    <?php endif; ?>
                    <a href="<?php echo url('customer/search?vendor=' . urlencode($vendor->id ?? '')); ?>" class="btn btn-primary btn-sm vendor-view-btn">View Profile</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Popular Services -->
    <?php if (!empty($popularServices)): ?>
    <section class="section">
        <div class="section-header">
            <h2>🔥 Popular Services</h2>
            <a href="<?php echo url('customer/search'); ?>" class="view-all-link">View All</a>
        </div>
        <?php foreach ($popularServices as $category => $services): ?>
            <div class="services-category">
                <h3 class="services-category-title"><?php echo htmlspecialchars($category); ?></h3>
                <div class="services-grid-vertical">
                    <?php foreach (array_slice($services, 0, 4) as $service): ?>
                        <div class="service-card-vertical">
                            <div class="service-header">
                                <h4><?php echo htmlspecialchars($service->title); ?></h4>
                                <p class="service-price">₵<?php echo number_format($service->price); ?></p>
                            </div>
                            <p class="service-description"><?php echo htmlspecialchars(substr($service->description ?? '', 0, 100)); ?><?php echo strlen($service->description ?? '') > 100 ? '...' : ''; ?></p>
                            <div class="service-footer">
                                <a href="<?php echo url('customer/service/' . urlencode($service->id ?? '')); ?>" class="btn btn-outline btn-sm">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- Quick Actions -->
    <section class="section">
        <div class="section-header">
            <h2>Quick Actions</h2>
        </div>
        <div class="quick-actions-grid">
            <a href="<?php echo url('customer/bookings'); ?>" class="quick-action-card">
                <div class="quick-action-icon">📋</div>
                <h3>My Bookings</h3>
                <p>View and manage your service requests</p>
            </a>
            <a href="<?php echo url('customer/search'); ?>" class="quick-action-card">
                <div class="quick-action-icon">🔍</div>
                <h3>Search Artisans</h3>
                <p>Find skilled professionals near you</p>
            </a>
            <a href="<?php echo url('customer/chat'); ?>" class="quick-action-card">
                <div class="quick-action-icon">💬</div>
                <h3>Messages</h3>
                <p>Chat with artisans</p>
            </a>
            <a href="<?php echo url('customer/profile'); ?>" class="quick-action-card">
                <div class="quick-action-icon">👤</div>
                <h3>Profile</h3>
                <p>Manage your account settings</p>
            </a>
        </div>
    </section>
</div>

<style>
.customer-home {
    max-width: 100%;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-lg);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--spacing-lg);
}

.hero-content {
    flex: 1;
    min-width: 300px;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 var(--spacing-sm) 0;
}

.hero-subtitle {
    font-size: 1.125rem;
    opacity: 0.9;
    margin: 0;
}

.hero-stats {
    display: flex;
    gap: var(--spacing-xl);
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: var(--spacing-xs);
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.8;
}

/* Search Section */
.search-section {
    margin-bottom: var(--spacing-xl);
}

.search-card {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    box-shadow: var(--shadow-md);
}

.search-form {
    margin-bottom: var(--spacing-md);
}

.search-input-group {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    background-color: white;
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    padding: var(--spacing-sm) var(--spacing-md);
    transition: border-color 0.2s;
}

.search-input-group:focus-within {
    border-color: var(--primary);
}

.search-icon {
    font-size: 1.25rem;
    color: var(--text-secondary);
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1rem;
    padding: var(--spacing-sm) 0;
}

.search-btn {
    padding: var(--spacing-sm) var(--spacing-lg);
    white-space: nowrap;
}

.quick-filters {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.quick-filters-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.quick-filter-tag {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-md);
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary);
    border-radius: var(--radius-full);
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.quick-filter-tag:hover {
    background-color: var(--primary);
    color: white;
}

/* Section Styles */
.section {
    margin-bottom: var(--spacing-2xl);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.section-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.view-all-link {
    font-size: 0.9375rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.view-all-link:hover {
    text-decoration: underline;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: var(--spacing-md);
}

.category-card {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    text-align: center;
    text-decoration: none;
    color: var(--text-primary);
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
    border: 2px solid transparent;
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}

.category-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto var(--spacing-md);
}

.category-name {
    font-size: 0.9375rem;
    font-weight: 600;
    margin: 0;
}

/* Bookings Preview */
.bookings-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-md);
}

.booking-card {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
    border-left: 4px solid var(--primary);
}

.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-md);
}

.booking-header h3 {
    font-size: 1.125rem;
    margin: 0;
    flex: 1;
}

.status-badge {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-sm);
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: var(--radius-sm);
    text-transform: uppercase;
}

.status-pending {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.status-accepted {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.status-inprogress {
    background-color: rgba(59, 130, 246, 0.1);
    color: var(--primary);
}

.booking-details {
    margin-bottom: var(--spacing-md);
}

.booking-detail {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xs);
    font-size: 0.9375rem;
}

.booking-icon {
    font-size: 1rem;
    width: 20px;
    text-align: center;
}

/* Vendors Grid */
.vendors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-lg);
}

.vendor-card {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
}

.vendor-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.vendor-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.vendor-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.vendor-info {
    flex: 1;
}

.vendor-info h3 {
    font-size: 1.125rem;
    margin: 0 0 var(--spacing-xs) 0;
}

.vendor-rating {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.875rem;
}

.stars {
    font-size: 1rem;
}

.vendor-location {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-sm);
}

.location-icon {
    font-size: 1rem;
}

.vendor-specializations {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-sm);
}

.specialization-tag {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-sm);
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
}

.vendor-rate {
    margin-bottom: var(--spacing-md);
}

.vendor-view-btn {
    width: 100%;
    text-align: center;
}

/* Services Grid */
.services-category {
    margin-bottom: var(--spacing-xl);
}

.services-category-title {
    font-size: 1.25rem;
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-md);
}

.service-card,
.service-card-vertical {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.service-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-sm);
}

.service-header h4 {
    font-size: 1.125rem;
    margin: 0;
    flex: 1;
}

.service-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
    margin: 0;
}

.service-description {
    font-size: 0.9375rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-md);
    line-height: 1.5;
}

.service-footer {
    display: flex;
    justify-content: flex-end;
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.quick-action-card {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    text-align: center;
    text-decoration: none;
    color: var(--text-primary);
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
    border: 2px solid transparent;
}

.quick-action-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary);
}

.quick-action-icon {
    font-size: 3rem;
    margin-bottom: var(--spacing-md);
}

.quick-action-card h3 {
    font-size: 1.125rem;
    margin: 0 0 var(--spacing-xs) 0;
}

.quick-action-card p {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-section {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-stats {
        width: 100%;
        justify-content: space-around;
    }
    
    .categories-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .vendors-grid,
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>
