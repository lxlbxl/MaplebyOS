// Mapleby shared header + mobile drawer behaviour.
(function () {
    const root = document.documentElement;
    const header = document.getElementById('mb-header');
    const burger = document.getElementById('mb-burger');
    const drawer = document.getElementById('mb-drawer');
    if (!header || !burger || !drawer) return;

    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 24);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    let lastFocus = null;
    const setOpen = (open) => {
        root.classList.toggle('mb-menu-open', open);
        burger.setAttribute('aria-expanded', String(open));
        burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        if (open) {
            lastFocus = document.activeElement;
            drawer.hidden = false;
            const first = drawer.querySelector('a');
            if (first) setTimeout(() => first.focus({ preventScroll: true }), 50);
        } else {
            drawer.hidden = true;
            if (lastFocus) lastFocus.focus({ preventScroll: true });
        }
    };
    const isOpen = () => root.classList.contains('mb-menu-open');

    burger.addEventListener('click', () => setOpen(!isOpen()));
    drawer.addEventListener('click', (e) => { if (e.target.closest('a')) setOpen(false); });
    document.addEventListener('keydown', (e) => {
        if (!isOpen()) return;
        if (e.key === 'Escape') setOpen(false);
        if (e.key === 'Tab') { // keep focus inside the open drawer
            const items = [burger, ...drawer.querySelectorAll('a')];
            const i = items.indexOf(document.activeElement);
            if (e.shiftKey && i <= 0) { e.preventDefault(); items[items.length - 1].focus(); }
            else if (!e.shiftKey && i === items.length - 1) { e.preventDefault(); items[0].focus(); }
        }
    });
    window.matchMedia('(min-width: 1180px)').addEventListener('change', (m) => { if (m.matches && isOpen()) setOpen(false); });

    // Pages still call the legacy helper from inline handlers.
    window.toggleMobileMenu = () => setOpen(!isOpen());

    // Wide tables scroll inside their own box instead of being clipped on phones.
    document.querySelectorAll('main table, section table').forEach((t) => {
        if (t.parentElement.classList.contains('mb-table-scroll')) return;
        const wrap = document.createElement('div');
        wrap.className = 'mb-table-scroll';
        t.parentNode.insertBefore(wrap, t);
        wrap.appendChild(t);
    });
})();
