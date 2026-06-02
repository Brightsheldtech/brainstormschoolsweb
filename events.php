<?php
$pageTitle = 'Events';
require_once 'includes/header.php';

$db = getDB();
$upcoming = $db->query("SELECT * FROM events WHERE is_published=1 AND event_date >= CURDATE() ORDER BY event_date ASC")->fetchAll();
$past     = $db->query("SELECT * FROM events WHERE is_published=1 AND event_date < CURDATE() ORDER BY event_date DESC LIMIT 6")->fetchAll();
?>

<section class="page-hero">
    <div class="container page-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Events</li>
            </ol>
        </nav>
        <h1>Events Calendar</h1>
        <p style="color:rgba(255,255,255,0.7)">Important dates, activities, and celebrations at Brainstorm School.</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">

        <!-- Upcoming Events -->
        <div class="mb-5">
            <span class="section-badge">What's Coming</span>
            <h2 class="section-title">Upcoming Events</h2>
            <div class="section-divider"></div>

            <?php if (!empty($upcoming)): ?>
            <div class="row g-4">
                <?php foreach ($upcoming as $ev):
                    $date     = new DateTime($ev['event_date']);
                    $daysLeft = (new DateTime('today'))->diff($date)->days;
                ?>
                <div class="col-lg-6">
                    <div class="news-card">
                        <div class="card-body d-flex gap-4">
                            <div class="text-center" style="min-width:70px;">
                                <div style="background:var(--navy);color:var(--white);border-radius:8px 8px 0 0;padding:6px 0 4px;font-size:0.75rem;font-weight:700;text-transform:uppercase;">
                                    <?= $date->format('M') ?>
                                </div>
                                <div style="background:var(--gold-pale);border:2px solid var(--gold);border-top:none;border-radius:0 0 8px 8px;padding:4px 0 8px;">
                                    <div style="font-size:2rem;font-weight:800;font-family:'Playfair Display',serif;color:var(--navy);line-height:1;">
                                        <?= $date->format('d') ?>
                                    </div>
                                    <div style="font-size:0.7rem;color:var(--gray-500);"><?= $date->format('Y') ?></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h5 class="mb-0"><?= e($ev['title']) ?></h5>
                                    <?php if ($daysLeft <= 7): ?>
                                    <span class="pill pill-danger">In <?= $daysLeft ?> day<?= $daysLeft !== 1 ? 's' : '' ?></span>
                                    <?php elseif ($daysLeft <= 30): ?>
                                    <span class="pill pill-warning">Coming soon</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($ev['event_time']): ?>
                                <p class="small text-muted mb-1"><i class="fas fa-clock me-1"></i> <?= date('g:ia', strtotime($ev['event_time'])) ?></p>
                                <?php endif; ?>
                                <?php if ($ev['venue']): ?>
                                <p class="small text-muted mb-1"><i class="fas fa-map-marker-alt me-1"></i> <?= e($ev['venue']) ?></p>
                                <?php endif; ?>
                                <?php if ($ev['description']): ?>
                                <p class="small text-muted mb-0"><?= e(substr($ev['description'], 0, 120)) ?><?= strlen($ev['description']) > 120 ? '…' : '' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div style="font-size:4rem;color:var(--gray-200);"><i class="fas fa-calendar-times"></i></div>
                <h5 class="text-muted mt-3">No upcoming events at the moment</h5>
                <p class="text-muted small">Check back soon — events will be posted here.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Past Events -->
        <?php if (!empty($past)): ?>
        <div>
            <h4 class="section-title" style="font-size:1.4rem;">Past Events</h4>
            <div class="section-divider"></div>
            <div class="row g-3">
                <?php foreach ($past as $ev): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="step-item" style="opacity:0.75;">
                        <div style="min-width:45px;text-align:center;">
                            <i class="fas fa-calendar-check text-gold" style="font-size:1.8rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0"><?= e($ev['title']) ?></h6>
                            <p class="small text-muted mb-0"><?= date('D, M j Y', strtotime($ev['event_date'])) ?></p>
                            <?php if ($ev['venue']): ?><p class="small text-muted mb-0"><?= e($ev['venue']) ?></p><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
