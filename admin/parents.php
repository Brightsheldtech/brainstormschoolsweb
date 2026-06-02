<?php
$pageTitle = 'Parent Accounts';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$action  = $_GET['action'] ?? '';
$success = $error = '';

// ── POST handler ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security error. Please try again.';
    } else {
        $postAction = $_POST['post_action'] ?? '';

        // ── Add / Edit parent account ──
        if ($postAction === 'save_parent') {
            $name     = trim($_POST['full_name']);
            $phone    = trim($_POST['phone']);
            $email    = trim($_POST['email'] ?? '');
            $editId   = (int)($_POST['edit_id'] ?? 0);
            $status   = $_POST['status'] ?? 'active';

            if (strlen($name) < 2 || strlen($phone) < 8) {
                $error = 'Full name and phone number are required.';
            } elseif ($editId) {
                $db->prepare("UPDATE parent_accounts SET full_name=?,phone=?,email=?,status=? WHERE id=?")
                   ->execute([$name, $phone, $email, $status, $editId]);
                if (!empty($_POST['new_password'])) {
                    $hash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
                    $db->prepare("UPDATE parent_accounts SET password=? WHERE id=?")->execute([$hash, $editId]);
                }
                $success = 'Parent account updated.';
            } else {
                if (empty($_POST['password'])) {
                    $error = 'Password is required for new accounts.';
                } else {
                    // Check phone unique
                    $chk = $db->prepare("SELECT id FROM parent_accounts WHERE phone=?");
                    $chk->execute([$phone]);
                    if ($chk->fetch()) {
                        $error = 'A parent account with that phone number already exists.';
                    } else {
                        $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
                        $db->prepare("INSERT INTO parent_accounts (full_name,phone,email,password,status) VALUES (?,?,?,?,?)")
                           ->execute([$name, $phone, $email, $hash, $status]);
                        $success = 'Parent account created. They can now log in at /parent-login.php.';
                    }
                }
            }
            if (!$error) $action = '';
        }

        // ── Link / unlink a child ──
        if ($postAction === 'link_child') {
            $parentId     = (int)$_POST['parent_id'];
            $studentId    = (int)$_POST['student_id'];
            $relationship = trim($_POST['relationship'] ?? 'Parent');

            if ($parentId && $studentId) {
                try {
                    $db->prepare("INSERT INTO parent_student_link (parent_id,student_id,relationship) VALUES (?,?,?)")
                       ->execute([$parentId, $studentId, $relationship]);
                    $success = 'Child linked successfully.';
                } catch (PDOException $e) {
                    $error = 'This child is already linked to that parent.';
                }
            }
        }

        if ($postAction === 'unlink_child') {
            $parentId  = (int)$_POST['parent_id'];
            $studentId = (int)$_POST['student_id'];
            $db->prepare("DELETE FROM parent_student_link WHERE parent_id=? AND student_id=?")
               ->execute([$parentId, $studentId]);
            $success = 'Child unlinked.';
        }
    }
}

// ── Toggle status ───────────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $db->prepare("UPDATE parent_accounts SET status = IF(status='active','inactive','active') WHERE id=?")
       ->execute([$_GET['toggle']]);
    $success = 'Status updated.';
}

// ── Delete parent ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $db->prepare("DELETE FROM parent_student_link WHERE parent_id=?")->execute([(int)$_POST['delete_id']]);
        $db->prepare("DELETE FROM parent_accounts WHERE id=?")->execute([(int)$_POST['delete_id']]);
        $success = 'Parent account deleted.';
    }
}

// ── Load edit target ────────────────────────────────────────
$editParent = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM parent_accounts WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editParent = $stmt->fetch();
}

// ── Load manage-children target ────────────────────────────
$manageParent = null;
$linkedChildren = $unlinkedStudents = [];
if ($action === 'children' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM parent_accounts WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $manageParent = $stmt->fetch();

    if ($manageParent) {
        $linkedChildren = $db->prepare("
            SELECT s.id, s.full_name, s.student_id, c.name AS class_name, psl.relationship
            FROM parent_student_link psl
            JOIN students s ON psl.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE psl.parent_id = ?
            ORDER BY s.full_name
        ");
        $linkedChildren->execute([$manageParent['id']]);
        $linkedChildren = $linkedChildren->fetchAll();

        $linkedIds = array_column($linkedChildren, 'id');
        $unlinkedStudents = $db->query("
            SELECT s.id, s.full_name, s.student_id, c.name AS class_name
            FROM students s LEFT JOIN classes c ON s.class_id = c.id
            ORDER BY c.name, s.full_name
        ")->fetchAll();

        // Filter out already-linked
        $unlinkedStudents = array_filter($unlinkedStudents, fn($s) => !in_array($s['id'], $linkedIds));
    }
}

// ── Main list ───────────────────────────────────────────────
$search  = trim($_GET['q'] ?? '');
$parents = $db->prepare("
    SELECT pa.*,
           COUNT(psl.student_id) AS child_count
    FROM parent_accounts pa
    LEFT JOIN parent_student_link psl ON pa.id = psl.parent_id
    WHERE (pa.full_name LIKE ? OR pa.phone LIKE ? OR pa.email LIKE ?)
    GROUP BY pa.id
    ORDER BY pa.full_name
");
$parents->execute(["%$search%", "%$search%", "%$search%"]);
$parents = $parents->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger mb-4"><i class="fas fa-exclamation-circle me-2"></i><?= e($error) ?></div>
<?php endif; ?>

<!-- ── Add / Edit Form ── -->
<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-user-plus me-2 text-gold"></i><?= $action === 'edit' ? 'Edit Parent Account' : 'Add Parent Account' ?></h6>
        <a href="/admin/parents.php" class="btn btn-sm btn-outline-gold">Cancel</a>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token"    value="<?= csrfToken() ?>">
            <input type="hidden" name="post_action"   value="save_parent">
            <?php if ($editParent): ?>
            <input type="hidden" name="edit_id"       value="<?= $editParent['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required
                           placeholder="e.g. Mrs. Adaobi Okafor"
                           value="<?= e($editParent['full_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number * <small class="text-muted">(used to log in)</small></label>
                    <input type="tel" name="phone" class="form-control" required
                           placeholder="e.g. 08012345678"
                           value="<?= e($editParent['phone'] ?? '') ?>"
                           <?= $editParent ? 'readonly' : '' ?>>
                    <?php if ($editParent): ?>
                    <div class="form-text">Phone number cannot be changed here — it is the login credential.</div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <small class="text-muted">(optional)</small></label>
                    <input type="email" name="email" class="form-control"
                           placeholder="parent@example.com"
                           value="<?= e($editParent['email'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Account Status</label>
                    <select name="status" class="form-select">
                        <option value="active"   <?= ($editParent['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($editParent['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <label class="form-label"><?= $editParent ? 'New Password <small class="text-muted">(leave blank to keep)</small>' : 'Password *' ?></label>
                    <input type="password" name="<?= $editParent ? 'new_password' : 'password' ?>"
                           class="form-control" <?= $editParent ? '' : 'required' ?>
                           placeholder="Minimum 8 characters">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-navy px-4">
                        <i class="fas fa-save me-2"></i><?= $editParent ? 'Save Changes' : 'Create Account' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── Manage Children ── -->
<?php if ($action === 'children' && $manageParent): ?>
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-users me-2 text-gold"></i>
            Manage Children — <span style="color:var(--gold);"><?= e($manageParent['full_name']) ?></span>
        </h6>
        <a href="/admin/parents.php" class="btn btn-sm btn-outline-gold">Back to list</a>
    </div>
    <div class="dash-card-body">

        <div class="row g-4">

            <!-- Linked children -->
            <div class="col-md-6">
                <h6 class="mb-3 text-navy"><i class="fas fa-link me-2"></i>Linked Children (<?= count($linkedChildren) ?>)</h6>
                <?php if (empty($linkedChildren)): ?>
                <p class="text-muted small">No children linked yet.</p>
                <?php else: ?>
                <div class="d-flex flex-column gap-2">
                <?php foreach ($linkedChildren as $ch): ?>
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border" style="background:var(--cream);">
                    <div>
                        <div class="fw-bold small"><?= e($ch['full_name']) ?></div>
                        <div class="text-muted" style="font-size:0.75rem;">
                            <?= e($ch['class_name'] ?? '—') ?> &bull; <?= e($ch['student_id']) ?>
                            &bull; <em><?= e($ch['relationship']) ?></em>
                        </div>
                    </div>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this child link?')">
                        <input type="hidden" name="csrf_token"  value="<?= csrfToken() ?>">
                        <input type="hidden" name="post_action" value="unlink_child">
                        <input type="hidden" name="parent_id"   value="<?= $manageParent['id'] ?>">
                        <input type="hidden" name="student_id"  value="<?= $ch['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="fas fa-unlink"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Link new child -->
            <div class="col-md-6">
                <h6 class="mb-3 text-navy"><i class="fas fa-user-plus me-2"></i>Link a Student</h6>
                <?php if (empty($unlinkedStudents)): ?>
                <p class="text-muted small">All students are already linked to this parent.</p>
                <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token"  value="<?= csrfToken() ?>">
                    <input type="hidden" name="post_action" value="link_child">
                    <input type="hidden" name="parent_id"   value="<?= $manageParent['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Select Student *</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">— Choose student —</option>
                            <?php
                            $grouped = [];
                            foreach ($unlinkedStudents as $s) {
                                $grouped[$s['class_name'] ?? 'No Class'][] = $s;
                            }
                            foreach ($grouped as $cls => $students): ?>
                            <optgroup label="<?= e($cls) ?>">
                                <?php foreach ($students as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['full_name']) ?> (<?= e($s['student_id']) ?>)</option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Relationship</label>
                        <select name="relationship" class="form-select">
                            <option value="Parent">Parent</option>
                            <option value="Guardian">Guardian</option>
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Sibling">Sibling</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-navy">
                        <i class="fas fa-link me-2"></i>Link Child
                    </button>
                </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Parent List ── -->
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-users me-2 text-gold"></i>
            Parent Accounts
            <span class="pill pill-gold ms-2" style="background:var(--gold-pale);color:var(--gold);font-size:0.75rem;">
                <?= count($parents) ?> total
            </span>
        </h6>
        <div class="d-flex gap-2 align-items-center">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-sm" style="width:200px;"
                       placeholder="Search name / phone..." value="<?= e($search) ?>">
                <button class="btn btn-sm btn-outline-gold">Search</button>
            </form>
            <a href="/admin/parents.php?action=add" class="btn btn-navy btn-sm">
                <i class="fas fa-plus me-1"></i>Add Parent
            </a>
        </div>
    </div>

    <?php if (empty($parents)): ?>
    <div class="dash-card-body text-center py-5">
        <div style="font-size:3rem;color:var(--gray-200);"><i class="fas fa-users"></i></div>
        <p class="text-muted mt-2 mb-3">
            <?= $search ? 'No results for "' . e($search) . '".' : 'No parent accounts yet.' ?>
        </p>
        <?php if (!$search): ?>
        <a href="/admin/parents.php?action=add" class="btn btn-navy">
            <i class="fas fa-plus me-2"></i>Create First Account
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Parent Name</th>
                    <th>Phone (Login)</th>
                    <th>Email</th>
                    <th class="text-center">Children</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parents as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e($p['full_name']) ?></td>
                    <td class="font-monospace small"><?= e($p['phone']) ?></td>
                    <td class="small text-muted"><?= e($p['email'] ?: '—') ?></td>
                    <td class="text-center">
                        <a href="?action=children&id=<?= $p['id'] ?>"
                           class="pill <?= $p['child_count'] > 0 ? 'pill-success' : 'pill-danger' ?>"
                           title="Manage children">
                            <i class="fas fa-user-graduate me-1"></i><?= $p['child_count'] ?>
                        </a>
                    </td>
                    <td>
                        <span class="pill <?= $p['status'] === 'active' ? 'pill-success' : 'pill-danger' ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                    <td class="small text-muted">
                        <?= $p['last_login'] ? date('d M Y', strtotime($p['last_login'])) : 'Never' ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="?action=children&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2" title="Manage children">
                                <i class="fas fa-users"></i>
                            </a>
                            <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Edit</a>
                            <a href="?toggle=<?= $p['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">
                                <?= $p['status'] === 'active' ? 'Disable' : 'Enable' ?>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this parent account and all their child links?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Help box -->
<div class="dash-card mt-4" style="background:var(--cream);border:1px solid var(--cream-dark);">
    <div class="dash-card-body">
        <h6 class="text-navy mb-3"><i class="fas fa-info-circle me-2 text-gold"></i>How to set up a parent account</h6>
        <ol class="small text-muted mb-0 ps-3" style="line-height:2;">
            <li>Click <strong>Add Parent</strong> and fill in the parent's name, phone number and a temporary password.</li>
            <li>After saving, click the <i class="fas fa-users text-gold"></i> icon (or the children count badge) to open the child-linking panel.</li>
            <li>Select the student(s) that belong to this parent and choose the relationship type, then click <strong>Link Child</strong>.</li>
            <li>Share the phone number and password with the parent — they log in at <code>/parent-login.php</code>.</li>
            <li>Ask them to change their password on first login via <strong>My Profile → Change Password</strong>.</li>
        </ol>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
