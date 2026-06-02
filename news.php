<?php
$pageTitle = 'News & Updates';
require_once 'includes/header.php';

$db = getDB();
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset  = ($page - 1) * $perPage;
$category = trim($_GET['category'] ?? '');

$where = "WHERE is_published = 1";
$params = [];
if ($category) { $where .= " AND category = ?"; $params[] = $category; }

$total = $db->prepare("SELECT COUNT(*) FROM news $where");
$total->execute($params);
$totalCount = $total->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$stmt = $db->prepare("SELECT * FROM news $where ORDER BY published_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$newsList = $stmt->fetchAll();

$categories = $db->query("SELECT DISTINCT category FROM news WHERE is_published = 1")->fetchAll(PDO::FETCH_COLUMN);
?>

<section class="page-hero">
    <div class="container page-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">News</li>
            </ol>
        </nav>
        <h1>News & Announcements</h1>
        <p style="color:rgba(255,255,255,0.7)">Stay updated with the latest happenings at Brainstorm School.</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <!-- Category Filter -->
        <div class="d-flex flex-wrap gap-2 mb-5">
            <a href="/news.php" class="btn <?= !$category ? 'btn-navy' : 'btn-outline-gold' ?> btn-sm">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="/news.php?category=<?= urlencode($cat) ?>" class="btn <?= $category === $cat ? 'btn-navy' : 'btn-outline-gold' ?> btn-sm">
                <?= e($cat) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($newsList)): ?>
        <div class="row g-4">
            <?php foreach ($newsList as $n): ?>
            <div class="col-lg-4 col-md-6">
                <div class="news-card h-100">
                    <?php if ($n['featured_image']): ?>
                        <img src="<?= e($n['featured_image']) ?>" alt="<?= e($n['title']) ?>" class="news-img">
                    <?php else: ?>
                        <div class="news-img-placeholder"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="news-meta">
                            <span class="category"><?= e($n['category']) ?></span>
                            <span class="ms-2"><i class="fas fa-calendar-alt me-1"></i>
                                <?= $n['published_at'] ? date('M j, Y', strtotime($n['published_at'])) : '' ?>
                            </span>
                        </div>
                        <h5><a href="/news-detail.php?slug=<?= e($n['slug']) ?>"><?= e($n['title']) ?></a></h5>
                        <p class="text-muted small mb-3"><?= e(substr($n['excerpt'] ?? strip_tags($n['content']), 0, 130)) ?>...</p>
                        <a href="/news-detail.php?slug=<?= e($n['slug']) ?>" class="text-gold fw-semibold small">
                            Read More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-5 d-flex justify-content-center">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?><?= $category ? '&category=' . urlencode($category) : '' ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="text-center py-5">
            <div style="font-size:4rem;color:var(--gray-200);"><i class="fas fa-newspaper"></i></div>
            <h5 class="text-muted mt-3">No news published yet</h5>
            <p class="text-muted small">Check back soon for updates and announcements.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
