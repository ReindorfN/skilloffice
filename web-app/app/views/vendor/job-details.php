<?php
$title = 'Job Details';
require 'app/views/layouts/header.php';
?>

<div class="job-details-page">
    <a href="<?php echo url('vendor/jobs'); ?>" class="btn btn-outline" style="margin-bottom: var(--spacing-lg);">← Back to Jobs</a>
    
    <?php if (isset($booking)): ?>
        <!-- Job Header -->
        <div class="job-header">
            <div>
                <h1><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service Request'); ?></h1>
                <span class="status-badge status-<?php echo $booking->status; ?>">
                    <?php echo ucfirst($booking->status); ?>
                </span>
            </div>
        </div>

        <!-- Job Details -->
        <div class="job-section">
            <h2>Job Details</h2>
            <div class="details-grid">
                <div class="detail-item">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value"><?php echo nl2br(htmlspecialchars($booking->description)); ?></span>
                </div>
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
            </div>
        </div>

        <!-- Special Requirements -->
        <?php if ($booking->specialRequirements): ?>
            <div class="job-section">
                <h2>Special Requirements</h2>
                <p><?php echo nl2br(htmlspecialchars($booking->specialRequirements)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Customer Notes -->
        <?php if ($booking->customerNotes): ?>
            <div class="job-section">
                <h2>Customer Notes</h2>
                <p><?php echo nl2br(htmlspecialchars($booking->customerNotes)); ?></p>
            </div>
        <?php endif; ?>

        <!-- Vendor Notes -->
        <div class="job-section">
            <h2>Your Notes</h2>
            <form id="vendorNotesForm" onsubmit="updateVendorNotes(event)">
                <textarea id="vendorNotes" name="vendorNotes" class="form-control" rows="4" placeholder="Add notes about this job..."><?php echo htmlspecialchars($booking->vendorNotes ?? ''); ?></textarea>
                <button type="submit" class="btn btn-outline btn-sm" style="margin-top: var(--spacing-sm);">Save Notes</button>
            </form>
        </div>

        <!-- Status Management -->
        <div class="job-section">
            <h2>Manage Job Status</h2>
            
            <?php if ($booking->status === 'pending'): ?>
                <div class="status-actions">
                    <div class="status-action-card">
                        <h3>Quote Price</h3>
                        <form id="quoteForm" onsubmit="quotePrice(event)">
                            <div class="form-group">
                                <label for="quotedPrice">Price (₵)</label>
                                <input type="number" id="quotedPrice" name="quotedPrice" class="form-control" min="0" step="0.01" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Quote & Accept Job</button>
                        </form>
                    </div>
                    <div class="status-action-buttons">
                        <button class="btn btn-success btn-lg" onclick="updateStatus('inProgress')">
                            ✓ Accept & Start Job
                        </button>
                        <button class="btn btn-danger btn-lg" onclick="updateStatus('rejected')">
                            ✗ Reject Job
                        </button>
                    </div>
                </div>
            <?php elseif ($booking->status === 'inProgress'): ?>
                <div class="status-actions">
                    <button class="btn btn-success btn-lg" onclick="updateStatus('completed')">
                        ✓ Mark as Completed
                    </button>
                </div>
            <?php elseif ($booking->status === 'completed'): ?>
                <div class="status-info">
                    <?php if ($booking->customerConfirmedCompletion ?? false): ?>
                        <p class="success-message">✓ This job has been completed and confirmed by the customer on <?php echo $booking->completedAt ? date('F j, Y g:i A', $booking->completedAt) : 'N/A'; ?></p>
                    <?php else: ?>
                        <p class="info-message">⏳ This job has been marked as completed. Waiting for customer confirmation.</p>
                        <p class="text-secondary" style="margin-top: var(--spacing-sm);">Completed on: <?php echo $booking->completedAt ? date('F j, Y g:i A', $booking->completedAt) : 'N/A'; ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Job not found</p>
        </div>
    <?php endif; ?>
</div>

<script>
const bookingId = '<?php echo $booking->id ?? ''; ?>';

function updateStatus(status) {
    if (!confirm(`Are you sure you want to ${status === 'accepted' ? 'accept' : status === 'rejected' ? 'reject' : status === 'inProgress' ? 'mark as in progress' : 'mark as completed'} this job?`)) {
        return;
    }
    
    const data = {
        status: status
    };
    
    fetch('<?php echo url('api/booking'); ?>/' + bookingId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Job status updated successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function quotePrice(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const quotedPrice = parseFloat(formData.get('quotedPrice'));
    
    if (isNaN(quotedPrice) || quotedPrice <= 0) {
        alert('Please enter a valid price');
        return;
    }
    
    const data = {
        status: 'inProgress',
        quotedPrice: quotedPrice
    };
    
    fetch('<?php echo url('api/booking'); ?>/' + bookingId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Price quoted and job accepted successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to quote price'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function updateVendorNotes(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const vendorNotes = formData.get('vendorNotes');
    
    const data = {
        status: '<?php echo $booking->status; ?>',
        vendorNotes: vendorNotes
    };
    
    fetch('<?php echo url('api/booking'); ?>/' + bookingId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Notes saved successfully!');
        } else {
            alert('Error: ' + (data.message || 'Failed to save notes'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

<style>
.job-details-page {
    max-width: 100%;
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-md);
}

.job-header h1 {
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

.job-section {
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
}

.job-section h2 {
    margin: 0 0 var(--spacing-md) 0;
    font-size: 1.5rem;
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

.status-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.status-action-card {
    background-color: rgba(37, 99, 235, 0.05);
    padding: var(--spacing-lg);
    border-radius: var(--radius-md);
    border: 2px solid var(--border);
}

.status-action-card h3 {
    margin: 0 0 var(--spacing-md) 0;
    font-size: 1.125rem;
}

.status-action-buttons {
    display: flex;
    gap: var(--spacing-md);
}

.status-info {
    padding: var(--spacing-lg);
    background-color: rgba(16, 185, 129, 0.1);
    border-radius: var(--radius-md);
}

.success-message {
    margin: 0;
    color: #10B981;
    font-weight: 500;
}

.info-message {
    margin: 0;
    color: #3B82F6;
    font-weight: 500;
}

.form-group {
    margin-bottom: var(--spacing-md);
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

@media (max-width: 768px) {
    .status-action-buttons {
        flex-direction: column;
    }
    
    .status-action-buttons .btn {
        width: 100%;
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>
