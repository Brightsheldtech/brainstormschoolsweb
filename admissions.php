<?php
$pageTitle = 'Admissions';
require_once 'includes/header.php';

$success = $error = '';
$appNo   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Please refresh and try again.';
    } else {
        $required = ['full_name','gender','class_applying','parent_name','parent_phone'];
        $missing  = array_filter($required, fn($f) => empty(trim($_POST[$f] ?? '')));

        if ($missing) {
            $error = 'Please fill in all required fields.';
        } else {
            $appNo = 'BS/' . date('Y') . '/' . strtoupper(substr(uniqid(), -6));
            $db    = getDB();
            $stmt  = $db->prepare("INSERT INTO admissions
                (application_no, full_name, date_of_birth, gender, class_applying, previous_school,
                 parent_name, parent_phone, parent_email, parent_whatsapp, address, how_heard)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $appNo,
                trim($_POST['full_name']),
                $_POST['date_of_birth'] ?: null,
                $_POST['gender'],
                $_POST['class_applying'],
                trim($_POST['previous_school'] ?? ''),
                trim($_POST['parent_name']),
                trim($_POST['parent_phone']),
                trim($_POST['parent_email'] ?? ''),
                trim($_POST['parent_whatsapp'] ?? ''),
                trim($_POST['address'] ?? ''),
                trim($_POST['how_heard'] ?? ''),
            ]);
            $success = "Application submitted successfully! Your application number is <strong>$appNo</strong>. Save this number — you will need it to track your application status.";
        }
    }
}
?>

<section class="page-hero">
    <div class="container page-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Admissions</li>
            </ol>
        </nav>
        <h1>Apply for Admission</h1>
        <p style="color:rgba(255,255,255,0.7)">Join the Brainstorm family — <?= ACADEMIC_YEAR ?> session now open.</p>
    </div>
</section>

<!-- Info Bar -->
<section class="py-4 bg-off border-bottom">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-calendar-check text-gold fa-lg"></i>
                    <div class="text-start">
                        <div class="small fw-bold text-navy">Session Open</div>
                        <div class="small text-muted"><?= ACADEMIC_YEAR ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-school text-gold fa-lg"></i>
                    <div class="text-start">
                        <div class="small fw-bold text-navy">Available Classes</div>
                        <div class="small text-muted">Nursery to JSS 3</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-clock text-gold fa-lg"></i>
                    <div class="text-start">
                        <div class="small fw-bold text-navy">Processing Time</div>
                        <div class="small text-muted">3–5 business days</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <i class="fab fa-whatsapp text-gold fa-lg"></i>
                    <div class="text-start">
                        <div class="small fw-bold text-navy">Need Help?</div>
                        <a href="https://wa.me/<?= SITE_WHATSAPP ?>" class="small text-gold">Chat on WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row g-5">

            <!-- How it Works -->
            <div class="col-lg-4">
                <span class="section-badge">Process</span>
                <h3 class="section-title">How to Apply</h3>
                <div class="section-divider"></div>
                <div class="d-flex flex-column gap-3 mb-4">
                    <?php
                    $steps = [
                        ['Fill the Application Form', 'Complete the online form with your child\'s details and parent/guardian information.'],
                        ['Receive Application Number', 'You\'ll get a unique application number to track your application status.'],
                        ['Entrance Assessment', 'Qualified applicants are invited for a brief entrance assessment and school tour.'],
                        ['Admission Decision', 'You\'ll be notified of the outcome within 3–5 business days via phone and WhatsApp.'],
                        ['Enrolment & Resumption', 'Accepted students complete the registration process and receive their school materials.'],
                    ];
                    foreach ($steps as $i => $step): ?>
                    <div class="step-item">
                        <div class="step-badge"><?= $i + 1 ?></div>
                        <div>
                            <h6 class="mb-1"><?= $step[0] ?></h6>
                            <p class="small text-muted mb-0"><?= $step[1] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="alert-school">
                    <i class="fas fa-info-circle me-2"></i>
                    For enquiries, call us at <strong><?= SITE_PHONE ?></strong> or
                    <a href="https://wa.me/<?= SITE_WHATSAPP ?>" class="text-gold fw-semibold">WhatsApp us</a>.
                </div>
            </div>

            <!-- Application Form -->
            <div class="col-lg-8">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-file-alt me-2 text-gold"></i> Admission Application Form — <?= ACADEMIC_YEAR ?></h6>
                    </div>
                    <div class="dash-card-body">
                        <?php if ($success): ?>
                            <div class="alert-school mb-4" style="border-left-color:var(--navy);">
                                <i class="fas fa-check-circle me-2 text-gold"></i>
                                <?= $success ?>
                                <div class="mt-3">
                                    <a href="/admissions.php" class="btn btn-gold btn-sm">Submit Another Application</a>
                                </div>
                            </div>
                        <?php else: ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger small mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= e($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                            <h6 class="text-gold mb-3 border-bottom pb-2">
                                <i class="fas fa-child me-2"></i> Student Information
                            </h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label class="form-label">Student Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control" placeholder="First name, middle name, surname" value="<?= e($_POST['full_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="male"   <?= ($_POST['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" value="<?= e($_POST['date_of_birth'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Class Applying For <span class="text-danger">*</span></label>
                                    <select name="class_applying" class="form-select" required>
                                        <option value="">Select Class</option>
                                        <?php
                                        $classes = ['Nursery 1','Nursery 2','KG 1','KG 2','Primary 1','Primary 2','Primary 3','Primary 4','Primary 5','Primary 6','JSS 1','JSS 2','JSS 3'];
                                        foreach ($classes as $c):
                                            $sel = ($_POST['class_applying'] ?? '') === $c ? 'selected' : '';
                                        ?>
                                        <option value="<?= $c ?>" <?= $sel ?>><?= $c ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Previous School (if any)</label>
                                    <input type="text" name="previous_school" class="form-control" placeholder="Name of last school attended" value="<?= e($_POST['previous_school'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Home Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="Residential address" value="<?= e($_POST['address'] ?? '') ?>">
                                </div>
                            </div>

                            <h6 class="text-gold mb-3 border-bottom pb-2">
                                <i class="fas fa-user-tie me-2"></i> Parent / Guardian Information
                            </h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Parent / Guardian Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="parent_name" class="form-control" placeholder="Full name" value="<?= e($_POST['parent_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="parent_phone" class="form-control" placeholder="+234 800 000 0000" value="<?= e($_POST['parent_phone'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="parent_email" class="form-control" placeholder="parent@example.com" value="<?= e($_POST['parent_email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">WhatsApp Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text" style="background:var(--off-white);border-color:var(--gray-200);"><i class="fab fa-whatsapp text-success"></i></span>
                                        <input type="tel" name="parent_whatsapp" class="form-control" placeholder="+234 800 000 0000 (for result alerts)" value="<?= e($_POST['parent_whatsapp'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">How did you hear about us?</label>
                                    <select name="how_heard" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Word of Mouth">Word of Mouth</option>
                                        <option value="Social Media">Social Media</option>
                                        <option value="Google Search">Google Search</option>
                                        <option value="Flyer / Banner">Flyer / Banner</option>
                                        <option value="Old Student">Old Student / Alumni</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-navy btn-lg px-5">
                                <i class="fas fa-paper-plane me-2"></i> Submit Application
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
