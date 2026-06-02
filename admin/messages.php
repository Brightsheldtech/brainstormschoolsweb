<?php
$pageTitle = 'Contact Messages';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db = getDB();

// Mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $db->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$_GET['read']]);
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (verifyCsrf($_POST['csrf_token'] ?? '')) {
        $db->prepare("DELETE FROM contact_messages WHERE id=?")->execute([(int)$_POST['delete_id']]);
    }
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$viewId   = isset($_GET['view']) ? (int)$_GET['view'] : null;
$viewMsg  = null;
if ($viewId) {
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id=?");
    $stmt->execute([$viewId]);
    $viewMsg = $stmt->fetch();
    // Mark read
    $db->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$viewId]);
}
?>

<?php if ($viewMsg): ?>
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-envelope-open me-2 text-gold"></i>Message from <?= e($viewMsg['full_name']) ?></h6>
        <a href="/admin/messages.php" class="btn btn-sm btn-outline-gold">Back to List</a>
    </div>
    <div class="dash-card-body">
        <div class="row g-3 mb-3 small">
            <div class="col-md-4"><strong>Name:</strong> <?= e($viewMsg['full_name']) ?></div>
            <div class="col-md-4"><strong>Email:</strong> <a href="mailto:<?= e($viewMsg['email']) ?>"><?= e($viewMsg['email']) ?></a></div>
            <div class="col-md-4"><strong>Phone:</strong> <?= e($viewMsg['phone'] ?: '—') ?></div>
            <div class="col-md-4"><strong>Subject:</strong> <?= e($viewMsg['subject'] ?: '—') ?></div>
            <div class="col-md-4"><strong>Date:</strong> <?= date('D, M j Y g:ia', strtotime($viewMsg['created_at'])) ?></div>
        </div>
        <div class="p-3 rounded-2" style="background:var(--off-white);border:1px solid var(--gray-200);">
            <p class="mb-0"><?= nl2br(e($viewMsg['message'])) ?></p>
        </div>
        <div class="mt-3 d-flex gap-3">
            <a href="mailto:<?= e($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['subject'] ?? 'Your Message') ?>" class="btn btn-navy btn-sm">
                <i class="fas fa-reply me-1"></i> Reply via Email
            </a>
            <?php if ($viewMsg['phone']): ?>
            <a href="https://wa.me/<?= preg_replace('/\D/', '', $viewMsg['phone']) ?>" target="_blank" class="btn btn-outline-gold btn-sm">
                <i class="fab fa-whatsapp me-1"></i> WhatsApp
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-envelope me-2 text-gold"></i>All Messages (<?= count($messages) ?>)</h6>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Subject</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No messages yet</td></tr>
                <?php else: ?>
                <?php foreach ($messages as $m): ?>
                <tr style="<?= !$m['is_read'] ? 'background:var(--gold-pale);' : '' ?>">
                    <td>
                        <div class="fw-semibold"><?= e($m['full_name']) ?></div>
                        <div class="small text-muted"><?= e($m['email']) ?></div>
                    </td>
                    <td><?= e($m['subject'] ?: substr($m['message'], 0, 50) . '…') ?></td>
                    <td class="small text-muted"><?= date('M j, Y', strtotime($m['created_at'])) ?></td>
                    <td><span class="pill <?= $m['is_read'] ? 'pill-success' : 'pill-warning' ?>"><?= $m['is_read'] ? 'Read' : 'Unread' ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="?view=<?= $m['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">View</a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this message?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
