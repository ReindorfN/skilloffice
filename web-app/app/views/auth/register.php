<?php
$title = 'Register';
$selectedRole = $role ?? 'customer';
require 'app/views/layouts/header.php';
?>

<div style="max-width: 500px; margin: 4rem auto;">
    <h1 class="text-center">Create Account</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo url('register'); ?>" class="card">
        <div class="form-group">
            <label class="form-label">I want to register as:</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md); margin-top: var(--spacing-sm);">
                <label style="cursor: pointer; padding: var(--spacing-md); border: 2px solid <?php echo $selectedRole === 'customer' ? 'var(--primary)' : 'var(--border)'; ?>; border-radius: var(--radius-md); text-align: center; transition: all 0.2s; background: <?php echo $selectedRole === 'customer' ? 'rgba(37, 99, 235, 0.1)' : 'transparent'; ?>;">
                    <input type="radio" name="role" value="customer" <?php echo $selectedRole === 'customer' ? 'checked' : ''; ?> style="display: none;">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">👤</div>
                    <div style="font-weight: 600; color: var(--text-primary);">Customer</div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: var(--spacing-xs);">Find artisans</div>
                </label>
                <label style="cursor: pointer; padding: var(--spacing-md); border: 2px solid <?php echo $selectedRole === 'artisan' ? 'var(--primary)' : 'var(--border)'; ?>; border-radius: var(--radius-md); text-align: center; transition: all 0.2s; background: <?php echo $selectedRole === 'artisan' ? 'rgba(37, 99, 235, 0.1)' : 'transparent'; ?>;">
                    <input type="radio" name="role" value="artisan" <?php echo $selectedRole === 'artisan' ? 'checked' : ''; ?> style="display: none;">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">🔧</div>
                    <div style="font-weight: 600; color: var(--text-primary);">Artisan</div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: var(--spacing-xs);">Offer services</div>
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="fullName" class="form-label">Full Name</label>
            <input type="text" id="fullName" name="fullName" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="phoneNumber" class="form-label">Phone Number</label>
            <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control">
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required minlength="6">
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Register</button>
        
        <p class="text-center mt-3">
            Already have an account? <a href="<?php echo url('login'); ?>">Login</a>
        </p>
    </form>
</div>

<script>
document.querySelectorAll('input[type="radio"][name="role"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('label[style*="border"]').forEach(label => {
            label.style.borderColor = 'var(--border)';
            label.style.background = 'transparent';
        });
        if (this.checked) {
            const label = this.closest('label');
            label.style.borderColor = 'var(--primary)';
            label.style.background = 'rgba(37, 99, 235, 0.1)';
        }
    });
});
</script>

<?php require 'app/views/layouts/footer.php'; ?>

