<?php
$title = 'Welcome';
require 'app/views/layouts/header.php';
?>

<div style="text-align: center; padding: 4rem 0;">
    <h1>SkillOffice</h1>
    <p style="font-size: 1.25rem; color: var(--text-secondary); margin: var(--spacing-xl) 0;">
        Connecting customers with skilled artisans
    </p>
    
    <div style="margin-top: var(--spacing-2xl);">
        <a href="<?php echo url('welcome'); ?>" class="btn btn-primary" style="margin-right: var(--spacing-md);">
            Get Started
        </a>
        <a href="<?php echo url('login'); ?>" class="btn btn-outline">
            Login
        </a>
    </div>
</div>

<script>
    // Auto-redirect after 3 seconds if user is logged in
    setTimeout(function() {
        window.location.href = '<?php echo url('welcome'); ?>';
    }, 3000);
</script>

<?php require 'app/views/layouts/footer.php'; ?>

