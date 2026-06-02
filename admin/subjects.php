<?php
$pageTitle = 'Subjects';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $data = [trim($_POST['name']), trim($_POST['code'] ?? ''), $_POST['class_id'] ?: null, $_POST['teacher_id'] ?: null];
        if ($_POST['edit_id'] ?? false) {
            $stmt = $db->prepare("UPDATE subjects SET name=?,code=?,class_id=?,teacher_id=? WHERE id=?");
            $data[] = (int)$_POST['edit_id'];
            $stmt->execute($data);
            $success = 'Subject updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO subjects (name,code,class_id,teacher_id) VALUES (?,?,?,?)");
            $stmt->execute($data);
            $success = 'Subject added.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $db->prepare("DELETE FROM subjects WHERE id=?")->execute([(int)$_POST['delete_id']]);
        $success = 'Subject deleted.';
    }
}

$classes  = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();
$teachers = $db->query("SELECT * FROM users WHERE role='teacher' AND status='active' ORDER BY full_name")->fetchAll();

$editSub = null;
$action  = $_GET['action'] ?? '';
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM subjects WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editSub = $stmt->fetch();
}

$subjects = $db->query("SELECT s.*, c.name AS class_name, u.full_name AS teacher_name FROM subjects s LEFT JOIN classes c ON s.class_id=c.id LEFT JOIN users u ON s.teacher_id=u.id ORDER BY c.name, s.name")->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>

<!-- Form -->
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-book me-2 text-gold"></i><?= $action === 'edit' ? 'Edit Subject' : 'Add Subject' ?></h6>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <?php if ($editSub): ?><input type="hidden" name="edit_id" value="<?= $editSub['id'] ?>"><?php endif; ?>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Subject Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Mathematics" value="<?= e($editSub['name'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. MTH" value="<?= e($editSub['code'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($editSub['class_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assign Teacher</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">Unassigned</option>
                        <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($editSub['teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= e($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-navy w-100">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- List -->
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-list me-2 text-gold"></i>All Subjects (<?= count($subjects) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Subject</th><th>Code</th><th>Class</th><th>Teacher</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No subjects yet</td></tr>
                <?php else: ?>
                <?php foreach ($subjects as $s): ?>
                <tr>
                    <td class="fw-semibold"><?= e($s['name']) ?></td>
                    <td class="small"><?= e($s['code'] ?: '—') ?></td>
                    <td><?= e($s['class_name'] ?: 'All') ?></td>
                    <td><?= e($s['teacher_name'] ?: 'Unassigned') ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Edit</a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this subject?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
