<?php
$pageTitle = 'Staff Login';
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Already logged in → redirect
if (isLoggedIn()) {
    redirect($_SESSION['user_role'] === 'admin' ? '/admin/index.php' : '/teacher/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security error. Please refresh the page and try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Please enter both email and password.';
        } else {
            $user = loginUser($email, $password);
            if ($user) {
                $redir = $_GET['redirect'] ?? '';
                if (!$redir || !str_starts_with($redir, '/') || str_starts_with($redir, '//')) {
                    $redir = $user['role'] === 'admin' ? '/admin/index.php' : '/teacher/index.php';
                }
                redirect($redir);
            } else {
                $error = 'Invalid email or password. Please try again.';
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
    <title>Staff Login — <?= SITE_NAME ?></title>
    <link rel="icon" href="/assets/images/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 60%, var(--navy-light) 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: var(--white); border-radius: 20px; box-shadow: 0 24px 64px rgba(7,14,42,0.45); overflow: hidden; width: 100%; max-width: 440px; }
        .login-header { background: linear-gradient(135deg, var(--navy-dark), var(--navy)); padding: 36px 36px 28px; text-align: center; }
        .login-header img { height: 72px; border-radius: 50%; border: 3px solid var(--gold); margin-bottom: 14px; }
        .login-body { padding: 32px 36px; }
        .show-pass { cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="/assets/images/logo.png" alt="<?= SITE_NAME ?>">
            <h4 style="color:var(--white);margin:0;font-size:1.3rem;"><?= SITE_NAME ?></h4>
            <p style="color:var(--gold);font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;margin:4px 0 0;">Staff Portal</p>
        </div>
        <div class="login-body">
            <h5 class="text-navy mb-1">Welcome Back</h5>
            <p class="text-muted small mb-4">Sign in to access the staff portal.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger small mb-3 py-2">
                <i class="fas fa-exclamation-circle me-1"></i> <?= e($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:var(--off-white);border-color:var(--gray-200);">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" name="email" class="form-control" placeholder="staff@brainstormschool.com"
                               value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:var(--off-white);border-color:var(--gray-200);">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" name="password" id="passwordField" class="form-control" placeholder="Your password" required>
                        <span class="input-group-text show-pass" onclick="togglePass()" style="border-color:var(--gray-200);cursor:pointer;">
                            <i class="fas fa-eye text-muted" id="passIcon"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn btn-navy w-100 py-2 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                </button>
            </form>

            <div class="text-center">
                <a href="/" class="small text-muted"><i class="fas fa-arrow-left me-1"></i> Back to School Website</a>
            </div>

            <hr class="my-4">
            <p class="text-center text-muted" style="font-size:0.78rem;">
                Forgot your password? Contact the school administrator.
            </p>
        </div>
    </div>

    <script>
    function togglePass() {
        const f = document.getElementById('passwordField');
        const i = document.getElementById('passIcon');
        if (f.type === 'password') { f.type = 'text'; i.classList.replace('fa-eye', 'fa-eye-slash'); }
        else { f.type = 'password'; i.classList.replace('fa-eye-slash', 'fa-eye'); }
    }
    </script>
</body>
</html>
