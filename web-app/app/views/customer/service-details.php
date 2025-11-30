<?php
$title = 'Service Details';
require 'app/views/layouts/header.php';
?>

<div class="service-details-page">
    <?php if ($service): ?>
        <!-- Service Header -->
        <div class="service-header">
            <div class="service-header-content">
                <h1><?php echo htmlspecialchars($service->title); ?></h1>
                <?php if ($service->category): ?>
                    <span class="service-category-badge"><?php echo htmlspecialchars($service->category); ?></span>
                <?php endif; ?>
                <div class="service-price-section">
                    <span class="service-price-large">₵<?php echo number_format($service->price); ?></span>
                    <?php if ($service->priceType): ?>
                        <span class="price-type-badge"><?php echo ucfirst($service->priceType); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="service-actions">
                <button class="btn btn-primary btn-lg" onclick="openBookingModal()">
                    📋 Book This Service
                </button>
                <?php if ($vendor): ?>
                    <a href="<?php echo url('customer/vendor/' . urlencode($vendor->id)); ?>" class="btn btn-outline btn-lg">
                        👤 View Artisan Profile
                    </a>
                    <a href="<?php echo url('customer/chat?vendor=' . urlencode($vendor->userId ?? $vendor->id)); ?>" class="btn btn-outline btn-lg">
                        💬 Chat with Artisan
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Service Description -->
        <?php if ($service->description): ?>
            <div class="service-section">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($service->description)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Service Details -->
        <div class="service-section">
            <h2>Service Details</h2>
            <div class="details-grid">
                <?php if ($service->duration): ?>
                    <div class="detail-item">
                        <span class="detail-label">⏱️ Duration:</span>
                        <span class="detail-value"><?php echo $service->duration; ?> minutes</span>
                    </div>
                <?php endif; ?>
                <?php if ($service->priceType): ?>
                    <div class="detail-item">
                        <span class="detail-label">💰 Pricing:</span>
                        <span class="detail-value"><?php echo ucfirst($service->priceType); ?></span>
                    </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-label">📊 Status:</span>
                    <span class="detail-value">
                        <?php echo $service->isActive ? '<span style="color: #10B981;">Active</span>' : '<span style="color: #EF4444;">Inactive</span>'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Reviews -->
        <?php if (!empty($reviews)): ?>
            <div class="service-section">
                <h2>Reviews (<?php echo count($reviews); ?>)</h2>
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
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="service-section">
                <h2>Reviews</h2>
                <p class="text-secondary">No reviews yet. Be the first to review this service!</p>
            </div>
        <?php endif; ?>

        <!-- Vendor Info -->
        <?php if ($vendor): ?>
            <div class="service-section">
                <h2>Artisan Information</h2>
                <div class="vendor-preview">
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
                    <div class="vendor-preview-info">
                        <h3><?php echo htmlspecialchars($vendor->businessName ?? 'Artisan'); ?></h3>
                        <?php if ($vendor->rating): ?>
                            <div class="vendor-rating">
                                <span class="stars">⭐</span>
                                <span><?php echo number_format($vendor->rating, 1); ?></span>
                                <?php if ($vendor->totalReviews): ?>
                                    <span class="review-count">(<?php echo $vendor->totalReviews; ?> reviews)</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($vendor->location): ?>
                            <p class="vendor-location">
                                <span class="location-icon">📍</span>
                                <?php echo htmlspecialchars($vendor->location); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="vendor-preview-actions">
                        <a href="<?php echo url('customer/vendor/' . urlencode($vendor->id)); ?>" class="btn btn-outline btn-sm">View Full Profile</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <p>Service not found</p>
        </div>
    <?php endif; ?>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Book Service</h2>
            <span class="close" onclick="closeBookingModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="bookingForm" onsubmit="submitBooking(event)">
                <input type="hidden" name="vendorId" value="<?php echo $vendor->id ?? ''; ?>">
                <input type="hidden" name="serviceId" value="<?php echo $service->id ?? ''; ?>">
                
                <div class="form-group">
                    <label for="description">Service Description *</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required placeholder="Describe the service you need..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" class="form-control" required placeholder="Enter service location">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="scheduledDate">Preferred Date</label>
                        <input type="date" id="scheduledDate" name="scheduledDate" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="scheduledTime">Preferred Time</label>
                        <input type="time" id="scheduledTime" name="scheduledTime" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="specialRequirements">Special Requirements</label>
                    <textarea id="specialRequirements" name="specialRequirements" class="form-control" rows="3" placeholder="Any special requirements or notes..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="customerNotes">Additional Notes</label>
                    <textarea id="customerNotes" name="customerNotes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="closeBookingModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Booking Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBookingModal() {
    document.getElementById('bookingModal').style.display = 'block';
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
}


function submitBooking(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const bookingData = {
        vendorId: formData.get('vendorId'),
        serviceId: formData.get('serviceId'),
        description: formData.get('description'),
        location: formData.get('location'),
        scheduledDate: formData.get('scheduledDate'),
        scheduledTime: formData.get('scheduledTime'),
        specialRequirements: formData.get('specialRequirements'),
        customerNotes: formData.get('customerNotes')
    };
    
    fetch('<?php echo url('api/booking/create'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(bookingData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Booking request submitted successfully!');
            closeBookingModal();
            window.location.href = '<?php echo url('customer/bookings'); ?>';
        } else {
            alert('Error: ' + (data.message || 'Failed to create booking'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('bookingModal');
    if (event.target == modal) {
        closeBookingModal();
    }
}
</script>

<style>
.service-details-page {
    max-width: 100%;
}

.service-header {
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-md);
}

.service-header-content h1 {
    margin: 0 0 var(--spacing-sm) 0;
    font-size: 2rem;
}

.service-category-badge {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-md);
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: var(--spacing-md);
}

.service-price-section {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.service-price-large {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary);
}

.price-type-badge {
    padding: var(--spacing-xs) var(--spacing-md);
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
}

.service-actions {
    display: flex;
    gap: var(--spacing-md);
    flex-wrap: wrap;
}

.service-section {
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
}

.service-section h2 {
    margin: 0 0 var(--spacing-md) 0;
    font-size: 1.5rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.detail-label {
    font-weight: 500;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.detail-value {
    font-size: 1rem;
    color: var(--text);
}

.vendor-preview {
    display: flex;
    gap: var(--spacing-lg);
    align-items: center;
    padding: var(--spacing-lg);
    background-color: rgba(37, 99, 235, 0.05);
    border-radius: var(--radius-md);
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

.vendor-preview-info {
    flex: 1;
}

.vendor-preview-info h3 {
    margin: 0 0 var(--spacing-xs) 0;
}

.modal-large {
    max-width: 600px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.form-group {
    margin-bottom: var(--spacing-lg);
}

.form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    font-weight: 500;
    color: var(--text);
}

.form-control {
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xl);
}

@media (max-width: 768px) {
    .service-actions {
        flex-direction: column;
    }
    
    .service-actions .btn {
        width: 100%;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>

