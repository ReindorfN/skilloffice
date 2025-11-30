<?php
$title = 'Edit Service';
require 'app/views/layouts/header.php';
?>

<div class="container" style="max-width: 600px; padding: var(--spacing-xl) var(--spacing-md);">
    <h1>Edit Service</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (isset($service)): ?>
        <form method="POST" action="<?php echo url('vendor/services/edit/' . $service->id); ?>" class="card">
            <div class="form-group">
                <label for="title" class="form-label">Service Title</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($service->title); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($service->description); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="category" class="form-label">Category</label>
                <input type="text" id="category" name="category" class="form-control" value="<?php echo htmlspecialchars($service->category); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price" class="form-label">Price (₵)</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?php echo $service->price; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="priceType" class="form-label">Price Type</label>
                <select id="priceType" name="priceType" class="form-control">
                    <option value="fixed" <?php echo $service->priceType === 'fixed' ? 'selected' : ''; ?>>Fixed</option>
                    <option value="hourly" <?php echo $service->priceType === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                    <option value="quote" <?php echo $service->priceType === 'quote' ? 'selected' : ''; ?>>Quote</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="duration" class="form-label">Duration (minutes, optional)</label>
                <input type="number" id="duration" name="duration" class="form-control" value="<?php echo $service->duration ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="isActive" value="1" <?php echo $service->isActive ? 'checked' : ''; ?>> Active (available for booking)
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Update Service</button>
            <a href="<?php echo url('vendor/services'); ?>" class="btn btn-outline btn-block" style="margin-top: var(--spacing-md);">Cancel</a>
        </form>
    <?php else: ?>
        <div class="card text-center" style="padding: var(--spacing-2xl);">
            <p style="font-size: 3rem; margin-bottom: var(--spacing-md);">❌</p>
            <h3>Service Not Found</h3>
            <a href="<?php echo url('vendor/services'); ?>" class="btn btn-primary" style="margin-top: var(--spacing-lg);">Back to Services</a>
        </div>
    <?php endif; ?>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

