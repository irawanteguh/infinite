(() => {
    'use strict';
    const root = document.getElementById('monitor');
    const $ = id => document.getElementById(id);
    const groups = ['internal', 'external', 'digital'];
    function fitScreen() {
        const scale = Math.min(window.innerWidth / 1600, window.innerHeight / 900);
        root.style.transform = 'scale(' + scale + ')';
        root.style.left = Math.max(0, (window.innerWidth - 1600 * scale) / 2) + 'px';
        root.style.top = Math.max(0, (window.innerHeight - 900 * scale) / 2) + 'px';
    }
    window.addEventListener('resize', fitScreen);
    fitScreen();
    const simulation = new URLSearchParams(window.location.search).get('simulate') === 'alert';
    root.classList.toggle('simulation-mode', simulation);
    $('simulation').textContent = simulation ? 'Exit Simulation' : 'Simulate Alert';
    $('simulation').setAttribute('aria-pressed', String(simulation));
    $('simulation').addEventListener('click', () => {
        const url = new URL(window.location.href);
        if (simulation) url.searchParams.delete('simulate'); else url.searchParams.set('simulate', 'alert');
        window.location.assign(url.toString());
    });
    if (simulation) {
        root.querySelector('.live').textContent = 'SIMULATION MODE';
        root.querySelector('.brand p').textContent = 'Demonstration only — no live readings or device changes.';
    }
    function simulationPayload() {
        const stamp = new Date().toISOString();
        const sensor = (desc, val, alert = false) => ({desc, val, status: 1, simulatedAlert: alert});
        const wrap = (key, rows) => ({ok: true, readAt: stamp, data: {data: {[key]: rows}}});
        return {checkedAt: stamp, groups: {
            internal: wrap('isens', [sensor('Internal Temperature', '35.80 C', true), sensor('Internal Humidity', '52.40 %'), sensor('Input Voltage', '13.80 V')]),
            external: wrap('esens', [sensor('Sensor Temperature L', '34.60 C', true), sensor('Sensor Humidity L', '53.10 %'), sensor('Sensor Temperature R', '22.30 C'), sensor('Sensor Humidity R', '51.70 %'), sensor('Water Leak On PAC', 'Leak Detected', true), sensor('Water Leak On Rack Server', 'Dry'), sensor('Fire Suppresion', 'Standby'), sensor('Vesda Action', 'No Smoke'), sensor('Vesda Fault', 'No Fault')]),
            digital: wrap('diginp', [sensor('PAC #1 General Alarm', 'Active', true), sensor('PAC #2 General Alarm', 'Open'), sensor('Panel PAC Trip', 'Closed'), sensor('Panel MSB Trip', 'Closed'), sensor('Panel PP-SERVER ICT-R Trip', 'Closed'), sensor('Panel PP-SERVER ICT-S Trip', 'Closed'), sensor('Panel PP-SERVER ICT-T Trip', 'Closed')])
        }};
    }
    // Audible alerts require an explicit user gesture; never autoplay on page load.
    let audioContext = null, soundEnabled = false, alertCount = 0, soundTimer = null;
    const activeTones = new Set();
    function stopTones() {
        activeTones.forEach(tone => { try { tone.stop(); } catch (_) {} });
        activeTones.clear();
    }
    function playAlert() {
        if (!soundEnabled || !alertCount || !audioContext || audioContext.state !== 'running') return;
        // Alternating sawtooth siren with a soft envelope to prevent clicks.
        const oscillator = audioContext.createOscillator();
        const gain = audioContext.createGain();
        const start = audioContext.currentTime;
        oscillator.type = 'sawtooth';
        [950, 650, 950, 650, 950, 650].forEach((frequency, index) => {
            oscillator.frequency.setValueAtTime(frequency, start + index * 0.16);
        });
        gain.gain.setValueAtTime(0, start);
        gain.gain.linearRampToValueAtTime(0.85, start + 0.015);
        gain.gain.setValueAtTime(0.85, start + 0.92);
        gain.gain.exponentialRampToValueAtTime(0.001, start + 0.98);
        oscillator.connect(gain); gain.connect(audioContext.destination);
        activeTones.add(oscillator);
        oscillator.onended = () => { activeTones.delete(oscillator); oscillator.disconnect(); gain.disconnect(); };
        oscillator.start(start); oscillator.stop(start + 1);
    }
    function syncAlertSound() {
        alertCount = root.querySelectorAll('.simulated-alert, .needs-review').length;
        if (!soundEnabled || !alertCount) {
            clearInterval(soundTimer); soundTimer = null; stopTones(); return;
        }
        if (soundTimer === null) { playAlert(); soundTimer = setInterval(playAlert, 2000); }
    }
    $('sound').addEventListener('click', async () => {
        const button = $('sound');
        button.disabled = true;
        try {
            if (soundEnabled) {
                soundEnabled = false;
            } else {
                const Audio = window.AudioContext || window.webkitAudioContext;
                if (!Audio) throw new Error('Audio is not supported by this browser.');
                if (!audioContext) audioContext = new Audio();
                await audioContext.resume();
                if (audioContext.state !== 'running') throw new Error('Audio could not be enabled. Click to retry.');
                soundEnabled = true;
            }
            button.textContent = soundEnabled ? 'Mute Sound' : 'Enable Sound';
            button.setAttribute('aria-pressed', String(soundEnabled));
            button.title = soundEnabled ? 'Sound enabled. Repeats while alerts require attention.' : 'Enable audible alerts';
            syncAlertSound();
        } catch (error) {
            soundEnabled = false; syncAlertSound();
            button.textContent = 'Retry Sound'; button.title = error.message;
            button.setAttribute('aria-pressed', 'false');
        } finally { button.disabled = false; }
    });
    window.addEventListener('pagehide', () => { soundEnabled = false; clearInterval(soundTimer); soundTimer = null; stopTones(); });
    let busy = false, next = 0;
    // Use one server clock and one timezone on every monitoring screen.
    const clockFormatter = new Intl.DateTimeFormat('en-GB', {timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hourCycle: 'h23'});
    const time = date => clockFormatter.format(new Date(date));
    let serverClock = null, clockSyncedAt = 0;
    function syncClock(timestamp) {
        const instant = Date.parse(timestamp);
        if (!Number.isFinite(instant)) return;
        serverClock = instant;
        clockSyncedAt = performance.now();
        updateClock();
    }
    function updateClock() {
        $('clock').textContent = serverClock === null ? '--:--:-- WIB' : time(serverClock + performance.now() - clockSyncedAt) + ' WIB';
        $('clock').title = 'Server time - Asia/Jakarta (UTC+07:00)';
    }
    updateClock();
    function badge(node, text, state = '') { node.textContent = text; node.className = 'badge ' + ({good: 'badge-light-success', bad: 'badge-light-danger'}[state] || 'badge-light-warning'); }
    function empty(container, message) {
        container.replaceChildren();
        const node = document.createElement('div'); node.className = 'empty'; node.textContent = message; container.append(node);
    }
    // Preserve vendor field names and values: no guessed units, scaling or alarm polarity.
    function records(data, path = 'Sensor', result = []) {
        if (!data || typeof data !== 'object') return result;
        const entries = Object.entries(data);
        const fields = entries.filter(([, value]) => value === null || typeof value !== 'object');
        if (typeof data.desc === 'string' && Object.prototype.hasOwnProperty.call(data, 'val')) {
            result.push({path: data.desc, value: data.val, status: data.status, conditionText: data.conditionText, simulatedAlert: data.simulatedAlert === true, fields: []});
            return result;
        }
        if (fields.length) result.push({path, fields});
        entries.forEach(([key, value]) => {
            if (value && typeof value === 'object') records(value, path + ' / ' + key, result);
        });
        return result;
    }
    function protectionReading(row) {
        const labels = {
            'Water Leak On PAC': ['Dry', 'Leak Detected'],
            'Water Leak On Rack Server': ['Dry', 'Leak Detected'],
            'Fire Suppresion': ['Standby', 'Activated'],
            'Vesda Action': ['No Smoke', 'Smoke Detected'],
            'Vesda Fault': ['No Fault', 'Fault Detected']
        };
        const pair = labels[row.path];
        if (!pair || simulation) return null;
        const condition = String(row.conditionText || '').trim();
        if (/^normal$/i.test(condition) && Number(row.status) === 1) return {value: pair[0], state: 'Normal'};
        if (/\b(alarm|alert|critical)\b/i.test(condition) && !/\b(no|not|cleared|normal)\b/i.test(condition)) return {value: pair[1], state: 'Alert'};
        // A verified Normal JSON code is sufficient when the summary page is unavailable.
        if (!condition && Number(row.status) === 1) return {value: pair[0], state: 'Normal'};
        return {value: 'Check Required', state: 'Review Required'};
    }
    function render(group, result) {
        const panel = root.querySelector('[data-group="' + group + '"]');
        const cards = panel.querySelector('[data-cards]');
        if (!result || !result.ok) {
            badge(panel.querySelector('[data-status]'), 'Unavailable', 'bad');
            empty(cards, result?.error || 'Pembacaan gagal. Data sebelumnya tidak ditampilkan sebagai data terbaru.');

            return;
        }
        badge(panel.querySelector('[data-status]'), 'Updated · ' + time(result.readAt), 'good');

        const rows = records(result.data.data || result.data);
        const alerts = rows.filter(row => row.simulatedAlert).length;
        if (simulation && alerts) badge(panel.querySelector('[data-status]'), alerts + ' simulated alert' + (alerts > 1 ? 's' : ''), 'bad');
        if (!rows.length) { empty(cards, 'No sensor data returned.'); return; }
        cards.replaceChildren();
        rows.forEach((row, index) => {
            const card = document.createElement('article'); card.className = 'sensor';
            const kind = /temperature/i.test(row.path) ? 'temperature' : /humidity/i.test(row.path) ? 'humidity' : /voltage/i.test(row.path) ? 'voltage' : group === 'digital' ? 'digital' : 'contact';
            card.dataset.kind = kind; card.style.setProperty('--i', index); card.title = row.path;
            const icon = document.createElement('span'); icon.className = 'sensor-icon'; icon.setAttribute('aria-hidden', 'true');
            const icons = {temperature: 'bi-thermometer-half', humidity: 'bi-droplet', voltage: 'bi-lightning-charge', digital: 'bi-toggles', contact: 'bi-broadcast'};
            const glyph = document.createElement('i'); glyph.className = 'bi ' + icons[kind]; icon.append(glyph); card.append(icon);
            const name = document.createElement('strong'); name.textContent = row.path;
            const fields = document.createElement('div'); fields.className = 'fields';
            row.fields.forEach(([key, value]) => {
                const label = document.createElement('span'); label.textContent = key;
                const reading = document.createElement('b'); reading.textContent = value === null ? '—' : String(value);
                fields.append(label, reading);
            });
            card.append(name);
            const protection = protectionReading(row);
            if (row.value !== undefined) {
                const value = document.createElement('div'); value.className = 'number'; value.textContent = protection ? protection.value : group === 'digital' ? String(row.value) : String(row.value).replace(/ C$/, ' °C'); card.append(value);
            }
            const state = document.createElement('span'); state.className = 'sensor-state';
            const code = row.status === null || row.status === undefined || row.status === '' ? null : Number(row.status);
            if (simulation && row.simulatedAlert) {
                state.textContent = 'Alert'; state.classList.add('attention');
                state.title = 'Simulated alert — demonstration only.';
                card.classList.add('simulated-alert');
            } else if (protection && protection.state !== 'Normal') {
                state.textContent = protection.state; state.classList.add('attention');
                state.title = row.conditionText || 'Device condition requires verification.';
                card.classList.add('needs-review');
            } else if (code === 1) {
                state.textContent = 'Normal'; state.classList.add('normal');
                state.title = 'No alarm reported by the NTI device.';
            } else if (code === 0 || code === null || !Number.isFinite(code)) {
                state.textContent = 'Unavailable'; state.classList.add('unknown');
                state.title = 'Sensor condition could not be verified.';
            } else {
                state.textContent = 'Review Required'; state.classList.add('attention');
                state.title = 'The device reports a non-normal status. Check the NTI alarm console.';
                card.classList.add('needs-review');
            }
            card.append(state);
            if (row.fields.length) card.append(fields); cards.append(card);
        });
    }
    async function refresh() {
        if (busy) return;
        busy = true; $('refresh').disabled = true; $('nextUpdate').textContent = 'Reading sensors…';
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 28000);
        try {
            let payload;
            if (simulation) { payload = simulationPayload(); } else {
            const response = await fetch(root.dataset.endpoint, {cache: 'no-store', credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}, signal: controller.signal});
            if (response.redirected) throw new Error('Sesi berakhir. Silakan masuk kembali ke Infinite.');
            payload = await response.json().catch(() => { throw new Error('Respons aplikasi tidak valid. Periksa sesi login dan konfigurasi server.'); });
            if (!response.ok) throw new Error(payload.error || payload.responseDesc || 'Pembacaan perangkat gagal.');
            }
            if (!payload.groups) throw new Error('Kelompok sensor tidak tersedia dalam respons.');
            let count = 0;
            groups.forEach(group => { render(group, payload.groups[group]); if (payload.groups[group]?.ok) count++; });
            $('groupCount').textContent = count;
            $('connection').textContent = count === 3 ? 'Connected' : count ? 'Partial' : 'Disconnected';
            badge($('connectionBadge'), count === 3 ? 'Device data available' : 'Check sensor readings', count === 3 ? 'good' : 'bad');
            syncClock(payload.checkedAt);
            if (count) $('lastUpdate').textContent = time(payload.checkedAt);
            $('notice').hidden = count === 3;
            $('notice').textContent = 'One or more sensor groups are unavailable. Check the device connection.';
            if (simulation) {
                $('connection').textContent = 'Simulation';
                badge($('connectionBadge'), 'DEMO DATA - NOT LIVE', 'bad');
                $('notice').hidden = false;
                $('notice').textContent = 'SIMULATION - 4 active alerts: high room temperature, high left-probe temperature, PAC water leak and PAC #1 alarm. Demonstration data only.';
            }
        } catch (error) {
            $('connection').textContent = 'Disconnected'; $('groupCount').textContent = '0';
            badge($('connectionBadge'), 'Connection unavailable', 'bad');
            $('notice').hidden = false; $('notice').textContent = error.name === 'AbortError' ? 'The device did not respond in time. Retrying automatically.' : error.message;
            groups.forEach(group => render(group, {ok: false, error: 'Current readings unavailable.'}));
        } finally {
            syncAlertSound();
            clearTimeout(timeout); busy = false; $('refresh').disabled = false; next = Date.now() + 30000;
        }
    }
    $('refresh').addEventListener('click', refresh);
    $('fullscreen').addEventListener('click', async () => {
        try { if (document.fullscreenElement) await document.exitFullscreen(); else await document.documentElement.requestFullscreen(); }
        catch (_) { $('notice').hidden = false; $('notice').textContent = 'Fullscreen mode is not available in this browser.'; }
    });
    document.addEventListener('fullscreenchange', () => { $('fullscreen').textContent = document.fullscreenElement ? 'Exit fullscreen' : 'Fullscreen'; });
    setInterval(() => {
        updateClock();
        if (!busy && next) {
            const seconds = Math.max(0, Math.ceil((next - Date.now()) / 1000));
            $('nextUpdate').textContent = 'Next update in ' + seconds + ' seconds';
            if (seconds === 0 && !document.hidden) refresh();
        }
    }, 1000);
    refresh();
})();
