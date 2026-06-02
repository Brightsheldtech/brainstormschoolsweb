<?php
$pageTitle = 'About Us';
$pageDesc  = 'Learn about Brainstorm School — our history, mission, vision, and dedicated team.';
require_once 'includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
    <div class="container page-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">About Us</li>
            </ol>
        </nav>
        <h1>About Brainstorm School</h1>
        <p style="color:rgba(255,255,255,0.7);max-width:500px;">Discover the story behind a school committed to nurturing brilliant futures.</p>
    </div>
</section>

<!-- Our Story -->
<section class="section-pad">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <span class="section-badge">Our Story</span>
                <h2 class="section-title">15 Years of Shaping Future Leaders</h2>
                <div class="section-divider"></div>
                <p class="text-muted mb-3">
                    Brainstorm School was founded with a singular vision: to create an institution where academic brilliance meets real-world preparation. From our humble beginnings, we have grown into one of the most trusted and respected schools in the region.
                </p>
                <p class="text-muted mb-3">
                    Our journey began with a small group of passionate educators who believed that every Nigerian child deserved a world-class education right here at home. Today, that belief drives everything we do — from how we train our teachers to how we design our curriculum.
                </p>
                <p class="text-muted">
                    The name <strong>"Brainstorm"</strong> was chosen deliberately — it represents the collision of ideas, curiosity, and creativity that we want every student to experience within our walls. Our motto, <em>"Home of Future Career"</em>, is not just a tagline; it is a promise.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="rounded-3 overflow-hidden" style="height:180px;background:linear-gradient(135deg,var(--navy),var(--navy-light));display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.2);font-size:4rem;">
                            <i class="fas fa-school"></i>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded-3 overflow-hidden" style="height:180px;background:linear-gradient(135deg,var(--gold),var(--gold-light));display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);font-size:4rem;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="rounded-3 overflow-hidden" style="height:200px;background:linear-gradient(135deg,var(--navy-light),var(--navy));display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.15);font-size:5rem;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission, Vision, Values -->
<section class="section-pad bg-off">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">Our Foundation</span>
            <h2 class="section-title">Mission, Vision & Core Values</h2>
            <div class="section-divider center"></div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon"><i class="fas fa-bullseye"></i></div>
                    <h5>Our Mission</h5>
                    <p>To provide a holistic, technology-integrated education that equips every student with the knowledge, skills, and character to thrive in a competitive world and contribute meaningfully to society.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon"><i class="fas fa-eye"></i></div>
                    <h5>Our Vision</h5>
                    <p>To be the leading centre of academic excellence in Nigeria — producing graduates who are critical thinkers, innovative leaders, and responsible citizens of the world.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon"><i class="fas fa-gem"></i></div>
                    <h5>Core Values</h5>
                    <ul class="text-start text-muted small list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-gold me-2"></i> <strong>Excellence</strong> — in every area of school life</li>
                        <li class="mb-2"><i class="fas fa-check text-gold me-2"></i> <strong>Integrity</strong> — honesty, ethics, and fairness</li>
                        <li class="mb-2"><i class="fas fa-check text-gold me-2"></i> <strong>Innovation</strong> — embracing creativity and change</li>
                        <li class="mb-2"><i class="fas fa-check text-gold me-2"></i> <strong>Respect</strong> — for all people and cultures</li>
                        <li><i class="fas fa-check text-gold me-2"></i> <strong>Community</strong> — family, school, and nation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Curriculum -->
<section class="section-pad" id="curriculum">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">Academics</span>
            <h2 class="section-title">Our Curriculum & Programmes</h2>
            <div class="section-divider center"></div>
        </div>
        <div class="row g-4">
            <?php
            $programmes = [
                ['fas fa-baby', 'Nursery & KG', 'Ages 2–5', ['Play-based learning', 'Phonics & early reading', 'Basic numeracy', 'Social & emotional development', 'Creative arts']],
                ['fas fa-book', 'Primary School', 'Pry 1–6', ['Core subjects (English, Maths, Science)', 'Civic & Social Studies', 'Computer Studies', 'Agricultural Science', 'Creative & Technical Skills']],
                ['fas fa-pencil-alt', 'Junior Secondary', 'JSS 1–3', ['Core & elective subjects', 'Basic Technology & ICT', 'Business Studies', 'BECE preparation', 'Career guidance & counselling']],
            ];
            foreach ($programmes as $p): ?>
            <div class="col-lg-4 col-md-6">
                <div class="feature-card h-100 text-start">
                    <div class="feature-icon mx-0 mb-3"><i class="<?= $p[0] ?>"></i></div>
                    <h5><?= $p[1] ?></h5>
                    <p class="badge-navy px-2 py-1 rounded small mb-3"><?= $p[2] ?></p>
                    <ul class="text-muted small list-unstyled mb-0">
                        <?php foreach ($p[3] as $item): ?>
                        <li class="mb-1"><i class="fas fa-check-circle text-gold me-2"></i><?= $item ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Our Team -->
<section class="section-pad bg-off">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">Our People</span>
            <h2 class="section-title">Meet the Leadership Team</h2>
            <div class="section-divider center"></div>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $team = [
                ['Principal',           'The Principal oversees all academic and administrative operations, ensuring the school meets its mission and vision at every level.'],
                ['Vice Principal (Academics)', 'Responsible for curriculum standards, teacher development, and maintaining academic excellence across all classes.'],
                ['Vice Principal (Admin)', 'Manages non-academic staff, facilities, student welfare, and ensures a safe, conducive school environment.'],
                ['Head of Guidance & Counseling', 'Supports students\' emotional wellbeing, career planning, and academic guidance throughout their school journey.'],
            ];
            foreach ($team as $i => $member): ?>
            <div class="col-lg-3 col-md-6">
                <div class="testimonial-card text-center" style="padding-top:2rem;">
                    <div class="mx-auto mb-3" style="width:90px;height:90px;border-radius:50%;background:var(--navy);display:flex;align-items:center;justify-content:center;border:4px solid var(--gold);color:var(--gold);font-size:2.2rem;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h6 class="mb-1"><?= $member[0] ?></h6>
                    <p class="small text-muted"><?= $member[1] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section-pad" style="background:linear-gradient(135deg,var(--navy-dark),var(--navy));">
    <div class="container text-center">
        <h2 class="text-white mb-3">Come See the School for Yourself</h2>
        <p style="color:rgba(255,255,255,0.7);" class="mb-4">Schedule a visit or apply today. We'd love to welcome you to the Brainstorm family.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/admissions.php" class="btn btn-gold btn-lg px-4">Apply for Admission</a>
            <a href="/contact.php" class="btn btn-outline-gold btn-lg px-4">Schedule a Visit</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
