<?php
$pageTitle = 'Manage Gallery';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$success = $error = '';

// Upload & save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    elseif (empty($_FILES['photo']['name'])) { $error = 'Please select a photo to upload.'; }
    else {
        $file     = $_FILES['photo'];
        $allowed  = ['image/jpeg','image/png','image/gif','image/webp'];
        $maxSize  = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowed)) { $error = 'Only JPG, PNG, GIF, and WEBP images are allowed.'; }
        elseif ($file['size'] > $maxSize) { $error = 'Image must be under 5MB.'; }
        else {
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'gallery_' . time() . '_' . rand(100, 999) . '.' . strtolower($ext);
            $dest     = __DIR__ . '/../assets/images/gallery/' . $filename;
            @mkdir(dirname($dest), 0755, true);
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $stmt = $db->prepare("INSERT INTO gallery (title, description, image_path, category) VALUES (?,?,?,?)");
                $stmt->execute([
                    trim($_POST['title']),
                    trim($_POST['description'] ?? ''),
                    '/assets/images/gallery/' . $filename,
                    trim($_POST['category'] ?? 'General'),
                ]);
                $success = 'Photo uploaded successfully.';
            } else { $error = 'Upload failed. Check folder permissions.'; }
        }
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $stmt = $db->prepare("SELECT image_path FROM gallery WHERE id=?");
        $stmt->execute([(int)$_POST['delete_id']]);
        $img = $stmt->fetchColumn();
        if ($img) @unlink(__DIR__ . '/..' . $img);
        $db->prepare("DELETE FROM gallery WHERE id=?")->execute([(int)$_POST['delete_id']]);
        $success = 'Photo deleted.';
    }
}

$photos = $db->query("SELECT * FROM gallery ORDER BY uploaded_at DESC")->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger mb-4"><?= e($error) ?></div>
<?php endif; ?>

<!-- Upload Form -->
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-upload me-2 text-gold"></i>Upload New Photo</h6>
    </div>
    <div class="dash-card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Photo Title *</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Sports Day 2024">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach (['General','Sports','Events','Awards','Classroom','Trips'] as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Photo (JPG/PNG, max 5MB)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-navy w-100"><i class="fas fa-upload me-1"></i>Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Gallery Grid -->
<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-images me-2 text-gold"></i>All Photos (<?= count($photos) ?>)</h6>
    </div>
    <div class="dash-card-body">
        <?php if (!empty($photos)): ?>
        <div class="row g-3">
            <?php foreach ($photos as $p): ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                <div class="border rounded-2 overflow-hidden" style="position:relative;">
                    <img src="<?= e($p['image_path']) ?>" alt="<?= e($p['title']) ?>"
                         style="width:100%;height:120px;object-fit:cover;display:block;">
                    <div class="p-2">
                        <div class="small fw-semibold text-truncate"><?= e($p['title']) ?></div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="pill pill-navy" style="font-size:0.65rem;"><?= e($p['category']) ?></span>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this photo?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-link text-danger p-0 small"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-4 text-muted"><i class="fas fa-images fa-2x mb-2 d-block"></i>No photos uploaded yet.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/dash_footer.php'; ?>
