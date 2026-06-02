<?php
$pageTitle = 'Check Results';
require_once 'includes/header.php';

$student  = null;
$results  = [];
$error    = '';
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searched   = true;
    $studentId  = trim($_POST['student_id'] ?? '');
    $term       = trim($_POST['term'] ?? '');
    $year       = trim($_POST['academic_year'] ?? '');

    if (!$studentId || !$term || !$year) {
        $error = 'Please fill in all fields to check results.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.student_id = ? AND s.status = 'active'");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();

        if (!$student) {
            $error = 'No active student found with that ID. Please check and try again or contact the school office.';
        } else {
            $rStmt = $db->prepare("
                SELECT r.*, sub.name AS subject_name
                FROM results r
                JOIN subjects sub ON r.subject_id = sub.id
                WHERE r.student_id = ? AND r.term = ? AND r.academic_year = ?
                ORDER BY sub.name
            ");
            $rStmt->execute([$student['id'], $term, $year]);
            $results = $rStmt->fetchAll();

            if (empty($results)) {
                $error = "Results for $term ($year) have not been published yet. Please check back later.";
            }
        }
    }
}

// Compute summary
$totalScore = 0; $totalSubjects = count($results);
$gradeCount = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0];
foreach ($results as $r) {
    $totalScore += $r['total'];
    $g = $r['grade'] ?? getGrade($r['total'])['grade'];
    if (isset($gradeCount[$g])) $gradeCount[$g]++;
}
$average   = $totalSubjects > 0 ? round($totalScore / $totalSubjects, 1) : 0;
$overallGr = $totalSubjects > 0 ? getGrade($average) : null;

$years = [];
for ($y = date('Y'); $y >= date('Y') - 5; $y--) { $years[] = $y . '/' . ($y + 1); }
?>

<section class="page-hero">
    <div class="container page-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Check Results</li>
            </ol>
        </nav>
        <h1>Student Result Portal</h1>
        <p style="color:rgba(255,255,255,0.7)">Enter your Student ID to view and print your academic results.</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Search Card -->
                <div class="dash-card mb-4">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-search me-2 text-gold"></i> Find Your Result</h6>
                    </div>
                    <div class="dash-card-body">
                        <form method="POST">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Student ID <span class="text-danger">*</span></label>
                                    <input type="text" name="student_id" class="form-control" placeholder="e.g. BS/2024/001"
                                           value="<?= e($_POST['student_id'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Term <span class="text-danger">*</span></label>
                                    <select name="term" class="form-select" required>
                                        <option value="">Select Term</option>
                                        <option value="First Term"  <?= ($_POST['term'] ?? '') === 'First Term'  ? 'selected' : '' ?>>First Term</option>
                                        <option value="Second Term" <?= ($_POST['term'] ?? '') === 'Second Term' ? 'selected' : '' ?>>Second Term</option>
                                        <option value="Third Term"  <?= ($_POST['term'] ?? '') === 'Third Term'  ? 'selected' : '' ?>>Third Term</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year" class="form-select" required>
                                        <option value="">Select Year</option>
                                        <?php foreach ($years as $yr): ?>
                                        <option value="<?= $yr ?>" <?= ($_POST['academic_year'] ?? ACADEMIC_YEAR) === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-navy px-5">
                                        <i class="fas fa-search me-2"></i> Check Results
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Error -->
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= e($error) ?>
                </div>
                <?php endif; ?>

                <!-- Result Sheet -->
                <?php if ($student && !empty($results)): ?>
                <div class="dash-card" id="resultSheet">
                    <!-- Print Header -->
                    <div class="result-print-header p-4 text-center border-bottom">
                        <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                            <img src="/assets/images/logo.png" alt="<?= SITE_NAME ?>" style="height:80px;">
                            <div class="text-start">
                                <h3 class="mb-0"><?= SITE_NAME ?></h3>
                                <p class="text-muted small mb-0"><?= SITE_TAGLINE ?> &bull; <?= SITE_ADDRESS ?></p>
                                <p class="text-muted small"><?= SITE_PHONE ?> &bull; <?= SITE_EMAIL ?></p>
                            </div>
                        </div>
                        <div class="bg-navy text-white rounded-2 py-2 px-4 d-inline-block">
                            <strong><?= e($_POST['term']) ?> Report Card &mdash; <?= e($_POST['academic_year']) ?></strong>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Student Info -->
                        <div class="row g-3 mb-4 p-3 rounded-2" style="background:var(--off-white);border:1px solid var(--gray-200);">
                            <div class="col-md-4">
                                <div class="small text-muted fw-semibold mb-1">Student Name</div>
                                <div class="fw-bold"><?= e($student['full_name']) ?></div>
                            </div>
                            <div class="col-md-2">
                                <div class="small text-muted fw-semibold mb-1">Student ID</div>
                                <div class="fw-bold"><?= e($student['student_id']) ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted fw-semibold mb-1">Class</div>
                                <div class="fw-bold"><?= e($student['class_name'] ?? '—') ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted fw-semibold mb-1">Gender</div>
                                <div class="fw-bold"><?= ucfirst(e($student['gender'])) ?></div>
                            </div>
                        </div>

                        <!-- Result Table -->
                        <div class="table-responsive mb-4">
                            <table class="result-table table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:30px">#</th>
                                        <th>Subject</th>
                                        <th class="text-center">CA 1 <small class="d-block text-white-50">/20</small></th>
                                        <th class="text-center">CA 2 <small class="d-block text-white-50">/20</small></th>
                                        <th class="text-center">Exam <small class="d-block text-white-50">/60</small></th>
                                        <th class="text-center">Total <small class="d-block text-white-50">/100</small></th>
                                        <th class="text-center">Grade</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $i => $r):
                                        $g = $r['grade'] ?? getGrade($r['total'])['grade'];
                                        $remark = $r['remark'] ?? getGrade($r['total'])['remark'];
                                        $gradeClass = 'grade-' . strtolower($g);
                                    ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= e($r['subject_name']) ?></td>
                                        <td class="text-center"><?= number_format($r['ca1'], 1) ?></td>
                                        <td class="text-center"><?= number_format($r['ca2'], 1) ?></td>
                                        <td class="text-center"><?= number_format($r['exam'], 1) ?></td>
                                        <td class="text-center fw-bold"><?= number_format($r['total'], 1) ?></td>
                                        <td class="text-center <?= $gradeClass ?>"><?= $g ?></td>
                                        <td><?= e($remark) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 rounded-2 border">
                                    <div class="h3 text-navy mb-0" style="font-family:'Playfair Display',serif;"><?= $totalSubjects ?></div>
                                    <div class="small text-muted">Subjects</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 rounded-2 border">
                                    <div class="h3 mb-0" style="font-family:'Playfair Display',serif;color:var(--navy);"><?= number_format($totalScore, 1) ?></div>
                                    <div class="small text-muted">Total Score</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 rounded-2 border">
                                    <div class="h3 mb-0" style="font-family:'Playfair Display',serif;color:var(--gold);"><?= $average ?>%</div>
                                    <div class="small text-muted">Average</div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3 rounded-2 border">
                                    <div class="h3 mb-0 grade-<?= strtolower($overallGr['grade'] ?? 'f') ?>"
                                         style="font-family:'Playfair Display',serif;"><?= $overallGr['grade'] ?? '—' ?></div>
                                    <div class="small text-muted"><?= $overallGr['remark'] ?? '—' ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Grading Key -->
                        <div class="p-3 rounded-2 mb-4" style="background:var(--off-white);border:1px solid var(--gray-200);">
                            <div class="small fw-bold text-navy mb-2">Grading Key:</div>
                            <div class="d-flex flex-wrap gap-3 small">
                                <span><strong class="grade-a">A</strong> — 75–100 (Excellent)</span>
                                <span><strong class="grade-b">B</strong> — 65–74 (Very Good)</span>
                                <span><strong class="grade-c">C</strong> — 55–64 (Good)</span>
                                <span><strong class="grade-d">D</strong> — 45–54 (Pass)</span>
                                <span><strong>E</strong> — 40–44 (Fair)</span>
                                <span><strong class="grade-f">F</strong> — 0–39 (Fail)</span>
                            </div>
                        </div>

                        <!-- Print Button -->
                        <div class="d-flex gap-3 no-print">
                            <button onclick="window.print()" class="btn btn-navy">
                                <i class="fas fa-print me-2"></i> Print Result
                            </button>
                            <a href="/results.php" class="btn btn-outline-gold">
                                <i class="fas fa-search me-2"></i> Check Another
                            </a>
                        </div>
                    </div>
                </div>
                <?php elseif ($searched && !$error): ?>
                <div class="alert-school">
                    <i class="fas fa-info-circle me-2"></i> No results found for the given details.
                </div>
                <?php endif; ?>

                <!-- Help Note -->
                <?php if (!$searched): ?>
                <div class="alert-school mt-3">
                    <i class="fas fa-lightbulb me-2"></i>
                    Your Student ID is on your admission letter or result slip (e.g. <strong>BS/2024/001</strong>).
                    Contact the school office if you do not have your ID.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
