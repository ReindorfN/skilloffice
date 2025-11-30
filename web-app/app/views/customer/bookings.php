<?php
$title = 'My Bookings';
require 'app/views/layouts/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) var(--spacing-md);">
    <h1>My Bookings</h1>
    
    <?php if (!empty($pendingBookings) || !empty($inProgressBookings) || !empty($completedBookings)): ?>
        <!-- Pending Bookings -->
        <?php if (!empty($pendingBookings)): ?>
            <div style="margin-top: var(--spacing-xl);">
                <h2 style="margin-bottom: var(--spacing-lg);">⏳ Pending</h2>
                <div style="display: grid; gap: var(--spacing-lg);">
                    <?php foreach ($pendingBookings as $booking): ?>
                        <div class="card booking-card" onclick="window.location.href='<?php echo url('customer/bookings/' . $booking->id); ?>'">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <h3><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service Request'); ?></h3>
                                    <p class="text-secondary"><?php echo htmlspecialchars(substr($booking->description, 0, 100)); ?><?php echo strlen($booking->description) > 100 ? '...' : ''; ?></p>
                                    <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; margin-top: var(--spacing-sm);">
                                        <?php if ($booking->location): ?>
                                            <p class="text-secondary" style="margin: 0;">📍 <?php echo htmlspecialchars($booking->location); ?></p>
                                        <?php endif; ?>
                                        <?php if ($booking->scheduledDate): ?>
                                            <p class="text-secondary" style="margin: 0;">📅 <?php echo date('M d, Y', $booking->scheduledDate); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($booking->quotedPrice): ?>
                                        <p class="text-primary" style="font-weight: bold; font-size: 1.25rem; margin-top: var(--spacing-sm);">₵<?php echo number_format($booking->quotedPrice); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="badge status-badge status-<?php echo $booking->status; ?>" style="padding: var(--spacing-sm) var(--spacing-md); border-radius: var(--radius-md);">
                                        <?php echo ucfirst($booking->status); ?>
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top: var(--spacing-md);">
                                <a href="<?php echo url('customer/bookings/' . $booking->id); ?>" class="btn btn-outline btn-sm">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- In Progress Bookings -->
        <?php if (!empty($inProgressBookings)): ?>
            <div style="margin-top: var(--spacing-xl);">
                <h2 style="margin-bottom: var(--spacing-lg);">🚀 In Progress</h2>
                <div style="display: grid; gap: var(--spacing-lg);">
                    <?php foreach ($inProgressBookings as $booking): ?>
                        <div class="card booking-card" onclick="window.location.href='<?php echo url('customer/bookings/' . $booking->id); ?>'">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <h3><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service Request'); ?></h3>
                                    <p class="text-secondary"><?php echo htmlspecialchars(substr($booking->description, 0, 100)); ?><?php echo strlen($booking->description) > 100 ? '...' : ''; ?></p>
                                    <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; margin-top: var(--spacing-sm);">
                                        <?php if ($booking->location): ?>
                                            <p class="text-secondary" style="margin: 0;">📍 <?php echo htmlspecialchars($booking->location); ?></p>
                                        <?php endif; ?>
                                        <?php if ($booking->scheduledDate): ?>
                                            <p class="text-secondary" style="margin: 0;">📅 <?php echo date('M d, Y', $booking->scheduledDate); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($booking->quotedPrice): ?>
                                        <p class="text-primary" style="font-weight: bold; font-size: 1.25rem; margin-top: var(--spacing-sm);">₵<?php echo number_format($booking->quotedPrice); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="badge status-badge status-<?php echo $booking->status; ?>" style="padding: var(--spacing-sm) var(--spacing-md); border-radius: var(--radius-md);">
                                        <?php echo ucfirst($booking->status); ?>
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top: var(--spacing-md);">
                                <a href="<?php echo url('customer/bookings/' . $booking->id); ?>" class="btn btn-outline btn-sm">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Completed Bookings -->
        <?php if (!empty($completedBookings)): ?>
            <div style="margin-top: var(--spacing-xl);">
                <h2 style="margin-bottom: var(--spacing-lg);">✓ Completed</h2>
                <div style="display: grid; gap: var(--spacing-lg);">
                    <?php foreach ($completedBookings as $booking): ?>
                        <div class="card booking-card" onclick="window.location.href='<?php echo url('customer/bookings/' . $booking->id); ?>'">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <h3><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service Request'); ?></h3>
                                    <p class="text-secondary"><?php echo htmlspecialchars(substr($booking->description, 0, 100)); ?><?php echo strlen($booking->description) > 100 ? '...' : ''; ?></p>
                                    <div style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; margin-top: var(--spacing-sm);">
                                        <?php if ($booking->location): ?>
                                            <p class="text-secondary" style="margin: 0;">📍 <?php echo htmlspecialchars($booking->location); ?></p>
                                        <?php endif; ?>
                                        <?php if ($booking->scheduledDate): ?>
                                            <p class="text-secondary" style="margin: 0;">📅 <?php echo date('M d, Y', $booking->scheduledDate); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($booking->quotedPrice): ?>
                                        <p class="text-primary" style="font-weight: bold; font-size: 1.25rem; margin-top: var(--spacing-sm);">₵<?php echo number_format($booking->quotedPrice); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="badge status-badge status-<?php echo $booking->status; ?>" style="padding: var(--spacing-sm) var(--spacing-md); border-radius: var(--radius-md);">
                                        <?php echo ucfirst($booking->status); ?>
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top: var(--spacing-md);">
                                <a href="<?php echo url('customer/bookings/' . $booking->id); ?>" class="btn btn-outline btn-sm">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="card text-center" style="padding: var(--spacing-2xl); margin-top: var(--spacing-lg);">
            <p style="font-size: 3rem; margin-bottom: var(--spacing-md);">📋</p>
            <h3>No Bookings Yet</h3>
            <p class="text-secondary">Your active and completed bookings will appear here</p>
            <a href="<?php echo url('customer/search'); ?>" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Find Artisans</a>
        </div>
    <?php endif; ?>
</div>

<style>
.booking-card {
    cursor: pointer;
    transition: all 0.2s;
}

.booking-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.status-badge {
    display: inline-block;
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

.status-rejected, .status-cancelled {
    background-color: rgba(239, 68, 68, 0.1);
    color: #EF4444;
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>
