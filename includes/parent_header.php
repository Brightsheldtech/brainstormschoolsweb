<?php
require_once __DIR__ . '/config.php';
requireParentLogin();
$pUser       = currentParent();
$db          = getDB();
$currentPath = $_SERVER['PHP_SELF'];

// Load all children for this parent
$childrenStmt = $db->prepare("
    SELECT s.id, s.full_name, s.student_id, s.gender, c.name AS class_name
    FROM parent_student_link psl
    JOIN students s ON psl.student_id = s.id
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE psl.parent_id = ?
    ORDER BY s.full_name
");
$childrenStmt->execute([$pUser['id']]);
$children = $childrenStmt->fetchAll();

// Switch active child
if (isset($_GET['child']) && is_numeric($_GET['child'])) {
    $ids = array_column($children, 'id');
    if (in_array((int)$_GET['child'], $ids)) {
        $_SESSION['active_child_id'] = (int)$_GET['child'];
        $pUser['child_id'] = (int)$_GET['child'];
    }
    $clean = strtok($_SERVER['REQUEST_URI'], '?');
    redirect($clean);
}

$activeChild = null;
if ($pUser['child_id']) {
    foreach ($children as $c) {
        if ($c['id'] == $pUser['child_id']) { $activeChild = $c; break; }
    }
}
if (!$activeChild && !empty($children)) {
    $activeChild = $children[0];
    $_SESSION['active_child_id'] = $activeChild['id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>Parent Portal | <?= SITE_NAME ?></title>
    <link rel="icon" href="/assets/images/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,600&family=Lato:wght@300;400;700&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        /* ── Parent sidebar colour overrides ── */
        .dash-sidebar { background: linear-gradient(180deg, #07142a 0%, #0d1b4b 100%); }
        .parent-child-card {
            background: rgba(201,162,39,0.1);
            border: 1px solid rgba(201,162,39,0.2);
            border-radius: 14px;
            padding: 16px;
            margin: 0 12px 16px;
        }
        .child-avatar {
            width: 48px; height: 48px;
            border-radius: 50%;
            background: var(--navy-light);
            color: var(--gold);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            border: 2px solid var(--gold);
            flex-shrink: 0;
        }
        .child-name { font-family:'Lato',sans-serif; font-weight:700; color:var(--white); font-size:0.9rem; }
        .child-class { font-size:0.75rem; color:var(--gold); font-family:'Lato',sans-serif; }
        .switch-child-btn {
            display: block;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 8px 12px;
            color: rgba(255,255,255,0.6);
            font-family: 'Lato', sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            text-align: center;
            margin-top: 10px;
            transition: all 0.25s;
            cursor: pointer;
        }
        .switch-child-btn:hover { background:rgba(201,162,39,0.15); color:var(--gold); }
        .parent-welcome { padding: 20px 22px 8px; }
        .parent-welcome .greeting {
            font-family:'Dancing Script',cursive;
            font-size:1rem;
            color:var(--gold);
        }
        .parent-welcome .pname {
            font-family:'Lato',sans-serif;
            font-weight:700;
            font-size:0.88rem;
            color:rgba(255,255,255,0.75);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="dash-sidebar" id="sidebar">
    <div class="dash-sidebar-logo">
        <img src="/assets/images/logo.png" alt="<?= SITE_NAME ?>">
        <div>
            <div class="name"><?= SITE_NAME ?></div>
            <div class="tag">Parent Portal</div>
        </div>
    </div>

    <!-- Greeting -->
    <div class="parent-welcome">
        <div class="greeting">Hello,</div>
        <div class="pname"><?= e(explode(' ', $pUser['name'])[0]) ?> 👋</div>
    </div>

    <!-- Active child card -->
    <?php if ($activeChild): ?>
    <div class="parent-child-card">
        <div class="d-flex align-items-center gap-3">
            <div class="child-avatar">
                <?= strtoupper(substr($activeChild['full_name'], 0, 1)) ?>
            </div>
            <div>
                <div class="child-name"><?= e($activeChild['full_name']) ?></div>
                <div class="child-class"><?= e($activeChild['class_name'] ?? '—') ?> &bull; <?= e($activeChild['student_id']) ?></div>
            </div>
        </div>
        <?php if (count($children) > 1): ?>
        <div class="dropdown">
            <div class="switch-child-btn dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-exchange-alt me-1"></i> Switch Child
            </div>
            <ul class="dropdown-menu" style="min-width:200px;">
                <?php foreach ($children as $ch): ?>
                <li>
                    <a class="dropdown-item <?= $ch['id'] == $activeChild['id'] ? 'active' : '' ?>"
                       href="?child=<?= $ch['id'] ?>">
                        <?= e($ch['full_name']) ?>
                        <small class="d-block text-muted"><?= e($ch['class_name'] ?? '') ?></small>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Nav -->
    <nav class="dash-nav">
        <div class="dash-nav-section">My Portal</div>
        <a href="/parent/index.php"      class="<?= str_contains($currentPath,'parent/index') ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="/parent/results.php"    class="<?= str_contains($currentPath,'parent/results') ? 'active' : '' ?>">
            <i class="fas fa-graduation-cap"></i> Results
        </a>
        <a href="/parent/attendance.php" class="<?= str_contains($currentPath,'parent/attendance') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-check"></i> Attendance
        </a>
        <a href="/parent/profile.php"    class="<?= str_contains($currentPath,'parent/profile') ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i> My Profile
        </a>

        <div class="dash-nav-section">School</div>
        <a href="/news.php" target="_blank"><i class="fas fa-newspaper"></i> News & Updates</a>
        <a href="/events.php" target="_blank"><i class="fas fa-calendar-alt"></i> Events</a>
        <a href="/contact.php" target="_blank"><i class="fas fa-envelope"></i> Contact School</a>

        <div class="dash-nav-section">Account</div>
        <a href="/"><i class="fas fa-globe"></i> School Website</a>
        <a href="/parent-logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </nav>
</aside>

<!-- Main content -->
<div class="dash-content">
    <div class="dash-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm d-lg-none" onclick="toggleSidebar()" style="border:none;background:none;font-size:1.2rem;color:var(--navy);">
                <i class="fas fa-bars"></i>
            </button>
            <h6 class="page-title"><?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?></h6>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?php if ($activeChild): ?>
            <span class="pill pill-navy d-none d-md-inline-flex align-items-center gap-1" style="font-size:0.8rem;padding:5px 14px;">
                <i class="fas fa-user-graduate me-1"></i>
                <?= e($activeChild['full_name']) ?>
            </span>
            <?php endif; ?>
            <div class="dropdown">
                <div class="user-chip dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="avatar-initials">
                        <?= strtoupper(substr($pUser['name'], 0, 1)) ?>
                    </div>
                    <span class="d-none d-sm-inline"><?= e(explode(' ', $pUser['name'])[0]) ?></span>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;">
                    <li><div class="px-3 py-2 border-bottom">
                        <div class="fw-bold small"><?= e($pUser['name']) ?></div>
                        <div class="text-muted" style="font-size:0.75rem;"><?= e($pUser['phone']) ?></div>
                        <span class="pill pill-gold mt-1" style="background:var(--gold-pale);color:var(--gold);">Parent</span>
                    </div></li>
                    <li><a class="dropdown-item" href="/parent/profile.php"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="/"><i class="fas fa-globe me-2"></i>School Website</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/parent-logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="dash-main">
