<?php
$pageTitle = 'Teacher Dashboard';
require_once '../includes/dash_header.php';
requireLogin();

$db = getDB();
$teacherId = $user['id'];

// Teacher's subjects & classes
$mySubjects = $db->prepare("SELECT s.*, c.name AS class_name FROM subjects s LEFT JOIN classes c ON s.class_id=c.id WHERE s.teacher_id=?");
$mySubjects->execute([$teacherId]);
$mySubjects = $mySubjects->fetchAll();

// Total students in my classes
$myClassIds = array_unique(array_column($mySubjects, 'class_id'));
$totalStudents = 0;
if (!empty($myClassIds)) {
    $in = implode(',', array_fill(0, count($myClassIds), '?'));
    $totalStudents = $db->prepare("SELECT COUNT(*) FROM students WHERE class_id IN ($in) AND status='active'");
    $totalStudents->execute($myClassIds);
    $totalStudents = $totalStudents->fetchColumn();
}

// Today's attendance I marked
$todayMarked = $db->prepare("SELECT COUNT(*) FROM attendance WHERE marked_by=? AND date=CURDATE()");
$todayMarked->execute([$teacherId]);
$todayMarked = $todayMarked->fetchColumn();

// Results I've entered this term
$resultsCount = $db->prepare("SELECT COUNT(*) FROM results r JOIN subjects s ON r.subject_id=s.id WHERE s.teacher_id=? AND r.term=? AND r.academic_year=?");
$resultsCount->execute([$teacherId, CURRENT_TERM, ACADEMIC_YEAR]);
$resultsCount = $resultsCount->fetchColumn();
?>

<!-- KPI -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fas fa-users"></i></div>
            <div><div class="kpi-num"><?= $totalStudents ?></div><div class="kpi-lbl">My Students</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon gold"><i class="fas fa-graduation-cap"></i></div>
            <div><div class="kpi-num"><?= $resultsCount ?></div><div class="kpi-lbl">Results Entered (<?= CURRENT_TERM ?>)</div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fas fa-clipboard-check"></i></div>
            <div><div class="kpi-num"><?= $todayMarked ?></div><div class="kpi-lbl">Attendance Marked Today</div></div>
        </div>
    </div>
</div>

<!-- My Subjects -->
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-book me-2 text-gold"></i>My Subjects</h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Subject</th><th>Class</th><th>Action</th></tr></thead>
            <tbody>
                <?php if (empty($mySubjects)): ?>
                <tr><td colspan="3" class="text-center text-muted py-4">No subjects assigned yet. Contact the admin.</td></tr>
                <?php else: ?>
                <?php foreach ($mySubjects as $s): ?>
                <tr>
                    <td><?= e($s['name']) ?></td>
                    <td><?= e($s['class_name'] ?? '—') ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="/teacher/results.php?subject=<?= $s['id'] ?>&class=<?= $s['class_id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Enter Results</a>
                            <a href="/teacher/attendance.php?class=<?= $s['class_id'] ?>" class="btn btn-sm btn-navy py-0 px-2">Attendance</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Links -->
<div class="dash-card">
    <div class="dash-card-header"><h6><i class="fas fa-bolt me-2 text-gold"></i>Quick Actions</h6></div>
    <div class="dash-card-body d-flex flex-wrap gap-3">
        <a href="/teacher/results.php" class="btn btn-navy"><i class="fas fa-graduation-cap me-2"></i>Enter Results</a>
        <a href="/teacher/attendance.php" class="btn btn-navy"><i class="fas fa-clipboard-check me-2"></i>Mark Attendance</a>
        <a href="/" class="btn btn-outline-gold"><i class="fas fa-globe me-2"></i>Visit Website</a>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
