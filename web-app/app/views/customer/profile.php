<?php
$title = 'Profile';
require 'app/views/layouts/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) var(--spacing-md);">
    <h1>My Profile</h1>
    
    <div class="card" style="margin-bottom: var(--spacing-lg);">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; margin-bottom: var(--spacing-md);">
                    <span style="font-size: 2.5rem; color: white;">👤</span>
                </div>
                <h2><?php echo htmlspecialchars($user->fullName ?? 'Customer'); ?></h2>
                <p class="text-secondary"><?php echo htmlspecialchars($user->email); ?></p>
                <?php if ($user->phoneNumber): ?>
                    <p class="text-secondary">📞 <?php echo htmlspecialchars($user->phoneNumber); ?></p>
                <?php endif; ?>
            </div>
            <a href="<?php echo url('customer/profile/edit'); ?>" class="btn btn-outline">Edit Profile</a>
        </div>
    </div>

    <div class="card">
        <h3>Account Information</h3>
        <div style="margin-top: var(--spacing-lg);">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user->email); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user->phoneNumber ?? 'Not provided'); ?></p>
            <p><strong>Account Status:</strong> <?php echo $user->isVerified ? '✅ Verified' : '⚠️ Not Verified'; ?></p>
            <?php if ($user->createdAt): ?>
                <p><strong>Member Since:</strong> <?php echo date('F Y', $user->createdAt); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

