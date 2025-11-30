<?php
$title = 'Profile';
require 'app/views/layouts/header.php';
?>

<div class="container" style="max-width: 800px; padding: var(--spacing-xl) var(--spacing-md);">
    <h1>Vendor Profile</h1>
    
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="text-center">
            <div style="width: 120px; height: 120px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--spacing-md);">
                <span style="font-size: 4rem; color: white;">👤</span>
            </div>
            <h2><?php echo htmlspecialchars($profile->businessName ?? $user->fullName ?? 'Vendor'); ?></h2>
            <p class="text-secondary"><?php echo htmlspecialchars($user->email); ?></p>
            <?php if ($profile->location): ?>
                <p class="text-secondary">📍 <?php echo htmlspecialchars($profile->location); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($profile): ?>
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <h3>Business Information</h3>
            <?php if ($profile->bio): ?>
                <p><?php echo htmlspecialchars($profile->bio); ?></p>
            <?php endif; ?>
            <?php if ($profile->hourlyRate): ?>
                <p><strong>Hourly Rate:</strong> ₵<?php echo number_format($profile->hourlyRate); ?>/hour</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($profile->skills)): ?>
            <div class="card" style="margin-bottom: var(--spacing-lg);">
                <h3>Skills</h3>
                <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-sm);">
                    <?php foreach ($profile->skills as $skill): ?>
                        <span class="badge" style="padding: var(--spacing-xs) var(--spacing-md); border-radius: var(--radius-md); background: var(--primary); color: white;"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($profile->serviceCategories)): ?>
            <div class="card" style="margin-bottom: var(--spacing-lg);">
                <h3>Service Categories</h3>
                <div style="display: flex; flex-wrap: wrap; gap: var(--spacing-sm);">
                    <?php foreach ($profile->serviceCategories as $category): ?>
                        <span class="badge" style="padding: var(--spacing-xs) var(--spacing-md); border-radius: var(--radius-md); background: var(--secondary); color: white;"><?php echo htmlspecialchars($category); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($profile->rating || $profile->completedJobs): ?>
            <div class="card" style="margin-bottom: var(--spacing-lg);">
                <h3>Statistics</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg); text-align: center;">
                    <?php if ($profile->rating): ?>
                        <div>
                            <p style="font-size: 2rem; font-weight: bold;">⭐ <?php echo number_format($profile->rating, 1); ?></p>
                            <p class="text-secondary"><?php echo $profile->totalReviews ?? 0; ?> reviews</p>
                        </div>
                    <?php endif; ?>
                    <?php if ($profile->completedJobs): ?>
                        <div>
                            <p style="font-size: 2rem; font-weight: bold;">✅ <?php echo $profile->completedJobs; ?></p>
                            <p class="text-secondary">Completed Jobs</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="card text-center" style="padding: var(--spacing-2xl);">
            <p style="font-size: 3rem; margin-bottom: var(--spacing-md);">📝</p>
            <h3>Profile Not Complete</h3>
            <p class="text-secondary">Complete your profile to start receiving job requests</p>
            <a href="<?php echo url('vendor/profile/edit'); ?>" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Complete Profile</a>
        </div>
    <?php endif; ?>

    <a href="<?php echo url('vendor/profile/edit'); ?>" class="btn btn-primary btn-block">Edit Profile</a>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

