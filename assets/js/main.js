// Semicolon - Premium Animations & Transitions

document.addEventListener('DOMContentLoaded', () => {

    // Initialize Barba
    barba.init({
        debug: true,
        prevent: ({ el }) => el.href.includes('view.php') || el.href.includes('admin'),
        transitions: [{
            name: 'premium-transition',
            sync: false, // Sequential: Leave then Enter

            leave(data) {
                return gsap.to(data.current.container, {
                    opacity: 0,
                    y: 20, // 2-4% downward slide
                    duration: 0.3,
                    ease: 'power2.inOut'
                });
            },

            enter(data) {
                window.scrollTo(0, 0);

                // Delay to let user absorb motion (200-300ms)
                return gsap.from(data.next.container, {
                    opacity: 0,
                    y: 20, // Lift by 2-3%
                    duration: 0.6,
                    delay: 0.2,
                    ease: 'power2.out',
                    onComplete: () => {
                        initScripts();
                    }
                });
            },

            afterEnter(data) {
                // Re-trigger animations for the new page
                initPageAnimations(data.next.namespace);
            }
        }]
    });

    // Initial Load
    initScripts();
    initPageAnimations(document.querySelector('main').dataset.barbaNamespace);
});

function initScripts() {
    // Re-bind Mobile Menu
    const btn = document.getElementById('mobile-menu-button');
    const menu = document.getElementById('mobile-menu');
    if (btn && menu) {
        // Clone to remove old listeners if any
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        newBtn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    }

    // Search Logic (if present)
    const searchInput = document.getElementById('global-search');
    if (searchInput) {
        // Re-attach search listeners if needed
    }
}

function initPageAnimations(namespace) {
    if (namespace === 'home') {
        initHomeAnimations();
    }

    // Navbar Animation (Global)
    gsap.from('header', {
        y: -20,
        opacity: 0,
        duration: 0.8,
        delay: 0.1,
        ease: 'power2.out'
    });
}

function initHomeAnimations() {
    const tl = gsap.timeline();

    // Hero Section
    tl.to('.hero-badge', { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' })
        .to('.hero-title', { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, '-=0.2')
        .to('.hero-desc', { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, '-=0.4')
        .to('.hero-cta', { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' }, '-=0.2');

    // Feature Cards
    gsap.to('.feature-card', {
        scrollTrigger: {
            trigger: '.feature-card',
            start: 'top 80%',
        },
        opacity: 1,
        scale: 1, // Expand from 96% (set in CSS or initial state)
        duration: 0.6,
        stagger: 0.1,
        ease: 'power2.out'
    });

    // Initial state for cards (if not handled by CSS)
    gsap.set('.feature-card', { scale: 0.96, opacity: 0 });
    gsap.to('.feature-card', {
        opacity: 1,
        scale: 1,
        duration: 0.6,
        stagger: 0.1,
        delay: 1.2, // Wait for hero
        ease: 'power2.out'
    });

    // Footer
    gsap.to('.footer-section', {
        opacity: 1,
        y: 0,
        duration: 0.8,
        delay: 1.8,
        ease: 'power2.out'
    });
    gsap.set('.footer-section', { y: 20, opacity: 0 });
}