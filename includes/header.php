<?php
require_once __DIR__ . '/config.php';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? e($pageDesc) : SITE_NAME . ' — ' . SITE_TAGLINE ?>">
    <link rel="icon" href="/assets/images/logo.png" type="image/png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Lato:wght@300;400;700&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <?= isset($extraHead) ? $extraHead : '' ?>
</head>
<body>

<!-- Top Info Bar -->
<div class="top-bar d-none d-md-block">
    <div class="container">
        <div class="row align-items-center py-1">
            <div class="col-md-8">
                <span class="me-4"><i class="fas fa-envelope me-1"></i> <?= SITE_EMAIL ?></span>
                <span class="me-4"><i class="fas fa-phone me-1"></i> <?= SITE_PHONE ?></span>
                <span><i class="fas fa-map-marker-alt me-1"></i> <?= SITE_ADDRESS ?></span>
            </div>
            <div class="col-md-4 text-end">
                <a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank" class="topbar-link me-3">
                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                </a>
                <a href="https://www.facebook.com" target="_blank" class="topbar-link me-3"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com" target="_blank" class="topbar-link"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/">
            <img src="/assets/images/logo.png" alt="<?= SITE_NAME ?> Logo" height="55" class="logo-img">
            <div class="d-none d-sm-block">
                <div class="brand-name"><?= SITE_NAME ?></div>
                <div class="brand-tagline"><?= SITE_TAGLINE ?></div>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" href="/about.php">About</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, ['admissions', 'results']) ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown">Academics</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admissions.php"><i class="fas fa-file-alt me-2 text-gold"></i>Admissions</a></li>
                        <li><a class="dropdown-item" href="/results.php"><i class="fas fa-graduation-cap me-2 text-gold"></i>Check Results</a></li>
                        <li><a class="dropdown-item" href="/about.php#curriculum"><i class="fas fa-book me-2 text-gold"></i>Curriculum</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'news' ? 'active' : '' ?>" href="/news.php">News</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'events' ? 'active' : '' ?>" href="/events.php">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'gallery' ? 'active' : '' ?>" href="/gallery.php">Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="/contact.php">Contact</a>
                </li>
                <li class="nav-item ms-lg-2 dropdown">
                    <a href="#" class="btn btn-gold px-3 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width:210px;">
                        <li>
                            <a class="dropdown-item py-2" href="/parent-login.php">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(13,27,75,0.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-users" style="color:var(--navy);font-size:0.95rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small" style="color:var(--navy);">Parent Portal</div>
                                        <div class="text-muted" style="font-size:0.72rem;">View results &amp; attendance</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2" href="/login.php">
                                <div class="d-flex align-items-center gap-3">
                                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(201,162,39,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i class="fas fa-user-shield" style="color:var(--gold);font-size:0.95rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small" style="color:var(--navy);">Staff Login</div>
                                        <div class="text-muted" style="font-size:0.72rem;">Admin &amp; teacher portal</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
