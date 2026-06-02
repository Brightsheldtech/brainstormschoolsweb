<?php
$pageTitle = 'Gallery';
require_once 'includes/header.php';

$db = getDB();
$categories = $db->query("SELECT DISTINCT category FROM gallery ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$photos     = $db->query("SELECT * FROM gallery ORDER BY uploaded_at DESC")->fetchAll();

$placeholders = [
    ['General',  'fas fa-camera',          'School Life'],
    ['Sports',   'fas fa-futbol',           'Sports Day'],
    ['Events',   'fas fa-calendar-star',    'Cultural Events'],
    ['Awards',   'fas fa-trophy',           'Prize Giving Day'],
    ['Classroom','fas fa-chalkboard',       'In the Classroom'],
    ['Trips',    'fas fa-bus',              'Educational Excursions'],
];
?>

<section class="page-hero">
    <div class="container page-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Gallery</li>
            </ol>
        </nav>
        <h1>School Gallery</h1>
        <p style="color:rgba(255,255,255,0.7)">Moments that capture the heart and spirit of Brainstorm School.</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">

        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap gap-2 mb-5 justify-content-center">
            <button class="btn btn-navy gallery-filter active" data-filter="all">All Photos</button>
            <?php foreach ($placeholders as $cat): ?>
            <button class="btn btn-outline-gold gallery-filter" data-filter="<?= strtolower($cat[0]) ?>">
                <i class="<?= $cat[1] ?> me-1"></i> <?= $cat[0] ?>
            </button>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($photos)): ?>
        <!-- Real Gallery -->
        <div class="gallery-grid" id="galleryGrid">
            <?php foreach ($photos as $photo): ?>
            <div class="gallery-item" data-category="<?= strtolower(e($photo['category'])) ?>"
                 onclick="openLightbox('<?= e($photo['image_path']) ?>', '<?= e($photo['title']) ?>')">
                <img src="<?= e($photo['image_path']) ?>" alt="<?= e($photo['title']) ?>">
                <div class="gallery-overlay">
                    <div>
                        <div class="fw-semibold small"><?= e($photo['title']) ?></div>
                        <div class="text-muted small" style="color:rgba(255,255,255,0.6) !important"><?= e($photo['category']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <!-- Placeholder Gallery when empty -->
        <div class="row g-4 text-center">
            <div class="col-12">
                <div class="alert-school">
                    <i class="fas fa-images me-2"></i>
                    Photos will appear here once the admin uploads them. Below is a preview of the gallery categories.
                </div>
            </div>
            <?php foreach ($placeholders as $cat): ?>
            <div class="col-lg-4 col-md-6">
                <div class="gallery-item" style="border-radius:var(--radius-lg);border:2px dashed var(--gray-200);">
                    <div style="width:100%;height:220px;background:linear-gradient(135deg,var(--navy),var(--navy-light));display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;color:rgba(255,255,255,0.3);border-radius:var(--radius);">
                        <i class="<?= $cat[1] ?>" style="font-size:3rem;"></i>
                        <span style="font-size:0.9rem;font-weight:600;"><?= $cat[2] ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white" id="lightboxTitle"></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="lightboxImg" src="" alt="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<JS
<script>
function openLightbox(src, title) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxTitle').textContent = title;
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}
document.querySelectorAll('.gallery-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.gallery-filter').forEach(b => b.classList.remove('active', 'btn-navy'));
        this.classList.add('active', 'btn-navy');
        const filter = this.dataset.filter;
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.style.display = (filter === 'all' || item.dataset.category === filter) ? '' : 'none';
        });
    });
});
</script>
JS;
require_once 'includes/footer.php';
?>
