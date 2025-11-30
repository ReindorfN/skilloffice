<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>SkillOffice</title>
    <link rel="stylesheet" href="<?php echo asset('css/app.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/layout.css'); ?>">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php if (isset($user) && $user): ?>
        <!-- 20:80 Layout with Sidebar -->
        <div class="app-layout">
            <?php require 'app/views/layouts/sidebar.php'; ?>
            
            <div class="main-content">
                <main class="main-content-body">
    <?php else: ?>
        <!-- No sidebar for non-authenticated pages -->
        <main class="container" style="padding: var(--spacing-xl) var(--spacing-md);">
    <?php endif; ?>

