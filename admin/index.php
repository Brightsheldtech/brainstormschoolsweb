<?php
$pageTitle = 'Admin Dashboard';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db = getDB();
$totalStudents    = $db->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetchColumn();
$totalTeachers    = $db->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='active'")->fetchColumn();
$totalAdmissions  = $db->query("SELECT COUNT(*) FROM admissions WHERE status='pending'")->fetchColumn();
$unreadMessages   = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn();
$recentAdmissions = $db->query("SELECT * FROM admissions ORDER BY created_at DESC LIMIT 8")->fetchAll();
$recentMessages   = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 6")->fetchAll();
?>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fas fa-user-graduate"></i></div>
            <div>
                <div class="kpi-num"><?= number_format($totalStudents) ?></div>
                <div class="kpi-lbl">Active Students</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon gold"><i class="fas fa-chalkboard-teacher"></i></div>
            <div>
                <div class="kpi-num"><?= number_format($totalTeachers) ?></div>
                <div class="kpi-lbl">Teaching Staff</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fas fa-file-alt"></i></div>
            <div>
                <div class="kpi-num"><?= number_format($totalAdmissions) ?></div>
                <div class="kpi-lbl">Pending Applications</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="kpi-card">
            <div class="kpi-icon red"><i class="fas fa-envelope"></i></div>
            <div>
                <div class="kpi-num"><?= number_format($unreadMessages) ?></div>
                <div class="kpi-lbl">Unread Messages</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-bolt me-2 text-gold"></i>Quick Actions</h6>
    </div>
    <div class="dash-card-body">
        <div class="d-flex flex-wrap gap-3">
            <a href="/admin/students.php?action=add" class="btn btn-navy btn-sm"><i class="fas fa-user-plus me-1"></i> Add Student</a>
            <a href="/admin/results.php" class="btn btn-navy btn-sm"><i class="fas fa-plus me-1"></i> Enter Results</a>
            <a href="/admin/admissions.php" class="btn btn-navy btn-sm"><i class="fas fa-file-alt me-1"></i> View Applications</a>
            <a href="/admin/news.php?action=add" class="btn btn-outline-gold btn-sm"><i class="fas fa-plus me-1"></i> Post News</a>
            <a href="/admin/events.php?action=add" class="btn btn-outline-gold btn-sm"><i class="fas fa-calendar-plus me-1"></i> Add Event</a>
            <a href="/admin/gallery.php?action=upload" class="btn btn-outline-gold btn-sm"><i class="fas fa-upload me-1"></i> Upload Photos</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Admissions -->
    <div class="col-lg-7">
        <div class="dash-card">
            <div class="dash-card-header">
                <h6><i class="fas fa-file-alt me-2 text-gold"></i>Recent Admission Applications</h6>
                <a href="/admin/admissions.php" class="btn btn-sm btn-outline-gold">View All</a>
            </div>
            <div class="dash-card-body p-0">
                <?php if (!empty($recentAdmissions)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>App No.</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentAdmissions as $a): ?>
                            <tr>
                                <td class="fw-semibold text-navy small"><?= e($a['application_no']) ?></td>
                                <td><?= e($a['full_name']) ?></td>
                                <td><span class="small"><?= e($a['class_applying']) ?></span></td>
                                <td class="small text-muted"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
                                <td>
                                    <span class="pill <?= $a['status'] === 'pending' ? 'pill-warning' : ($a['status'] === 'approved' ? 'pill-success' : 'pill-danger') ?>">
                                        <?= ucfirst($a['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/admin/admissions.php?view=<?= $a['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No applications yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Unread Messages -->
    <div class="col-lg-5">
        <div class="dash-card">
            <div class="dash-card-header">
                <h6><i class="fas fa-envelope me-2 text-gold"></i>Recent Messages</h6>
                <a href="/admin/messages.php" class="btn btn-sm btn-outline-gold">View All</a>
            </div>
            <div class="dash-card-body p-0">
                <?php if (!empty($recentMessages)): ?>
                <?php foreach ($recentMessages as $msg): ?>
                <div class="d-flex align-items-start gap-3 p-3 border-bottom <?= !$msg['is_read'] ? 'bg-gold-pale' : '' ?>" style="<?= !$msg['is_read'] ? 'background:var(--gold-pale);' : '' ?>">
                    <div style="width:36px;height:36px;min-width:36px;border-radius:50%;background:var(--navy);color:var(--gold);display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;">
                        <?= strtoupper(substr($msg['full_name'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold small"><?= e($msg['full_name']) ?></span>
                            <span class="text-muted" style="font-size:0.72rem;"><?= date('M j', strtotime($msg['created_at'])) ?></span>
                        </div>
                        <div class="small text-muted text-truncate"><?= e($msg['subject'] ?? $msg['message']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No messages yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
