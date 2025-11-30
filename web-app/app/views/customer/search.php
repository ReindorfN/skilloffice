<?php
$title = 'Search';
require 'app/views/layouts/header.php';
?>

<div class="search-page">
    <!-- Search Header -->
    <div class="search-header">
        <h1>Search Artisans & Services</h1>
        <p class="text-secondary">Find skilled professionals and services near you</p>
    </div>
    
    <!-- Search Bar -->
    <div class="search-section">
        <div class="search-card">
            <form method="GET" action="<?php echo url('customer/search'); ?>" class="search-form">
                <div class="search-input-group">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($query ?? ''); ?>" class="search-input" placeholder="Search for services, artisans, skills, or categories...">
                    <button type="submit" class="btn btn-primary search-btn">Search</button>
                </div>
            </form>
            <div class="quick-filters">
                <span class="quick-filters-label">Quick filters:</span>
                <a href="<?php echo url('customer/search?q=plumbing'); ?>" class="quick-filter-tag">Plumbing</a>
                <a href="<?php echo url('customer/search?q=electrical'); ?>" class="quick-filter-tag">Electrical</a>
                <a href="<?php echo url('customer/search?q=carpentry'); ?>" class="quick-filter-tag">Carpentry</a>
                <a href="<?php echo url('customer/search?q=cleaning'); ?>" class="quick-filter-tag">Cleaning</a>
                <a href="<?php echo url('customer/search?q=welding'); ?>" class="quick-filter-tag">Welding</a>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <?php if (isset($query) && !empty($query)): ?>
        <div class="search-results">
            <!-- Results Summary -->
            <div class="results-summary">
                <h2>Search Results for "<strong><?php echo htmlspecialchars($query); ?></strong>"</h2>
                <p class="text-secondary">
                    Found <?php echo count($vendorResults ?? []); ?> artisan<?php echo count($vendorResults ?? []) !== 1 ? 's' : ''; ?> 
                    and <?php echo count($serviceResults ?? []); ?> service<?php echo count($serviceResults ?? []) !== 1 ? 's' : ''; ?>
                </p>
            </div>

            <!-- Vendor Results -->
            <?php if (!empty($vendorResults)): ?>
                <section class="results-section">
                    <div class="section-header">
                        <h2>👨‍🔧 Artisans (<?php echo count($vendorResults); ?>)</h2>
                    </div>
                    <div class="vendors-grid">
                        <?php foreach ($vendorResults as $vendor): ?>
                            <div class="vendor-card">
                                <div class="vendor-header">
                                    <div class="vendor-avatar">
                                        <?php 
                                        $initials = '';
                                        if (isset($vendor->businessName) && $vendor->businessName) {
                                            $nameParts = explode(' ', $vendor->businessName);
                                            $initials = strtoupper(($nameParts[0][0] ?? '') . ($nameParts[1][0] ?? $nameParts[0][1] ?? ''));
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
                                                <?php if ($vendor->totalReviews): ?>
                                                    <span class="review-count">(<?php echo $vendor->totalReviews; ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($vendor->bio): ?>
                                    <p class="vendor-bio"><?php echo htmlspecialchars(substr($vendor->bio, 0, 120)); ?><?php echo strlen($vendor->bio) > 120 ? '...' : ''; ?></p>
                                <?php endif; ?>
                                
                                <?php if ($vendor->location): ?>
                                    <p class="vendor-location">
                                        <span class="location-icon">📍</span>
                                        <?php echo htmlspecialchars($vendor->location); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($vendor->skills) || !empty($vendor->serviceCategories)): ?>
                                    <div class="vendor-specializations">
                                        <?php 
                                        $specs = [];
                                        if (is_array($vendor->skills) && !empty($vendor->skills)) {
                                            $specs = array_merge($specs, $vendor->skills);
                                        }
                                        if (is_array($vendor->serviceCategories) && !empty($vendor->serviceCategories)) {
                                            $specs = array_merge($specs, $vendor->serviceCategories);
                                        }
                                        foreach (array_slice($specs, 0, 4) as $spec): 
                                        ?>
                                            <span class="specialization-tag"><?php echo htmlspecialchars(is_array($spec) ? implode(', ', $spec) : $spec); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($vendor->hourlyRate): ?>
                                    <p class="vendor-rate">
                                        <strong class="text-primary">₵<?php echo number_format($vendor->hourlyRate); ?>/hr</strong>
                                    </p>
                                <?php endif; ?>
                                
                                <a href="<?php echo url('customer/vendor/' . urlencode($vendor->id ?? '')); ?>" class="btn btn-primary btn-sm vendor-view-btn">View Profile</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Service Results -->
            <?php if (!empty($serviceResults)): ?>
                <section class="results-section">
                    <div class="section-header">
                        <h2>🛠️ Services (<?php echo count($serviceResults); ?>)</h2>
                    </div>
                    <div class="services-grid">
                        <?php foreach ($serviceResults as $service): ?>
                            <div class="service-card">
                                <div class="service-header">
                                    <div>
                                        <h3><?php echo htmlspecialchars($service->title); ?></h3>
                                        <?php if ($service->category): ?>
                                            <span class="service-category"><?php echo htmlspecialchars($service->category); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($service->description): ?>
                                    <p class="service-description"><?php echo htmlspecialchars(substr($service->description, 0, 150)); ?><?php echo strlen($service->description) > 150 ? '...' : ''; ?></p>
                                <?php endif; ?>
                                
                                <div class="service-footer">
                                    <div class="service-price-info">
                                        <p class="service-price">₵<?php echo number_format($service->price); ?></p>
                                        <?php if ($service->priceType): ?>
                                            <span class="price-type"><?php echo ucfirst($service->priceType); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo url('customer/service/' . urlencode($service->id ?? '')); ?>" class="btn btn-outline btn-sm">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- No Results -->
            <?php if (empty($vendorResults) && empty($serviceResults)): ?>
                <div class="empty-state">
                    <p class="empty-icon">🔍</p>
                    <h3>No results found</h3>
                    <p class="text-secondary">Try adjusting your search terms or browse by category</p>
                    <div class="empty-state-actions">
                        <a href="<?php echo url('customer/home'); ?>" class="btn btn-primary">Browse Categories</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Empty Search State -->
        <div class="empty-state">
            <p class="empty-icon">🔍</p>
            <h3>Search for Artisans & Services</h3>
            <p class="text-secondary">Enter a search term to find skilled professionals or browse available services</p>
            <div class="empty-state-actions">
                <a href="<?php echo url('customer/home'); ?>" class="btn btn-primary">Browse Categories</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.search-page {
    max-width: 100%;
}

.search-header {
    margin-bottom: var(--spacing-xl);
}

.search-header h1 {
    margin: 0 0 var(--spacing-sm) 0;
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

/* Search Results */
.search-results {
    margin-top: var(--spacing-xl);
}

.results-summary {
    margin-bottom: var(--spacing-xl);
}

.results-summary h2 {
    margin: 0 0 var(--spacing-xs) 0;
    font-size: 1.5rem;
}

.results-section {
    margin-bottom: var(--spacing-2xl);
}

.section-header {
    margin-bottom: var(--spacing-lg);
}

.section-header h2 {
    margin: 0;
    font-size: 1.25rem;
}

/* Vendors Grid */
.vendors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.25rem;
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

.review-count {
    color: var(--text-secondary);
    font-size: 0.8125rem;
}

.vendor-bio {
    font-size: 0.9375rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-md);
    line-height: 1.5;
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
    margin-bottom: var(--spacing-md);
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
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-lg);
}

.service-card {
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
    margin-bottom: var(--spacing-md);
}

.service-header h3 {
    font-size: 1.125rem;
    margin: 0 0 var(--spacing-xs) 0;
}

.service-category {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-sm);
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
}

.service-description {
    font-size: 0.9375rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-md);
    line-height: 1.5;
}

.service-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--spacing-md);
}

.service-price-info {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-sm);
}

.service-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    margin: 0;
}

.price-type {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: capitalize;
}

/* Empty State */
.empty-state {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
    text-align: center;
    box-shadow: var(--shadow-sm);
    margin-top: var(--spacing-xl);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-md);
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 var(--spacing-sm) 0;
}

.empty-state-actions {
    margin-top: var(--spacing-lg);
}

/* Responsive */
@media (max-width: 768px) {
    .vendors-grid,
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .service-footer {
        flex-direction: column;
        align-items: stretch;
    }
    
    .service-price-info {
        justify-content: center;
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>
