<?php
$pageTitle = 'Results Management';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$success = $error = '';

// Save results (bulk from form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_results'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $classId = (int)$_POST['class_id'];
        $subjectId = (int)$_POST['subject_id'];
        $term    = $_POST['term'];
        $year    = $_POST['academic_year'];
        $scores  = $_POST['scores'] ?? [];
        $count   = 0;
        foreach ($scores as $studentId => $sc) {
            $ca1  = min(20, max(0, (float)($sc['ca1'] ?? 0)));
            $ca2  = min(20, max(0, (float)($sc['ca2'] ?? 0)));
            $exam = min(60, max(0, (float)($sc['exam'] ?? 0)));
            $total = $ca1 + $ca2 + $exam;
            $g    = getGrade($total);
            $stmt = $db->prepare("INSERT INTO results (student_id,subject_id,class_id,term,academic_year,ca1,ca2,exam,grade,remark)
                VALUES (?,?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE ca1=VALUES(ca1),ca2=VALUES(ca2),exam=VALUES(exam),grade=VALUES(grade),remark=VALUES(remark)");
            $stmt->execute([(int)$studentId, $subjectId, $classId, $term, $year, $ca1, $ca2, $exam, $g['grade'], $g['remark']]);
            $count++;
        }
        $success = "$count result(s) saved successfully.";
    }
}

$classes  = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();
$subjects = $db->query("SELECT s.*, c.name AS class_name FROM subjects s LEFT JOIN classes c ON s.class_id=c.id ORDER BY s.name")->fetchAll();

$selectedClass   = $_GET['class'] ?? '';
$selectedSubject = $_GET['subject'] ?? '';
$selectedTerm    = $_GET['term'] ?? CURRENT_TERM;
$selectedYear    = $_GET['year'] ?? ACADEMIC_YEAR;

$studentsForClass = [];
$existingResults  = [];

if ($selectedClass && $selectedSubject) {
    $stmt = $db->prepare("SELECT * FROM students WHERE class_id=? AND status='active' ORDER BY full_name");
    $stmt->execute([$selectedClass]);
    $studentsForClass = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT * FROM results WHERE class_id=? AND subject_id=? AND term=? AND academic_year=?");
    $stmt->execute([$selectedClass, $selectedSubject, $selectedTerm, $selectedYear]);
    foreach ($stmt->fetchAll() as $r) {
        $existingResults[$r['student_id']] = $r;
    }
}
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>

<!-- Selector -->
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-filter me-2 text-gold"></i>Select Class, Subject & Term</h6>
    </div>
    <div class="dash-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select name="class" class="form-select" required onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject" class="form-select">
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $s): ?>
                    <?php if (!$selectedClass || $s['class_id'] == $selectedClass): ?>
                    <option value="<?= $s['id'] ?>" <?= $selectedSubject == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Term</label>
                <select name="term" class="form-select">
                    <option value="First Term"  <?= $selectedTerm === 'First Term'  ? 'selected' : '' ?>>First Term</option>
                    <option value="Second Term" <?= $selectedTerm === 'Second Term' ? 'selected' : '' ?>>Second Term</option>
                    <option value="Third Term"  <?= $selectedTerm === 'Third Term'  ? 'selected' : '' ?>>Third Term</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Session</label>
                <select name="year" class="form-select">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): $yr = "$y/" . ($y+1); ?>
                    <option value="<?= $yr ?>" <?= $selectedYear === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-navy w-100">Load Students</button>
            </div>
        </form>
    </div>
</div>

<!-- Score Entry Table -->
<?php if (!empty($studentsForClass)): ?>
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-pencil-alt me-2 text-gold"></i>
            Enter Scores — <?= $selectedTerm ?> (<?= $selectedYear ?>)
        </h6>
        <span class="small text-muted"><?= count($studentsForClass) ?> student(s)</span>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <input type="hidden" name="save_results" value="1">
            <input type="hidden" name="class_id" value="<?= $selectedClass ?>">
            <input type="hidden" name="subject_id" value="<?= $selectedSubject ?>">
            <input type="hidden" name="term" value="<?= e($selectedTerm) ?>">
            <input type="hidden" name="academic_year" value="<?= e($selectedYear) ?>">

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>ID</th>
                            <th class="text-center">CA 1 <span class="fw-normal">(max 20)</span></th>
                            <th class="text-center">CA 2 <span class="fw-normal">(max 20)</span></th>
                            <th class="text-center">Exam <span class="fw-normal">(max 60)</span></th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($studentsForClass as $i => $s):
                            $ex = $existingResults[$s['id']] ?? null;
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($s['full_name']) ?></td>
                            <td class="text-muted small"><?= e($s['student_id']) ?></td>
                            <td class="text-center">
                                <input type="number" name="scores[<?= $s['id'] ?>][ca1]" class="form-control form-control-sm text-center score-input"
                                       min="0" max="20" step="0.5" style="width:70px;margin:auto;"
                                       value="<?= $ex ? $ex['ca1'] : '' ?>" oninput="calcTotal(this)">
                            </td>
                            <td class="text-center">
                                <input type="number" name="scores[<?= $s['id'] ?>][ca2]" class="form-control form-control-sm text-center score-input"
                                       min="0" max="20" step="0.5" style="width:70px;margin:auto;"
                                       value="<?= $ex ? $ex['ca2'] : '' ?>" oninput="calcTotal(this)">
                            </td>
                            <td class="text-center">
                                <input type="number" name="scores[<?= $s['id'] ?>][exam]" class="form-control form-control-sm text-center score-input"
                                       min="0" max="60" step="0.5" style="width:70px;margin:auto;"
                                       value="<?= $ex ? $ex['exam'] : '' ?>" oninput="calcTotal(this)">
                            </td>
                            <td class="text-center fw-bold total-cell" id="total_<?= $s['id'] ?>">
                                <?= $ex ? number_format($ex['total'], 1) : '—' ?>
                            </td>
                            <td class="text-center grade-cell" id="grade_<?= $s['id'] ?>">
                                <?php if ($ex): $g = $ex['grade']; echo "<span class='grade-" . strtolower($g) . "'>$g</span>"; endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-navy px-5">
                    <i class="fas fa-save me-2"></i> Save All Results
                </button>
            </div>
        </form>
    </div>
</div>
<?php elseif ($selectedClass && $selectedSubject): ?>
<div class="alert-school"><i class="fas fa-info-circle me-2"></i>No active students found in the selected class.</div>
<?php endif; ?>

<?php
$extraScripts = <<<JS
<script>
const grades = [[75,'A'],[65,'B'],[55,'C'],[45,'D'],[40,'E'],[0,'F']];
function calcTotal(el) {
    const row  = el.closest('tr');
    const id   = row.querySelector('[name$="[exam]"]').name.match(/\[(\d+)\]/)[1];
    const ca1  = parseFloat(row.querySelector('[name$="[ca1]"]').value) || 0;
    const ca2  = parseFloat(row.querySelector('[name$="[ca2]"]').value) || 0;
    const exam = parseFloat(row.querySelector('[name$="[exam]"]').value) || 0;
    const total = ca1 + ca2 + exam;
    document.getElementById('total_' + id).textContent = total.toFixed(1);
    const g = grades.find(([s]) => total >= s)?.[1] || 'F';
    const gc = document.getElementById('grade_' + id);
    gc.innerHTML = '<span class="grade-' + g.toLowerCase() + '">' + g + '</span>';
}
</script>
JS;
require_once '../includes/dash_footer.php';
?>
