<?php
require_once 'includes/header.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: /news.php'); exit; }

$db   = getDB();
$stmt = $db->prepare("SELECT n.*, u.full_name AS author_name FROM news n LEFT JOIN users u ON n.author_id=u.id WHERE n.slug=? AND n.is_published=1");
$stmt->execute([$slug]);
$news = $stmt->fetch();

if (!$news) { header('Location: /news.php'); exit; }

$pageTitle = $news['title'];
$pageDesc  = $news['excerpt'] ?? substr(strip_tags($news['content']), 0, 160);

// Related news
$related = $db->prepare("SELECT * FROM news WHERE is_published=1 AND id != ? AND category=? ORDER BY published_at DESC LIMIT 3");
$related->execute([$news['id'], $news['category']]);
$related = $related->fetchAll();
?>

<section class="page-hero">
    <div class="container page-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="/news.php">News</a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width:300px;"><?= e($news['title']) ?></li>
            </ol>
        </nav>
        <h1 style="font-size:clamp(1.5rem,3vw,2.2rem);"><?= e($news['title']) ?></h1>
        <div class="d-flex flex-wrap gap-3 mt-2" style="color:rgba(255,255,255,0.65);font-size:0.85rem;">
            <span><i class="fas fa-tag me-1"></i><?= e($news['category']) ?></span>
            <span><i class="fas fa-calendar-alt me-1"></i><?= $news['published_at'] ? date('D, M j Y', strtotime($news['published_at'])) : '' ?></span>
            <?php if ($news['author_name']): ?>
            <span><i class="fas fa-user me-1"></i><?= e($news['author_name']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <?php if ($news['featured_image']): ?>
                <img src="<?= e($news['featured_image']) ?>" alt="<?= e($news['title']) ?>" class="img-fluid rounded-3 mb-4 w-100" style="max-height:420px;object-fit:cover;">
                <?php endif; ?>
                <article class="news-content" style="line-height:1.9;color:var(--gray-700);">
                    <?= $news['content'] /* already HTML from admin — admin-entered, trusted */ ?>
                </article>

                <div class="mt-5 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/news-detail.php?slug=' . $news['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-gold"><i class="fab fa-facebook me-1"></i>Share</a>
                        <a href="https://wa.me/?text=<?= urlencode($news['title'] . ' — ' . SITE_URL . '/news-detail.php?slug=' . $news['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-gold"><i class="fab fa-whatsapp me-1"></i>Share</a>
                    </div>
                    <a href="/news.php" class="btn btn-outline-gold btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to News</a>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <?php if (!empty($related)): ?>
                <div class="dash-card mb-4">
                    <div class="dash-card-header"><h6>Related News</h6></div>
                    <div class="dash-card-body p-0">
                        <?php foreach ($related as $r): ?>
                        <a href="/news-detail.php?slug=<?= e($r['slug']) ?>" class="d-flex gap-3 p-3 border-bottom text-decoration-none" style="transition:background 0.2s;" onmouseover="this.style.background='var(--off-white)'" onmouseout="this.style.background=''">
                            <div style="min-width:60px;height:60px;background:linear-gradient(135deg,var(--navy),var(--navy-light));border-radius:8px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);font-size:1.5rem;">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div>
                                <div class="small fw-semibold text-navy"><?= e(substr($r['title'], 0, 60)) ?>...</div>
                                <div class="text-muted" style="font-size:0.75rem;"><?= $r['published_at'] ? date('M j, Y', strtotime($r['published_at'])) : '' ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="dash-card">
                    <div class="dash-card-header"><h6>Quick Links</h6></div>
                    <div class="dash-card-body">
                        <div class="d-flex flex-column gap-2">
                            <a href="/admissions.php" class="btn btn-navy btn-sm"><i class="fas fa-file-alt me-2"></i>Apply for Admission</a>
                            <a href="/results.php" class="btn btn-outline-gold btn-sm"><i class="fas fa-graduation-cap me-2"></i>Check Results</a>
                            <a href="/contact.php" class="btn btn-outline-gold btn-sm"><i class="fas fa-envelope me-2"></i>Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
