<?php
$pageTitle = 'Welcome';
$pageDesc  = 'Brainstorm School — Home of Future Career. A warm, caring school where every child thrives.';
require_once 'includes/header.php';

$db = getDB();
$latestNews     = $db->query("SELECT * FROM news WHERE is_published=1 ORDER BY published_at DESC LIMIT 3")->fetchAll();
$upcomingEvents = $db->query("SELECT * FROM events WHERE is_published=1 AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 4")->fetchAll();
$totalStudents  = $db->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetchColumn();
$totalTeachers  = $db->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='active'")->fetchColumn();
?>

<!-- ── Announcement Bar ─────────────────────────── -->
<div class="announce-bar">
    <i class="fas fa-bell me-2"></i>
    <?= ACADEMIC_YEAR ?> Admission is Now Open &nbsp;•&nbsp;
    Limited seats available &nbsp;•&nbsp;
    <a href="/admissions.php" style="color:var(--navy-dark);text-decoration:underline;font-weight:800;">Apply Today</a>
    &nbsp;•&nbsp; Call us: <?= SITE_PHONE ?>
</div>

<!-- ── Hero ──────────────────────────────────────── -->
<section class="hero">
    <div class="hero-bg-pattern"></div>
    <div class="hero-dots"></div>

    <div class="container py-5">
        <div class="row align-items-center gy-5">

            <!-- Left -->
            <div class="col-lg-6 hero-content">
                <div class="hero-label">
                    <span class="live-dot"></span>
                    <span>Enrolment Open — <?= ACADEMIC_YEAR ?></span>
                </div>

                <h1>
                    Where Your Child<br>
                    Finds Their <span class="accent">Spark</span>
                </h1>

                <p>
                    At Brainstorm School, we don't just teach subjects — we raise thinkers, leaders, and confident young people who are ready for the world. Every child matters here.
                </p>

                <div class="hero-checks">
                    <span><i class="fas fa-check-circle"></i> Nursery to JSS 3</span>
                    <span><i class="fas fa-check-circle"></i> Government Approved</span>
                    <span><i class="fas fa-check-circle"></i> Online Result Portal</span>
                    <span><i class="fas fa-check-circle"></i> WhatsApp Parent Updates</span>
                </div>

                <div class="hero-btns">
                    <a href="/admissions.php" class="btn btn-gold btn-lg">
                        <i class="fas fa-file-alt me-2"></i> Apply for Admission
                    </a>
                    <a href="/results.php" class="btn btn-outline-white btn-lg">
                        <i class="fas fa-graduation-cap me-2"></i> Check Results
                    </a>
                </div>
            </div>

            <!-- Right panel -->
            <div class="col-lg-5 offset-lg-1 hero-content">
                <div class="hero-panel">
                    <div class="hero-panel-title">
                        <i class="fas fa-chart-line"></i>
                        Our School at a Glance
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-bubble">
                                <span class="num counter" data-target="<?= $totalStudents > 0 ? $totalStudents : 500 ?>">
                                    <?= $totalStudents > 0 ? $totalStudents : '500' ?>+
                                </span>
                                <span class="lbl">Active Students</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-bubble">
                                <span class="num counter" data-target="<?= $totalTeachers > 0 ? $totalTeachers : 30 ?>">
                                    <?= $totalTeachers > 0 ? $totalTeachers : '30' ?>+
                                </span>
                                <span class="lbl">Qualified Teachers</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-bubble">
                                <span class="num">100%</span>
                                <span class="lbl">Transition Rate</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-bubble">
                                <span class="num">15+</span>
                                <span class="lbl">Years of Trust</span>
                            </div>
                        </div>
                    </div>

                    <hr style="border-color:rgba(255,255,255,0.08);margin:22px 0 18px;">

                    <p style="font-size:0.82rem;color:rgba(255,255,255,0.5);margin:0 0 14px;font-family:'Lato',sans-serif;">
                        Quick access
                    </p>
                    <div class="d-flex gap-2">
                        <a href="/admissions.php" class="btn btn-gold btn-sm flex-fill" style="border-radius:10px;">
                            Apply Now
                        </a>
                        <a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank" class="btn btn-outline-white btn-sm flex-fill" style="border-radius:10px;">
                            <i class="fab fa-whatsapp me-1"></i> Chat Us
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Wave -->
    <div class="hero-wave">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C240,80 480,0 720,40 C960,80 1200,0 1440,40 L1440,80 L0,80 Z" fill="#fffdf9"/>
        </svg>
    </div>
</section>

<!-- ── Marquee Band ───────────────────────────────── -->
<div class="marquee-band">
    <div class="marquee-track">
        <?php
        $items = [
            ['fas fa-graduation-cap', 'Academic Excellence'],
            ['fas fa-laptop-code',    'Online Student Portal'],
            ['fab fa-whatsapp',       'WhatsApp Result Alerts'],
            ['fas fa-shield-alt',     'Safe Learning Environment'],
            ['fas fa-trophy',         'Top Academic Results'],
            ['fas fa-users',          'Small Class Sizes'],
            ['fas fa-chalkboard-teacher', 'Experienced Teachers'],
            ['fas fa-calendar-check', 'Digital Attendance Tracking'],
            ['fas fa-file-alt',       'Online Admissions'],
            ['fas fa-images',         'Gallery & Events'],
        ];
        // Duplicate for infinite loop
        $all = array_merge($items, $items);
        foreach ($all as $item): ?>
        <div class="marquee-item">
            <i class="<?= $item[0] ?>"></i>
            <span><?= $item[1] ?></span>
        </div>
        <div class="marquee-dot"></div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── What We Offer ──────────────────────────────── -->
<section class="section-pad" style="background: var(--warm-white);">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow">What We Offer</span>
            <h2 class="section-title">Everything Your Child Needs to Thrive</h2>
            <div class="divider-wave center"></div>
            <p class="lead-text mx-auto text-center" style="max-width:520px;">
                From nursery to JSS 3, we provide modern, technology-supported education backed by teachers who genuinely care.
            </p>
        </div>

        <div class="row g-4">
            <?php
            $features = [
                ['fas fa-laptop-code',        'fi-blue',   'Online Student Portal',     'Students and parents check results, view schedules, and stay updated through a secure online portal — anytime, anywhere.'],
                ['fas fa-graduation-cap',     'fi-gold',   'Term Result Management',    'Detailed report cards with subject scores, CA scores, exam scores, grades, and teacher comments — all printable.'],
                ['fas fa-file-signature',     'fi-green',  'Online Admission',          'Apply for your child from home. Fill the form, get an application number, and we handle the rest.'],
                ['fab fa-whatsapp',           'fi-teal',   'WhatsApp Result Alerts',    'The moment results are published, parents get an instant WhatsApp notification. No need to chase the school.'],
                ['fas fa-clipboard-check',    'fi-purple', 'Attendance Tracking',       'Daily digital attendance with parent notifications. You\'ll always know when your child is present or absent.'],
                ['fas fa-chalkboard-teacher', 'fi-orange', 'Dedicated Teacher Portal',  'Teachers enter scores, mark attendance, and manage their classes through a simple, private staff portal.'],
                ['fas fa-images',             'fi-red',    'Gallery & Events',          'Every Sports Day, Prize Giving, and graduation is captured and shared — so you never miss a proud moment.'],
                ['fas fa-shield-alt',         'fi-navy',   'Safe & Secure Campus',      'A structured, disciplined environment where every child feels protected, respected, and free to learn.'],
            ];
            foreach ($features as $f): ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon-wrap <?= $f[1] ?>">
                        <i class="<?= $f[0] ?>"></i>
                    </div>
                    <h5><?= $f[2] ?></h5>
                    <p><?= $f[3] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Stats Band ────────────────────────────────── -->
<section class="stats-band">
    <div class="container">
        <div class="row g-0">
            <?php
            $stats = [
                ['fas fa-user-graduate', '500+',  '500', 'Students Enrolled'],
                ['fas fa-users',         '30+',   '30',  'Expert Teachers'],
                ['fas fa-trophy',        '100%',  '100', 'Student Transition Rate'],
                ['fas fa-calendar-alt',  '15+',   '15',  'Years of Excellence'],
                ['fas fa-medal',         '50+',   '50',  'Academic Awards'],
                ['fas fa-book-open',     '12',    '12',  'Classes Offered'],
            ];
            foreach ($stats as $s): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="stat-item">
                    <div class="stat-icon-bg"><i class="<?= $s[0] ?>"></i></div>
                    <span class="big-num counter" data-target="<?= $s[2] ?>"><?= $s[1] ?></span>
                    <span class="stat-label"><?= $s[3] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Why Choose Brainstorm ─────────────────────── -->
<section class="section-pad" style="background: var(--cream);">
    <div class="container">
        <div class="row align-items-center gy-5">

            <!-- Left copy -->
            <div class="col-lg-5">
                <span class="eyebrow">Why Parents Choose Us</span>
                <h2 class="section-title">
                    We Treat Every Child Like <em>Our Own</em>
                </h2>
                <div class="divider-wave"></div>
                <p class="lead-text mb-4">
                    Parents don't just choose Brainstorm for the results — they stay because of how their children feel when they come home from school. Happy. Curious. Confident.
                </p>
                <blockquote style="border-left:4px solid var(--gold);padding:16px 20px;background:var(--gold-pale);border-radius:0 var(--radius) var(--radius) 0;margin-bottom:24px;">
                    <p style="font-family:'Playfair Display',serif;font-style:italic;font-size:1rem;color:var(--navy);margin:0;">
                        "The school that doesn't just teach your child — it builds them."
                    </p>
                </blockquote>
                <a href="/about.php" class="btn btn-navy">
                    Learn Our Story <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>

            <!-- Right cards -->
            <div class="col-lg-7">
                <div class="row g-3">
                    <?php
                    $whys = [
                        ['fas fa-star-half-alt',    'Consistently High Results',     'Our pupils consistently score among the best in external assessments and transition exams.'],
                        ['fas fa-brain',            'Early Talent Discovery',        'We help every child identify their strengths from an early age so they\'re ready for secondary school and beyond.'],
                        ['fas fa-users',            'Maximum 30 Students Per Class', 'Small classes mean every teacher knows every child\'s name, pace, and potential.'],
                        ['fas fa-laptop',           'Modern ICT Integration',        'Computer labs, digital classrooms, and tools that give students a 21st-century edge.'],
                        ['fas fa-heart',            'Character Over Everything',     'Respect, integrity, and discipline are woven into every day — not just on assembly day.'],
                        ['fas fa-mobile-alt',       'Parents Are Always in the Loop','WhatsApp updates, portal access, and regular parent-teacher meetings keep you informed.'],
                    ];
                    foreach ($whys as $w): ?>
                    <div class="col-md-6">
                        <div class="why-card">
                            <div class="why-icon"><i class="<?= $w[0] ?>"></i></div>
                            <div>
                                <h6><?= $w[1] ?></h6>
                                <p><?= $w[2] ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Testimonials ──────────────────────────────── -->
<section class="section-pad testi-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow">What Our Families Say</span>
            <h2 class="section-title">Real Words from Real Parents</h2>
            <div class="divider-wave center"></div>
        </div>

        <div class="row g-4">
            <?php
            $testimonials = [
                ['My son changed completely when he joined Brainstorm. He went from shy and struggling to topping his class in two terms. The teachers here are exceptional.',
                 'Mrs. Adaobi Okonkwo', 'Mother — JSS 3 Student', 'AO'],
                ['I live abroad and the WhatsApp result notifications are a lifesaver. I never have to wonder how my daughter is doing — the school keeps me in the loop always.',
                 'Mr. Kayode Adesanya', 'Parent — Primary 5 Student', 'KA'],
                ['My son got into one of the best junior secondary schools in the state. His teachers at Brainstorm built his confidence and academic foundation. We are so proud.',
                 'Mrs. Ngozi Eze', 'Parent — JSS 1 Student (Graduated)', 'NE'],
            ];
            foreach ($testimonials as $t): ?>
            <div class="col-lg-4 col-md-6">
                <div class="testi-card">
                    <div class="testi-quote-icon">"</div>
                    <div class="testi-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testi-text">"<?= $t[0] ?>"</p>
                    <div class="testi-author">
                        <div class="testi-avatar"><?= $t[3] ?></div>
                        <div>
                            <div class="testi-name"><?= $t[1] ?></div>
                            <div class="testi-role"><?= $t[2] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Latest News ───────────────────────────────── -->
<?php if (!empty($latestNews)): ?>
<section class="section-pad" style="background:var(--warm-white);">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-5 flex-wrap gap-3">
            <div>
                <span class="eyebrow">From the School</span>
                <h2 class="section-title mb-0">Latest News & Updates</h2>
                <div class="divider-wave" style="margin-bottom:0;"></div>
            </div>
            <a href="/news.php" class="btn btn-outline-gold">
                All News <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="row g-4">
            <?php foreach ($latestNews as $n): ?>
            <div class="col-lg-4 col-md-6">
                <div class="news-card">
                    <div class="news-img-wrap">
                        <?php if ($n['featured_image']): ?>
                        <img src="<?= e($n['featured_image']) ?>" alt="<?= e($n['title']) ?>" class="news-img">
                        <?php else: ?>
                        <div class="news-img-placeholder"><i class="fas fa-newspaper"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <span class="news-cat"><?= e($n['category']) ?></span>
                        <div class="news-date">
                            <i class="fas fa-calendar-alt me-1"></i>
                            <?= $n['published_at'] ? date('M j, Y', strtotime($n['published_at'])) : '' ?>
                        </div>
                        <h5><a href="/news-detail.php?slug=<?= e($n['slug']) ?>"><?= e($n['title']) ?></a></h5>
                        <p><?= e(substr($n['excerpt'] ?? strip_tags($n['content']), 0, 110)) ?>…</p>
                        <a href="/news-detail.php?slug=<?= e($n['slug']) ?>" class="read-more">
                            Read more <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── Upcoming Events ──────────────────────────── -->
<?php if (!empty($upcomingEvents)): ?>
<section class="section-pad" style="background:var(--cream);">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-5 flex-wrap gap-3">
            <div>
                <span class="eyebrow">School Calendar</span>
                <h2 class="section-title mb-0">What's Coming Up</h2>
                <div class="divider-wave" style="margin-bottom:0;"></div>
            </div>
            <a href="/events.php" class="btn btn-outline-gold">
                Full Calendar <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="row g-3">
            <?php foreach ($upcomingEvents as $ev):
                $date     = new DateTime($ev['event_date']);
                $daysLeft = (new DateTime('today'))->diff($date)->days;
            ?>
            <div class="col-lg-6">
                <div class="event-card">
                    <div class="event-date-box">
                        <div class="event-month"><?= $date->format('M') ?></div>
                        <div class="event-day"><?= $date->format('d') ?></div>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h6 class="mb-0"><?= e($ev['title']) ?></h6>
                            <?php if ($daysLeft <= 7): ?>
                            <span class="pill pill-danger" style="font-size:0.68rem;">In <?= $daysLeft ?>d</span>
                            <?php elseif ($daysLeft <= 14): ?>
                            <span class="pill pill-warning" style="font-size:0.68rem;">Coming soon</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($ev['event_time']): ?>
                        <p class="ev-meta"><i class="fas fa-clock me-1"></i><?= date('g:ia', strtotime($ev['event_time'])) ?></p>
                        <?php endif; ?>
                        <?php if ($ev['venue']): ?>
                        <p class="ev-meta"><i class="fas fa-map-marker-alt me-1"></i><?= e($ev['venue']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── CTA Section ──────────────────────────────── -->
<section class="section-pad" style="background:linear-gradient(135deg, var(--navy-dark) 0%, #0d1b4b 100%); position:relative; overflow:hidden;">
    <!-- decorative circle -->
    <div style="position:absolute;right:-120px;top:-120px;width:500px;height:500px;border-radius:50%;background:rgba(201,162,39,0.05);pointer-events:none;"></div>
    <div style="position:absolute;left:-80px;bottom:-80px;width:300px;height:300px;border-radius:50%;background:rgba(42,68,148,0.3);pointer-events:none;"></div>

    <div class="container text-center" style="position:relative;z-index:1;">
        <span class="eyebrow" style="color:var(--gold);">Enrolment is Open</span>
        <h2 class="section-title text-white mt-2" style="font-size:clamp(1.8rem,4vw,2.8rem);">
            Give Your Child a School <br>They'll Thank You For
        </h2>
        <p style="color:rgba(255,255,255,0.65);max-width:480px;margin:16px auto 36px;font-size:1rem;line-height:1.8;">
            Seats for the <?= ACADEMIC_YEAR ?> session are filling up fast. Don't wait until it's too late — apply today or chat with us first.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/admissions.php" class="btn btn-gold btn-lg px-5">
                <i class="fas fa-file-alt me-2"></i> Apply for Admission
            </a>
            <a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank" class="btn btn-outline-white btn-lg px-5">
                <i class="fab fa-whatsapp me-2"></i> Chat on WhatsApp
            </a>
        </div>
        <p style="color:rgba(255,255,255,0.35);margin-top:28px;font-size:0.82rem;">
            No form fees &nbsp;•&nbsp; Application takes 5 minutes &nbsp;•&nbsp; We'll call you back within 24 hours
        </p>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
