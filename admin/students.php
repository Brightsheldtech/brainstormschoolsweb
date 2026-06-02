<?php
$pageTitle = 'Students';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$action  = $_GET['action'] ?? '';
$success = $error = '';

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $data = [
            trim($_POST['full_name']),
            trim($_POST['student_id']),
            $_POST['class_id'] ?: null,
            $_POST['date_of_birth'] ?: null,
            $_POST['gender'],
            trim($_POST['parent_name'] ?? ''),
            trim($_POST['parent_phone'] ?? ''),
            trim($_POST['parent_email'] ?? ''),
            trim($_POST['parent_whatsapp'] ?? ''),
            trim($_POST['address'] ?? ''),
            $_POST['admission_date'] ?: null,
        ];
        if ($_POST['edit_id'] ?? false) {
            $stmt = $db->prepare("UPDATE students SET full_name=?,student_id=?,class_id=?,date_of_birth=?,gender=?,parent_name=?,parent_phone=?,parent_email=?,parent_whatsapp=?,address=?,admission_date=? WHERE id=?");
            $data[] = (int)$_POST['edit_id'];
            $stmt->execute($data);
            $success = 'Student record updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO students (full_name,student_id,class_id,date_of_birth,gender,parent_name,parent_phone,parent_email,parent_whatsapp,address,admission_date) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute($data);
            $success = 'Student added successfully.';
        }
        $action = '';
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $db->prepare("DELETE FROM students WHERE id=?")->execute([(int)$_POST['delete_id']]);
        $success = 'Student removed.';
    }
}

// Fetch classes for dropdown
$classes = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();

// Fetch student for edit
$editStudent = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM students WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editStudent = $stmt->fetch();
}

// Search & list
$search = trim($_GET['q'] ?? '');
$classFilter = $_GET['class'] ?? '';
$params = [];
$where  = "WHERE 1=1";
if ($search) { $where .= " AND (s.full_name LIKE ? OR s.student_id LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($classFilter) { $where .= " AND s.class_id=?"; $params[] = $classFilter; }

$stmt = $db->prepare("SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id=c.id $where ORDER BY s.full_name");
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger mb-4"><?= e($error) ?></div>
<?php endif; ?>

<!-- Add / Edit Form -->
<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-user-plus me-2 text-gold"></i><?= $action === 'edit' ? 'Edit Student' : 'Add New Student' ?></h6>
        <a href="/admin/students.php" class="btn btn-sm btn-outline-gold">Cancel</a>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <?php if ($editStudent): ?>
            <input type="hidden" name="edit_id" value="<?= $editStudent['id'] ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= e($editStudent['full_name'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Student ID *</label>
                    <input type="text" name="student_id" class="form-control" placeholder="BS/2024/001" required value="<?= e($editStudent['student_id'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gender *</label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select</option>
                        <option value="male"   <?= ($editStudent['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= ($editStudent['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?= e($editStudent['date_of_birth'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($editStudent['class_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Admission Date</label>
                    <input type="date" name="admission_date" class="form-control" value="<?= e($editStudent['admission_date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parent Name</label>
                    <input type="text" name="parent_name" class="form-control" value="<?= e($editStudent['parent_name'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parent Phone</label>
                    <input type="tel" name="parent_phone" class="form-control" value="<?= e($editStudent['parent_phone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parent Email</label>
                    <input type="email" name="parent_email" class="form-control" value="<?= e($editStudent['parent_email'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">WhatsApp (for Results)</label>
                    <input type="tel" name="parent_whatsapp" class="form-control" value="<?= e($editStudent['parent_whatsapp'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="<?= e($editStudent['address'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-navy px-4">
                        <i class="fas fa-save me-2"></i><?= $action === 'edit' ? 'Update Student' : 'Add Student' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Search & Filter -->
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-users me-2 text-gold"></i>All Students (<?= count($students) ?>)</h6>
        <a href="/admin/students.php?action=add" class="btn btn-navy btn-sm">
            <i class="fas fa-plus me-1"></i> Add Student
        </a>
    </div>
    <div class="dash-card-body border-bottom pb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by name or student ID..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-4">
                <select name="class" class="form-select form-select-sm">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $classFilter == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-navy btn-sm flex-fill">Filter</button>
                <a href="/admin/students.php" class="btn btn-outline-gold btn-sm">Clear</a>
            </div>
        </form>
    </div>
    <div class="p-0">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Full Name</th>
                        <th>Class</th>
                        <th>Gender</th>
                        <th>Parent Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No students found</td></tr>
                    <?php else: ?>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td class="fw-semibold text-navy small"><?= e($s['student_id']) ?></td>
                        <td><?= e($s['full_name']) ?></td>
                        <td><?= e($s['class_name'] ?? '—') ?></td>
                        <td><?= ucfirst(e($s['gender'])) ?></td>
                        <td><?= e($s['parent_phone'] ?? '—') ?></td>
                        <td><span class="pill <?= $s['status'] === 'active' ? 'pill-success' : 'pill-gray' ?>"><?= ucfirst($s['status']) ?></span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Edit</a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this student? This will also remove their results and attendance records.')">
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
</div>

<?php require_once '../includes/dash_footer.php'; ?>
