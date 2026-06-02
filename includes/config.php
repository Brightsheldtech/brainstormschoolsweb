<?php
// Database Configuration — update these with your Hostinger credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'brainstorm_school');

// Site Configuration
define('SITE_NAME', 'Brainstorm School');
define('SITE_TAGLINE', 'Home of Future Career');
define('SITE_URL', 'https://yourdomain.com'); // update after deployment
define('SITE_EMAIL', 'info@brainstormschool.com');
define('SITE_PHONE', '+234 000 000 0000');
define('SITE_ADDRESS', 'No. 1 School Road, Your City, Nigeria');
define('SITE_WHATSAPP', '2340000000000');
define('ACADEMIC_YEAR', '2024/2025');
define('CURRENT_TERM', 'First Term');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="padding:20px;background:#fee;border:1px solid #f00;font-family:sans-serif;">
                <strong>Database connection failed.</strong> Please check your config.php settings.<br>
                <small>' . htmlspecialchars($e->getMessage()) . '</small>
            </div>');
        }
    }
    return $pdo;
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit;
}

// Helper: sanitize output
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper: grade from score
function getGrade($score) {
    if ($score >= 75) return ['grade' => 'A', 'remark' => 'Excellent'];
    if ($score >= 65) return ['grade' => 'B', 'remark' => 'Very Good'];
    if ($score >= 55) return ['grade' => 'C', 'remark' => 'Good'];
    if ($score >= 45) return ['grade' => 'D', 'remark' => 'Pass'];
    if ($score >= 40) return ['grade' => 'E', 'remark' => 'Fair'];
    return ['grade' => 'F', 'remark' => 'Fail'];
}

// Helper: check if logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: require login
function requireLogin($role = null) {
    if (!isLoggedIn()) {
        redirect('/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
    if ($role && $_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        redirect('/login.php?error=unauthorized');
    }
}

// Helper: CSRF token
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Parent Portal Helpers ──────────────────────────

function isParentLoggedIn() {
    return isset($_SESSION['parent_id']);
}

function requireParentLogin() {
    if (!isParentLoggedIn()) {
        redirect('/parent-login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

function currentParent() {
    if (!isParentLoggedIn()) return null;
    return [
        'id'       => $_SESSION['parent_id'],
        'name'     => $_SESSION['parent_name'],
        'phone'    => $_SESSION['parent_phone'],
        'child_id' => $_SESSION['active_child_id'] ?? null,
    ];
}

function loginParent($phone, $password) {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM parent_accounts WHERE phone = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$phone]);
    $parent = $stmt->fetch();

    if ($parent && password_verify($password, $parent['password'])) {
        $_SESSION['parent_id']    = $parent['id'];
        $_SESSION['parent_name']  = $parent['full_name'];
        $_SESSION['parent_phone'] = $parent['phone'];

        // Load their first child as the active child
        $link = $db->prepare("SELECT student_id FROM parent_student_link WHERE parent_id = ? LIMIT 1");
        $link->execute([$parent['id']]);
        $first = $link->fetchColumn();
        $_SESSION['active_child_id'] = $first ?: null;

        // Update last login
        $db->prepare("UPDATE parent_accounts SET last_login = NOW() WHERE id = ?")->execute([$parent['id']]);

        session_regenerate_id(true);
        return $parent;
    }
    return false;
}

function logoutParent() {
    unset(
        $_SESSION['parent_id'],
        $_SESSION['parent_name'],
        $_SESSION['parent_phone'],
        $_SESSION['active_child_id']
    );
    redirect('/parent-login.php');
}
