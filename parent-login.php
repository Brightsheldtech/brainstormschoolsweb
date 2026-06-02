<?php
$pageTitle = 'Parent & Student Portal';
require_once 'includes/config.php';

// Already logged in → redirect to dashboard
if (isParentLoggedIn()) {
    redirect('/parent/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security error. Please refresh and try again.';
    } else {
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$phone || !$password) {
            $error = 'Please enter your phone number and password.';
        } else {
            $parent = loginParent($phone, $password);
            if ($parent) {
                $redir = $_GET['redirect'] ?? '';
                if (!$redir || !str_starts_with($redir, '/') || str_starts_with($redir, '//')) {
                    $redir = '/parent/index.php';
                }
                redirect($redir);
            } else {
                $error = 'Incorrect phone number or password. Please try again or contact the school.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent & Student Portal — <?= SITE_NAME ?></title>
    <link rel="icon" href="/assets/images/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,600&family=Lato:wght@400;700&family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #fdf6ec 0%, #f5ede0 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .portal-wrap {
            width: 100%;
            max-width: 920px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(13,27,75,0.18);
        }

        /* Left panel */
        .portal-left {
            background: linear-gradient(160deg, var(--navy-dark) 0%, var(--navy) 60%, #1a2e6e 100%);
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .portal-left::before {
            content: '';
            position: absolute;
            bottom: -60px; right: -60px;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(201,162,39,0.08);
        }
        .portal-left::after {
            content: '';
            position: absolute;
            top: -40px; left: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
        }
        .portal-left-content { position: relative; z-index: 1; }
        .portal-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 44px;
        }
        .portal-logo img {
            height: 60px;
            border-radius: 50%;
            border: 3px solid var(--gold);
            box-shadow: 0 0 0 5px rgba(201,162,39,0.15);
        }
        .portal-logo .school-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--white);
            line-height: 1.15;
        }
        .portal-logo .school-tag {
            font-family: 'Dancing Script', cursive;
            font-size: 0.9rem;
            color: var(--gold);
        }

        .portal-left h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--white);
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .portal-left h2 em { color: var(--gold); font-style: italic; }
        .portal-left p {
            color: rgba(255,255,255,0.6);
            font-size: 0.93rem;
            line-height: 1.75;
            margin-bottom: 32px;
        }
        .portal-features { display: flex; flex-direction: column; gap: 14px; }
        .portal-feature {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .portal-feature .icon {
            width: 38px; height: 38px; min-width: 38px;
            border-radius: 10px;
            background: rgba(201,162,39,0.15);
            border: 1px solid rgba(201,162,39,0.2);
            display: flex; align-items: center; justify-content: center;
            color: var(--gold);
            font-size: 0.95rem;
        }
        .portal-feature span {
            font-family: 'Lato', sans-serif;
            font-size: 0.87rem;
            font-weight: 700;
            color: rgba(255,255,255,0.75);
        }
        .portal-left-footer {
            position: relative; z-index: 1;
            margin-top: 44px;
        }
        .portal-left-footer p {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.35);
            margin: 0;
        }

        /* Right panel (form) */
        .portal-right {
            background: var(--white);
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .portal-right h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--navy);
            margin-bottom: 6px;
        }
        .portal-right .sub {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 32px;
            font-family: 'Lato', sans-serif;
        }
        .show-pass { cursor: pointer; border-left: none !important; }
        .portal-divider {
            display: flex; align-items: center; gap: 14px;
            margin: 24px 0;
        }
        .portal-divider::before,
        .portal-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--gray-200);
        }
        .portal-divider span {
            font-family: 'Lato', sans-serif;
            font-size: 0.78rem;
            color: var(--gray-400);
            font-weight: 700;
            white-space: nowrap;
        }

        @media (max-width: 700px) {
            .portal-wrap { grid-template-columns: 1fr; }
            .portal-left { padding: 36px 28px; }
            .portal-right { padding: 36px 28px; }
        }
    </style>
</head>
<body>

<div class="portal-wrap">

    <!-- Left: Branding -->
    <div class="portal-left">
        <div class="portal-left-content">
            <div class="portal-logo">
                <img src="/assets/images/logo.png" alt="<?= SITE_NAME ?>">
                <div>
                    <div class="school-name"><?= SITE_NAME ?></div>
                    <div class="school-tag"><?= SITE_TAGLINE ?></div>
                </div>
            </div>

            <h2>Your Child's <em>School Life</em> in Your Hands</h2>
            <p>Log in to see results, attendance, school news, and more — all in one place, from anywhere in the world.</p>

            <div class="portal-features">
                <div class="portal-feature">
                    <div class="icon"><i class="fas fa-graduation-cap"></i></div>
                    <span>View term results & report cards</span>
                </div>
                <div class="portal-feature">
                    <div class="icon"><i class="fas fa-clipboard-check"></i></div>
                    <span>Check daily attendance records</span>
                </div>
                <div class="portal-feature">
                    <div class="icon"><i class="fas fa-print"></i></div>
                    <span>Download & print report cards</span>
                </div>
                <div class="portal-feature">
                    <div class="icon"><i class="fas fa-newspaper"></i></div>
                    <span>Stay updated with school news</span>
                </div>
                <div class="portal-feature">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <span>Manage multiple children in one account</span>
                </div>
            </div>
        </div>

        <div class="portal-left-footer">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Secured portal.</p>
        </div>
    </div>

    <!-- Right: Login Form -->
    <div class="portal-right">
        <h3>Welcome Back</h3>
        <p class="sub">Sign in with the phone number registered at the school office.</p>

        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2 small" style="border-radius:10px;">
            <i class="fas fa-exclamation-circle"></i>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                    <input type="tel" name="phone" class="form-control"
                           placeholder="+234 800 000 0000"
                           value="<?= e($_POST['phone'] ?? '') ?>"
                           required autofocus>
                </div>
                <div class="form-text text-muted" style="font-size:0.78rem;">
                    Use the phone number provided during your child's admission.
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" id="passField" class="form-control" placeholder="Your password" required>
                    <span class="input-group-text show-pass" onclick="togglePass()">
                        <i class="fas fa-eye text-muted" id="passIcon"></i>
                    </span>
                </div>
                <div class="form-text text-muted" style="font-size:0.78rem;">
                    Password was set by the school admin. Contact the school if you haven't received it.
                </div>
            </div>

            <button type="submit" class="btn btn-navy w-100 py-2 mb-3" style="border-radius:12px;font-size:0.95rem;">
                <i class="fas fa-sign-in-alt me-2"></i> Sign In to Portal
            </button>
        </form>

        <div class="portal-divider"><span>or</span></div>

        <!-- Quick result check — no login needed -->
        <a href="/results.php" class="btn btn-outline-gold w-100 py-2 mb-4" style="border-radius:12px;font-size:0.92rem;">
            <i class="fas fa-search me-2"></i> Quick Result Check (No Login)
        </a>

        <div class="text-center" style="font-size:0.82rem;color:var(--text-muted);">
            <i class="fas fa-info-circle me-1"></i>
            Don't have a portal account?
            <a href="/contact.php" class="text-gold fw-bold">Contact the school office</a>
            to get your login details.
        </div>

        <hr style="margin:24px 0;border-color:var(--gray-200);">

        <div class="text-center">
            <a href="/login.php" class="text-muted" style="font-size:0.82rem;">
                <i class="fas fa-user-tie me-1"></i> Staff Login (Teachers & Admin)
            </a>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <a href="/" class="text-muted" style="font-size:0.82rem;">
                <i class="fas fa-arrow-left me-1"></i> Back to Website
            </a>
        </div>
    </div>

</div>

<script>
function togglePass() {
    const f = document.getElementById('passField');
    const i = document.getElementById('passIcon');
    if (f.type === 'password') { f.type = 'text'; i.classList.replace('fa-eye','fa-eye-slash'); }
    else { f.type = 'password'; i.classList.replace('fa-eye-slash','fa-eye'); }
}
</script>
</body>
</html>
