(() => {
    'use strict';
    const page = document.getElementById('infinite-landing');
    if (!page) return;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const header = page.querySelector('.lp-header');
    const menuToggle = page.querySelector('.lp-menu-toggle');
    const navigation = page.querySelector('.lp-navigation');
    const mobile = window.matchMedia('(max-width: 767px)');

    function closeMenu() {
        navigation.classList.remove('is-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-label', 'Buka navigasi');
    }
    menuToggle.addEventListener('click', () => {
        const open = menuToggle.getAttribute('aria-expanded') !== 'true';
        navigation.classList.toggle('is-open', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.setAttribute('aria-label', open ? 'Tutup navigasi' : 'Buka navigasi');
    });
    page.addEventListener('keydown', event => {
        if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
            closeMenu();
            menuToggle.focus();
        }
    });
    page.addEventListener('click', event => {
        if (!event.target.closest('.lp-nav')) closeMenu();
        const link = event.target.closest('a[href^="#"]');
        if (!link) return;
        const target = document.getElementById(link.hash.slice(1));
        if (!target) return;
        event.preventDefault();
        closeMenu();
        target.scrollIntoView({behavior: reducedMotion.matches ? 'instant' : 'smooth', block: 'start'});
        target.setAttribute('tabindex', '-1');
        target.focus({preventScroll: true});
        history.replaceState(null, '', link.hash);
    });
    mobile.addEventListener('change', closeMenu);

    const nodes = [...page.querySelectorAll('[data-ecosystem]')];
    const descriptions = [...page.querySelectorAll('[data-ecosystem-detail]')];
    const connections = [...page.querySelectorAll('[data-connection]')];
    const ecosystemMap = page.querySelector('.lp-ecosystem-map');
    const ecosystemDetail = page.querySelector('#lp-ecosystem-detail');
    let ecosystemIndex = 0;
    let ecosystemVisible = !('IntersectionObserver' in window);
    let ecosystemTimer = null;
    let hoveredNode = null;
    let focusedNode = null;
    const ecosystemOrder = [0, 3, 4, 5, 2, 1];
    function showEcosystem(index, manual = false) {
        ecosystemIndex = Number(index);
        ecosystemDetail.setAttribute('aria-live', manual ? 'polite' : 'off');
        nodes.forEach(node => node.setAttribute('aria-pressed', String(node.dataset.ecosystem === index)));
        descriptions.forEach(detail => detail.hidden = detail.dataset.ecosystemDetail !== index);
        connections.forEach(connection => {
            connection.classList.toggle('is-active', connection.dataset.connection === index);
            connection.classList.toggle('is-emphasized',
                connection.dataset.connection === (focusedNode || hoveredNode)?.dataset.ecosystem);
        });
    }
    function scheduleEcosystem() {
        clearTimeout(ecosystemTimer);
        ecosystemTimer = null;
        const running = ecosystemVisible && !document.hidden && !reducedMotion.matches;
        ecosystemMap.classList.toggle('is-cycling', running);
        if (!running) return;
        ecosystemTimer = setTimeout(() => {
            const next = (ecosystemOrder.indexOf(ecosystemIndex) + 1) % ecosystemOrder.length;
            showEcosystem(String(ecosystemOrder[next]));
            scheduleEcosystem();
        }, 4000);
    }
    function followInteraction() {
        const node = focusedNode || hoveredNode;
        connections.forEach(connection => connection.classList.toggle('is-emphasized',
            connection.dataset.connection === node?.dataset.ecosystem));
    }
    nodes.forEach(node => {
        node.addEventListener('click', () => {
            showEcosystem(node.dataset.ecosystem, true);
        });
        node.addEventListener('mouseenter', () => { hoveredNode = node; followInteraction(); });
        node.addEventListener('mouseleave', () => { hoveredNode = null; followInteraction(); });
        node.addEventListener('focus', () => { focusedNode = node; followInteraction(); });
        node.addEventListener('blur', () => { focusedNode = null; followInteraction(); });
    });
    showEcosystem('0');
    scheduleEcosystem();
    const methodDiagrams = [...page.querySelectorAll('[data-method-diagram]')];
    const methodCycles = methodDiagrams.map(diagram => {
        const options = [...diagram.querySelectorAll('[data-method-option]')];
        const branches = [...diagram.querySelectorAll('[data-method-branch]')];
        const movingArrow = diagram.querySelector('animateMotion');
        const order = branches.length ? [0, 1, 2, 5, 4, 3] : [0, 1, 2, 3];
        let index = 0;
        let visible = !('IntersectionObserver' in window);
        let timer = null;
        function showMethod(option, manual = false) {
            index = options.indexOf(option);
            options.forEach(control => control.setAttribute('aria-pressed', String(control === option)));
            const detail = document.getElementById(option.getAttribute('aria-controls'));
            detail.setAttribute('aria-live', manual ? 'polite' : 'off');
            detail.textContent = option.dataset.methodDescription;
            branches.forEach(branch => branch.classList.toggle('is-active',
                Number(branch.dataset.methodBranch) === index));
        }
        function schedule() {
            clearTimeout(timer);
            timer = null;
            const running = visible && !document.hidden && !reducedMotion.matches;
            const wasRunning = diagram.classList.contains('is-animating');
            diagram.classList.toggle('is-animating', running);
            if (movingArrow && running !== wasRunning) {
                if (running) movingArrow.beginElement();
                else movingArrow.endElement();
            }
            if (!running) return;
            timer = setTimeout(() => {
                showMethod(options[order[(order.indexOf(index) + 1) % order.length]]);
                schedule();
            }, 4000);
        }
        options.forEach((option, optionIndex) => {
            option.addEventListener('click', () => showMethod(option, true));
            const branch = branches.find(item => Number(item.dataset.methodBranch) === optionIndex);
            const emphasize = () => branch?.classList.toggle('is-emphasized',
                option.matches(':hover, :focus-visible'));
            option.addEventListener('mouseenter', emphasize);
            option.addEventListener('mouseleave', emphasize);
            option.addEventListener('focus', emphasize);
            option.addEventListener('blur', emphasize);
        });
        showMethod(options[0]);
        schedule();
        return {diagram, schedule, stop: () => clearTimeout(timer), setVisible(value) { visible = value; schedule(); }};
    });
    window.addEventListener('pagehide', () => {
        clearTimeout(ecosystemTimer);
        methodCycles.forEach(cycle => cycle.stop());
    });
    window.addEventListener('pageshow', () => {
        scheduleEcosystem();
        methodCycles.forEach(cycle => cycle.schedule());
    });

    const insightData = {
        quality: {
            title: 'Capaian indikator mutu',
            description: 'Contoh perbandingan capaian bulanan untuk mendukung evaluasi indikator.',
            values: [48, 61, 56, 70, 64, 78, 73, 84, 76, 89, 83, 94],
        },
        claims: {
            title: 'Pola pemanfaatan pelayanan',
            description: 'Contoh indeks relatif bulanan untuk menggambarkan penelusuran pola data e-Klaim.',
            values: [72, 60, 81, 68, 74, 63, 85, 78, 68, 88, 74, 82],
        },
        experience: {
            title: 'Evaluasi pengalaman pasien',
            description: 'Contoh tren hasil survei bulanan sebagai bahan evaluasi pelayanan.',
            values: [64, 68, 63, 76, 73, 82, 80, 86, 83, 91, 87, 92],
        },
    };
    const insightButtons = [...page.querySelectorAll('[data-insight]')];
    const bars = [...page.querySelectorAll('[data-chart-bar]')];
    insightButtons.forEach(button => button.addEventListener('click', () => {
        const data = insightData[button.dataset.insight];
        insightButtons.forEach(control => control.setAttribute('aria-pressed', String(control === button)));
        page.querySelector('#lp-insight-title').textContent = data.title;
        page.querySelector('#lp-insight-description').textContent = data.description;
        bars.forEach((bar, index) => bar.style.setProperty('--bar-height', data.values[index] + '%'));
        page.querySelector('.lp-bars').setAttribute('aria-label',
            'Data simulasi ' + data.title + ', Januari hingga Desember: ' + data.values.join(', ') + '.');
    }));
    page.querySelector('.lp-bars').setAttribute('aria-label',
        'Data simulasi capaian indikator, Januari hingga Desember: ' + insightData.quality.values.join(', ') + '.');

    const revealItems = [...page.querySelectorAll('.lp-reveal')];
    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            });
        }, {threshold: 0.08});
        revealItems.forEach(item => revealObserver.observe(item));
        if (!reducedMotion.matches) page.classList.add('lp-motion-ready');

        const navLinks = [...navigation.querySelectorAll('a[href^="#"]')];
        const sectionObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                navLinks.forEach(link => {
                    if (link.hash === '#' + entry.target.id) link.setAttribute('aria-current', 'location');
                    else link.removeAttribute('aria-current');
                });
            });
        }, {rootMargin: '-15% 0px -55% 0px', threshold: 0});
        navLinks.forEach(link => {
            const section = document.getElementById(link.hash.slice(1));
            if (section) sectionObserver.observe(section);
        });

        const ecosystemObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                ecosystemVisible = entry.isIntersecting;
                if (ecosystemVisible) showEcosystem(String(ecosystemIndex));
                else connections.forEach(connection => connection.classList.remove('is-active'));
                scheduleEcosystem();
            });
        });
        ecosystemObserver.observe(page.querySelector('.lp-ecosystem-map'));

        const methodObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => methodCycles.find(cycle => cycle.diagram === entry.target).setVisible(entry.isIntersecting));
        }, {threshold: 0.2});
        methodDiagrams.forEach(diagram => methodObserver.observe(diagram));
    }
    reducedMotion.addEventListener('change', () => {
        page.classList.toggle('lp-motion-ready', !reducedMotion.matches);
        if (reducedMotion.matches) revealItems.forEach(item => item.classList.add('is-visible'));
        scheduleEcosystem();
        methodCycles.forEach(cycle => cycle.schedule());
    });
    document.addEventListener('visibilitychange', () => {
        page.classList.toggle('lp-paused', document.hidden);
        scheduleEcosystem();
        methodCycles.forEach(cycle => cycle.schedule());
    });
    let scrollPending = false;
    window.addEventListener('scroll', () => {
        if (scrollPending) return;
        scrollPending = true;
        requestAnimationFrame(() => {
            header.classList.toggle('is-scrolled', window.scrollY > 10);
            scrollPending = false;
        });
    }, {passive: true});
    page.classList.add('lp-js');
})();
