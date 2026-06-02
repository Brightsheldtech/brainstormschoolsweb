/* Brainstorm School — Main JS */

document.addEventListener('DOMContentLoaded', () => {

    // Animated stat counters
    const counters = document.querySelectorAll('.counter');
    if (counters.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        counters.forEach(c => observer.observe(c));
    }

    function animateCounter(el) {
        const target   = parseInt(el.dataset.target) || 0;
        const suffix   = el.textContent.replace(/\d+/, '').trim(); // e.g. "+"
        const duration = 1600;
        const step     = Math.ceil(target / (duration / 16));
        let current    = 0;
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString() + suffix;
            if (current >= target) clearInterval(timer);
        }, 16);
    }

    // Navbar shrink on scroll
    const navbar = document.querySelector('.main-navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    // Smooth reveal on scroll (fade-in-up)
    const revealEls = document.querySelectorAll('.feature-card, .news-card, .testimonial-card, .kpi-card, .why-item, .step-item');
    if (revealEls.length && 'IntersectionObserver' in window) {
        const revealObs = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.style.opacity = '1';
                    e.target.style.transform = 'translateY(0)';
                    revealObs.unobserve(e.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        revealEls.forEach((el, i) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = `opacity 0.5s ease ${i % 4 * 0.08}s, transform 0.5s ease ${i % 4 * 0.08}s`;
            revealObs.observe(el);
        });
    }

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert:not(.alert-school)').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Form: confirm before delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    // Dashboard sidebar toggle for mobile
    const sidebarOverlay = document.createElement('div');
    sidebarOverlay.id = 'sidebarOverlay';
    sidebarOverlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99;display:none;';
    document.body.appendChild(sidebarOverlay);

    sidebarOverlay.addEventListener('click', () => closeSidebar());

    window.toggleSidebar = function() {
        const sb = document.getElementById('sidebar');
        if (sb) {
            sb.classList.toggle('open');
            sidebarOverlay.style.display = sb.classList.contains('open') ? 'block' : 'none';
        }
    };

    window.closeSidebar = function() {
        const sb = document.getElementById('sidebar');
        if (sb) {
            sb.classList.remove('open');
            sidebarOverlay.style.display = 'none';
        }
    };

    // Print helper
    window.printResult = function() { window.print(); };
});
