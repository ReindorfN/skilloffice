<?php
$title = 'Create Service';
require 'app/views/layouts/header.php';
?>

<div class="container" style="max-width: 600px; padding: var(--spacing-xl) var(--spacing-md);">
    <h1>Create Service</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo url('vendor/services/create'); ?>" class="card">
        <div class="form-group">
            <label for="title" class="form-label">Service Title</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="category" class="form-label">Category</label>
            <input type="text" id="category" name="category" class="form-control" required placeholder="e.g., Plumbing, Electrical">
        </div>
        
        <div class="form-group">
            <label for="price" class="form-label">Price (₵)</label>
            <input type="number" id="price" name="price" class="form-control" step="0.01" required>
        </div>
        
        <div class="form-group">
            <label for="priceType" class="form-label">Price Type</label>
            <select id="priceType" name="priceType" class="form-control">
                <option value="fixed">Fixed</option>
                <option value="hourly">Hourly</option>
                <option value="quote">Quote</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="duration" class="form-label">Duration (minutes, optional)</label>
            <input type="number" id="duration" name="duration" class="form-control">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="isActive" value="1" checked> Active (available for booking)
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Create Service</button>
        <a href="<?php echo url('vendor/services'); ?>" class="btn btn-outline btn-block" style="margin-top: var(--spacing-md);">Cancel</a>
    </form>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

