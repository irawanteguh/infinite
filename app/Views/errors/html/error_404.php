<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="color-scheme" content="light">
    <title>404 — Halaman tidak ditemukan | INFINITE</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f5f4; color: #242122; font-family: "Segoe UI", Arial, sans-serif; }
        main { min-height: 100vh; min-height: 100svh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 24px; text-align: center; }
        .illustration { display: block; width: min(100%, 920px); height: auto; margin-bottom: 40px; animation: enter 1s ease-out both; }
        .back { display: inline-flex; align-items: center; gap: 16px; padding: 16px 32px; border: 1px solid #3d3940; border-radius: 14px; background: #242122; color: #fff; font-size: 16px; letter-spacing: 2px; font-weight: 700; line-height: 1.2; text-decoration: none; box-shadow: 0 8px 20px #24212214; transition: transform .2s, box-shadow .2s, background .2s; animation: enter .7s .15s both; }
        .back::before { content: "\2190"; font-size: 22px; line-height: 1; font-weight: 400; transition: transform .2s; }
        .back:hover { background: #403b3c; transform: translateY(-3px); box-shadow: 0 12px 24px #24212226; }
        .back:hover::before { transform: translateX(-4px); }
        .back:focus-visible { outline: 3px solid #ed7b25; outline-offset: 5px; }
        .description { max-width: 650px; margin: 28px 0 0; font-size: clamp(18px, 2vw, 24px); font-weight: 600; line-height: 1.5; letter-spacing: -.4px; animation: enter .7s .25s both; }
        .description span { display: block; margin-top: 8px; color: #77747d; font-size: 14px; font-weight: 400; letter-spacing: 0; line-height: 1.8; }
        .signal { stroke: #ff952f; stroke-width: 5; stroke-linecap: round; stroke-dasharray: 0 1000; filter: drop-shadow(0 0 4px #ffad45); }
        .spark { transform-box: fill-box; transform-origin: center; animation: spark 2.8s ease-in-out infinite; }
        .spark-halo { animation: halo 2.8s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .number { fill: url(#number-tone); }
        .dynamite { transform-box: fill-box; transform-origin: center;  }
        .blast { opacity: 0; transform: scale(.1);  }
        .smoke { opacity: 0;  }
        .debris { opacity: 0;  }
        .dynamite.is-exploding { animation: detonate 1.4s linear both; }
        .explosion.is-exploding .blast { animation: blast 1.4s ease-out both; }
        .explosion.is-exploding .smoke { animation: smoke 1.4s ease-out both; }
        .explosion.is-exploding .debris { animation: debris 1.4s ease-out both; }
        @keyframes detonate { 0%, 12% { opacity: 1; transform: translateX(-3px) rotate(-2deg); } 6%, 18% { opacity: 1; transform: translateX(3px) rotate(2deg); } 24%, 85% { opacity: 0; transform: scale(.8); } 100% { opacity: 1; transform: scale(1); } }
        @keyframes blast { 0%, 18% { opacity: 0; transform: scale(.1); } 25% { opacity: .85; transform: scale(.6); } 65%, 100% { opacity: 0; transform: scale(1.8); } }
        @keyframes smoke { 0%, 18% { opacity: 0; transform: translateY(0) scale(.4); } 35% { opacity: .65; transform: translateY(-12px) scale(1); } 100% { opacity: 0; transform: translateY(-65px) scale(1.7); } }
        @keyframes debris { 0%, 18% { opacity: 0; transform: scale(.2) rotate(0); } 25% { opacity: 1; } 100% { opacity: 0; transform: scale(2) rotate(25deg); } }
        @keyframes spark { 0%, 100% { opacity: .45; transform: scale(.8); } 50% { opacity: 1; transform: scale(1.15); } }
        @keyframes halo { 0%, 100% { opacity: .15; transform: scale(.8); } 50% { opacity: .4; transform: scale(1.3); } }
        @keyframes enter { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; border: 0; }
        @media (max-width: 600px) { main { padding: 40px 18px; } .illustration { margin-bottom: 28px; } .description { margin-top: 24px; } }
        @media (prefers-reduced-motion: reduce) { *, *::before { animation: none !important; transition: none !important; } .signal, .explosion { display: none; } .back:hover, .back:hover::before { transform: none; } }
    </style>
</head>
<body>
    <main aria-labelledby="page-title">
        <h1 id="page-title" class="sr-only">404 — Halaman tidak ditemukan</h1>
        <svg class="illustration" viewBox="0 0 1000 540" fill="none" aria-hidden="true" focusable="false">
            <defs>
                <linearGradient id="number-tone" x1="500" y1="35" x2="500" y2="350" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#c9cbd0"/><stop offset="1" stop-color="#dedfe2"/>
                </linearGradient>
                <pattern id="sticks" width="28" height="120" patternUnits="userSpaceOnUse">
                    <rect width="28" height="120" fill="#ff7820"/>
                    <rect width="7" height="120" fill="#ffa62e"/>
                    <rect x="22" width="6" height="120" fill="#ee6420"/>
                </pattern>
                <g id="bundle">
                    <rect width="84" height="126" rx="3" fill="url(#sticks)"/>
                    <path d="M0 20H84M0 105H84" stroke="#34344e" stroke-width="11"/>
                    <path d="M0 28H84M0 113H84" stroke="#4a4251" stroke-opacity=".3" stroke-width="3"/>
                </g>
            </defs>
            <ellipse cx="510" cy="336" rx="455" ry="18" fill="#e5e5e5"/>
            <text class="number" x="500" y="335" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="420" font-weight="900" letter-spacing="-20">404</text>
            <g stroke="#32324f" stroke-width="5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M205 218V215Q205 202 220 202H241Q268 202 268 232V399"/>
                <path d="M588 273H598Q610 273 610 288V317Q610 330 598 330H588"/>
                <path d="M588 302H606Q630 302 630 329V371Q630 400 600 400H70A48 48 0 0 0 70 496H915"/>
                <path d="M630 366H675Q706 366 706 334V215Q706 180 741 180H823Q839 180 839 197V218"/>
                <path d="M812 219V209Q812 195 827 195H850Q865 195 865 210V219"/>
            </g>
            <g class="fire-network">
                <path class="signal" data-start="0" data-end="3500" pathLength="1000" d="M935 496H70A48 48 0 0 1 70 400H268"/>
                <path class="signal" data-start="3500" data-end="4900" pathLength="1000" d="M268 400V232Q268 202 241 202H220Q205 202 205 215V218"/>
                <path class="signal" data-start="3500" data-end="5300" pathLength="1000" d="M268 400H600Q630 400 630 371V366"/>
                <path class="signal" data-start="5300" data-end="6100" pathLength="1000" d="M630 366V329Q630 302 606 302H588"/>
                <path class="signal" data-start="6100" data-end="6900" pathLength="1000" d="M588 330H598Q610 330 610 317V288Q610 273 598 273H588"/>
                <path class="signal" data-start="5300" data-end="7300" pathLength="1000" d="M630 366H675Q706 366 706 334V215Q706 180 741 180H823Q839 180 839 197V214"/>
                <path class="signal" data-start="7300" data-end="7900" pathLength="1000" d="M839 195H827Q812 195 812 209V219M839 195H850Q865 195 865 210V219"/>
            </g>
            <g class="dynamite" data-bomb="left"><use href="#bundle" x="134" y="211"/></g>
            <path d="M130 217H221L219 277H132Z" fill="url(#number-tone)"/>
            <circle class="spark-halo" cx="205" cy="212" r="17" fill="#ff9b32"/>
            <g class="spark" stroke="#ff8a26" stroke-width="3" stroke-linecap="round">
                <path d="M205 196V191M193 200L189 196M217 200L221 196M190 212H185M220 212H225"/>
                <circle cx="205" cy="212" r="4" fill="#ffba52" stroke="none"/>
            </g>
            <g class="dynamite" data-bomb="center"><use href="#bundle" transform="translate(591 263) rotate(90)"/></g>
            <g class="dynamite" data-bomb="right"><use href="#bundle" x="798" y="214"/></g>
            <g class="explosion" data-effect="center" transform="translate(528 305)">
                <g class="smoke" fill="#b9bdc7">
                    <circle cx="-30" cy="0" r="29"/><circle cx="0" cy="-23" r="36"/>
                    <circle cx="33" cy="-5" r="30"/><circle cx="7" cy="18" r="30"/>
                </g>
                <g class="blast">
                    <circle r="58" stroke="#ff992e" stroke-width="5"/>
                    <path d="M0-54 12-25 40-40 28-12 58 0 28 13 40 41 12 28 0 58-13 28-41 40-28 12-58 0-28-12-40-40-12-26Z" fill="#ff9c32"/>
                    <path d="m0-30 9 19 23 11-23 10-9 22-10-22-22-10 22-11Z" fill="#ffe0a0"/>
                </g>
                <g class="debris" stroke="#f28a2d" stroke-width="5" stroke-linecap="round">
                    <path d="m-63-38-9-7M58-42l8-10M69 24l12 4M-62 33l-10 8M7-67l2-12"/>
                    <path d="m-30 62-4 10M39 56l7 9" stroke="#757588"/>
                </g>
            </g>
            <g>
                <path d="M935 413V456M924 416H946" stroke="#32324f" stroke-width="5"/>
                <rect x="912" y="402" width="46" height="12" rx="1.5" fill="#ff7922"/>
                <path d="M912 404H958" stroke="#ffa232" stroke-width="3"/>
                <rect x="903" y="455" width="64" height="68" fill="#a15e4e"/>
                <path d="M910 456V523M924 456V523M938 456V523M952 456V523" stroke="#cd8b77" stroke-width="4"/>
                <path d="M916 456V523M930 456V523M944 456V523M958 456V523" stroke="#804635" stroke-width="3"/>
                <path d="M901 455H970M901 524H970" stroke="#dc9784" stroke-width="4" stroke-linecap="round"/>
            </g>
        </svg>
        <a class="back" id="back-link" href="<?= esc(base_url(), 'attr') ?>" aria-label="Kembali ke halaman sebelumnya">BACK</a>
        <p class="description">Halaman yang Anda cari tidak ditemukan.<span>Periksa kembali alamatnya atau kembali ke halaman sebelumnya.</span></p>
    </main>
    <script>
        const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
        const effectTemplate = document.querySelector('[data-effect="center"]');
        ['left', 'right'].forEach(function (name) {
            const effect = effectTemplate.cloneNode(true);
            effect.dataset.effect = name;
            effect.setAttribute('transform', name === 'left' ? 'translate(176 285)' : 'translate(840 277)');
            effectTemplate.parentNode.appendChild(effect);
        });
        const firePaths = Array.from(document.querySelectorAll('.signal'));
        const bombs = [
            { name: 'left', arrival: 4900 },
            { name: 'center', arrival: 6900 },
            { name: 'right', arrival: 7900 }
        ];
        let frameId;
        let cycleStart;
        function animateFuse(timestamp) {
            if (cycleStart === undefined) cycleStart = timestamp;
            const elapsed = (timestamp - cycleStart) % 11000;
            firePaths.forEach(function (path) {
                const progress = Math.max(0, Math.min(1, (elapsed - Number(path.dataset.start)) / (Number(path.dataset.end) - Number(path.dataset.start))));
                path.style.strokeDasharray = (progress * 1000) + ' 1000';
                path.style.opacity = elapsed < 9500 ? '1' : String(Math.max(0, (10500 - elapsed) / 1000));
            });
            bombs.forEach(function (bomb) {
                const exploding = elapsed >= bomb.arrival && elapsed < bomb.arrival + 1500;
                document.querySelector('[data-bomb="' + bomb.name + '"]').classList.toggle('is-exploding', exploding);
                document.querySelector('[data-effect="' + bomb.name + '"]').classList.toggle('is-exploding', exploding);
            });
            frameId = window.requestAnimationFrame(animateFuse);
        }
        function updateMotion() {
            window.cancelAnimationFrame(frameId);
            cycleStart = undefined;
            document.querySelectorAll('.is-exploding').forEach(function (element) { element.classList.remove('is-exploding'); });
            if (!motionPreference.matches) frameId = window.requestAnimationFrame(animateFuse);
        }
        motionPreference.addEventListener('change', updateMotion);
        updateMotion();

        document.getElementById('back-link').addEventListener('click', function (event) {
            try {
                if (document.referrer && new URL(document.referrer).origin === window.location.origin && window.history.length > 1) {
                    event.preventDefault();
                    window.history.back();
                }
            } catch (error) {
                // Keep the home link available when the referrer cannot be parsed.
            }
        });
    </script>
</body>
</html>
