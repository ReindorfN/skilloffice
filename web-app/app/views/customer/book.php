<?php
$title = 'Book Service';
require 'app/views/layouts/header.php';
?>

<div class="book-service-page">
    <h1>Book Service</h1>
    
    <?php if ($vendor || $service): ?>
        <!-- Vendor/Service Info -->
        <?php if ($vendor): ?>
            <div class="info-card">
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
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($service): ?>
            <div class="info-card">
                <h2>Service Information</h2>
                <h3><?php echo htmlspecialchars($service->title); ?></h3>
                <?php if ($service->category): ?>
                    <span class="service-category"><?php echo htmlspecialchars($service->category); ?></span>
                <?php endif; ?>
                <p class="service-price-large">₵<?php echo number_format($service->price); ?></p>
                <?php if ($service->description): ?>
                    <p><?php echo htmlspecialchars($service->description); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Booking Form -->
        <div class="booking-form-card">
            <h2>Booking Details</h2>
            <form id="bookingForm" onsubmit="submitBooking(event)">
                <input type="hidden" name="vendorId" value="<?php echo $vendor->id ?? ''; ?>">
                <input type="hidden" name="serviceId" value="<?php echo $service->id ?? ''; ?>">
                
                <div class="form-group">
                    <label for="description">Service Description *</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required placeholder="Describe the service you need..."><?php echo $service ? htmlspecialchars($service->title . ' - ' . $service->description) : ''; ?></textarea>
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
                    <a href="<?php echo $vendor ? url('customer/vendor/' . $vendor->id) : url('customer/search'); ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Booking Request</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No vendor or service selected</p>
            <a href="<?php echo url('customer/search'); ?>" class="btn btn-primary">Search for Artisans</a>
        </div>
    <?php endif; ?>
</div>

<script>
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
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
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
            window.location.href = '<?php echo url('customer/bookings'); ?>';
        } else {
            alert('Error: ' + (data.message || 'Failed to create booking'));
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}
</script>

<style>
.book-service-page {
    max-width: 800px;
    margin: 0 auto;
}

.info-card {
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
}

.info-card h2 {
    margin: 0 0 var(--spacing-md) 0;
    font-size: 1.25rem;
}

.vendor-preview {
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
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

.vendor-preview-info h3 {
    margin: 0 0 var(--spacing-xs) 0;
}

.service-category {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-sm);
    background-color: rgba(37, 99, 235, 0.1);
    color: var(--primary);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: var(--spacing-sm);
}

.service-price-large {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin: var(--spacing-sm) 0;
}

.booking-form-card {
    background-color: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.booking-form-card h2 {
    margin: 0 0 var(--spacing-lg) 0;
    font-size: 1.5rem;
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
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xl);
}

.empty-state {
    text-align: center;
    padding: var(--spacing-2xl);
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>

