<?php
$title = 'Earnings';
require 'app/views/layouts/header.php';
?>

<div class="earnings-page">
    <!-- Header -->
    <div class="earnings-header">
        <h1>Earnings & Payment History</h1>
        <p class="text-secondary">Track your earnings and payment history</p>
    </div>

    <!-- Summary Cards -->
    <div class="earnings-summary-grid">
        <div class="summary-card total-earnings">
            <div class="summary-icon">💰</div>
            <div class="summary-content">
                <p class="summary-label">Total Earnings</p>
                <h2 class="summary-value">₵<?php echo number_format($totalEarnings ?? 0, 2); ?></h2>
            </div>
        </div>
        <div class="summary-card monthly-earnings">
            <div class="summary-icon">📅</div>
            <div class="summary-content">
                <p class="summary-label">This Month</p>
                <h2 class="summary-value">₵<?php echo number_format($monthlyEarnings ?? 0, 2); ?></h2>
            </div>
        </div>
        <div class="summary-card pending-earnings">
            <div class="summary-content">
                <p class="summary-label">Pending</p>
                <h2 class="summary-value">₵<?php echo number_format($pendingTotal ?? 0, 2); ?></h2>
            </div>
        </div>
        <div class="summary-card transactions-count">
            <div class="summary-icon">📊</div>
            <div class="summary-content">
                <p class="summary-label">Total Transactions</p>
                <h2 class="summary-value"><?php echo count($successfulEarnings ?? []); ?></h2>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="earnings-filter-section">
        <h2>Payment History</h2>
        <div class="filter-controls">
            <form method="GET" action="<?php echo url('vendor/earnings'); ?>" class="filter-form">
                <select name="month" class="form-control" style="width: auto; display: inline-block;">
                    <?php
                    $months = [
                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                    ];
                    foreach ($months as $num => $name) {
                        $selected = ($selectedMonth ?? date('m')) == $num ? 'selected' : '';
                        echo "<option value=\"{$num}\" {$selected}>{$name}</option>";
                    }
                    ?>
                </select>
                <select name="year" class="form-control" style="width: auto; display: inline-block; margin-left: var(--spacing-sm);">
                    <?php
                    $currentYear = date('Y');
                    for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                        $selected = ($selectedYear ?? $currentYear) == $year ? 'selected' : '';
                        echo "<option value=\"{$year}\" {$selected}>{$year}</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-outline btn-sm" style="margin-left: var(--spacing-sm);">Filter</button>
            </form>
        </div>
    </div>

    <!-- Earnings List -->
    <div class="earnings-list">
        <?php if (!empty($successfulEarnings)): ?>
            <div class="earnings-table-container">
                <table class="earnings-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Service</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($successfulEarnings as $earning): ?>
                            <tr>
                                <td>
                                    <?php 
                                    if ($earning->paidAt) {
                                        echo date('M j, Y', $earning->paidAt);
                                    } else {
                                        echo date('M j, Y', $earning->createdAt ?? time());
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="service-info">
                                        <strong><?php echo htmlspecialchars($earning->serviceTitle ?? 'Service'); ?></strong>
                                    </div>
                                </td>
                                <td class="amount-cell">
                                    <strong>₵<?php echo number_format($earning->amount, 2); ?></strong>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $earning->paymentStatus; ?>">
                                        <?php echo ucfirst($earning->paymentStatus); ?>
                                    </span>
                                </td>
                                <td class="reference-cell">
                                    <code><?php echo htmlspecialchars(substr($earning->paystackReference ?? 'N/A', 0, 20)); ?></code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">💰</div>
                <h3>No Earnings Yet</h3>
                <p class="text-secondary">Your payment history will appear here once customers make payments for your services.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pending Payments Section -->
    <?php if (!empty($pendingEarnings)): ?>
        <div class="pending-payments-section">
            <h2>Pending Payments</h2>
            <div class="pending-list">
                <?php foreach ($pendingEarnings as $earning): ?>
                    <div class="pending-item">
                        <div class="pending-info">
                            <h4><?php echo htmlspecialchars($earning->serviceTitle ?? 'Service'); ?></h4>
                            <p class="text-secondary">
                                <?php echo date('M j, Y', $earning->createdAt ?? time()); ?>
                            </p>
                        </div>
                        <div class="pending-amount">
                            <strong>₵<?php echo number_format($earning->amount, 2); ?></strong>
                            <span class="status-badge status-pending">Pending</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.earnings-page {
    max-width: 100%;
}

.earnings-header {
    margin-bottom: var(--spacing-xl);
}

.earnings-header h1 {
    margin: 0 0 var(--spacing-xs) 0;
}

.earnings-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.summary-card {
    background: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
}

.summary-card.total-earnings {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.summary-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
}

.summary-content {
    flex: 1;
}

.summary-label {
    margin: 0 0 var(--spacing-xs) 0;
    font-size: 0.875rem;
    opacity: 0.9;
}

.summary-value {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
}

.earnings-filter-section {
    background: var(--surface);
    padding: var(--spacing-lg);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
}

.earnings-filter-section h2 {
    margin: 0 0 var(--spacing-md) 0;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.earnings-list {
    background: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    margin-bottom: var(--spacing-xl);
}

.earnings-table-container {
    overflow-x: auto;
}

.earnings-table {
    width: 100%;
    border-collapse: collapse;
}

.earnings-table thead {
    background-color: rgba(37, 99, 235, 0.05);
}

.earnings-table th {
    padding: var(--spacing-md);
    text-align: left;
    font-weight: 600;
    color: var(--text);
    border-bottom: 2px solid var(--border);
}

.earnings-table td {
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--border);
}

.earnings-table tbody tr:hover {
    background-color: rgba(37, 99, 235, 0.02);
}

.service-info {
    display: flex;
    flex-direction: column;
}

.amount-cell {
    font-size: 1.1rem;
    color: var(--primary);
}

.reference-cell code {
    background-color: rgba(37, 99, 235, 0.1);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    color: var(--primary);
}

.status-badge {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
}

.status-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: #10B981;
}

.status-pending {
    background-color: rgba(245, 158, 11, 0.1);
    color: #F59E0B;
}

.status-failed {
    background-color: rgba(239, 68, 68, 0.1);
    color: #EF4444;
}

.empty-state {
    text-align: center;
    padding: var(--spacing-2xl);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: var(--spacing-md);
}

.empty-state h3 {
    margin: 0 0 var(--spacing-sm) 0;
}

.pending-payments-section {
    background: var(--surface);
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
}

.pending-payments-section h2 {
    margin: 0 0 var(--spacing-lg) 0;
}

.pending-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.pending-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    background-color: rgba(245, 158, 11, 0.05);
    border-radius: var(--radius-md);
    border-left: 4px solid #F59E0B;
}

.pending-info h4 {
    margin: 0 0 var(--spacing-xs) 0;
}

.pending-amount {
    text-align: right;
}

.pending-amount strong {
    display: block;
    font-size: 1.25rem;
    color: var(--primary);
    margin-bottom: var(--spacing-xs);
}
</style>

<?php require 'app/views/layouts/footer.php'; ?>
