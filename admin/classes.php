<?php
$pageTitle = 'Classes';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $data = [trim($_POST['name']), trim($_POST['section'] ?? ''), $_POST['teacher_id'] ?: null, trim($_POST['academic_year'])];
        if ($_POST['edit_id'] ?? false) {
            $stmt = $db->prepare("UPDATE classes SET name=?,section=?,teacher_id=?,academic_year=? WHERE id=?");
            $data[] = (int)$_POST['edit_id'];
            $stmt->execute($data);
            $success = 'Class updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO classes (name,section,teacher_id,academic_year) VALUES (?,?,?,?)");
            $stmt->execute($data);
            $success = 'Class added.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $db->prepare("DELETE FROM classes WHERE id=?")->execute([(int)$_POST['delete_id']]);
        $success = 'Class deleted.';
    }
}

$teachers = $db->query("SELECT * FROM users WHERE role='teacher' AND status='active' ORDER BY full_name")->fetchAll();
$editClass = null;
$action    = $_GET['action'] ?? '';
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM classes WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editClass = $stmt->fetch();
}

$classes = $db->query("SELECT c.*, u.full_name AS teacher_name, COUNT(s.id) AS student_count FROM classes c LEFT JOIN users u ON c.teacher_id=u.id LEFT JOIN students s ON s.class_id=c.id AND s.status='active' GROUP BY c.id ORDER BY c.name")->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>

<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-layer-group me-2 text-gold"></i><?= $action === 'edit' ? 'Edit Class' : 'Add Class' ?></h6>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <?php if ($editClass): ?><input type="hidden" name="edit_id" value="<?= $editClass['id'] ?>"><?php endif; ?>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Class Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. JSS 1" value="<?= e($editClass['name'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Section</label>
                    <input type="text" name="section" class="form-control" placeholder="A, B, C..." value="<?= e($editClass['section'] ?? 'A') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class Teacher</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">None</option>
                        <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($editClass['teacher_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= e($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Academic Year</label>
                    <input type="text" name="academic_year" class="form-control" value="<?= e($editClass['academic_year'] ?? ACADEMIC_YEAR) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-navy w-100">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-layer-group me-2 text-gold"></i>All Classes (<?= count($classes) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Class</th><th>Section</th><th>Class Teacher</th><th>Students</th><th>Session</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($classes as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= e($c['name']) ?></td>
                    <td><?= e($c['section'] ?: '—') ?></td>
                    <td><?= e($c['teacher_name'] ?: 'Unassigned') ?></td>
                    <td><span class="pill pill-navy"><?= $c['student_count'] ?></span></td>
                    <td class="small"><?= e($c['academic_year']) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="?action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Edit</a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete class? Students will be unassigned.')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
