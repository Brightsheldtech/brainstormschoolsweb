<?php
$pageTitle = 'Attendance Record';
require_once '../includes/parent_header.php';

if (!$activeChild) { redirect('/parent/index.php'); }

$childId = $activeChild['id'];
$db      = getDB();

$selectedMonth = $_GET['month'] ?? date('Y-m');

// Summary for selected month
$summary = $db->prepare("
    SELECT
        SUM(status='present') AS present,
        SUM(status='absent')  AS absent,
        SUM(status='late')    AS late,
        COUNT(*) AS total
    FROM attendance
    WHERE student_id = ?
      AND DATE_FORMAT(date,'%Y-%m') = ?
");
$summary->execute([$childId, $selectedMonth]);
$summary = $summary->fetch();

// All records for selected month
$records = $db->prepare("
    SELECT * FROM attendance
    WHERE student_id = ? AND DATE_FORMAT(date,'%Y-%m') = ?
    ORDER BY date DESC
");
$records->execute([$childId, $selectedMonth]);
$records = $records->fetchAll();

// All-time summary
$allTime = $db->prepare("
    SELECT
        SUM(status='present') AS present,
        SUM(status='absent')  AS absent,
        SUM(status='late')    AS late,
        COUNT(*) AS total
    FROM attendance
    WHERE student_id = ?
");
$allTime->execute([$childId]);
$allTime = $allTime->fetch();

$attRate = ($allTime['total'] > 0) ? round(($allTime['present'] / $allTime['total']) * 100) : 0;

$months = [];
for ($i = 0; $i < 8; $i++) {
    $months[] = date('Y-m', strtotime("-$i months"));
}
?>

<!-- All-time KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="kpi-num"><?= $allTime['present'] ?? 0 ?></div>
                <div class="kpi-lbl">Days Present</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="fas fa-times-circle"></i></div>
            <div>
                <div class="kpi-num"><?= $allTime['absent'] ?? 0 ?></div>
                <div class="kpi-lbl">Days Absent</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon gold"><i class="fas fa-clock"></i></div>
            <div>
                <div class="kpi-num"><?= $allTime['late'] ?? 0 ?></div>
                <div class="kpi-lbl">Days Late</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="kpi-card">
            <div class="kpi-icon <?= $attRate >= 80 ? 'green' : ($attRate >= 60 ? 'gold' : 'red') ?>">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div>
                <div class="kpi-num"><?= $attRate ?>%</div>
                <div class="kpi-lbl">Overall Rate</div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance alert if low -->
<?php if ($attRate > 0 && $attRate < 75): ?>
<div class="alert-school mb-4" style="border-left-color:var(--red);">
    <i class="fas fa-exclamation-triangle me-2" style="color:var(--red);"></i>
    <strong>Attendance Warning:</strong> <?= e($activeChild['full_name']) ?>'s overall attendance is <?= $attRate ?>%, which is below the recommended 75%.
    Please ensure your child attends school regularly. <a href="/contact.php" class="text-gold fw-bold">Contact us</a> if there's a concern.
</div>
<?php endif; ?>

<!-- Month filter + records -->
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-clipboard-check me-2 text-gold"></i>Monthly Attendance Record</h6>
        <form method="GET" class="d-flex align-items-center gap-2">
            <select name="month" class="form-select form-select-sm" style="width:160px;" onchange="this.form.submit()">
                <?php foreach ($months as $m): ?>
                <option value="<?= $m ?>" <?= $selectedMonth === $m ? 'selected' : '' ?>>
                    <?= date('F Y', strtotime($m . '-01')) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- Month summary bar -->
    <?php if ($summary['total'] > 0): ?>
    <div class="px-4 py-3 border-bottom" style="background:var(--cream);">
        <div class="d-flex gap-4 flex-wrap small">
            <span><strong class="text-success"><?= $summary['present'] ?></strong> present</span>
            <span><strong class="text-danger"><?= $summary['absent'] ?></strong> absent</span>
            <span><strong class="text-warning"><?= $summary['late'] ?></strong> late</span>
            <span class="text-muted"><?= $summary['total'] ?> school days recorded</span>
        </div>
        <!-- Visual bar -->
        <div class="mt-2 d-flex rounded-3 overflow-hidden" style="height:10px;">
            <?php if ($summary['total'] > 0):
                $pPct = round(($summary['present'] / $summary['total']) * 100);
                $aPct = round(($summary['absent'] / $summary['total']) * 100);
                $lPct = 100 - $pPct - $aPct;
            ?>
            <div style="width:<?= $pPct ?>%;background:#16a34a;" title="Present"></div>
            <div style="width:<?= $lPct ?>%;background:#ca8a04;" title="Late"></div>
            <div style="width:<?= $aPct ?>%;background:#dc2626;" title="Absent"></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Records table -->
    <?php if (!empty($records)): ?>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Status</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td class="fw-bold"><?= date('M j, Y', strtotime($r['date'])) ?></td>
                    <td class="text-muted"><?= date('l', strtotime($r['date'])) ?></td>
                    <td>
                        <span class="pill <?= $r['status'] === 'present' ? 'pill-success' : ($r['status'] === 'late' ? 'pill-warning' : 'pill-danger') ?>">
                            <?php if ($r['status'] === 'present'): ?>
                                <i class="fas fa-check me-1"></i>
                            <?php elseif ($r['status'] === 'absent'): ?>
                                <i class="fas fa-times me-1"></i>
                            <?php else: ?>
                                <i class="fas fa-clock me-1"></i>
                            <?php endif; ?>
                            <?= ucfirst($r['status']) ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= e($r['note'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="dash-card-body text-center py-4">
        <div style="font-size:3rem;color:var(--gray-200);"><i class="fas fa-calendar-times"></i></div>
        <p class="text-muted mt-2 mb-0 small">No attendance records for <?= date('F Y', strtotime($selectedMonth . '-01')) ?>.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/parent_footer.php'; ?>
