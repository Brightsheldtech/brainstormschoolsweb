<?php
$pageTitle = 'Manage News';
require_once '../includes/dash_header.php';
requireLogin('admin');

$db      = getDB();
$action  = $_GET['action'] ?? '';
$success = $error = '';

function slugify($text) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
}

// Save / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $title   = trim($_POST['title']);
        $content = $_POST['content'];
        $excerpt = trim($_POST['excerpt'] ?? '');
        $cat     = trim($_POST['category'] ?? 'News');
        $publish = isset($_POST['is_published']) ? 1 : 0;
        $pubDate = $publish ? date('Y-m-d H:i:s') : null;

        if ($_POST['edit_id'] ?? false) {
            $stmt = $db->prepare("UPDATE news SET title=?,content=?,excerpt=?,category=?,is_published=?,published_at=? WHERE id=?");
            $stmt->execute([$title, $content, $excerpt, $cat, $publish, $pubDate, (int)$_POST['edit_id']]);
            $success = 'News post updated.';
        } else {
            $slug = slugify($title) . '-' . time();
            $stmt = $db->prepare("INSERT INTO news (title,slug,content,excerpt,category,author_id,is_published,published_at) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$title, $slug, $content, $excerpt, $cat, $user['id'], $publish, $pubDate]);
            $success = 'News post published.';
        }
        $action = '';
    }
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security error.'; }
    else {
        $db->prepare("DELETE FROM news WHERE id=?")->execute([(int)$_POST['delete_id']]);
        $success = 'Post deleted.';
    }
}

// Edit fetch
$editPost = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editPost = $stmt->fetch();
}

$posts = $db->query("SELECT n.*, u.full_name AS author FROM news n LEFT JOIN users u ON n.author_id=u.id ORDER BY n.created_at DESC")->fetchAll();
?>

<?php if ($success): ?>
<div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="dash-card mb-4">
    <div class="dash-card-header">
        <h6><i class="fas fa-pen me-2 text-gold"></i><?= $action === 'edit' ? 'Edit Post' : 'Add New Post' ?></h6>
        <a href="/admin/news.php" class="btn btn-sm btn-outline-gold">Cancel</a>
    </div>
    <div class="dash-card-body">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <?php if ($editPost): ?><input type="hidden" name="edit_id" value="<?= $editPost['id'] ?>"><?php endif; ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?= e($editPost['title'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <?php foreach (['News','Events','Academic','Sports','Announcement'] as $c): ?>
                        <option value="<?= $c ?>" <?= ($editPost['category'] ?? 'News') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Excerpt (short summary)</label>
                    <input type="text" name="excerpt" class="form-control" placeholder="One line summary shown in listings..." value="<?= e($editPost['excerpt'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Content *</label>
                    <textarea name="content" class="form-control" rows="12" required><?= e($editPost['content'] ?? '') ?></textarea>
                    <small class="text-muted">HTML tags are supported (p, strong, em, ul, li, h3, h4).</small>
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_published" id="publish" class="form-check-input" <?= ($editPost['is_published'] ?? 0) ? 'checked' : '' ?>>
                        <label for="publish" class="form-check-label">Publish immediately</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-navy px-4"><i class="fas fa-save me-2"></i>Save Post</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="dash-card">
    <div class="dash-card-header">
        <h6><i class="fas fa-newspaper me-2 text-gold"></i>All Posts (<?= count($posts) ?>)</h6>
        <a href="/admin/news.php?action=add" class="btn btn-navy btn-sm"><i class="fas fa-plus me-1"></i>New Post</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($posts)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No posts yet</td></tr>
                <?php else: ?>
                <?php foreach ($posts as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e(substr($p['title'], 0, 60)) ?><?= strlen($p['title']) > 60 ? '…' : '' ?></td>
                    <td><span class="pill pill-navy"><?= e($p['category']) ?></span></td>
                    <td class="small"><?= e($p['author'] ?? '—') ?></td>
                    <td class="small text-muted"><?= date('M j, Y', strtotime($p['created_at'])) ?></td>
                    <td><span class="pill <?= $p['is_published'] ? 'pill-success' : 'pill-warning' ?>"><?= $p['is_published'] ? 'Published' : 'Draft' ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-gold py-0 px-2">Edit</a>
                            <a href="/news-detail.php?slug=<?= e($p['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-gold py-0 px-2">View</a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this post?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
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
