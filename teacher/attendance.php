<?php
$pageTitle = 'Mark Attendance';
require_once '../includes/dash_header.php';
requireLogin();

$db        = getDB();
$teacherId = $user['id'];
$success   = $error = '';

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $classId = (int)$_POST['class_id'];
        $date    = $_POST['att_date'];
        foreach ($_POST['att'] ?? [] as $studentId => $status) {
            $stmt = $db->prepare("INSERT INTO attendance (student_id,class_id,date,status,marked_by) VALUES (?,?,?,?,?)
                ON DUPLICATE KEY UPDATE status=VALUES(status),marked_by=VALUES(marked_by)");
            $stmt->execute([(int)$studentId, $classId, $date, $status, $teacherId]);
        }
        $success = 'Attendance saved for ' . date('D, M j Y', strtotime($date)) . '.';
    }
}

// My classes
$myClasses = $db->prepare("SELECT DISTINCT c.id, c.name FROM subjects s JOIN classes c ON s.class_id=c.id WHERE s.teacher_id=? ORDER BY c.name");
$myClasses->execute([$teacherId]);
$myClasses = $myClasses->fetchAll();

$selectedClass = $_GET['class'] ?? '';
$selectedDate  = $_GET['date'] ?? date('Y-m-d');
$students      = [];
$attendance    = [];

if ($selectedClass) {
    $stmt = $db->prepare("SELECT * FROM students WHERE class_id=? AND status='active' ORDER BY full_name");
    $stmt->execute([$selectedClass]);
    $students = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT student_id, status FROM attendance WHERE class_id=? AND date=?");
    $stmt->execute([$selectedClass, $selectedDate]);
    foreach ($stmt->fetchAll() as $a) { $attendance[$a['student_id']] = $a['status']; }
}
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>

<div class="dash-card mb-4">
    <div class="dash-card-header"><h6><i class="fas fa-filter me-2 text-gold"></i>Select Class & Date</h6></div>
    <div class="dash-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">My Class</label>
                <select name="class" class="form-select" onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($myClasses as $c): ?>
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

<?php if (!empty($students)): ?>
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-clipboard-check me-2 text-gold"></i>Attendance — <?= date('D, M j Y', strtotime($selectedDate)) ?></h6>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-gold" onclick="markAll('present')">All Present</button>
            <button class="btn btn-sm btn-outline-gold" onclick="markAll('absent')">All Absent</button>
        </div>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="class_id" value="<?= $selectedClass ?>">
            <input type="hidden" name="att_date" value="<?= e($selectedDate) ?>">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Student Name</th><th class="text-center">Present</th><th class="text-center">Absent</th><th class="text-center">Late</th></tr></thead>
                    <tbody>
                        <?php foreach ($students as $i => $s):
                            $status = $attendance[$s['id']] ?? '';
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($s['full_name']) ?></td>
                            <td class="text-center"><input type="radio" name="att[<?= $s['id'] ?>]" value="present" class="att-radio" <?= $status === 'present' ? 'checked' : '' ?>></td>
                            <td class="text-center"><input type="radio" name="att[<?= $s['id'] ?>]" value="absent"  class="att-radio" <?= $status === 'absent'  ? 'checked' : '' ?>></td>
                            <td class="text-center"><input type="radio" name="att[<?= $s['id'] ?>]" value="late"    class="att-radio" <?= $status === 'late'    ? 'checked' : '' ?>></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-navy px-5"><i class="fas fa-save me-2"></i>Save Attendance</button>
            </div>
        </form>
    </div>
</div>
<?php elseif ($selectedClass): ?>
<div class="alert-school"><i class="fas fa-info-circle me-2"></i>No students found.</div>
<?php endif; ?>

<?php
$extraScripts = '<script>function markAll(s){document.querySelectorAll(".att-radio").forEach(r=>{if(r.value===s)r.checked=true;});}</script>';
require_once '../includes/dash_footer.php';
?>
