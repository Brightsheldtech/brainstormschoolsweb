<?php
$pageTitle = 'Dashboard';
require_once '../includes/parent_header.php';

// Need an active child to show anything useful
if (!$activeChild) { ?>
    <div class="dash-card p-5 text-center">
        <div style="font-size:4rem;color:var(--gray-200);"><i class="fas fa-user-graduate"></i></div>
        <h5 class="text-muted mt-3">No child linked to your account yet</h5>
        <p class="text-muted small mb-4">Please contact the school office and ask them to link your child to your parent portal account.</p>
        <a href="/contact.php" class="btn btn-navy">Contact the School</a>
    </div>
<?php
    require_once '../includes/parent_footer.php';
    exit;
}

$childId = $activeChild['id'];
$db      = getDB();

// Latest results — current term
$latestResults = $db->prepare("
    SELECT r.*, sub.name AS subject_name
    FROM results r
    JOIN subjects sub ON r.subject_id = sub.id
    WHERE r.student_id = ? AND r.term = ? AND r.academic_year = ?
    ORDER BY sub.name
");
$latestResults->execute([$childId, CURRENT_TERM, ACADEMIC_YEAR]);
$latestResults = $latestResults->fetchAll();

$totalSubjects = count($latestResults);
$totalScore    = array_sum(array_column($latestResults, 'total'));
$average       = $totalSubjects > 0 ? round($totalScore / $totalSubjects, 1) : null;
$overallGrade  = $average !== null ? getGrade($average) : null;

// Attendance — last 30 days
$attStmt = $db->prepare("
    SELECT
        SUM(status='present') AS present,
        SUM(status='absent')  AS absent,
        SUM(status='late')    AS late,
        COUNT(*) AS total
    FROM attendance
    WHERE student_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
");
$attStmt->execute([$childId]);
$att = $attStmt->fetch();
$attRate = ($att['total'] > 0) ? round(($att['present'] / $att['total']) * 100) : null;

// Recent attendance rows
$recentAtt = $db->prepare("
    SELECT * FROM attendance
    WHERE student_id = ?
    ORDER BY date DESC LIMIT 7
");
$recentAtt->execute([$childId]);
$recentAtt = $recentAtt->fetchAll();

// Latest 2 news items
$news = $db->query("SELECT * FROM news WHERE is_published=1 ORDER BY published_at DESC LIMIT 2")->fetchAll();
?>

<!-- Greeting banner -->
<div class="dash-card mb-4" style="background:linear-gradient(135deg,var(--navy) 0%,var(--navy-light) 100%);border:none;">
    <div class="dash-card-body d-flex align-items-center gap-4 flex-wrap" style="padding:28px 28px;">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(201,162,39,0.15);border:3px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--gold);flex-shrink:0;">
            <?= strtoupper(substr($activeChild['full_name'], 0, 1)) ?>
        </div>
        <div>
            <p style="color:rgba(255,255,255,0.6);font-size:0.85rem;margin:0 0 2px;font-family:'Lato',sans-serif;">
                <?= CURRENT_TERM ?> &bull; <?= ACADEMIC_YEAR ?>
            </p>
            <h4 style="color:var(--white);margin:0 0 4px;font-size:1.4rem;">
                <?= e($activeChild['full_name']) ?>
            </h4>
            <p style="color:var(--gold);margin:0;font-size:0.88rem;font-family:'Lato',sans-serif;font-weight:700;">
                <?= e($activeChild['class_name'] ?? '—') ?> &bull; ID: <?= e($activeChild['student_id']) ?>
            </p>
        </div>
        <div class="ms-auto d-flex gap-2 flex-wrap">
            <a href="/parent/results.php" class="btn btn-gold btn-sm">
                <i class="fas fa-graduation-cap me-1"></i> View Results
            </a>
            <a href="/parent/attendance.php" class="btn btn-outline-white btn-sm">
                <i class="fas fa-clipboard-check me-1"></i> Attendance
            </a>
        </div>
    </div>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon gold"><i class="fas fa-book-open"></i></div>
            <div>
                <div class="kpi-num"><?= $totalSubjects ?: '—' ?></div>
                <div class="kpi-lbl">Subjects This Term</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon <?= $average >= 50 ? 'green' : 'red' ?>">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <div class="kpi-num"><?= $average !== null ? $average . '%' : '—' ?></div>
                <div class="kpi-lbl">Term Average</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon <?= ($attRate >= 80) ? 'green' : (($attRate >= 60) ? 'gold' : 'red') ?>">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="kpi-num"><?= $attRate !== null ? $attRate . '%' : '—' ?></div>
                <div class="kpi-lbl">Attendance (30 days)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon navy"><i class="fas fa-award"></i></div>
            <div>
                <div class="kpi-num <?= $overallGrade ? 'grade-' . strtolower($overallGrade['grade']) : '' ?>">
                    <?= $overallGrade ? $overallGrade['grade'] : '—' ?>
                </div>
                <div class="kpi-lbl"><?= $overallGrade ? $overallGrade['remark'] : 'No results yet' ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Results Summary -->
    <div class="col-lg-7">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h6><i class="fas fa-graduation-cap me-2 text-gold"></i>
                    <?= CURRENT_TERM ?> Results Summary
                </h6>
                <a href="/parent/results.php" class="btn btn-sm btn-outline-gold">Full Report</a>
            </div>
            <?php if (!empty($latestResults)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Subject</th><th class="text-center">CA</th><th class="text-center">Exam</th><th class="text-center">Total</th><th class="text-center">Grade</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestResults as $r):
                            $g = $r['grade'] ?? getGrade($r['total'])['grade'];
                        ?>
                        <tr>
                            <td><?= e($r['subject_name']) ?></td>
                            <td class="text-center"><?= number_format($r['ca1'] + $r['ca2'], 1) ?></td>
                            <td class="text-center"><?= number_format($r['exam'], 1) ?></td>
                            <td class="text-center fw-bold"><?= number_format($r['total'], 1) ?></td>
                            <td class="text-center grade-<?= strtolower($g) ?>"><?= $g ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="dash-card-body text-center py-4">
                <div style="font-size:3rem;color:var(--gray-200);"><i class="fas fa-graduation-cap"></i></div>
                <p class="text-muted mt-2 mb-0 small">
                    Results for <?= CURRENT_TERM ?> haven't been published yet.<br>
                    Check back after exams.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Attendance + News -->
    <div class="col-lg-5 d-flex flex-column gap-4">

        <!-- Recent Attendance -->
        <div class="dash-card">
            <div class="dash-card-header">
                <h6><i class="fas fa-clipboard-check me-2 text-gold"></i>Recent Attendance</h6>
                <a href="/parent/attendance.php" class="btn btn-sm btn-outline-gold">Full Record</a>
            </div>
            <?php if (!empty($recentAtt)): ?>
            <div class="dash-card-body p-0">
                <?php foreach ($recentAtt as $a): ?>
                <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom">
                    <span class="small fw-bold" style="color:var(--navy);">
                        <?= date('D, M j', strtotime($a['date'])) ?>
                    </span>
                    <span class="pill <?= $a['status'] === 'present' ? 'pill-success' : ($a['status'] === 'late' ? 'pill-warning' : 'pill-danger') ?>">
                        <?= ucfirst($a['status']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="dash-card-body text-center py-3">
                <p class="text-muted small mb-0">No attendance records found.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- School News -->
        <?php if (!empty($news)): ?>
        <div class="dash-card">
            <div class="dash-card-header">
                <h6><i class="fas fa-newspaper me-2 text-gold"></i>From the School</h6>
                <a href="/news.php" target="_blank" class="btn btn-sm btn-outline-gold">All News</a>
            </div>
            <div class="dash-card-body p-0">
                <?php foreach ($news as $n): ?>
                <a href="/news-detail.php?slug=<?= e($n['slug']) ?>" target="_blank"
                   class="d-flex gap-3 p-3 border-bottom text-decoration-none"
                   style="transition:background 0.2s;" onmouseover="this.style.background='var(--cream)'" onmouseout="this.style.background=''">
                    <div style="width:44px;height:44px;min-width:44px;border-radius:10px;background:linear-gradient(135deg,var(--navy),var(--navy-light));display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);font-size:1.1rem;">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div>
                        <div class="small fw-bold text-navy" style="line-height:1.3;"><?= e(substr($n['title'], 0, 55)) ?>…</div>
                        <div class="text-muted" style="font-size:0.73rem;">
                            <?= $n['published_at'] ? date('M j, Y', strtotime($n['published_at'])) : '' ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once '../includes/parent_footer.php'; ?>
