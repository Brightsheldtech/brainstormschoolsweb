<?php
$pageTitle = 'Attendance';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$success = $error = '';

// Save attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $classId = (int)$_POST['class_id'];
        $date    = $_POST['att_date'];
        $records = $_POST['att'] ?? [];
        foreach ($records as $studentId => $status) {
            $stmt = $db->prepare("INSERT INTO attendance (student_id, class_id, date, status, marked_by) VALUES (?,?,?,?,?)
                ON DUPLICATE KEY UPDATE status=VALUES(status), marked_by=VALUES(marked_by)");
            $stmt->execute([(int)$studentId, $classId, $date, $status, $user['id']]);
        }
        $success = 'Attendance saved for ' . date('D, M j Y', strtotime($date)) . '.';
    }
}

$classes = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();
$selectedClass = $_GET['class'] ?? '';
$selectedDate  = $_GET['date'] ?? date('Y-m-d');

$students   = [];
$attendance = [];

if ($selectedClass) {
    $stmt = $db->prepare("SELECT * FROM students WHERE class_id=? AND status='active' ORDER BY full_name");
    $stmt->execute([$selectedClass]);
    $students = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT student_id, status FROM attendance WHERE class_id=? AND date=?");
    $stmt->execute([$selectedClass, $selectedDate]);
    foreach ($stmt->fetchAll() as $a) { $attendance[$a['student_id']] = $a['status']; }
}

// Stats for today
$todayPresent = $db->query("SELECT COUNT(*) FROM attendance WHERE date=CURDATE() AND status='present'")->fetchColumn();
$todayAbsent  = $db->query("SELECT COUNT(*) FROM attendance WHERE date=CURDATE() AND status='absent'")->fetchColumn();
$todayLate    = $db->query("SELECT COUNT(*) FROM attendance WHERE date=CURDATE() AND status='late'")->fetchColumn();
?>

<!-- Today's Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fas fa-check"></i></div>
            <div><div class="kpi-num"><?= $todayPresent ?></div><div class="kpi-lbl">Present Today</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="fas fa-times"></i></div>
            <div><div class="kpi-num"><?= $todayAbsent ?></div><div class="kpi-lbl">Absent Today</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon gold"><i class="fas fa-clock"></i></div>
            <div><div class="kpi-num"><?= $todayLate ?></div><div class="kpi-lbl">Late Today</div></div>
        </div>
    </div>
</div>

<?php if ($success): ?>
<div class="alert-school mb-3"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-filter me-2 text-gold"></i>Select Class & Date</h6>
    </div>
    <div class="dash-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Class</label>
                <select name="class" class="form-select" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="<?= e($selectedDate) ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-navy w-100">Load</button>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Sheet -->
<?php if (!empty($students)): ?>
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-clipboard-check me-2 text-gold"></i>
            Attendance — <?= date('D, M j Y', strtotime($selectedDate)) ?>
        </h6>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-gold" onclick="markAll('present')">Mark All Present</button>
            <button class="btn btn-sm btn-outline-gold" onclick="markAll('absent')">Mark All Absent</button>
        </div>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="class_id" value="<?= $selectedClass ?>">
            <input type="hidden" name="att_date" value="<?= e($selectedDate) ?>">

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>#</th><th>Student Name</th><th>ID</th><th class="text-center">Present</th><th class="text-center">Absent</th><th class="text-center">Late</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $s):
                            $status = $attendance[$s['id']] ?? '';
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($s['full_name']) ?></td>
                            <td class="small text-muted"><?= e($s['student_id']) ?></td>
                            <td class="text-center">
                                <input type="radio" name="att[<?= $s['id'] ?>]" value="present" class="att-radio" <?= $status === 'present' ? 'checked' : '' ?>>
                            </td>
                            <td class="text-center">
                                <input type="radio" name="att[<?= $s['id'] ?>]" value="absent" class="att-radio" <?= $status === 'absent' ? 'checked' : '' ?>>
                            </td>
                            <td class="text-center">
                                <input type="radio" name="att[<?= $s['id'] ?>]" value="late" class="att-radio" <?= $status === 'late' ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-navy px-5">
                    <i class="fas fa-save me-2"></i> Save Attendance
                </button>
            </div>
        </form>
    </div>
</div>
<?php elseif ($selectedClass): ?>
<div class="alert-school"><i class="fas fa-info-circle me-2"></i>No active students found in this class.</div>
<?php endif; ?>

<?php
$extraScripts = <<<JS
<script>
function markAll(status) {
    document.querySelectorAll('.att-radio').forEach(r => {
        if (r.value === status) r.checked = true;
    });
}
</script>
JS;
require_once '../includes/dash_footer.php';
?>
