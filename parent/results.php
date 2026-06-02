<?php
$pageTitle = 'Results';
require_once '../includes/parent_header.php';

if (!$activeChild) { redirect('/parent/index.php'); }

$childId = $activeChild['id'];
$db      = getDB();

$selectedTerm = $_GET['term'] ?? CURRENT_TERM;
$selectedYear = $_GET['year'] ?? ACADEMIC_YEAR;

$results = $db->prepare("
    SELECT r.*, sub.name AS subject_name
    FROM results r
    JOIN subjects sub ON r.subject_id = sub.id
    WHERE r.student_id = ? AND r.term = ? AND r.academic_year = ?
    ORDER BY sub.name
");
$results->execute([$childId, $selectedTerm, $selectedYear]);
$results = $results->fetchAll();

$totalSubjects = count($results);
$totalScore    = array_sum(array_column($results, 'total'));
$average       = $totalSubjects > 0 ? round($totalScore / $totalSubjects, 1) : 0;
$overallGrade  = $totalSubjects > 0 ? getGrade($average) : null;

$years = [];
for ($y = date('Y'); $y >= date('Y') - 5; $y--) { $years[] = $y . '/' . ($y + 1); }
?>

<!-- Term Selector -->
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-filter me-2 text-gold"></i>Select Term & Session</h6>
    </div>
    <div class="dash-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Term</label>
                <select name="term" class="form-select">
                    <option value="First Term"  <?= $selectedTerm === 'First Term'  ? 'selected' : '' ?>>First Term</option>
                    <option value="Second Term" <?= $selectedTerm === 'Second Term' ? 'selected' : '' ?>>Second Term</option>
                    <option value="Third Term"  <?= $selectedTerm === 'Third Term'  ? 'selected' : '' ?>>Third Term</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Academic Year</label>
                <select name="year" class="form-select">
                    <?php foreach ($years as $yr): ?>
                    <option value="<?= $yr ?>" <?= $selectedYear === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-navy w-100">View Results</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($results)): ?>

<!-- Report Card -->
<div class="dash-card" id="reportCard">

    <!-- Print header (visible when printing) -->
    <div class="p-4 text-center border-bottom">
        <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
            <img src="/assets/images/logo.png" alt="<?= SITE_NAME ?>" style="height:72px;">
            <div class="text-start">
                <h4 class="mb-0"><?= SITE_NAME ?></h4>
                <p class="text-muted small mb-0"><?= SITE_TAGLINE ?></p>
                <p class="text-muted small"><?= SITE_ADDRESS ?></p>
            </div>
        </div>
        <div style="background:var(--navy);color:var(--white);border-radius:10px;padding:10px 24px;display:inline-block;">
            <strong><?= e($selectedTerm) ?> Report Card — <?= e($selectedYear) ?></strong>
        </div>
    </div>

    <!-- Student Info -->
    <div class="p-4">
        <div class="row g-3 mb-4 p-3 rounded-3" style="background:var(--cream);border:1px solid var(--cream-dark);">
            <div class="col-md-4">
                <div class="small text-muted fw-bold mb-1">Student Name</div>
                <div class="fw-bold"><?= e($activeChild['full_name']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted fw-bold mb-1">Student ID</div>
                <div class="fw-bold"><?= e($activeChild['student_id']) ?></div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted fw-bold mb-1">Class</div>
                <div class="fw-bold"><?= e($activeChild['class_name'] ?? '—') ?></div>
            </div>
            <div class="col-md-2">
                <div class="small text-muted fw-bold mb-1">Term</div>
                <div class="fw-bold"><?= e($selectedTerm) ?></div>
            </div>
        </div>

        <!-- Scores Table -->
        <div class="table-responsive mb-4">
            <table class="result-table table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th class="text-center">CA 1<br><small class="fw-normal opacity-75">/20</small></th>
                        <th class="text-center">CA 2<br><small class="fw-normal opacity-75">/20</small></th>
                        <th class="text-center">Exam<br><small class="fw-normal opacity-75">/60</small></th>
                        <th class="text-center">Total<br><small class="fw-normal opacity-75">/100</small></th>
                        <th class="text-center">Grade</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $i => $r):
                        $g      = $r['grade'] ?? getGrade($r['total'])['grade'];
                        $remark = $r['remark'] ?? getGrade($r['total'])['remark'];
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($r['subject_name']) ?></td>
                        <td class="text-center"><?= number_format($r['ca1'], 1) ?></td>
                        <td class="text-center"><?= number_format($r['ca2'], 1) ?></td>
                        <td class="text-center"><?= number_format($r['exam'], 1) ?></td>
                        <td class="text-center fw-bold"><?= number_format($r['total'], 1) ?></td>
                        <td class="text-center grade-<?= strtolower($g) ?>"><?= $g ?></td>
                        <td><?= e($remark) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary Boxes -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="text-center p-3 rounded-3 border">
                    <div class="h3 text-navy mb-0" style="font-family:'Playfair Display',serif;"><?= $totalSubjects ?></div>
                    <div class="small text-muted">Subjects</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3 rounded-3 border">
                    <div class="h3 mb-0" style="font-family:'Playfair Display',serif;color:var(--navy);"><?= number_format($totalScore, 1) ?></div>
                    <div class="small text-muted">Total Score</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3 rounded-3 border">
                    <div class="h3 mb-0 text-gold" style="font-family:'Playfair Display',serif;"><?= $average ?>%</div>
                    <div class="small text-muted">Average</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3 rounded-3 border">
                    <div class="h3 mb-0 grade-<?= strtolower($overallGrade['grade']) ?>" style="font-family:'Playfair Display',serif;"><?= $overallGrade['grade'] ?></div>
                    <div class="small text-muted"><?= $overallGrade['remark'] ?></div>
                </div>
            </div>
        </div>

        <!-- Grading Key -->
        <div class="p-3 rounded-3 mb-4" style="background:var(--cream);border:1px solid var(--cream-dark);">
            <div class="small fw-bold text-navy mb-2">Grading Scale:</div>
            <div class="d-flex flex-wrap gap-3 small">
                <span><strong class="grade-a">A</strong> — 75–100 (Excellent)</span>
                <span><strong class="grade-b">B</strong> — 65–74 (Very Good)</span>
                <span><strong class="grade-c">C</strong> — 55–64 (Good)</span>
                <span><strong class="grade-d">D</strong> — 45–54 (Pass)</span>
                <span><strong>E</strong> — 40–44 (Fair)</span>
                <span><strong class="grade-f">F</strong> — 0–39 (Fail)</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex gap-3 no-print">
            <button onclick="window.print()" class="btn btn-navy">
                <i class="fas fa-print me-2"></i> Print Report Card
            </button>
            <a href="/parent/results.php" class="btn btn-outline-gold">
                <i class="fas fa-redo me-2"></i> Check Another Term
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<div class="dash-card">
    <div class="dash-card-body text-center py-5">
        <div style="font-size:4rem;color:var(--gray-200);"><i class="fas fa-graduation-cap"></i></div>
        <h5 class="text-muted mt-3">No results found</h5>
        <p class="text-muted small mb-4">
            Results for <strong><?= e($selectedTerm) ?> (<?= e($selectedYear) ?>)</strong> have not been published yet.<br>
            Please check back after the examination period.
        </p>
        <a href="/contact.php" class="btn btn-outline-gold btn-sm">Contact the School</a>
    </div>
</div>
<?php endif; ?>

<?php require_once '../includes/parent_footer.php'; ?>
