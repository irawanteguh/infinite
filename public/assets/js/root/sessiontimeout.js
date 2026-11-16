(function () {
    'use strict';
    const element = document.getElementById('session-idle-modal');
    if (!element || !window.bootstrap || !bootstrap.Modal) return;
    const idleMs = 60000;
    const graceMs = 10000;
    const key = 'infinite.idle.' + element.dataset.sessionKey;
    const logoutKey = key + '.logout';
    const modal = new bootstrap.Modal(element, {backdrop: 'static', keyboard: false});
    const countdown = document.getElementById('session-idle-countdown');
    const stay = document.getElementById('session-idle-stay');
    let lastActivity = Date.now();
    let lastStored = 0;
    let warning = false;
    let leaving = false;
    let timer;

    function readActivity() {
        try {
            const value = Number(localStorage.getItem(key));
            if (Number.isFinite(value) && value > 0 && value <= Date.now()) lastActivity = Math.max(lastActivity, value);
        } catch (_) { /* Local timers still work when storage is unavailable. */ }
    }
    function storeActivity(force) {
        if (!force && Date.now() - lastStored < 1000) return;
        lastStored = Date.now();
        try { localStorage.setItem(key, String(lastActivity)); } catch (_) {}
    }
    function hideWarning() {
        if (!warning) return;
        warning = false;
        modal.hide();
    }
    function leave(broadcast = true) {
        if (leaving) return;
        leaving = true;
        clearTimeout(timer);
        stay.disabled = true;
        document.getElementById('session-idle-logout').disabled = true;
        if (broadcast) {
            try { localStorage.setItem(logoutKey, String(Date.now())); } catch (_) {}
        }
        // End the server session even if an unsaved-form prompt delays navigation.
        const url = element.dataset.logoutUrl;
        fetch(url, {method: 'GET', credentials: 'same-origin', cache: 'no-store', keepalive: true})
            .catch(() => {}).finally(() => window.location.replace(url));
    }
    function check() {
        clearTimeout(timer);
        if (leaving) return;
        const remaining = lastActivity + idleMs + graceMs - Date.now();
        if (remaining <= 0) { leave(); return; }
        if (Date.now() - lastActivity >= idleMs) {
            countdown.textContent = String(Math.ceil(remaining / 1000));
            if (!warning) {
                warning = true;
                document.getElementById('session-idle-announcement').textContent = 'Sesi tidak aktif. Logout otomatis dalam 10 detik.';
                modal.show();
            }
        } else hideWarning();
        timer = setTimeout(check, Math.min(1000, remaining));
    }
    function activity() {
        if (warning || leaving) return;
        if (Date.now() - lastActivity >= idleMs) { check(); return; }
        lastActivity = Date.now();
        storeActivity(false);
    }
    try {
        if (localStorage.getItem(logoutKey)) { leave(false); return; }
        const stored = Number(localStorage.getItem(key));
        if (Number.isFinite(stored) && stored > 0 && stored <= Date.now()) lastActivity = stored;
        else storeActivity(true);
    } catch (_) {}
    ['pointerdown', 'pointermove', 'keydown', 'input', 'wheel', 'touchstart'].forEach(name => {
        document.addEventListener(name, activity, {passive: true, capture: true});
    });
    stay.addEventListener('click', () => {
        if (Date.now() >= lastActivity + idleMs + graceMs) { leave(); return; }
        lastActivity = Date.now();
        storeActivity(true);
        hideWarning();
        check();
    });
    document.getElementById('session-idle-logout').addEventListener('click', () => leave());
    element.addEventListener('shown.bs.modal', () => stay.focus());
    window.addEventListener('storage', event => {
        if (event.key === logoutKey && event.newValue) { leave(false); return; }
        if (event.key === key) { readActivity(); check(); }
    });
    document.addEventListener('visibilitychange', () => { readActivity(); check(); });
    window.addEventListener('focus', () => { readActivity(); check(); });
    check();
})();