<?php
$title = 'Artisan Profile';
require 'app/views/layouts/header.php';
?>

<div class="vendor-profile-page">
    <?php if ($vendor): ?>
        <!-- Vendor Header -->
        <div class="vendor-header">
            <div class="vendor-avatar-large">
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
            <div class="vendor-info-header">
                <h1><?php echo htmlspecialchars($vendor->businessName ?? 'Artisan'); ?></h1>
                <?php if (isset($averageRating) && $averageRating > 0): ?>
                    <div class="vendor-rating-large">
                        <span class="stars">⭐</span>
                        <span class="rating-value"><?php echo number_format($averageRating, 1); ?></span>
                        <?php if (isset($totalReviews) && $totalReviews > 0): ?>
                            <span class="review-count">(<?php echo $totalReviews; ?> reviews)</span>
                        <?php endif; ?>
                    </div>
                <?php elseif ($vendor->rating): ?>
                    <div class="vendor-rating-large">
                        <span class="stars">⭐</span>
                        <span class="rating-value"><?php echo number_format($vendor->rating, 1); ?></span>
                        <?php if ($vendor->totalReviews): ?>
                            <span class="review-count">(<?php echo $vendor->totalReviews; ?> reviews)</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($vendor->location): ?>
                    <p class="vendor-location-header">
                        <span class="location-icon">📍</span>
                        <?php echo htmlspecialchars($vendor->location); ?>
                    </p>
                <?php endif; ?>
                <?php if ($vendor->isAvailable): ?>
                    <span class="availability-badge available">Available</span>
                <?php else: ?>
                    <span class="availability-badge unavailable">Not Available</span>
                <?php endif; ?>
            </div>
            <div class="vendor-actions">
                <a href="<?php echo url('customer/chat?vendor=' . urlencode($vendor->userId ?? $vendor->id)); ?>" class="btn btn-primary">
                    💬 Start Chat
                </a>
                <a href="<?php echo url('customer/book?vendor=' . urlencode($vendor->id)); ?>" class="btn btn-outline">
                    📋 Book Service
                </a>
            </div>
        </div>

        <!-- Vendor Bio -->
        <?php if ($vendor->bio): ?>
            <div class="vendor-section">
                <h2>About</h2>
                <p><?php echo nl2br(htmlspecialchars($vendor->bio)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Skills & Categories -->
        <?php if (!empty($vendor->skills) || !empty($vendor->serviceCategories)): ?>
            <div class="vendor-section">
                <h2>Skills & Specializations</h2>
                <div class="specializations-list">
                    <?php 
                    $specs = [];
                    if (is_array($vendor->skills) && !empty($vendor->skills)) {
                        $specs = array_merge($specs, $vendor->skills);
                    }
                    if (is_array($vendor->serviceCategories) && !empty($vendor->serviceCategories)) {
                        $specs = array_merge($specs, $vendor->serviceCategories);
                    }
                    foreach ($specs as $spec): 
                    ?>
                        <span class="specialization-tag-large"><?php echo htmlspecialchars(is_array($spec) ? implode(', ', $spec) : $spec); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pricing -->
        <?php if ($vendor->hourlyRate): ?>
            <div class="vendor-section">
                <h2>Pricing</h2>
                <p class="pricing-info">
                    <strong class="price-large">₵<?php echo number_format($vendor->hourlyRate); ?></strong>
                    <span class="price-unit">per hour</span>
                </p>
            </div>
        <?php endif; ?>

        <!-- Reviews & Ratings -->
        <?php if (!empty($reviews)): ?>
            <div class="vendor-section">
                <h2>Reviews & Ratings (<?php echo count($reviews); ?>)</h2>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $review->rating ? 'filled' : ''; ?>">⭐</span>
                                    <?php endfor; ?>
                                    <span class="rating-value-small"><?php echo $review->rating; ?>/5</span>
                                </div>
                                <span class="review-date"><?php echo date('M j, Y', $review->createdAt ?? time()); ?></span>
                            </div>
                            <?php if ($review->comment): ?>
                                <p class="review-comment"><?php echo nl2br(htmlspecialchars($review->comment)); ?></p>
                            <?php endif; ?>
                            <?php if ($review->serviceTitle): ?>
                                <p class="review-service">Service: <?php echo htmlspecialchars($review->serviceTitle); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Services -->
        <?php if (!empty($services)): ?>
            <div class="vendor-section">
                <h2>Services Offered (<?php echo count($services); ?>)</h2>
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                        <div class="service-card" onclick="openServiceModal('<?php echo $service->id; ?>')">
                            <h3><?php echo htmlspecialchars($service->title); ?></h3>
                            <?php if ($service->category): ?>
                                <span class="service-category"><?php echo htmlspecialchars($service->category); ?></span>
                            <?php endif; ?>
                            <?php if ($service->description): ?>
                                <p class="service-description"><?php echo htmlspecialchars(substr($service->description, 0, 100)); ?>...</p>
                            <?php endif; ?>
                            <div class="service-price-info">
                                <p class="service-price">₵<?php echo number_format($service->price); ?></p>
                                <?php if ($service->priceType): ?>
                                    <span class="price-type"><?php echo ucfirst($service->priceType); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary btn-sm">View Details</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="vendor-section">
            <h2>Performance</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $vendor->completedJobs ?? 0; ?></div>
                    <div class="stat-label">Jobs Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $vendor->totalReviews ?? 0; ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $vendor->rating ? number_format($vendor->rating, 1) : 'N/A'; ?></div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Vendor not found</p>
        </div>
    <?php endif; ?>
</div>

<!-- Chat Modal -->
<div id="chatModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Start Conversation</h2>
            <span class="close" onclick="closeChatModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Redirecting to chat...</p>
        </div>
    </div>
</div>

<script>
function openServiceModal(serviceId) {
    window.location.href = '<?php echo url('customer/service'); ?>/' + serviceId;
}
</script>

<style>
.vendor-profile-page {
    max-width: 100%;
}

.vendor-header {
    display: flex;
    gap: var(--spacing-xl);
    align-items: flex-start;
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-md);
}

.vendor-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 2.5rem;
    flex-shrink: 0;
}

.vendor-info-header {
    flex: 1;
}

.vendor-info-header h1 {
    margin: 0 0 var(--spacing-sm) 0;
    font-size: 2rem;
}

.vendor-rating-large {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-sm);
    font-size: 1.125rem;
}

.rating-value {
    font-weight: 600;
}

.review-count {
    color: var(--text-secondary);
    font-size: 0.9375rem;
}

.vendor-location-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-sm);
}

.availability-badge {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-md);
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 500;
}

.availability-badge.available {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

.availability-badge.unavailable {
    background-color: rgba(239, 68, 68, 0.1);
    color: #EF4444;
}

.vendor-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.vendor-section {
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
}

.vendor-section h2 {
    margin: 0 0 var(--spacing-md) 0;
    font-size: 1.5rem;
}

.specializations-list {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.specialization-tag-large {
    display: inline-block;
    padding: var(--spacing-sm) var(--spacing-lg);
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary);
    border-radius: var(--radius-md);
    font-size: 0.9375rem;
    font-weight: 500;
}

.pricing-info {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-sm);
}

.price-large {
    font-size: 2rem;
    color: var(--primary);
}

.price-unit {
    color: var(--text-secondary);
    font-size: 1rem;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-lg);
}

.service-card {
    background-color: white;
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    padding: var(--spacing-lg);
    cursor: pointer;
    transition: all 0.2s;
}

.service-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--spacing-lg);
}

.stat-card {
    text-align: center;
    padding: var(--spacing-lg);
    background-color: rgba(37, 99, 235, 0.05);
    border-radius: var(--radius-md);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: var(--spacing-xs);
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: var(--surface);
    margin: 10% auto;
    padding: 0;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border);
}

.modal-header h2 {
    margin: 0;
}

.close {
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    color: var(--text-secondary);
}

.close:hover {
    color: var(--text);
}

.modal-body {
    padding: var(--spacing-lg);
}

@media (max-width: 768px) {
    .vendor-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .vendor-actions {
        width: 100%;
    }
    
    .vendor-actions .btn {
        width: 100%;
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>

