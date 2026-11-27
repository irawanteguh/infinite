(async () => {
    const page = document.getElementById('encounter-page');
    if (!page) return;
    const el = id => document.getElementById('encounter-' + id);
    const classes = {EMER: 'Emergency', AMB: 'Outpatient', IMP: 'Inpatient'};
    const states = {draft: 'Draft', sending: 'Sedang dikirim', sent: 'Terkirim', failed: 'Ditolak', unknown: 'Hasil belum pasti'};
    let metadata = {records: [], environments: []};
    let record = null, form = null, busy = false, roomRecord = null, roomEpisodeId = '';
    const roomModal = bootstrap.Modal.getOrCreateInstance(el('room-modal'));
    const dateInput = el('arrived');
    let picker;
    if (typeof flatpickr === 'function') {
        dateInput.type = 'text';
        picker = flatpickr(dateInput, {
            enableTime: true, time_24hr: true, dateFormat: 'Y-m-d H:i:S', altInput: true,
            altFormat: 'd/m/Y H:i', disableMobile: true, allowInput: false,
            onReady: (_, __, instance) => {
                instance.calendarContainer.classList.add('app-date-calendar');
                instance.altInput.classList.add('app-date-input');
                instance.altInput.id = 'encounter-arrived-display';
                page.querySelector('label[for="encounter-arrived"]').htmlFor = instance.altInput.id;
            }
        });
    }
    function message(text, error = false) {
        el('message').textContent = text;
        el('message').hidden = !text;
        el('message').className = 'alert ' + (error ? 'alert-danger' : 'alert-info');
    }
    function tone(state) { return state === 'sent' ? 'success' : state === 'failed' ? 'error' : 'waiting'; }
    function badge(state) {
        const node = document.createElement('span'); node.className = 'encounter-state'; node.dataset.tone = tone(state); node.textContent = states[state] || state; return node;
    }
    function editable() { return !record || ['draft', 'failed'].includes(record.state); }
    function updateControls() {
        const edit = editable();
        el('save').disabled = busy || !edit;
        el('send').disabled = busy || !edit;
        page.querySelectorAll('#encounter-form input, #encounter-form select, #encounter-form button').forEach(control => control.disabled = busy || !edit);
        el('room-form').querySelectorAll('input, select, button').forEach(control => control.disabled = busy);
        if (busy) roomPicker?.close();
        el('room-save').disabled = busy || !roomEpisodeId;
        el('new').disabled = busy; el('refresh').disabled = busy; el('back').disabled = busy;
        el('reconcile').disabled = busy || !record || !['sending', 'unknown', 'failed'].includes(record.state);
        if (record?.target_hash) el('environment').disabled = true;
    }
    async function request(endpoint, data, loadingTitle = '') {
        return await new Promise((resolve, reject) => {
            $.ajax({
                url: page.dataset.url + '/' + endpoint, method: data === undefined ? 'GET' : 'POST',
                data: data === undefined ? undefined : JSON.stringify(data),
                contentType: 'application/json', dataType: 'json', timeout: 110000,
                headers: {'X-CSRF-TOKEN': page.dataset.csrf},
                beforeSend: () => { if (loadingTitle) showDataLoading(loadingTitle, 'Mohon tunggu hingga proses selesai.'); }
            }).done(payload => {
                if (payload.csrfHash) page.dataset.csrf = payload.csrfHash;
                resolve(payload.data);
            }).fail(xhr => {
                if (xhr.responseJSON?.csrfHash) page.dataset.csrf = xhr.responseJSON.csrfHash;
                reject(new Error(xhr.responseJSON?.error || (xhr.status === 0 ? 'Koneksi terputus. Muat ulang daftar untuk memeriksa hasil.' : 'Permintaan belum dapat diproses. Periksa sesi login.')));
            });
        });
    }
    async function work(operation) {
        if (busy) return;
        busy = true; page.setAttribute('aria-busy', 'true'); updateControls();
        try { await operation(); }
        catch (error) { message(error.message, true); }
        finally { busy = false; page.removeAttribute('aria-busy'); updateControls(); }
    }
    function disposeTable(table) {
        if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
        table.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(button => bootstrap.Dropdown.getInstance(button)?.dispose());
    }
    async function refresh() {
        metadata = await request('data', undefined, 'Memuat daftar kunjungan');
        const table = el('table'); disposeTable(table); table.tBodies[0].replaceChildren();
        metadata.records.forEach(row => {
            const tr = document.createElement('tr');
            const environment = metadata.environments.find(item => item.id === row.environment_id)?.name || row.environment_id;
            for (const text of [row.registration_no, row.patient_name, classes[row.visit_class] || row.visit_class, environment]) {
                const cell = tr.insertCell(); cell.textContent = text;
            }
            tr.insertCell().append(badge(row.state));
            tr.insertCell().textContent = row.satusehat_id || '—';
            const actions = tr.insertCell(); actions.className = 'text-end';
            const group = document.createElement('div'); group.className = 'btn-group';
            const toggle = document.createElement('button'); toggle.type = 'button';
            toggle.className = 'btn btn-sm btn-light-primary dropdown-toggle'; toggle.textContent = 'Actions';
            toggle.dataset.bsToggle = 'dropdown'; toggle.setAttribute('aria-expanded', 'false');
            const menu = document.createElement('div'); menu.className = 'dropdown-menu dropdown-menu-end';
            const open = document.createElement('button'); open.type = 'button'; open.className = 'dropdown-item'; open.textContent = 'Buka';
            open.onclick = () => work(async () => { record = await request('detail?id=' + row.encounter_tx_id, undefined, 'Membuka kunjungan'); form = structuredClone(record.form); showEditor(); });
            const room = document.createElement('button');
            room.type = 'button'; room.className = 'dropdown-item'; room.textContent = 'Masuk ruang (Ibu)';
            room.disabled = !row.satusehat_id || !['sent', 'unknown'].includes(row.state);
            room.onclick = () => openRoom(row.encounter_tx_id);
            menu.append(open, room); group.append(toggle, menu); actions.append(group); table.tBodies[0].append(tr);
            new bootstrap.Dropdown(toggle, {popperConfig: options => ({...options, strategy: 'fixed'})});
        });
        initDataTable(table, '#encounter-search', 10, {columnDefs: [{targets: -1, searchable: false}]});
    }
    function positionRoomCalendar(picker) {
        const input = picker.altInput || picker.input;
        const bounds = input.getBoundingClientRect();
        const calendar = picker.calendarContainer;
        const width = calendar.offsetWidth || 308;
        const height = calendar.offsetHeight || 350;
        const below = bounds.bottom + 6;
        const above = bounds.top - height - 6;
        const top = below + height <= window.innerHeight - 8 ? below : Math.max(8, above);
        calendar.style.top = top + 'px';
        calendar.style.left = Math.max(8, Math.min(bounds.left, window.innerWidth - width - 8)) + 'px';
        calendar.style.right = 'auto';
    }
    let roomPicker;
    if (typeof flatpickr === 'function') {
        el('room-time').type = 'text';
        roomPicker = flatpickr(el('room-time'), {
            enableTime: true, time_24hr: true, dateFormat: 'Y-m-d H:i:S',
            altInput: true, altFormat: 'd/m/Y H:i', disableMobile: true, allowInput: false,
            appendTo: el('room-modal'),
            position: picker => positionRoomCalendar(picker),
            onOpen: (_, __, picker) => positionRoomCalendar(picker),
            onReady: (_, __, picker) => {
                picker.calendarContainer.classList.add('app-date-calendar');
                picker.altInput.classList.add('app-date-input');
                picker.altInput.id = 'encounter-room-time-display';
                el('room-form').querySelector('label[for="encounter-room-time"]').htmlFor = picker.altInput.id;
            }
        });
    }
    el('room-modal').addEventListener('hidden.bs.modal', () => roomPicker?.close());
    el('room-modal').querySelector('.modal-body').addEventListener('scroll', () => roomPicker?.close());
    window.addEventListener('resize', () => { if (roomPicker?.isOpen) positionRoomCalendar(roomPicker); });
    function renderEpisodes(resources, selectedId) {
        const table = el('room-episodes');
        disposeTable(table);
        table.tBodies[0].replaceChildren();
        roomEpisodeId = '';
        const dateText = value => {
            if (!value) return '—';
            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? value : date.toLocaleString('id-ID');
        };
        resources.filter(resource => resource.resourceType === 'EpisodeOfCare').forEach(episode => {
            const row = table.tBodies[0].insertRow();
            const input = document.createElement('input');
            input.type = 'radio'; input.className = 'form-check-input';
            input.name = 'encounter-room-episode'; input.value = episode.id;
            input.setAttribute('aria-label', 'Pilih episode ANC ' + episode.id);
            input.checked = episode.id === selectedId;
            if (input.checked) roomEpisodeId = episode.id;
            input.onchange = () => {
                roomEpisodeId = episode.id;
                $(table).DataTable().rows().nodes().toArray().forEach(row => {
                    const radio = row.querySelector('input[type="radio"]');
                    if (radio) radio.checked = radio.value === roomEpisodeId;
                });
                updateControls();
            };
            row.insertCell().append(input);
            for (const value of [episode.id, episode.status === 'active' ? 'Aktif' : episode.status, dateText(episode.period?.start), dateText(episode.period?.end)]) row.insertCell().textContent = value || '—';
        });
        el('room-episodes-search').value = '';
        initDataTable(table, '#encounter-room-episodes-search', 5, {columnDefs: [{targets: 0, searchable: false}], language: {emptyTable: 'Episode ANC aktif pasien belum ditemukan.'}});
        $(table).off('draw.dt.episodeSelection').on('draw.dt.episodeSelection', () => {
            table.querySelectorAll('input[type="radio"]').forEach(input => { input.checked = input.value === roomEpisodeId; });
        });
    }
    el('room-modal').addEventListener('shown.bs.modal', () => {
        if ($.fn.DataTable.isDataTable(el('room-episodes'))) $(el('room-episodes')).DataTable().columns.adjust();
    });
    el('room-episodes-search').addEventListener('keydown', event => {
        if (event.key === 'Enter') event.preventDefault();
    });
    function openRoom(id) {
        return work(async () => {
            roomRecord = await request('detail?id=' + id, undefined, 'Membuka kunjungan');
            if (!roomRecord.satusehat_id || !['sent', 'unknown'].includes(roomRecord.state)) throw new Error('Encounter harus sudah terkirim sebelum masuk ruang.');
            const episodes = await request('episodes', {id: roomRecord.encounter_tx_id}, 'Memuat episode ANC aktif');
            const locations = await request('search', {resource: 'Location', environment_id: roomRecord.environment_id}, 'Memuat ruang pelayanan');
            el('room-location').replaceChildren(new Option('Pilih ruang pelayanan', ''));
            locations.resources.filter(resource => resource.resourceType === 'Location').forEach(resource => {
                el('room-location').add(new Option(resource.name || resource.id, resource.id));
            });
            const pending = roomRecord.form.pending_room;
            renderEpisodes(episodes.resources, pending?.episode_id || roomRecord.form.episode_id);
            el('room-location').value = pending?.location.id || '';
            if ($.fn.select2) {
                if (!$(el('room-location')).hasClass('select2-hidden-accessible')) $(el('room-location')).select2({dropdownParent: $(el('room-modal')), width: '100%'});
                $(el('room-location')).trigger('change');
            }
            if (roomPicker) roomPicker.setDate(pending?.entered_at ? new Date(pending.entered_at) : null, false);
            else el('room-time').value = pending?.entered_at ? new Date(new Date(pending.entered_at).getTime() - new Date(pending.entered_at).getTimezoneOffset() * 60000).toISOString().slice(0, 16) : '';
            el('room-patient').textContent = roomRecord.patient_name + ' · ' + roomRecord.registration_no;
            el('room-message').textContent = episodes.resources.length ? '' : 'Episode ANC aktif pasien belum ditemukan.';
            el('room-message').hidden = Boolean(episodes.resources.length);
            roomModal.show();
        });
    }
    el('room-form').onsubmit = event => {
        event.preventDefault();
        work(async () => {
            const selectedLocation = el('room-location').selectedOptions[0];
            const date = roomPicker ? roomPicker.selectedDates[0] : (el('room-time').value ? new Date(el('room-time').value) : null);
            if (!roomRecord || !roomEpisodeId || !selectedLocation?.value || !date || Number.isNaN(date.getTime())) {
                el('room-message').textContent = 'Pilih episode ANC, ruang, dan waktu masuk.';
                el('room-message').hidden = false;
                return;
            }
            try {
                const updated = await request('room', {
                    id: roomRecord.encounter_tx_id, revision: roomRecord.revision, episode_id: roomEpisodeId,
                    location: {id: selectedLocation.value, name: selectedLocation.text},
                    entered_at: date.toISOString()
                }, 'Memperbarui masuk ruang');
                roomRecord = updated;
                if (record?.encounter_tx_id === updated.encounter_tx_id) {
                    record = updated; form = structuredClone(updated.form); showPeople(); renderResult();
                }
                if (updated.form.room_state === 'success') {
                    roomModal.hide(); await refresh(); message('Encounter berhasil diperbarui. Pasien masuk ruang pelayanan.');
                } else {
                    el('room-message').textContent = updated.form.room_error || 'Hasil pembaruan belum pasti. Periksa status kunjungan.';
                    el('room-message').hidden = false;
                    await refresh();
                }
            } catch (error) {
                try { roomRecord = await request('detail?id=' + roomRecord.encounter_tx_id); } catch {}
                el('room-message').textContent = error.message;
                el('room-message').hidden = false;
            }
        });
    };
    function blankForm() {
        return {environment_id: metadata.environments[0]?.id || '', registration_no: '', visit_class: '', arrived_at: '', patient: null, practitioner: null, location: null};
    }
    const lookupModal = bootstrap.Modal.getOrCreateInstance(el('lookup-modal'));
    el('lookup-modal').addEventListener('shown.bs.modal', () => {
        if ($.fn.DataTable.isDataTable(el('results-table'))) $(el('results-table')).DataTable().columns.adjust();
    });
    el('lookup-modal').addEventListener('hidden.bs.modal', () => {
        const target = el('lookup-modal').dataset.lookupTarget;
        if (target) el(target + '-find')?.focus();
    });
    function clearResults() {
        lookupModal.hide();
        disposeTable(el('results-table')); el('results').hidden = true;
        el('results-table').tHead.replaceChildren(); el('results-table').tBodies[0].replaceChildren();
    }
    function showPeople() {
        for (const key of ['patient', 'practitioner']) {
            el(key + '-name').value = form[key]?.name || '';
            el(key + '-ihs').value = form[key]?.id || '';
        }
        el('location-name').value = form.location?.name || '';
    }
    function showEditor() {
        message('');
        el('list').hidden = true; el('editor').hidden = false; clearResults();
        el('environment').replaceChildren(new Option('Pilih environment', ''));
        metadata.environments.forEach(environment => el('environment').add(new Option(environment.name, environment.id)));
        el('environment').value = form.environment_id;
        el('number').value = form.registration_no;
        el('class').value = form.visit_class;
        $([el('environment'), el('class')]).trigger('change.select2');
        const time = form.arrived_at ? new Date(form.arrived_at) : null;
        if (picker) picker.setDate(time, false);
        else dateInput.value = time ? new Date(time.getTime() - time.getTimezoneOffset() * 60000).toISOString().slice(0, 16) : '';
        for (const key of ['patient', 'practitioner']) el(key + '-identifier').value = '';
        showPeople(); renderResult(); updateControls();
    }
    function capture() {
        form.environment_id = el('environment').value;
        form.registration_no = el('number').value.trim();
        form.visit_class = el('class').value;
        const date = picker ? picker.selectedDates[0] : (dateInput.value ? new Date(dateInput.value) : null);
        form.arrived_at = date && !Number.isNaN(date.getTime()) ? date.toISOString() : '';
        return structuredClone(form);
    }
    async function saveDraft() {
        const data = capture();
        if (!data.patient) throw new Error('Cari dan pilih pasien terlebih dahulu.');
        if (!data.environment_id || !data.visit_class || !data.arrived_at) throw new Error('Lengkapi environment, jenis kunjungan, dan waktu kedatangan.');
        record = await request('save', {id: record?.encounter_tx_id || null, revision: record?.revision || 0, form: data}, 'Menyimpan pendaftaran');
        form = structuredClone(record.form); el('number').value = form.registration_no; renderResult();
    }
    function renderResult() {
        const state = record?.state || 'draft';
        el('state').replaceWith(Object.assign(badge(state), {id: 'encounter-state'}));
        el('result').hidden = !record || state === 'draft';
        const output = el('result-content'); output.replaceChildren();
        if (!record) return;
        if (record.satusehat_id) {
            const text = document.createElement('p'); text.className = 'fw-semibold text-break'; text.textContent = 'ID Encounter · ' + record.satusehat_id; output.append(text);
        }
        const result = record.result || {};
        if (state !== 'draft') {
            const detail = document.createElement('button');
            detail.type = 'button';
            detail.className = 'btn btn-sm btn-light-primary mb-4';
            detail.textContent = 'Lihat request dan response';
            detail.onclick = () => work(async () => {
                const exchange = await request('exchange?id=' + encodeURIComponent(record.encounter_tx_id));
                for (const key of ['request', 'response']) {
                    const json = JSON.stringify(exchange[key + 'Body'], null, 2);
                    el('json-' + key).textContent = json;
                    el('json-' + key + '-lines').textContent = json.split('\n').map((line, index) => index + 1).join('\n');
                }
                el('exchange-status').textContent = exchange.status ? 'HTTP ' + exchange.status : 'Respons belum tersedia.';
                bootstrap.Modal.getOrCreateInstance(el('exchange-modal')).show();
            });
            output.append(detail);
        }

        if (record.form.room_entered_at) {
            const room = document.createElement('p');
            room.textContent = 'Masuk ruang · ' + record.form.location.name + ' · ' + new Date(record.form.room_entered_at).toLocaleString('id-ID');
            output.append(room);
        }
        if (record.form.room_error) {
            const error = document.createElement('p'); error.className = 'alert alert-warning'; error.textContent = record.form.room_error; output.append(error);
        }
        if (state === 'unknown' || state === 'sending') {
            const note = document.createElement('div'); note.className = 'alert alert-warning';
            note.textContent = 'Hasil pengiriman belum pasti. Gunakan Periksa status untuk mencari kunjungan berdasarkan nomor registrasi.';
            output.append(note);
        }
        if (result.message) {
            const text = document.createElement('p'); text.textContent = result.message; output.append(text);
        }
        for (const issue of result.issue || []) {
            const text = document.createElement('p'); text.className = 'text-break';
            text.textContent = issue.details?.text || issue.diagnostics || issue.code; output.append(text);
        }
        if (record.http_status) {
            const text = document.createElement('p'); text.className = 'text-muted mb-0'; text.textContent = 'HTTP ' + record.http_status; output.append(text);
        }
    }
    function renderSearch(resources, key, source) {
        const table = el('results-table'); disposeTable(table);
        table.tHead.replaceChildren(); table.tBodies[0].replaceChildren();
        const head = table.tHead.insertRow();
        const location = key === 'location';
        for (const label of location ? ['Nama lokasi', 'ID SATUSEHAT', 'Pilih'] : ['NIK', 'Nama', 'Gender', 'Tanggal lahir', 'Nomor IHS', 'Pilih']) {
            const th = document.createElement('th'); th.scope = 'col'; th.textContent = label;
            if (label === 'Pilih') th.className = 'text-end';
            head.append(th);
        }
        resources.filter(item => item.resourceType === (location ? 'Location' : key === 'patient' ? 'Patient' : 'Practitioner')).forEach(resource => {
            const name = location ? resource.name : resource.name?.find(item => item.use === 'official')?.text || resource.name?.[0]?.text || '';
            const nik = resource.identifier?.find(item => item.system === 'https://fhir.kemkes.go.id/id/nik')?.value || '';
            const values = location ? [name, resource.id] : [nik, name, resource.gender, resource.birthDate, resource.id];
            const tr = table.tBodies[0].insertRow();
            values.forEach(value => { tr.insertCell().textContent = value || '—'; });
            const cell = tr.insertCell(); cell.className = 'text-end';
            const choose = document.createElement('button');
            choose.type = 'button'; choose.className = 'btn btn-sm btn-light-primary'; choose.textContent = 'Pilih';
            choose.disabled = !resource.id || !name;
            choose.onclick = () => {
                if (busy || !editable()) return;
                form[key] = location ? {id: resource.id, name} : {id: resource.id, name, nik, gender: resource.gender || null, birthDate: resource.birthDate || null};
                showPeople(); clearResults(); message('');
            };
            cell.append(choose);
        });
        el('results-title').textContent = location ? 'Hasil pencarian lokasi' : 'Hasil pencarian ' + (key === 'patient' ? 'pasien' : 'Practitioner');
        el('results-description').textContent = source;
        el('results').hidden = false;
        el('results-search').value = '';
        el('lookup-title').textContent = location ? 'Pilih lokasi pelayanan' : key === 'patient' ? 'Pilih pasien' : 'Pilih Practitioner';
        el('lookup-modal').dataset.lookupTarget = key;
        lookupModal.show();
        initDataTable(table, '#encounter-results-search', 10, {columnDefs: [{targets: -1, searchable: false}]});
    }
    el('new').onclick = () => {
        if (busy) return;
        record = null; form = blankForm(); showEditor();
    };
    el('refresh').onclick = () => work(async () => { await refresh(); message('Daftar kunjungan diperbarui.'); });
    el('back').onclick = () => work(async () => { await refresh(); record = null; form = null; el('editor').hidden = true; el('list').hidden = false; message(''); });
    el('save').onclick = () => work(async () => { await saveDraft(); message('Draft pendaftaran berhasil disimpan.'); });
    el('send').onclick = () => work(async () => {
        capture();
        if (!form.location) throw new Error('Cari dan pilih poli / lokasi pelayanan.');
        const answer = await Swal.fire({
            title: 'Kirim pendaftaran?', text: form.patient?.name + ' · ' + (metadata.environments.find(env => env.id === form.environment_id)?.name || ''),
            icon: 'question', showCancelButton: true, confirmButtonText: 'Kirim', cancelButtonText: 'Batal',
            buttonsStyling: false, customClass: {confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light ms-3'}
        });
        if (!answer.isConfirmed) return;
        await saveDraft();
        try {
            record = await request('send', {id: record.encounter_tx_id, revision: record.revision}, 'Mengirim pendaftaran');
            form = structuredClone(record.form);
            renderResult();
            message(record.state === 'sent' ? 'Pendaftaran berhasil dikirim ke SATUSEHAT.' : states[record.state] + '. Periksa hasil pengiriman.', record.state !== 'sent');
        } catch (error) {
            try { record = await request('detail?id=' + record.encounter_tx_id); renderResult(); } catch {}
            throw error;
        }
    });
    el('reconcile').onclick = () => work(async () => {
        record = await request('reconcile', {id: record.encounter_tx_id}, 'Memeriksa status kunjungan');
        form = structuredClone(record.form); el('number').value = form.registration_no; renderResult(); message('Encounter ditemukan di SATUSEHAT.');
    });
    for (const key of ['environment', 'class']) {
        $(el(key)).select2({
            width: '100%',
            dropdownParent: $(document.body),
            placeholder: key === 'environment' ? 'Pilih environment' : 'Pilih jenis kunjungan',
            minimumResultsForSearch: Infinity
        });
    }
    $(el('environment')).on('change', () => {
        if (!form) return;
        form.environment_id = el('environment').value;
        form.patient = null; form.practitioner = null; form.location = null;
        showPeople(); clearResults();
    });
    for (const [key, resource] of [['patient', 'Patient'], ['practitioner', 'Practitioner']]) {
        el(key + '-find').onclick = () => work(async () => {
            capture();
            const data = await request('search', {resource, environment_id: form.environment_id, identifier: el(key + '-identifier').value.trim()}, 'Mencari ' + (key === 'patient' ? 'pasien' : 'Practitioner'));
            renderSearch(data.resources, key, data.source);
        });
    }
    el('practitioner-clear').onclick = () => { if (!busy && editable()) { form.practitioner = null; showPeople(); } };
    el('location-find').onclick = () => work(async () => {
        capture();
        const data = await request('search', {resource: 'Location', environment_id: form.environment_id}, 'Mencari lokasi pelayanan');
        renderSearch(data.resources, 'location', data.source);
    });
    el('form').onsubmit = event => event.preventDefault();
    await work(refresh);
})();
