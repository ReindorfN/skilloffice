<?php
$title = 'Jobs';
require 'app/views/layouts/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) var(--spacing-md);">
    <h1>Jobs & Services</h1>
    <p class="text-secondary">Manage job requests and service listings</p>
    
    <!-- Tabs -->
    <div style="border-bottom: 2px solid var(--border); margin: var(--spacing-lg) 0;">
        <div class="d-flex gap-2">
            <a href="<?php echo url('vendor/jobs'); ?>?tab=requests" class="nav-link <?php echo (!isset($_GET['tab']) || $_GET['tab'] === 'requests') ? 'active' : ''; ?>">Job Requests</a>
            <a href="<?php echo url('vendor/jobs'); ?>?tab=active" class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'active') ? 'active' : ''; ?>">Active Current Jobs</a>
            <a href="<?php echo url('vendor/jobs'); ?>?tab=completed" class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'completed') ? 'active' : ''; ?>">Jobs Completed</a>
        </div>
    </div>

    <?php if (!isset($_GET['tab']) || $_GET['tab'] === 'requests'): ?>
        <!-- Job Requests (Pending) -->
        <div>
            <h2>Job Requests</h2>
            <?php if (!empty($pendingBookings)): ?>
                <div style="display: grid; gap: var(--spacing-lg); margin-top: var(--spacing-lg);">
                    <?php foreach ($pendingBookings as $booking): ?>
                        <div class="card" style="margin-bottom: var(--spacing-md);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h4><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service Request'); ?></h4>
                                    <p class="text-secondary"><?php echo htmlspecialchars($booking->description); ?></p>
                                    <p class="text-secondary">📍 <?php echo htmlspecialchars($booking->location); ?></p>
                                    <p class="text-secondary">📅 <?php echo date('M d, Y', $booking->scheduledDate); ?></p>
                                    <?php if ($booking->quotedPrice): ?>
                                        <p class="text-primary" style="font-weight: bold;">₵<?php echo number_format($booking->quotedPrice); ?></p>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo url('vendor/jobs/' . $booking->id); ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card text-center" style="padding: var(--spacing-2xl); margin-top: var(--spacing-lg);">
                    <p style="font-size: 3rem; margin-bottom: var(--spacing-md);">📋</p>
                    <h3>No Job Requests</h3>
                    <p class="text-secondary">Job requests from customers will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'active'): ?>
        <!-- Active Current Jobs (In Progress) -->
        <div>
            <h2>Active Current Jobs</h2>
            <?php if (!empty($inProgressBookings)): ?>
                <div style="display: grid; gap: var(--spacing-lg); margin-top: var(--spacing-lg);">
                    <?php foreach ($inProgressBookings as $booking): ?>
                        <div class="card" style="margin-bottom: var(--spacing-md);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <h4><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service Request'); ?></h4>
                                    <p class="text-secondary"><?php echo htmlspecialchars(substr($booking->description, 0, 100)); ?><?php echo strlen($booking->description) > 100 ? '...' : ''; ?></p>
                                    <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; margin-top: var(--spacing-sm);">
                                        <p class="text-secondary" style="margin: 0;">📍 <?php echo htmlspecialchars($booking->location); ?></p>
                                        <?php if ($booking->scheduledDate): ?>
                                            <p class="text-secondary" style="margin: 0;">📅 <?php echo date('M d, Y', $booking->scheduledDate); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($booking->quotedPrice): ?>
                                        <p class="text-primary" style="font-weight: bold; font-size: 1.25rem; margin-top: var(--spacing-sm);">₵<?php echo number_format($booking->quotedPrice); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                                    <a href="<?php echo url('vendor/jobs/' . $booking->id); ?>" class="btn btn-outline">View Details</a>
                                    <button class="btn btn-success" onclick="markAsCompleted('<?php echo $booking->id; ?>')">
                                        ✓ Mark as Completed
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card text-center" style="padding: var(--spacing-2xl); margin-top: var(--spacing-lg);">
                    <p style="font-size: 3rem; margin-bottom: var(--spacing-md);">🚀</p>
                    <h3>No Active Jobs</h3>
                    <p class="text-secondary">Jobs that are in progress will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif (isset($_GET['tab']) && $_GET['tab'] === 'completed'): ?>
        <!-- Jobs Completed (Confirmed by both parties) -->
        <div>
            <h2>Jobs Completed</h2>
            <?php 
            // Filter completed bookings that have been confirmed by both parties
            $confirmedCompletedBookings = array_filter($completedBookings ?? [], function($booking) {
                return ($booking->status === 'completed' && ($booking->customerConfirmedCompletion ?? false));
            });
            ?>
            <?php if (!empty($confirmedCompletedBookings)): ?>
                <div style="display: grid; gap: var(--spacing-lg); margin-top: var(--spacing-lg);">
                    <?php foreach ($confirmedCompletedBookings as $booking): ?>
                        <div class="card" style="margin-bottom: var(--spacing-md);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <h4><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service Request'); ?></h4>
                                    <p class="text-secondary"><?php echo htmlspecialchars(substr($booking->description, 0, 100)); ?><?php echo strlen($booking->description) > 100 ? '...' : ''; ?></p>
                                    <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; margin-top: var(--spacing-sm);">
                                        <p class="text-secondary" style="margin: 0;">📍 <?php echo htmlspecialchars($booking->location); ?></p>
                                        <?php if ($booking->completedAt): ?>
                                            <p class="text-secondary" style="margin: 0;">✅ Completed: <?php echo date('M d, Y', $booking->completedAt); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($booking->quotedPrice): ?>
                                        <p class="text-primary" style="font-weight: bold; font-size: 1.25rem; margin-top: var(--spacing-sm);">₵<?php echo number_format($booking->quotedPrice); ?></p>
                                    <?php endif; ?>
                                    <span class="status-badge status-completed" style="display: inline-block; margin-top: var(--spacing-sm); padding: var(--spacing-xs) var(--spacing-sm); border-radius: var(--radius-sm); background-color: rgba(16, 185, 129, 0.1); color: #10B981;">
                                        ✓ Completed & Confirmed
                                    </span>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                                    <a href="<?php echo url('vendor/jobs/' . $booking->id); ?>" class="btn btn-outline">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card text-center" style="padding: var(--spacing-2xl); margin-top: var(--spacing-lg);">
                    <p style="font-size: 3rem; margin-bottom: var(--spacing-md);">✅</p>
                    <h3>No Completed Jobs Yet</h3>
                    <p class="text-secondary">Jobs that are completed and confirmed by both parties will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function markAsCompleted(bookingId) {
    if (!confirm('Are you sure you want to mark this job as completed? The customer will need to confirm before it is fully completed.')) {
        return;
    }
    
    const data = {
        status: 'completed'
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
            alert('Job marked as completed! Waiting for customer confirmation.');
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
</script>

<style>
.nav-link {
    padding: var(--spacing-sm) var(--spacing-lg);
    text-decoration: none;
    color: var(--text-secondary);
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}

.nav-link:hover {
    color: var(--primary);
}

.nav-link.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    font-weight: 500;
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>
