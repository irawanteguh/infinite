(() => {
    const page = document.querySelector('.landing-page');
    const header = page?.querySelector('.landing-header');
    if (!header) return;

    const links = [...header.querySelectorAll('.landing-section-link')];
    const sections = links.map(link => document.getElementById(link.hash.slice(1))).filter(Boolean);
    const palettes = {
        beranda: [11, 23, 48],
        platform: [242, 240, 252],
        mutu: [237, 249, 244],
        ekosistem: [37, 71, 119]
    };
    let scheduled = false;

    function updateNavigation() {
        scheduled = false;
        const headerHeight = header.getBoundingClientRect().height;
        page.style.setProperty('--landing-header-height', `${headerHeight}px`);
        const threshold = headerHeight + Math.min(window.innerHeight * 0.18, 140);
        const palettePoint = headerHeight + 180;
        let active = sections[0];
        sections.forEach(section => {
            if (section.getBoundingClientRect().top <= threshold) active = section;
        });
        if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2) {
            active = sections[sections.length - 1];
        }
        if (!active) return;
        header.dataset.section = active.id;
        const activeIndex = Math.max(0, sections.indexOf(active));
        const nextSection = sections[activeIndex + 1];
        const progress = nextSection
            ? Math.max(0, Math.min(1, (palettePoint - nextSection.getBoundingClientRect().top) / 180))
            : 0;
        const currentColor = palettes[active.id] || palettes.beranda;
        const nextColor = nextSection ? (palettes[nextSection.id] || currentColor) : currentColor;
        const blended = currentColor.map((channel, index) => Math.round(channel + (nextColor[index] - channel) * progress));
        header.style.setProperty('--landing-header-rgb', blended.join(' '));
        header.style.setProperty('--landing-logo-filter', active.id === 'platform' || active.id === 'mutu'
            ? 'brightness(0) saturate(100%) invert(25%) sepia(20%) saturate(1800%) hue-rotate(176deg) brightness(88%) contrast(94%)'
            : 'brightness(0) invert(1)');
        links.forEach(link => {
            const selected = link.hash === `#${active.id}`;
            link.classList.toggle('is-active', selected);
            if (selected) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    }

    function scheduleUpdate() {
        if (!scheduled) {
            scheduled = true;
            window.requestAnimationFrame(updateNavigation);
        }
    }

    header.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
            const section = document.getElementById(link.hash.slice(1));
            if (!section) return;
            event.preventDefault();
            history.pushState(null, '', link.hash);
            section.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'start'
            });
        });
    });

    page.querySelectorAll('a[href*="/auth/login"]').forEach(link => {
        link.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0
                || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            event.preventDefault();
            page.classList.add('is-leaving');
            window.setTimeout(() => window.location.assign(link.href), 220);
        });
    });

    window.addEventListener('pageshow', () => page.classList.remove('is-leaving'));

    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate);
    window.addEventListener('load', scheduleUpdate);
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(scheduleUpdate).observe(header);
    updateNavigation();
})();
