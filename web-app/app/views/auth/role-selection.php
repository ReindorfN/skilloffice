<?php
$title = 'Choose Your Role';
require 'app/views/layouts/header.php';
?>

<div style="max-width: 600px; margin: 4rem auto; text-align: center;">
    <h1>Welcome to SkillOffice</h1>
    <p style="font-size: 1.125rem; color: var(--text-secondary); margin-bottom: var(--spacing-2xl);">
        Choose how you want to use SkillOffice
    </p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-xl);">
        <div class="card" style="cursor: pointer; transition: transform 0.2s;" onclick="window.location.href='<?php echo url('register?role=customer'); ?>'">
            <h2>👤 Customer</h2>
            <p>Find skilled artisans for your projects</p>
            <a href="<?php echo url('register?role=customer'); ?>" class="btn btn-primary btn-block">Continue as Customer</a>
        </div>
        
        <div class="card" style="cursor: pointer; transition: transform 0.2s;" onclick="window.location.href='<?php echo url('register?role=artisan'); ?>'">
            <h2>🔧 Artisan</h2>
            <p>Offer your services and grow your business</p>
            <a href="<?php echo url('register?role=artisan'); ?>" class="btn btn-secondary btn-block">Continue as Artisan</a>
        </div>
    </div>
    
    <p class="mt-4">
        Already have an account? <a href="<?php echo url('login'); ?>">Login</a>
    </p>
</div>

<?php require 'app/views/layouts/footer.php'; ?>

