<?php
$pageTitle = 'My Profile';
require_once '../includes/parent_header.php';

$db      = getDB();
$pUser   = currentParent();
$success = $error = '';

// Fetch full parent record
$stmt = $db->prepare("SELECT * FROM parent_accounts WHERE id = ?");
$stmt->execute([$pUser['id']]);
$parent = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security error. Please try again.';
    } else {
        $tab = $_POST['tab'] ?? 'info';

        if ($tab === 'info') {
            $name  = trim($_POST['full_name']);
            $email = trim($_POST['email'] ?? '');

            if (strlen($name) < 2) {
                $error = 'Full name must be at least 2 characters.';
            } else {
                $db->prepare("UPDATE parent_accounts SET full_name=?, email=? WHERE id=?")
                   ->execute([$name, $email, $pUser['id']]);
                $_SESSION['parent_name'] = $name;
                $parent['full_name'] = $name;
                $parent['email']     = $email;
                $success = 'Your profile has been updated.';
            }

        } elseif ($tab === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password']     ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!password_verify($current, $parent['password'])) {
                $error = 'Current password is incorrect.';
            } elseif (strlen($new) < 8) {
                $error = 'New password must be at least 8 characters.';
            } elseif ($new !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $hash = password_hash($new, PASSWORD_BCRYPT);
                $db->prepare("UPDATE parent_accounts SET password=? WHERE id=?")->execute([$hash, $pUser['id']]);
                $success = 'Password changed successfully.';
            }
        }
    }
}
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger mb-4"><i class="fas fa-exclamation-circle me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<div class="row g-4">

    <!-- Profile sidebar -->
    <div class="col-lg-3">
        <div class="dash-card text-center p-4">
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--navy),var(--navy-light));border:3px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:2rem;color:var(--gold);margin:0 auto 16px;">
                <?= strtoupper(substr($parent['full_name'], 0, 1)) ?>
            </div>
            <div class="fw-bold" style="font-size:1rem;"><?= e($parent['full_name']) ?></div>
            <div class="text-muted small mt-1"><?= e($parent['phone']) ?></div>
            <span class="pill pill-gold mt-2" style="background:var(--gold-pale);color:var(--gold);">Parent Account</span>

            <hr class="my-3">

            <div class="text-start small">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Linked Children</span>
                    <?php
                    $cc = $db->prepare("SELECT COUNT(*) FROM parent_student_link WHERE parent_id=?");
                    $cc->execute([$pUser['id']]);
                    ?>
                    <strong><?= $cc->fetchColumn() ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span class="text-muted">Account Status</span>
                    <span class="pill pill-success" style="font-size:0.7rem;padding:2px 8px;">Active</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Member Since</span>
                    <strong><?= date('M Y', strtotime($parent['created_at'])) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Forms -->
    <div class="col-lg-9">

        <!-- Profile info -->
        <div class="dash-card mb-4">
            <div class="dash-card-header">
                <h6><i class="fas fa-user-edit me-2 text-gold"></i>Personal Information</h6>
            </div>
            <div class="dash-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="tab" value="info">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required
                                   value="<?= e($parent['full_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" value="<?= e($parent['phone']) ?>" disabled>
                            <div class="form-text">Contact the school office to change your phone number.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= e($parent['email'] ?? '') ?>"
                                   placeholder="optional — for notifications">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-navy px-4">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change password -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h6><i class="fas fa-lock me-2 text-gold"></i>Change Password</h6>
            </div>
            <div class="dash-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="tab" value="password">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required
                                   placeholder="Enter your current password">
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <label class="form-label">New Password *</label>
                            <input type="password" name="new_password" class="form-control" required
                                   placeholder="Minimum 8 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required
                                   placeholder="Repeat new password">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-navy px-4">
                                <i class="fas fa-key me-2"></i>Update Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/parent_footer.php'; ?>
