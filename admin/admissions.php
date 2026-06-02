<?php
$pageTitle = 'Admission Applications';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$success = $error = '';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $stmt = $db->prepare("UPDATE admissions SET status=?, notes=? WHERE id=?");
        $stmt->execute([$_POST['status'], trim($_POST['notes'] ?? ''), (int)$_POST['app_id']]);
        $success = 'Application status updated.';
    }
}

// View single
$view = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $stmt = $db->prepare("SELECT * FROM admissions WHERE id=?");
    $stmt->execute([$_GET['view']]);
    $view = $stmt->fetch();
}

// Filter & list
$statusFilter = $_GET['status'] ?? '';
$where  = "WHERE 1=1";
$params = [];
if ($statusFilter) { $where .= " AND status=?"; $params[] = $statusFilter; }

$stmt = $db->prepare("SELECT * FROM admissions $where ORDER BY created_at DESC");
$stmt->execute($params);
$apps = $stmt->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>

<!-- View Modal -->
<?php if ($view): ?>
<div class="dash-card mb-4" style="border-left:4px solid var(--gold);">
    <div class="dash-card-header">
        <h6><i class="fas fa-file-alt me-2 text-gold"></i>Application — <?= e($view['application_no']) ?></h6>
        <a href="/admin/admissions.php" class="btn btn-sm btn-outline-gold">Back to List</a>
    </div>
    <div class="dash-card-body">
        <div class="row g-4">
            <div class="col-md-8">
                <h6 class="text-gold border-bottom pb-2">Student Details</h6>
                <div class="row g-2 mb-3 small">
                    <div class="col-md-6"><strong>Full Name:</strong> <?= e($view['full_name']) ?></div>
                    <div class="col-md-3"><strong>Gender:</strong> <?= ucfirst(e($view['gender'])) ?></div>
                    <div class="col-md-3"><strong>DOB:</strong> <?= $view['date_of_birth'] ? date('M j, Y', strtotime($view['date_of_birth'])) : '—' ?></div>
                    <div class="col-md-4"><strong>Class Applying:</strong> <?= e($view['class_applying']) ?></div>
                    <div class="col-md-4"><strong>Previous School:</strong> <?= e($view['previous_school'] ?: '—') ?></div>
                    <div class="col-md-4"><strong>Applied:</strong> <?= date('M j, Y', strtotime($view['created_at'])) ?></div>
                </div>
                <h6 class="text-gold border-bottom pb-2">Parent / Guardian</h6>
                <div class="row g-2 small">
                    <div class="col-md-6"><strong>Name:</strong> <?= e($view['parent_name']) ?></div>
                    <div class="col-md-6"><strong>Phone:</strong> <a href="tel:<?= e($view['parent_phone']) ?>"><?= e($view['parent_phone']) ?></a></div>
                    <div class="col-md-6"><strong>Email:</strong> <?= $view['parent_email'] ? '<a href="mailto:' . e($view['parent_email']) . '">' . e($view['parent_email']) . '</a>' : '—' ?></div>
                    <div class="col-md-6"><strong>WhatsApp:</strong> <?= $view['parent_whatsapp'] ? '<a href="https://wa.me/' . preg_replace('/\D/', '', $view['parent_whatsapp']) . '" target="_blank">Chat</a>' : '—' ?></div>
                    <div class="col-12"><strong>Address:</strong> <?= e($view['address'] ?: '—') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <h6 class="text-gold border-bottom pb-2">Update Status</h6>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="app_id" value="<?= $view['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending"  <?= $view['status'] === 'pending'  ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $view['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $view['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= e($view['notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-navy w-100">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Applications Table -->
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-file-alt me-2 text-gold"></i>All Applications (<?= count($apps) ?>)</h6>
        <div class="d-flex gap-2">
            <a href="?status=pending"  class="btn btn-sm <?= $statusFilter === 'pending' ? 'btn-navy' : 'btn-outline-gold' ?>">Pending</a>
            <a href="?status=approved" class="btn btn-sm <?= $statusFilter === 'approved' ? 'btn-navy' : 'btn-outline-gold' ?>">Approved</a>
            <a href="?status=rejected" class="btn btn-sm <?= $statusFilter === 'rejected' ? 'btn-navy' : 'btn-outline-gold' ?>">Rejected</a>
            <a href="/admin/admissions.php" class="btn btn-sm btn-outline-gold">All</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>App No.</th><th>Student Name</th><th>Class</th>
                    <th>Parent Phone</th><th>Date</th><th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($apps)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No applications found</td></tr>
                <?php else: ?>
                <?php foreach ($apps as $a): ?>
                <tr>
                    <td class="fw-semibold text-navy small"><?= e($a['application_no']) ?></td>
                    <td><?= e($a['full_name']) ?></td>
                    <td><?= e($a['class_applying']) ?></td>
                    <td><a href="tel:<?= e($a['parent_phone']) ?>"><?= e($a['parent_phone']) ?></a></td>
                    <td class="small text-muted"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
                    <td><span class="pill <?= $a['status'] === 'pending' ? 'pill-warning' : ($a['status'] === 'approved' ? 'pill-success' : 'pill-danger') ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td><a href="?view=<?= $a['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Review</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
