<?php
$title = 'Login';
require 'app/views/layouts/header.php';
?>

<div style="max-width: 400px; margin: 4rem auto;">
    <h1 class="text-center">Login</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo url('login'); ?>" class="card">
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-block">Login</button>

        <p class="text-center mt-3">
            Don't have an account? <a href="<?php echo url('register'); ?>">Register</a>
        </p>
    </form>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

