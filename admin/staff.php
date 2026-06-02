<?php
$pageTitle = 'Staff Management';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$action  = $_GET['action'] ?? '';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $name    = trim($_POST['full_name']);
        $email   = trim($_POST['email']);
        $role    = $_POST['role'];
        $phone   = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');

        if ($_POST['edit_id'] ?? false) {
            $stmt = $db->prepare("UPDATE users SET full_name=?,email=?,role=?,phone=?,subject=? WHERE id=?");
            $stmt->execute([$name, $email, $role, $phone, $subject, (int)$_POST['edit_id']]);
            if (!empty($_POST['new_password'])) {
                $hash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
                $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, (int)$_POST['edit_id']]);
            }
            $success = 'Staff member updated.';
        } else {
            if (empty($_POST['password'])) { $error = 'Password is required for new staff.'; }
            else {
                $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO users (full_name,email,password,role,phone,subject) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$name, $email, $hash, $role, $phone, $subject]);
                $success = 'Staff account created.';
            }
        }
        if (!$error) $action = '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $db->prepare("DELETE FROM users WHERE id=? AND id != ?")->execute([(int)$_POST['delete_id'], $user['id']]);
        $success = 'Staff member removed.';
    }
}

if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $db->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE id=?")->execute([$_GET['toggle']]);
    $success = 'Status updated.';
}

$editUser = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editUser = $stmt->fetch();
}

$staff = $db->query("SELECT * FROM users ORDER BY role, full_name")->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger mb-4"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-user-plus me-2 text-gold"></i><?= $action === 'edit' ? 'Edit Staff' : 'Add Staff Member' ?></h6>
        <a href="/admin/staff.php" class="btn btn-sm btn-outline-gold">Cancel</a>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <?php if ($editUser): ?><input type="hidden" name="edit_id" value="<?= $editUser['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= e($editUser['full_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($editUser['email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-select">
                        <option value="teacher" <?= ($editUser['role'] ?? 'teacher') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        <option value="admin"   <?= ($editUser['role'] ?? '') === 'admin'   ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($editUser['phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Subject / Dept</label>
                    <input type="text" name="subject" class="form-control" placeholder="e.g. Mathematics" value="<?= e($editUser['subject'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= $editUser ? 'New Password (leave blank to keep)' : 'Password *' ?></label>
                    <input type="password" name="<?= $editUser ? 'new_password' : 'password' ?>" class="form-control" <?= $editUser ? '' : 'required' ?> placeholder="Min 8 characters">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-navy px-4"><i class="fas fa-save me-2"></i>Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-users me-2 text-gold"></i>All Staff (<?= count($staff) ?>)</h6>
        <a href="/admin/staff.php?action=add" class="btn btn-navy btn-sm"><i class="fas fa-plus me-1"></i>Add Staff</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Subject</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($staff as $s): ?>
                <tr>
                    <td class="fw-semibold"><?= e($s['full_name']) ?></td>
                    <td class="small"><?= e($s['email']) ?></td>
                    <td><span class="pill <?= $s['role'] === 'admin' ? 'pill-navy' : 'pill-info' ?>"><?= ucfirst($s['role']) ?></span></td>
                    <td class="small"><?= e($s['subject'] ?: '—') ?></td>
                    <td><span class="pill <?= $s['status'] === 'active' ? 'pill-success' : 'pill-danger' ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Edit</a>
                            <?php if ($s['id'] != $user['id']): ?>
                            <a href="?toggle=<?= $s['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2"><?= $s['status'] === 'active' ? 'Disable' : 'Enable' ?></a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this staff member?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Del</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
