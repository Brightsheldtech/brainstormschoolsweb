<?php
$pageTitle = 'Manage Events';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$action  = $_GET['action'] ?? '';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $data = [
            trim($_POST['title']),
            trim($_POST['description'] ?? ''),
            $_POST['event_date'],
            $_POST['event_time'] ?: null,
            $_POST['end_date'] ?: null,
            trim($_POST['venue'] ?? ''),
            isset($_POST['is_published']) ? 1 : 0,
        ];
        if ($_POST['edit_id'] ?? false) {
            $stmt = $db->prepare("UPDATE events SET title=?,description=?,event_date=?,event_time=?,end_date=?,venue=?,is_published=? WHERE id=?");
            $data[] = (int)$_POST['edit_id'];
            $stmt->execute($data);
            $success = 'Event updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO events (title,description,event_date,event_time,end_date,venue,is_published) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute($data);
            $success = 'Event added.';
        }
        $action = '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $db->prepare("DELETE FROM events WHERE id=?")->execute([(int)$_POST['delete_id']]);
        $success = 'Event deleted.';
    }
}

$editEv = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM events WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editEv = $stmt->fetch();
}

$events = $db->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-calendar-plus me-2 text-gold"></i><?= $action === 'edit' ? 'Edit Event' : 'Add New Event' ?></h6>
        <a href="/admin/events.php" class="btn btn-sm btn-outline-gold">Cancel</a>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <?php if ($editEv): ?><input type="hidden" name="edit_id" value="<?= $editEv['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Event Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?= e($editEv['title'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Event Date *</label>
                    <input type="date" name="event_date" class="form-control" required value="<?= e($editEv['event_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Event Time</label>
                    <input type="time" name="event_time" class="form-control" value="<?= e($editEv['event_time'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= e($editEv['end_date'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Venue</label>
                    <input type="text" name="venue" class="form-control" placeholder="Location or venue name" value="<?= e($editEv['venue'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?= e($editEv['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_published" id="publish" class="form-check-input" <?= ($editEv['is_published'] ?? 1) ? 'checked' : '' ?>>
                        <label for="publish" class="form-check-label">Publish on website</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-navy px-4"><i class="fas fa-save me-2"></i>Save Event</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-calendar-alt me-2 text-gold"></i>All Events (<?= count($events) ?>)</h6>
        <a href="/admin/events.php?action=add" class="btn btn-navy btn-sm"><i class="fas fa-plus me-1"></i>Add Event</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Date</th><th>Venue</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($events)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No events yet</td></tr>
                <?php else: ?>
                <?php foreach ($events as $ev): ?>
                <tr>
                    <td class="fw-semibold"><?= e($ev['title']) ?></td>
                    <td><?= date('M j, Y', strtotime($ev['event_date'])) ?></td>
                    <td class="small"><?= e($ev['venue'] ?: '—') ?></td>
                    <td><span class="pill <?= $ev['is_published'] ? 'pill-success' : 'pill-warning' ?>"><?= $ev['is_published'] ? 'Published' : 'Hidden' ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="?action=edit&id=<?= $ev['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Edit</a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this event?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id" value="<?= $ev['id'] ?>">
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
