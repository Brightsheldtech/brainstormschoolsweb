<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
$currentPath = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?> Portal</title>
    <link rel="icon" href="/assets/images/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Lato:wght@300;400;700&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<!-- Sidebar -->
<aside class="dash-sidebar" id="sidebar">
    <div class="dash-sidebar-logo">
        <img src="/assets/images/logo.png" alt="<?= SITE_NAME ?>">
        <div>
            <div class="name"><?= SITE_NAME ?></div>
            <div class="tag"><?= ucfirst($user['role']) ?> Portal</div>
        </div>
    </div>
    <nav class="dash-nav">
        <?php if ($user['role'] === 'admin'): ?>
        <div class="dash-nav-section">Main</div>
        <a href="/admin/index.php"       class="<?= str_contains($currentPath, '/admin/index') ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>

        <div class="dash-nav-section">Students</div>
        <a href="/admin/students.php"    class="<?= str_contains($currentPath, 'students') ? 'active' : '' ?>">
            <i class="fas fa-user-graduate"></i> All Students
        </a>
        <a href="/admin/attendance.php"  class="<?= str_contains($currentPath, 'attendance') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-check"></i> Attendance
        </a>

        <div class="dash-nav-section">Academics</div>
        <a href="/admin/results.php"     class="<?= str_contains($currentPath, 'results') ? 'active' : '' ?>">
            <i class="fas fa-graduation-cap"></i> Results
        </a>
        <a href="/admin/subjects.php"    class="<?= str_contains($currentPath, 'subjects') ? 'active' : '' ?>">
            <i class="fas fa-book"></i> Subjects
        </a>
        <a href="/admin/classes.php"     class="<?= str_contains($currentPath, 'classes') ? 'active' : '' ?>">
            <i class="fas fa-layer-group"></i> Classes
        </a>

        <div class="dash-nav-section">Administration</div>
        <a href="/admin/admissions.php"  class="<?= str_contains($currentPath, 'admissions') ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Admissions
        </a>
        <a href="/admin/parents.php"     class="<?= str_contains($currentPath, 'parents') ? 'active' : '' ?>">
            <i class="fas fa-user-friends"></i> Parents
        </a>
        <a href="/admin/staff.php"       class="<?= str_contains($currentPath, 'staff') ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Staff
        </a>
        <a href="/admin/news.php"        class="<?= str_contains($currentPath, '/admin/news') ? 'active' : '' ?>">
            <i class="fas fa-newspaper"></i> News
        </a>
        <a href="/admin/events.php"      class="<?= str_contains($currentPath, '/admin/events') ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i> Events
        </a>
        <a href="/admin/gallery.php"     class="<?= str_contains($currentPath, '/admin/gallery') ? 'active' : '' ?>">
            <i class="fas fa-images"></i> Gallery
        </a>
        <a href="/admin/messages.php"    class="<?= str_contains($currentPath, 'messages') ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Messages
        </a>

        <?php else: ?>
        <div class="dash-nav-section">Main</div>
        <a href="/teacher/index.php"     class="<?= str_contains($currentPath, 'teacher/index') ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="/teacher/results.php"   class="<?= str_contains($currentPath, 'teacher/results') ? 'active' : '' ?>">
            <i class="fas fa-graduation-cap"></i> Enter Results
        </a>
        <a href="/teacher/attendance.php" class="<?= str_contains($currentPath, 'teacher/attendance') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-check"></i> Attendance
        </a>
        <?php endif; ?>

        <div class="dash-nav-section">Account</div>
        <a href="/"><i class="fas fa-globe"></i> School Website</a>
        <a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </nav>
</aside>

<!-- Main Content -->
<div class="dash-content">
    <!-- Topbar -->
    <div class="dash-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-lg-none" onclick="toggleSidebar()" style="border:none;background:none;font-size:1.2rem;color:var(--navy);">
                <i class="fas fa-bars"></i>
            </button>
            <h6 class="page-title"><?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?></h6>
        </div>
        <div class="dropdown">
            <div class="user-chip dropdown-toggle" data-bs-toggle="dropdown">
                <div class="avatar-initials">
                    <?= strtoupper(substr($user['name'], 0, 1) . substr(strrchr($user['name'], ' '), 1, 1)) ?>
                </div>
                <span><?= e(explode(' ', $user['name'])[0]) ?></span>
            </div>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;">
                <li><div class="px-3 py-2 border-bottom">
                    <div class="fw-bold small"><?= e($user['name']) ?></div>
                    <div class="text-muted" style="font-size:0.78rem;"><?= e($user['email']) ?></div>
                    <span class="pill pill-navy mt-1"><?= ucfirst($user['role']) ?></span>
                </div></li>
                <li><a class="dropdown-item" href="/"><i class="fas fa-globe me-2"></i>Visit Website</a></li>
                <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
            </ul>
        </div>
    </div>

    <div class="dash-main">
