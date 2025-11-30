<?php
$title = 'Edit Profile';
require 'app/views/layouts/header.php';
?>

<div class="container" style="max-width: 600px; padding: var(--spacing-xl) var(--spacing-md);">
    <h1>Edit Profile</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo url('vendor/profile/edit'); ?>" class="card">
        <div class="form-group">
            <label for="businessName" class="form-label">Business Name</label>
            <input type="text" id="businessName" name="businessName" class="form-control" value="<?php echo htmlspecialchars($profile->businessName ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="bio" class="form-label">Bio</label>
            <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($profile->bio ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="location" class="form-label">Location</label>
            <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($profile->location ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="hourlyRate" class="form-label">Hourly Rate (₵)</label>
            <input type="number" id="hourlyRate" name="hourlyRate" class="form-control" step="0.01" value="<?php echo $profile->hourlyRate ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="skills" class="form-label">Skills (comma-separated)</label>
            <input type="text" id="skills" name="skills" class="form-control" value="<?php echo htmlspecialchars(implode(', ', $profile->skills ?? [])); ?>" placeholder="e.g., Plumbing, Electrical, Carpentry">
        </div>
        
        <div class="form-group">
            <label for="serviceCategories" class="form-label">Service Categories (comma-separated)</label>
            <input type="text" id="serviceCategories" name="serviceCategories" class="form-control" value="<?php echo htmlspecialchars(implode(', ', $profile->serviceCategories ?? [])); ?>" placeholder="e.g., Repair, Installation">
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
        <a href="<?php echo url('vendor/profile'); ?>" class="btn btn-outline btn-block" style="margin-top: var(--spacing-md);">Cancel</a>
    </form>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

