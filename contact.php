<?php
$pageTitle = 'Contact Us';
require_once 'includes/header.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $name    = trim($_POST['full_name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$name || !$email || !$message) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $db   = getDB();
            $stmt = $db->prepare("INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?,?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $subject, $message]);
            $success = 'Thank you! Your message has been received. We will get back to you within 24 hours.';
        }
    }
}
?>

<section class="page-hero">
    <div class="container page-hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Contact Us</li>
            </ol>
        </nav>
        <h1>Contact Us</h1>
        <p style="color:rgba(255,255,255,0.7)">We'd love to hear from you. Reach out any time.</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <span class="section-badge">Get in Touch</span>
                <h3 class="section-title">We're Here to Help</h3>
                <div class="section-divider"></div>
                <p class="text-muted mb-4">Have a question about admissions, results, or anything else? Don't hesitate to reach out to us.</p>

                <div class="d-flex flex-column gap-4">
                    <div class="why-item">
                        <div class="why-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h6 class="mb-1">Our Address</h6>
                            <p><?= SITE_ADDRESS ?></p>
                        </div>
                    </div>
                    <div class="why-item">
                        <div class="why-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <h6 class="mb-1">Call Us</h6>
                            <p><a href="tel:<?= SITE_PHONE ?>"><?= SITE_PHONE ?></a></p>
                        </div>
                    </div>
                    <div class="why-item">
                        <div class="why-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h6 class="mb-1">Email Us</h6>
                            <p><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
                        </div>
                    </div>
                    <div class="why-item">
                        <div class="why-icon"><i class="fab fa-whatsapp"></i></div>
                        <div>
                            <h6 class="mb-1">WhatsApp</h6>
                            <p><a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank">Chat with us on WhatsApp</a></p>
                        </div>
                    </div>
                    <div class="why-item">
                        <div class="why-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <h6 class="mb-1">School Hours</h6>
                            <p class="mb-0">Monday – Friday: 7:30am – 3:00pm</p>
                            <p class="mb-0">Saturday: 8:00am – 12:00pm</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6><i class="fas fa-paper-plane me-2 text-gold"></i> Send Us a Message</h6>
                    </div>
                    <div class="dash-card-body">
                        <?php if ($success): ?>
                            <div class="alert-school mb-4"><i class="fas fa-check-circle me-2"></i><?= e($success) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger small mb-4"><i class="fas fa-exclamation-circle me-2"></i><?= e($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control" placeholder="Your full name" value="<?= e($_POST['full_name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="your@email.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+234 000 000 0000" value="<?= e($_POST['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subject</label>
                                    <select name="subject" class="form-select">
                                        <option value="">Select a subject</option>
                                        <option value="Admissions Enquiry">Admissions Enquiry</option>
                                        <option value="Result Request">Result Request</option>
                                        <option value="School Visit">Schedule a School Visit</option>
                                        <option value="Fees Enquiry">Fees Enquiry</option>
                                        <option value="Complaint">Complaint / Feedback</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea name="message" class="form-control" rows="6" placeholder="Write your message here..." required><?= e($_POST['message'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-navy px-5">
                                        <i class="fas fa-paper-plane me-2"></i> Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section — replace the div below with your Google Maps iframe -->
<section class="pb-5">
    <div class="container">
        <div class="rounded-3 overflow-hidden" style="height:360px;background:linear-gradient(135deg,var(--navy),var(--navy-light));display:flex;align-items:center;justify-content:center;flex-direction:column;gap:12px;color:rgba(255,255,255,0.4);">
            <i class="fas fa-map-marked-alt" style="font-size:3rem;"></i>
            <p class="mb-0">Map coming soon</p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
