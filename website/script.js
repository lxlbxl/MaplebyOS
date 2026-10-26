document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // 2. Scroll Animation Observer
    const observerOptions = {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target); // Once revealed, stay revealed
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal-on-scroll').forEach(el => {
        observer.observe(el);
    });

    // 3. Navbar Scroll Effect
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('bg-[#0A0F14]', 'border-b', 'border-white/10', 'py-4', 'shadow-lg');
                navbar.classList.remove('py-6');
            } else {
                navbar.classList.remove('bg-[#0A0F14]', 'border-b', 'border-white/10', 'py-4', 'shadow-lg');
                navbar.classList.add('py-6');
            }
        });
    }

    // 4. GSAP Parallax (Enhancement)
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);

        // Parallax for Hero Images
        gsap.utils.toArray('.parallax-container').forEach(container => {
            const img = container.querySelector('img');
            if (img) {
                gsap.to(img, {
                    yPercent: 20,
                    ease: "none",
                    scrollTrigger: {
                        trigger: container,
                        start: "top bottom",
                        end: "bottom top",
                        scrub: true
                    }
                });
            }
        });
    }
});

// 5. Global UI Functions (Exposed to Window)
window.toggleMobileMenu = function () {
    const menu = document.getElementById('mobile-menu');
    const body = document.body;
    if (menu) {
        menu.classList.toggle('translate-x-full');
        // Prevent scrolling when menu is open
        if (!menu.classList.contains('translate-x-full')) {
            body.style.overflow = 'hidden';
        } else {
            body.style.overflow = '';
        }
    }
};

window.toggleModal = function () {
    const modal = document.getElementById('quote-modal');
    if (modal) {
        modal.classList.toggle('hidden');
    }
};
