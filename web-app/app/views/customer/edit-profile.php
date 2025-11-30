<?php
$title = 'Edit Profile';
require 'app/views/layouts/header.php';
?>

<div class="container" style="max-width: 600px; padding: var(--spacing-xl) var(--spacing-md);">
    <h1>Edit Profile</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo url('customer/profile/edit'); ?>" class="card">
        <div class="form-group">
            <label for="fullName" class="form-label">Full Name</label>
            <input type="text" id="fullName" name="fullName" class="form-control" value="<?php echo htmlspecialchars($user->fullName ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phoneNumber" class="form-label">Phone Number</label>
            <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control" value="<?php echo htmlspecialchars($user->phoneNumber ?? ''); ?>">
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
        <a href="<?php echo url('customer/profile'); ?>" class="btn btn-outline btn-block" style="margin-top: var(--spacing-md);">Cancel</a>
    </form>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

