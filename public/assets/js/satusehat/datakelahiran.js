(async () => {
    const page = document.getElementById('birth-page');
    if (!page) return;
    const el = id => document.getElementById(id);
    let metadata, record, action, busy = false;
    const labels = {
        Patient_Id_Ibu: 'ID SATUSEHAT pasien ibu', Patient_Id_Bayi: 'ID SATUSEHAT pasien bayi',
        Practitioner_Id: 'ID SATUSEHAT Practitioner', Practitioner_Name: 'Nama Practitioner',
        Practitioner_Id_Bayi: 'ID SATUSEHAT nakes bayi', Practitioner_Name_Bayi: 'Nama nakes bayi',
        EpisodeOfCare_id_Ibu: 'ID episode ANC ibu', EpisodeOfCare_id_Bayi: 'ID episode neonatus bayi',
        Encounter_id_INC: 'ID kunjungan ibu', Encounter_id_Bayi: 'ID kunjungan bayi',
        Loc_id_INC: 'ID lokasi INC', Loc_id_BedINC: 'ID tempat tidur INC', Loc_id_Anak: 'ID lokasi pemeriksaan bayi',
        Location_Name: 'Nama lokasi pemeriksaan', Task_ID: 'Nomor pengajuan akta', Task_id: 'ID Task',
        TransactionNumber: 'Nomor transaksi SATUSEHAT', NomorTransaksi: 'Nomor transaksi SATUSEHAT'
    };
    function message(text, error = false) {
        el('birth-status').hidden = !text;
        el('birth-status').className = 'alert ' + (error ? 'alert-danger' : 'alert-info');
        el('birth-status').textContent = text;
    }
    async function request(endpoint, data) {
        const options = {credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json'}};
        if (data !== undefined) {
            options.method = 'POST';
            options.headers['Content-Type'] = 'application/json';
            options.headers['X-CSRF-TOKEN'] = page.dataset.csrf;
            options.body = JSON.stringify(data);
        }
        const response = await fetch(page.dataset.url + '/' + endpoint, options);
        let payload;
        try { payload = await response.json(); } catch { throw Error('Respons server belum dapat dibaca. Periksa sesi login.'); }
        if (payload.csrfHash) page.dataset.csrf = payload.csrfHash;
        if (!response.ok) throw Error(payload.error || 'Permintaan gagal.');
        return payload.data;
    }
    async function work(fn) {
        if (busy) return;
        busy = true; page.setAttribute('aria-busy', 'true');
        const buttons = [...page.querySelectorAll('button')];
        buttons.forEach(button => { button.dataset.wasDisabled = button.disabled ? '1' : '0'; button.disabled = true; });
        try { await fn(); } catch (error) { message(error.message, true); }
        finally {
            busy = false; page.removeAttribute('aria-busy');
            buttons.forEach(button => { button.disabled = button.dataset.wasDisabled === '1'; });
            if (action && record) renderAction();
        }
    }
    async function refresh() {
        metadata = await request('data');
        metadata.actions = metadata.actions.filter(item => item.name !== 'GET EpisodeOfCare ANC by Patient, type, status, organization');
        const table = el('birth-table');
        if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
        table.tBodies[0].replaceChildren();
        metadata.transactions.forEach(item => {
            const row = document.createElement('tr');
            for (const [index, value] of [item.number, item.mother, item.baby, new Date(item.updated).toLocaleString('id-ID'), item.sent].entries()) {
                const cell = document.createElement('td'); cell.textContent = value;
                if (index === 4) cell.className = 'text-end';
                row.append(cell);
            }
            const cell = document.createElement('td'); cell.className = 'text-end';
            const open = document.createElement('button'); open.type = 'button'; open.className = 'btn btn-sm btn-light-primary'; open.textContent = 'Buka';
            open.onclick = () => work(async () => { record = await request('detail?id=' + item.id); showEditor(); });
            cell.append(open); row.append(cell); table.tBodies[0].append(row);
        });
        initDataTable(table, '#birth-search', 10, {columnDefs: [{targets: -1, searchable: false}]});
    }
    function newRecord() {
        return {number: '', mother: '', baby: '', environment: metadata.environments[0]?.id || '', context: {}, drafts: {}, results: {}};
    }
    function showEditor() {
        action = metadata.actions[0];
        el('birth-list').hidden = true; el('birth-editor').hidden = false;
        for (const key of ['number', 'mother', 'baby']) el('birth-' + key).value = record[key];
        const select = el('birth-env'); select.replaceChildren();
        metadata.environments.forEach(env => select.add(new Option(env.name, env.id)));
        select.value = record.environment; select.disabled = Boolean(Object.keys(record.results).length);
        el('birth-lookup-results').replaceChildren();
        renderStages(); renderAction(); message('');
    }
    function capture() {
        if (!record) return;
        for (const key of ['number', 'mother', 'baby']) record[key] = el('birth-' + key).value.trim();
        record.environment = el('birth-env').value;
        page.querySelectorAll('[data-birth-context]').forEach(input => record.context[input.dataset.birthContext] = input.value);
        if (action) {
            const draft = record.drafts[action.id] || {};
            el('birth-fields').querySelectorAll('[data-birth-field]').forEach(input => {
                if (input.type !== 'file') draft[input.dataset.birthField] = input.dataset.birthDateTime && input.value ? new Date(input.value.replace(' ', 'T')).toISOString() : input.value;
            });
            record.drafts[action.id] = draft;
        }
    }
    async function save() {
        capture();
        const latest = await request('save', record);
        record = latest;
        return record;
    }
    function actionLabel(item) {
        if (item.name === 'Encounter - Kunjungan Ibu') return 'Encounter';
        return item.name.replace(/^(Encounter|Observation|RelatedPerson|DocumentReference|EpisodeOfCare)\s*-\s*/, '');
    }
    function renderStages() {
        const container = el('birth-stages'); container.replaceChildren();
        const stages = [...new Set(metadata.actions.map(item => item.stage))];
        for (const [index, stage] of stages.entries()) {
            const section = document.createElement('section');
            const title = document.createElement('h3');
            title.className = 'fs-6 text-muted mb-3';
            title.textContent = String(index + 1).padStart(2, '0') + '. ' + stage.replace(/^[0-9]+\.\s*/, '');
            section.append(title);
            metadata.actions.filter(item => item.stage === stage).forEach(item => {
                const button = document.createElement('button');
                button.type = 'button'; button.className = 'birth-step btn btn-sm w-100 text-start mb-2' + (action?.id === item.id ? ' active' : '');
                button.textContent = actionLabel(item);
                const state = record.results[item.id]?.state;
                if (state) {
                    const icon = document.createElement('i'); icon.className = 'bi me-2 ' + (state === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle');
                    button.prepend(icon);
                }
                button.onclick = () => { if (busy) return; capture(); action = item; renderStages(); renderAction(); };
                section.append(button);
            });
            container.append(section);
        }
    }
    function inputField(label, key, value, type = 'text', context = false, required = false) {
        const wrapper = document.createElement('div'); wrapper.className = 'col-md-6';
        const title = document.createElement('label'); title.className = 'form-label'; title.textContent = label;
        const id = 'birth-input-' + Math.random().toString(36).slice(2); title.htmlFor = id;
        const input = ['boolean', 'encounter-class'].includes(type) ? document.createElement('select') : document.createElement('input');
        input.id = id; input.className = ['boolean', 'encounter-class'].includes(type) ? 'form-select' : 'form-control';
        if (type === 'encounter-class') {
            input.add(new Option('Pilih jenis transaksi', ''));
            for (const [code, label] of [['EMER', 'Emergency'], ['AMB', 'Outpatient'], ['IMP', 'Inpatient']]) input.add(new Option(label, code));
        } else if (type === 'boolean') {
            input.add(new Option('Pilih', '')); input.add(new Option('Ya', 'true')); input.add(new Option('Tidak', 'false'));
        } else {
            input.type = ({datetime: 'datetime-local', number: 'number', date: 'date', time: 'time', file: 'file'})[type] || 'text';
            if (type === 'number') input.step = 'any';
            if (type === 'file') input.accept = 'application/pdf,image/jpeg,image/png';
        }
        if (type !== 'file') input.value = value ?? '';
        if (context) input.dataset.birthContext = key;
        else input.dataset.birthField = key;
        input.required = !context && required && !(type === 'file' && value);
        if (type === 'file') {
            input.onchange = async () => {
                const file = input.files[0];
                if (!file) return;
                if (file.size > 4 * 1024 * 1024) { message('Ukuran berkas maksimal 4 MB.', true); input.value = ''; return; }
                const transaction = record;
                const actionId = action.id;
                const bytes = new Uint8Array(await file.arrayBuffer());
                let binary = ''; for (const byte of bytes) binary += String.fromCharCode(byte);
                transaction.drafts[actionId] ||= {};
                transaction.drafts[actionId][key] = btoa(binary);
            };
            if (value) { const note = document.createElement('p'); note.className = 'text-muted fs-8 mt-2'; note.textContent = 'Berkas sudah tersedia pada draft. Pilih berkas untuk mengganti.'; wrapper.append(note); }
        }
        wrapper.prepend(title, input);
        if (type === 'datetime' && typeof flatpickr === 'function') {
            input.type = 'text';
            input.dataset.birthDateTime = 'true';
            flatpickr(input, {
                enableTime: true, time_24hr: true, dateFormat: 'Y-m-d H:i:S',
                altInput: true, altFormat: 'd/m/Y H:i', allowInput: false, disableMobile: true,
                defaultDate: value ? new Date(value) : null,
                onReady: (_, __, picker) => {
                    picker.calendarContainer.classList.add('app-date-calendar');
                    picker.altInput.classList.add('app-date-input');
                    picker.altInput.id = id + '-display'; title.htmlFor = picker.altInput.id;
                }
            });
        }
        return wrapper;
    }
    function renderAction() {
        if (!action || !record) return;
        el('birth-action-title').textContent = actionLabel(action);
        el('birth-action-group').textContent = action.group;
        el('birth-action-error').hidden = !action.error;
        el('birth-action-error').textContent = action.error;
        const context = el('birth-context'); context.replaceChildren();
        const variables = [...new Set(['Patient_Id_Ibu', 'Patient_Id_Bayi', ...action.variables])].filter(key => !['Patient_Name_Ibu', 'Patient_Name_Bayi', 'EpisodeOfCare_id_Ibu', 'Loc_id_INC'].includes(key));
        variables.forEach(key => {
            if (key === 'Patient_Id_Bayi') {
                const field = inputField('Nama pasien', 'baby-name', record.baby, 'text', false);
                field.querySelector('input').readOnly = true;
                field.querySelector('input').removeAttribute('data-birth-field');
                context.append(field);
                return;
            }
            if (['Practitioner_Id', 'Practitioner_Name'].includes(key)) return;
            context.append(inputField(labels[key] || key.replace(/_/g, ' '), key, record.context[key], 'text', true));
        });
        if (variables.includes('Practitioner_Id') || variables.includes('Practitioner_Name')) {
            const section = document.createElement('div');
            section.className = 'col-12';
            const divider = document.createElement('hr');
            divider.className = 'my-5';
            section.append(divider);
            const row = document.createElement('div');
            row.className = 'row g-4 mb-4';
            const modeColumn = document.createElement('div');
            modeColumn.className = 'col-md-4';
            const modeLabel = document.createElement('label');
            modeLabel.className = 'form-label'; modeLabel.htmlFor = 'birth-nakes-mode';
            modeLabel.textContent = 'Pencarian Practitioner';
            const mode = document.createElement('select');
            mode.id = 'birth-nakes-mode'; mode.className = 'form-select';
            mode.add(new Option('IHS', 'ihs')); mode.add(new Option('NIK', 'nik'));
            modeColumn.append(modeLabel, mode);
            const numberColumn = document.createElement('div');
            numberColumn.className = 'col-md-8';
            const numberLabel = document.createElement('label');
            numberLabel.className = 'form-label'; numberLabel.htmlFor = 'birth-nakes-identifier';
            numberLabel.textContent = 'Nomor IHS';
            const group = document.createElement('div'); group.className = 'input-group';
            const identifier = document.createElement('input');
            identifier.id = 'birth-nakes-identifier'; identifier.className = 'form-control'; identifier.autocomplete = 'off';
            const find = document.createElement('button');
            find.type = 'button'; find.className = 'btn btn-light-primary'; find.textContent = 'Cari';
            find.onclick = () => lookupPerson('Practitioner', 'practitionerMother', mode.value, identifier.value.trim());
            mode.onchange = () => {
                const nik = mode.value === 'nik';
                numberLabel.textContent = nik ? 'NIK' : 'Nomor IHS';
                identifier.value = ''; identifier.maxLength = nik ? 16 : 64;
            };
            group.append(identifier, find); numberColumn.append(numberLabel, group);
            row.append(modeColumn, numberColumn);
            const results = document.createElement('div'); results.className = 'row g-4';
            const ihs = inputField('ID SATUSEHAT Practitioner', 'nakes-ihs', record.context.Practitioner_Id || '');
            const name = inputField('Nama Practitioner', 'nakes-name', record.context.Practitioner_Name || '');
            for (const field of [ihs, name]) {
                const input = field.querySelector('input');
                input.readOnly = true;
                input.removeAttribute('data-birth-field');
            }
            results.append(ihs, name);
            section.append(row, results); context.append(section);
        }
        const fields = el('birth-fields');
        fields.querySelectorAll('input').forEach(input => input._flatpickr?.destroy());
        fields.replaceChildren();
        for (const field of action.fields) fields.append(inputField(field.label, field.path, record.drafts[action.id]?.[field.path], field.type, false, field.required));
        const result = record.results[action.id];
        const stateLabels = {success: 'Terkirim', failed: 'Ditolak SATUSEHAT', unknown: 'Hasil belum pasti. Periksa SATUSEHAT sebelum mengirim ulang.', pending: 'Pengiriman berlangsung'};
        el('birth-send-status').textContent = result ? stateLabels[result.state] : 'Draft';
        el('birth-send-status').className = result?.state === 'success' ? 'text-success' : 'text-muted';
        el('birth-result').hidden = !result;
        const resultView = el('birth-result-text');
        resultView.replaceChildren();
        if (result) {
            const summary = document.createElement('p');
            summary.className = 'alert ' + (result.state === 'success' ? 'alert-success' : 'alert-warning');
            summary.textContent = (stateLabels[result.state] || result.state) + (result.status ? ' · HTTP ' + result.status : '');
            resultView.append(summary);
            const resource = result.body || {};
            if (resource.id) {
                const text = document.createElement('p');
                text.textContent = 'ID SATUSEHAT · ' + resource.id;
                resultView.append(text);
            }
            for (const issue of resource.issue || []) {
                const text = document.createElement('p');
                text.textContent = issue.details?.text || issue.diagnostics || issue.code;
                resultView.append(text);
            }
            for (const entry of resource.entry || []) {
                const resource = entry.resource;
                if (!resource) continue;
                const text = document.createElement('p');
                text.textContent = [resource.resourceType, resource.id, resource.status].filter(Boolean).join(' · ');
                resultView.append(text);
                if (resource.resourceType === 'EpisodeOfCare') {
                    const choose = document.createElement('button');
                    choose.className = 'btn btn-sm btn-light-primary mb-3'; choose.type = 'button';
                    choose.textContent = 'Gunakan episode ANC ini';
                    choose.onclick = () => {
                        capture();
                        record.context.EpisodeOfCare_id_Ibu = resource.id;
                        renderAction();
                        message('Episode ANC dipilih. Simpan draft untuk menyimpan referensi.');
                    };
                    resultView.append(choose);
                }
            }
        }
        el('birth-send').disabled = Boolean(action.error || (result && ['pending', 'unknown', 'success'].includes(result.state) && action.method !== 'GET'));
    }
    el('birth-new').onclick = () => { record = newRecord(); showEditor(); };
    el('birth-save').onclick = () => work(async () => { await save(); message('Draft transaksi berhasil disimpan.'); renderStages(); });
    el('birth-back').onclick = () => work(async () => { action = null; record = null; el('birth-editor').hidden = true; el('birth-list').hidden = false; await refresh(); });
    el('birth-send').onclick = () => work(async () => {
        if (!el('birth-fields').reportValidity()) return;
        const confirm = await Swal.fire({title: 'Kirim data ke SATUSEHAT?', text: el('birth-env').selectedOptions[0]?.text + ' · ' + actionLabel(action), icon: 'question', showCancelButton: true, confirmButtonText: 'Kirim', cancelButtonText: 'Batal'});
        if (!confirm.isConfirmed) return;
        await save();
        const title = el('birth-send').textContent; el('birth-send').textContent = 'Mengirim…';
        try {
            record = await request('send', {id: record.id, revision: record.revision, action: action.id});
            message(record.results[action.id].state === 'success' ? 'Tahap berhasil dikirim.' : 'SATUSEHAT mengembalikan validasi. Periksa hasil pengiriman.', record.results[action.id].state !== 'success');
        } catch (error) {
            try { record = await request('detail?id=' + record.id); } catch {}
            throw error;
        } finally { el('birth-send').textContent = title; renderStages(); }
    });
    el('birth-lookup-mode').onchange = () => {
        const nik = el('birth-lookup-mode').value === 'nik';
        el('birth-lookup-label').textContent = nik ? 'NIK' : 'Nomor IHS';
        el('birth-identifier').value = '';
        el('birth-identifier').maxLength = nik ? 16 : 64;
        el('birth-lookup-results').replaceChildren();
    };
    el('birth-lookup').onclick = () => lookupPerson('Patient', action?.variables.includes('Patient_Id_Bayi') ? 'baby' : 'mother', el('birth-lookup-mode').value, el('birth-identifier').value.trim());
    function lookupPerson(resource, target, mode, identifier) {
        return work(async () => {
        capture();
        const results = await request('search', {environment: record.environment, resource, mode, identifier});
        const container = el('birth-lookup-results');
        const previous = container.querySelector('table');
        if (previous && $.fn.DataTable.isDataTable(previous)) $(previous).DataTable().destroy();
        container.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(toggle => bootstrap.Dropdown.getInstance(toggle)?.dispose());
        container.replaceChildren();
        const entries = results.resourceType === resource ? [{resource: results}] : results.entry || [];
        const patients = entries.map(entry => entry.resource).filter(person => person?.resourceType === resource);
        const table = document.createElement('table');
        table.id = 'birth-patient-results';
        table.className = 'table table-row-dashed align-middle w-100';
        table.dataset.datatable = 'manual';
        const head = table.createTHead().insertRow();
        for (const label of ['NIK', 'Nama', 'Gender', 'birthDate', 'Nomor IHS', 'Actions']) {
            const cell = document.createElement('th'); cell.scope = 'col'; cell.textContent = label;
            if (label === 'Actions') cell.className = 'text-end';
            head.append(cell);
        }
        const body = table.createTBody();
        patients.forEach(person => {
            const name = person.name?.find(item => item.use === 'official')?.text || person.name?.[0]?.text || '';
            const nik = person.identifier?.find(item => item.system === 'https://fhir.kemkes.go.id/id/nik')?.value || '';
            const row = body.insertRow();
            for (const value of [nik, name, person.gender, person.birthDate, person.id]) {
                const cell = row.insertCell(); cell.textContent = value || '—';
            }
            const cell = row.insertCell(); cell.className = 'text-end';
            const group = document.createElement('div'); group.className = 'btn-group';
            const toggle = document.createElement('button');
            toggle.type = 'button'; toggle.className = 'btn btn-sm btn-light-primary dropdown-toggle';
            toggle.dataset.bsToggle = 'dropdown'; toggle.setAttribute('aria-expanded', 'false'); toggle.textContent = 'Actions';
            const menu = document.createElement('div'); menu.className = 'dropdown-menu dropdown-menu-end';
            const button = document.createElement('button');
            button.type = 'button'; button.className = 'dropdown-item'; button.textContent = resource === 'Patient' ? 'Pilih pasien' : 'Pilih nakes';
            const icon = document.createElement('i'); icon.className = 'bi bi-person-check'; icon.setAttribute('aria-hidden', 'true');
            button.prepend(icon);
            button.disabled = !person.id;
            button.onclick = () => {
                capture();
                if (target === 'practitionerMother') {
                    record.context.Practitioner_Id = person.id;
                    record.context.Practitioner_Name = name;
                } else {
                    const field = target === 'mother' ? 'mother' : 'baby';
                    record.context[field === 'mother' ? 'Patient_Id_Ibu' : 'Patient_Id_Bayi'] = person.id;
                    record[field] = name;
                    record.patients ||= {};
                    record.patients[field] = {ihs: person.id, nik, name, gender: person.gender || null, birthDate: person.birthDate || null};
                    el('birth-' + field).value = name;
                }
                renderAction();
                if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy();
                container.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(toggle => bootstrap.Dropdown.getInstance(toggle)?.dispose());
                container.replaceChildren();
                message((resource === 'Patient' ? 'Pasien' : 'Nakes') + ' dipilih. Nomor IHS ' + person.id + '.');
            };
            menu.append(button); group.append(toggle, menu); cell.append(group);
            new bootstrap.Dropdown(toggle, {popperConfig: options => ({...options, strategy: 'fixed'})});
        });
        const card = document.createElement('section');
        card.className = 'card app-table-card mb-5';
        card.setAttribute('aria-labelledby', 'birth-patient-title');
        const header = document.createElement('div');
        header.className = 'card-header app-table-heading';
        const heading = document.createElement('div');
        const title = document.createElement('h3'); title.id = 'birth-patient-title'; title.className = 'app-table-title'; title.textContent = resource === 'Patient' ? 'Hasil pencarian pasien' : 'Hasil pencarian nakes';
        const description = document.createElement('p'); description.className = 'app-table-description'; description.textContent = results.source || (resource === 'Patient' ? 'Pilih pasien untuk menggunakan Nomor IHS pada transaksi.' : 'Pilih Practitioner untuk menggunakan Nomor IHS pada transaksi.');
        const search = document.createElement('div'); search.className = 'app-table-search';
        const searchInput = document.createElement('input');
        searchInput.id = 'birth-patient-search'; searchInput.type = 'search'; searchInput.className = 'form-control';
        searchInput.placeholder = 'Cari NIK, nama, atau IHS'; searchInput.setAttribute('aria-label', 'Cari hasil pencarian pasien');
        search.append(searchInput);
        heading.append(title, description); header.append(heading, search);
        const content = document.createElement('div'); content.className = 'card-body'; content.append(table);
        card.append(header, content); container.append(card);
        initDataTable(table, '#birth-patient-search', 10, {columnDefs: [{targets: -1, searchable: false}], language: {emptyTable: 'Data pasien tidak ditemukan.'}});

        });
    }
    await work(refresh);
})();
