<?php
$title = 'Dashboard';
require 'app/views/layouts/header.php';
?>

<div class="dashboard-container">
    <!-- Welcome Header -->
    <div class="dashboard-header">
        <div>
            <h1>Welcome back, <?php 
                $userName = isset($user) && $user ? ($user->fullName ?? 'Vendor') : 'Vendor';
                $nameParts = explode(' ', $userName);
                $firstName = $nameParts[0] ?? 'Vendor';
                echo htmlspecialchars($firstName);
            ?>!</h1>
            <p class="text-secondary">Here's your dashboard overview</p>
        </div>
        <div class="dashboard-date">
            <p class="text-secondary"><?php echo date('l, F j, Y'); ?></p>
        </div>
    </div>

    <!-- Earnings Summary Card -->
    <div class="earnings-card">
        <div class="earnings-header">
            <div>
                <p class="earnings-label">Total Earnings</p>
                <h2 class="earnings-amount">₵<?php echo number_format($totalEarnings ?? 0, 2); ?></h2>
            </div>
            <div class="earnings-icon">💰</div>
        </div>
        <div class="earnings-breakdown">
            <div class="earnings-item">
                <p class="earnings-item-label">This Month</p>
                <p class="earnings-item-value">₵<?php echo number_format($monthlyEarnings ?? 0, 2); ?></p>
            </div>
            <div class="earnings-item">
                <p class="earnings-item-label">Pending</p>
                <p class="earnings-item-value">₵<?php echo number_format($pendingEarnings ?? 0, 2); ?></p>
            </div>
            <div class="earnings-item">
                <p class="earnings-item-label">Completed Jobs</p>
                <p class="earnings-item-value"><?php echo count($completedBookings ?? []); ?></p>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon">📋</div>
            <div class="metric-content">
                <h3><?php echo $activeJobs ?? 0; ?></h3>
                <p class="text-secondary">Active Jobs</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">✅</div>
            <div class="metric-content">
                <h3><?php echo count($completedBookings ?? []); ?></h3>
                <p class="text-secondary">Completed</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">⭐</div>
            <div class="metric-content">
                <h3><?php echo number_format($averageRating ?? 0, 1); ?></h3>
                <p class="text-secondary">Average Rating</p>
                <?php if (($totalReviews ?? 0) > 0): ?>
                    <p class="metric-subtext"><?php echo $totalReviews; ?> reviews</p>
                <?php else: ?>
                    <p class="metric-subtext">No reviews yet</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon">📊</div>
            <div class="metric-content">
                <h3><?php echo $completionRate ?? 0; ?>%</h3>
                <p class="text-secondary">Completion Rate</p>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="dashboard-grid">
        <!-- Left Column: Active Job Requests -->
        <div class="dashboard-column">
            <div class="section-header">
                <h2>Active Job Requests</h2>
                <a href="<?php echo url('vendor/jobs'); ?>" class="view-all-link">View All</a>
            </div>
            
            <?php if (!empty($pendingBookings)): ?>
                <div class="job-requests-list">
                    <?php foreach (array_slice($pendingBookings, 0, 5) as $booking): ?>
                        <div class="job-request-card">
                            <div class="job-request-header">
                                <h3><?php echo htmlspecialchars($booking->serviceTitle ?? 'Service Request'); ?></h3>
                                <span class="status-badge status-pending">Pending</span>
                            </div>
                            <div class="job-request-details">
                                <p class="job-detail">
                                    <span class="job-icon">📍</span>
                                    <?php echo htmlspecialchars($booking->location ?? 'Location not specified'); ?>
                                </p>
                                <p class="job-detail">
                                    <span class="job-icon">📅</span>
                                    <?php 
                                    $scheduledDate = $booking->scheduledDate ?? $booking->createdAt ?? time();
                                    echo date('M d, Y', $scheduledDate);
                                    ?>
                                </p>
                                <?php if ($booking->quotedPrice): ?>
                                    <p class="job-detail">
                                        <span class="job-icon">💰</span>
                                        <strong class="text-primary">₵<?php echo number_format($booking->quotedPrice); ?></strong>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="job-request-actions">
                                <a href="<?php echo url('vendor/jobs/' . $booking->id); ?>" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p class="empty-icon">📭</p>
                    <p class="text-secondary">No active job requests</p>
                    <p class="text-secondary" style="font-size: 0.875rem;">New job requests will appear here</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Recent Earnings -->
        <div class="dashboard-column">
            <div class="section-header">
                <h2>Recent Earnings</h2>
                <a href="<?php echo url('vendor/earnings'); ?>" class="view-all-link">View All</a>
            </div>
            
            <?php 
            require_once 'app/services/EarningsService.php';
            $recentEarnings = EarningsService::getVendorEarnings($user->id, 'success');
            $recentEarnings = array_slice($recentEarnings, 0, 5);
            ?>
            <?php if (!empty($recentEarnings)): ?>
                <div class="earnings-list">
                    <?php foreach ($recentEarnings as $earning): ?>
                        <div class="earning-item">
                            <div class="earning-header">
                                <h3><?php echo htmlspecialchars($earning->serviceTitle ?? 'Service'); ?></h3>
                                <span class="status-badge status-success">Paid</span>
                            </div>
                            <div class="earning-details">
                                <p class="earning-detail">
                                    <span class="earning-icon">💰</span>
                                    <strong class="text-primary">₵<?php echo number_format($earning->amount, 2); ?></strong>
                                </p>
                                <p class="earning-detail">
                                    <span class="earning-icon">📅</span>
                                    <?php 
                                    $paidDate = $earning->paidAt ?? $earning->createdAt ?? time();
                                    echo date('M d, Y', $paidDate);
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p class="empty-icon">💰</p>
                    <p class="text-secondary">No earnings yet</p>
                    <p class="text-secondary" style="font-size: 0.875rem;">Completed and paid jobs will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 100%;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-xl);
}

.dashboard-date {
    text-align: right;
}

/* Earnings Card */
.earnings-card {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-lg);
}

.earnings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.earnings-label {
    opacity: 0.9;
    margin-bottom: var(--spacing-sm);
    font-size: 0.9375rem;
}

.earnings-amount {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
}

.earnings-icon {
    font-size: 3rem;
    opacity: 0.8;
}

.earnings-breakdown {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--spacing-md);
}

.earnings-item {
    background: rgba(255, 255, 255, 0.15);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    backdrop-filter: blur(10px);
}

.earnings-item-label {
    font-size: 0.875rem;
    opacity: 0.8;
    margin-bottom: var(--spacing-xs);
}

.earnings-item-value {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0;
}

/* Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-xl);
}

.metric-card {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.metric-icon {
    font-size: 2.5rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(37, 99, 235, 0.1);
    border-radius: var(--radius-md);
}

.metric-content h3 {
    font-size: 2rem;
    margin: 0 0 var(--spacing-xs) 0;
    color: var(--text-primary);
}

.metric-subtext {
    font-size: 0.75rem;
    margin: var(--spacing-xs) 0 0 0;
    color: var(--text-light);
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
}

.dashboard-column {
    display: flex;
    flex-direction: column;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.section-header h2 {
    margin: 0;
}

.view-all-link {
    font-size: 0.875rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.view-all-link:hover {
    text-decoration: underline;
}

/* Job Requests */
.job-requests-list,
.appointments-list,
.earnings-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.job-request-card,
.appointment-card,
.earning-item {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
    border-left: 4px solid var(--primary);
    transition: transform 0.2s, box-shadow 0.2s;
}

.job-request-card:hover,
.appointment-card:hover,
.earning-item:hover {
    transform: translateX(4px);
    box-shadow: var(--shadow-md);
}

.job-request-header,
.appointment-header,
.earning-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-md);
}

.job-request-header h3,
.appointment-header h3,
.earning-header h3 {
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

.status-accepted,
.status-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.job-request-details,
.appointment-details,
.earning-details {
    margin-bottom: var(--spacing-md);
}

.job-detail,
.appointment-detail,
.earning-detail {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xs);
    font-size: 0.9375rem;
}

.job-icon,
.appointment-icon,
.earning-icon {
    font-size: 1rem;
    width: 20px;
    text-align: center;
}

.job-request-actions,
.appointment-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.btn-sm {
    padding: var(--spacing-xs) var(--spacing-md);
    font-size: 0.875rem;
}

/* Empty State */
.empty-state {
    background-color: var(--surface);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
    text-align: center;
    box-shadow: var(--shadow-sm);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-md);
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .earnings-breakdown {
        grid-template-columns: 1fr;
    }
    
    .metrics-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .earnings-amount {
        font-size: 2rem;
    }
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>
