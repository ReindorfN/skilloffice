<?php
$title = 'Services';
require 'app/views/layouts/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) var(--spacing-md);">
    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: var(--spacing-lg);">
        <h1>Service Listings</h1>
        <a href="<?php echo url('vendor/services/create'); ?>" class="btn btn-primary">Add New Service</a>
    </div>
    
    <?php if (!empty($services)): ?>
        <div style="display: grid; gap: var(--spacing-lg);">
            <?php foreach ($services as $service): ?>
                <div class="card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3><?php echo htmlspecialchars($service->title); ?></h3>
                            <p class="text-secondary"><?php echo htmlspecialchars($service->category); ?></p>
                            <p class="text-secondary"><?php echo htmlspecialchars($service->description); ?></p>
                            <p class="text-primary" style="font-weight: bold; font-size: 1.25rem;">₵<?php echo number_format($service->price); ?><?php echo $service->priceType ? '/' . $service->priceType : ''; ?></p>
                            <span class="badge" style="padding: var(--spacing-xs) var(--spacing-sm); border-radius: var(--radius-sm); background: <?php echo $service->isActive ? 'var(--success)' : 'var(--text-secondary)'; ?>; color: white;">
                                <?php echo $service->isActive ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo url('vendor/services/edit/' . $service->id); ?>" class="btn btn-outline">Edit</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card text-center" style="padding: var(--spacing-2xl);">
            <p style="font-size: 3rem; margin-bottom: var(--spacing-md);">📝</p>
            <h3>No Services Yet</h3>
            <p class="text-secondary">Create your first service listing</p>
            <a href="<?php echo url('vendor/services/create'); ?>" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Add Service</a>
        </div>
    <?php endif; ?>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

