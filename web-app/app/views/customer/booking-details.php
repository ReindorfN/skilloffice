<?php
$title = 'Booking Details';
require 'app/views/layouts/header.php';
?>

<div class="booking-details-page">
    <?php if ($booking): ?>
        <!-- Booking Header -->
        <div class="booking-header">
            <div>
                <h1>Booking #<?php echo substr($booking->id, 0, 8); ?></h1>
                <span class="status-badge status-<?php echo $booking->status; ?>">
                    <?php echo ucfirst($booking->status); ?>
                </span>
            </div>
            <div class="booking-actions">
                <?php if ($vendor): ?>
                    <a href="<?php echo url('customer/chat?vendor=' . urlencode($vendor->userId ?? $vendor->id)); ?>" class="btn btn-primary">
                        💬 Chat with Artisan
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Service Info -->
        <?php if ($booking->serviceTitle): ?>
            <div class="booking-section">
                <h2>Service</h2>
                <p class="service-title"><?php echo htmlspecialchars($booking->serviceTitle); ?></p>
            </div>
        <?php endif; ?>

        <!-- Description -->
        <?php if ($booking->description): ?>
            <div class="booking-section">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($booking->description)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Booking Details -->
        <div class="booking-section">
            <h2>Booking Details</h2>
            <div class="details-grid">
                <?php if ($booking->location): ?>
                    <div class="detail-item">
                        <span class="detail-label">📍 Location:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->location); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($booking->scheduledDate): ?>
                    <div class="detail-item">
                        <span class="detail-label">📅 Scheduled Date:</span>
                        <span class="detail-value"><?php echo date('F j, Y', $booking->scheduledDate); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($booking->scheduledTime): ?>
                    <div class="detail-item">
                        <span class="detail-label">⏰ Scheduled Time:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking->scheduledTime); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($booking->quotedPrice): ?>
                    <div class="detail-item">
                        <span class="detail-label">💰 Quoted Price:</span>
                        <span class="detail-value price-value">₵<?php echo number_format($booking->quotedPrice); ?></span>
                    </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-label">📊 Job Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-<?php echo $booking->status; ?>">
                            <?php echo ucfirst($booking->status); ?>
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">💳 Payment Status:</span>
                    <span class="detail-value">
                        <span class="payment-status payment-<?php echo $booking->paymentStatus; ?>">
                            <?php echo ucfirst($booking->paymentStatus); ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Special Requirements -->
        <?php if ($booking->specialRequirements): ?>
            <div class="booking-section">
                <h2>Special Requirements</h2>
                <p><?php echo nl2br(htmlspecialchars($booking->specialRequirements)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Customer Notes -->
        <?php if ($booking->customerNotes): ?>
            <div class="booking-section">
                <h2>Your Notes</h2>
                <p><?php echo nl2br(htmlspecialchars($booking->customerNotes)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Vendor Notes -->
        <?php if ($booking->vendorNotes): ?>
            <div class="booking-section">
                <h2>Artisan Notes</h2>
                <p><?php echo nl2br(htmlspecialchars($booking->vendorNotes)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Vendor Info -->
        <?php if ($vendor): ?>
            <div class="booking-section">
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
                        <?php if ($vendor->location): ?>
                            <p class="vendor-location">
                                <span class="location-icon">📍</span>
                                <?php echo htmlspecialchars($vendor->location); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="vendor-preview-actions">
                        <a href="<?php echo url('customer/vendor/' . urlencode($vendor->id)); ?>" class="btn btn-outline btn-sm">View Profile</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <?php if ($booking->status === 'inProgress'): ?>
            <div class="booking-section">
                <h2>Actions</h2>
                <div class="booking-actions-grid">
                    <?php if ($booking->paymentStatus === 'paid'): ?>
                        <div class="payment-success-message">
                            <p class="success-icon">✓</p>
                            <h3>Payment Successful</h3>
                            <p class="text-secondary">Your payment of ₵<?php echo number_format($booking->quotedPrice); ?> has been received.</p>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-primary btn-lg" onclick="initiatePayment()">
                            💳 Make Payment
                        </button>
                        <p class="text-secondary" style="margin-top: var(--spacing-sm); font-size: 0.875rem;">
                            Pay ₵<?php echo number_format($booking->quotedPrice); ?> for this service
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php 
        // Show payment status messages
        if (isset($_GET['payment'])) {
            if ($_GET['payment'] === 'success'): ?>
                <div class="alert alert-success" style="margin-bottom: var(--spacing-lg); padding: var(--spacing-md); background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); color: #10B981;">
                    <strong>✓ Payment Successful!</strong> Your payment has been processed successfully.
                </div>
            <?php elseif ($_GET['payment'] === 'failed'): ?>
                <div class="alert alert-error" style="margin-bottom: var(--spacing-lg); padding: var(--spacing-md); background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); color: #EF4444;">
                    <strong>✗ Payment Failed</strong> Please try again or contact support if the problem persists.
                </div>
            <?php endif;
        }
        ?>
        
        <?php if ($booking->status === 'completed' && !$booking->customerConfirmedCompletion): ?>
            <div class="booking-section">
                <h2>Confirm Completion</h2>
                <div class="completion-confirmation">
                    <p>The artisan has marked this job as completed. Please confirm that the work has been completed to your satisfaction and provide a review.</p>
                    <button class="btn btn-success btn-lg" onclick="openReviewModal()">
                        ✓ Confirm Completion & Review
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Review Modal (Mandatory) -->
        <div id="reviewModal" class="modal" style="display: none;">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h2>Rate & Review Service</h2>
                    <span class="close" onclick="closeReviewModal()" style="display: none;">&times;</span>
                </div>
                <div class="modal-body">
                    <p class="text-secondary" style="margin-bottom: var(--spacing-lg);">
                        Please rate and review the service you received. This is required to confirm job completion.
                    </p>
                    <form id="reviewForm" onsubmit="submitReview(event)">
                        <div class="form-group">
                            <label for="rating">Rating <span style="color: red;">*</span></label>
                            <div class="rating-input">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5" class="star-label">⭐</label>
                                <input type="radio" id="star4" name="rating" value="4" required>
                                <label for="star4" class="star-label">⭐</label>
                                <input type="radio" id="star3" name="rating" value="3" required>
                                <label for="star3" class="star-label">⭐</label>
                                <input type="radio" id="star2" name="rating" value="2" required>
                                <label for="star2" class="star-label">⭐</label>
                                <input type="radio" id="star1" name="rating" value="1" required>
                                <label for="star1" class="star-label">⭐</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Review Comment</label>
                            <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="Share your experience with this service..."></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                Submit Review & Confirm Completion
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="booking-section">
            <h2>Timeline</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker"></div>
                    <div class="timeline-content">
                        <h4>Booking Created</h4>
                        <p><?php echo $booking->createdAt ? date('F j, Y g:i A', $booking->createdAt) : 'N/A'; ?></p>
                    </div>
                </div>
                <?php if ($booking->status === 'inProgress' || $booking->status === 'completed'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h4>Job Started</h4>
                            <p><?php echo $booking->updatedAt ? date('F j, Y g:i A', $booking->updatedAt) : 'N/A'; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($booking->status === 'completed' && $booking->completedAt): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h4>Artisan Marked as Completed</h4>
                            <p><?php echo date('F j, Y g:i A', $booking->completedAt); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($booking->paymentStatus === 'paid' && isset($paymentRecord) && $paymentRecord->paidAt): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h4>Payment Made</h4>
                            <p><?php echo date('F j, Y g:i A', $paymentRecord->paidAt); ?></p>
                            <p class="text-secondary" style="font-size: 0.875rem; margin-top: var(--spacing-xs);">
                                Amount: ₵<?php echo number_format($paymentRecord->amount, 2); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($booking->status === 'completed' && $booking->customerConfirmedCompletion): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h4>You Confirmed Completion</h4>
                            <p>Job fully completed</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Booking not found</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function initiatePayment() {
    const bookingId = '<?php echo $booking->id; ?>';
    const amount = <?php echo $booking->quotedPrice ?? 0; ?>;
    const email = '<?php echo isset($user) && $user ? htmlspecialchars($user->email ?? '') : ''; ?>';
    
    if (!email) {
        alert('Please ensure your email is set in your profile.');
        return;
    }
    
    if (amount <= 0) {
        alert('Invalid payment amount.');
        return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Processing...';
    
    // Initialize payment
    fetch('<?php echo url('api/payment/initialize'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            bookingId: bookingId,
            amount: amount,
            email: email
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.authorization_url) {
            // Redirect to Paystack payment page
            window.location.href = data.authorization_url;
        } else {
            alert('Error: ' + (data.message || 'Failed to initialize payment'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function openReviewModal() {
    document.getElementById('reviewModal').style.display = 'block';
    // Prevent closing modal by clicking outside or close button (mandatory)
    document.querySelector('#reviewModal .close').style.display = 'none';
}

function closeReviewModal() {
    // Only allow closing if form is submitted successfully
    document.getElementById('reviewModal').style.display = 'none';
}

function submitReview(event) {
    event.preventDefault();
    
    const bookingId = '<?php echo $booking->id; ?>';
    const rating = document.querySelector('input[name="rating"]:checked');
    const comment = document.getElementById('comment').value;
    
    if (!rating) {
        alert('Please select a rating (1-5 stars)');
        return;
    }
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Submitting...';
    
    fetch('<?php echo url('api/booking'); ?>/' + bookingId + '/confirm', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            rating: parseInt(rating.value),
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Thank you for your review! Completion confirmed successfully.');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to submit review and confirm completion'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}
</script>

<style>
.booking-details-page {
    max-width: 100%;
}

.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-md);
}

.booking-header h1 {
    margin: 0 0 var(--spacing-sm) 0;
}

.status-badge {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-md);
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 500;
}

.status-pending {
    background-color: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.status-accepted {
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary);
}

.status-inProgress {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3B82F6;
}

.status-completed {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

.status-rejected, .status-cancelled {
    background-color: rgba(239, 68, 68, 0.1);
    color: #EF4444;
}

.booking-section {
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
}

.booking-section h2 {
    margin: 0 0 var(--spacing-md) 0;
    font-size: 1.5rem;
}

.service-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--primary);
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

.price-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary);
}

.status-badge {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
}

.status-pending {
    background-color: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.status-inProgress {
    background-color: rgba(59, 130, 246, 0.1);
    color: #3B82F6;
}

.status-completed {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

.payment-status {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
}

.payment-pending {
    background-color: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.payment-paid {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10B981;
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

.timeline {
    position: relative;
    padding-left: var(--spacing-xl);
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: var(--border);
}

.timeline-item {
    position: relative;
    margin-bottom: var(--spacing-xl);
}

.timeline-marker {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background-color: var(--primary);
    border: 3px solid var(--surface);
}

.timeline-content h4 {
    margin: 0 0 var(--spacing-xs) 0;
    font-size: 1rem;
}

.timeline-content p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.booking-actions-grid {
    text-align: center;
}

.completion-confirmation {
    text-align: center;
    padding: var(--spacing-lg);
    background-color: rgba(16, 185, 129, 0.05);
    border-radius: var(--radius-md);
    border: 2px solid rgba(16, 185, 129, 0.2);
}

.completion-confirmation p {
    margin-bottom: var(--spacing-lg);
    color: var(--text);
}

.payment-success-message {
    text-align: center;
    padding: var(--spacing-lg);
    background-color: rgba(16, 185, 129, 0.05);
    border-radius: var(--radius-md);
    border: 2px solid rgba(16, 185, 129, 0.2);
}

.payment-success-message .success-icon {
    font-size: 3rem;
    color: #10B981;
    margin-bottom: var(--spacing-md);
}

.payment-success-message h3 {
    margin: 0 0 var(--spacing-sm) 0;
    color: #10B981;
}

/* Review Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow: auto;
}

.modal-content {
    background-color: var(--surface);
    margin: 5% auto;
    padding: 0;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    animation: modalSlideIn 0.3s;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: var(--spacing-xl);
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
}

.close {
    color: var(--text-secondary);
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: var(--text);
}

.modal-body {
    padding: var(--spacing-xl);
}

.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: var(--spacing-xs);
    margin: var(--spacing-md) 0;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-input .star-label {
    font-size: 2rem;
    cursor: pointer;
    color: #ddd;
    transition: color 0.2s;
}

.rating-input input[type="radio"]:checked ~ .star-label,
.rating-input input[type="radio"]:checked + .star-label,
.rating-input .star-label:hover,
.rating-input .star-label:hover ~ .star-label {
    color: #FFD700;
}

.form-actions {
    margin-top: var(--spacing-lg);
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>

