(function () {
    'use strict';
    const root = document.getElementById('pe-master');
    if (!root) return;
    const $ = (id) => document.getElementById(id);
    const instrumentFilter = $('pe-instrument-filter');
    let instruments = [];
    let questions = [];
    let domains = [];
    let csrfHash = root.dataset.csrfHash;
    let writing = false;
    const current = () => instruments.find((row) => row.instrument_id === instrumentFilter.value);
    const instrumentQuestions = () => questions.filter((row) => row.instrument_id === instrumentFilter.value);
    const escape = (value) => String(value == null ? '' : value).replace(/[&<>"']/g, (char) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[char]));
    const modal = (id) => bootstrap.Modal.getOrCreateInstance($(id));

    function syncSelect(element) {
        jQuery(element).trigger('change.select2');
    }

    function renderDomains() {
        $('pe-domain-list').innerHTML = domains.map((row) => '<tr><td>' + escape(row.name) + '</td><td><span class="badge ' + (row.active === '1' ? 'badge-light-success' : 'badge-light-secondary') + '">' + (row.active === '1' ? 'Aktif' : 'Nonaktif') + '</span></td><td class="text-end"><button type="button" class="btn btn-sm btn-light-primary pe-edit-domain" data-id="' + escape(row.domain_id) + '">Edit</button></td></tr>').join('') || '<tr><td colspan="3" class="text-center text-muted">Belum ada domain.</td></tr>';
    }

    function openDomain(row) {
        const form = $('pe-domain-form');
        form.reset();
        form.elements.namedItem('domain_id').value = '';
        form.querySelector('.pe-form-error').classList.add('d-none');
        if (row) Object.keys(row).forEach((key) => { if (form.elements.namedItem(key)) form.elements.namedItem(key).value = row[key]; });
        $('pe-domain-form-title').textContent = row ? 'Edit domain' : 'Tambah domain';
        syncSelect($('pe-domain-active'));
    }

    function message(text, error) {
        const box = $('pe-message');
        box.className = 'alert ' + (error ? 'alert-danger' : 'alert-success');
        box.textContent = text;
    }

    async function request(action, data) {
        const options = {credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}};
        if (data) {
            options.method = 'POST';
            data.set(root.dataset.csrfName, csrfHash);
            options.body = data;
        }
        const response = await fetch(root.dataset.endpoint + '/' + action, options);
        if (response.redirected || !(response.headers.get('content-type') || '').includes('application/json')) {
            throw new Error('Sesi berakhir atau respons tidak valid. Silakan muat ulang halaman.');
        }
        const result = await response.json();
        if (result.csrfHash) csrfHash = result.csrfHash;
        if (!response.ok || result.responseCode !== '00') throw new Error(result.responseDesc || 'Data gagal diproses.');
        return result;
    }

    async function refresh(selected) {
        const result = await request('data');
        instruments = result.responseResult.instruments;
        questions = result.responseResult.questions;
        domains = result.responseResult.domains;
        instrumentFilter.replaceChildren();
        instruments.forEach((row) => instrumentFilter.add(new Option(row.name + (row.active === '0' ? ' (Nonaktif)' : ''), row.instrument_id)));
        if (!instruments.length) instrumentFilter.add(new Option('Belum ada instrumen', ''));
        if (instruments.some((row) => row.instrument_id === selected)) instrumentFilter.value = selected;
        syncSelect(instrumentFilter);
        renderDomains();
        $('pe-count-instruments').textContent = instruments.length;
        $('pe-count-questions').textContent = questions.length;
        $('pe-count-active').textContent = questions.filter((row) => row.active === '1' && instruments.some((i) => i.instrument_id === row.instrument_id && i.active === '1')).length;
        render();
    }

    function render() {
        const selected = current();
        $('pe-edit-instrument').disabled = !selected;
        $('pe-add-question').disabled = !selected;
        $('pe-preview').disabled = !selected;
        $('pe-instrument-info').classList.toggle('d-none', !selected);
        if (selected) {
            $('pe-type').textContent = selected.type;
            $('pe-name').textContent = selected.name;
            $('pe-respondent').textContent = selected.respondent;
            $('pe-measurement').textContent = selected.measurement;
            $('pe-instrument-status').textContent = selected.active === '1' ? 'Aktif' : 'Nonaktif';
            $('pe-instrument-status').className = 'badge ' + (selected.active === '1' ? 'badge-light-success' : 'badge-light-secondary');
            let note = '';
            if (selected.code === 'PROM_SC') note = 'Catatan sumber: judul nomor 5 “Menggigil” berbeda dengan pernyataan tentang BAB/BAK/buang angin. Tinjau sebelum digunakan.';
            if (selected.code === 'PREM_RS') note = 'Catatan sumber: skor 3 tertulis “Netral” pada tabel PDF dan “Ragu-ragu” pada pilihan pertanyaan. Pratinjau mengikuti pilihan pertanyaan.';
            $('pe-source-note').textContent = note;
            $('pe-source-note').classList.toggle('d-none', !note);
        }
        const search = $('pe-search').value.trim().toLocaleLowerCase('id');
        const status = $('pe-status-filter').value;
        const all = instrumentQuestions();
        const rows = all.filter((row) => (!status || row.active === status) && (row.domain + ' ' + row.question).toLocaleLowerCase('id').includes(search));
        $('pe-questions').innerHTML = rows.length ? rows.map((row) => '<tr><td class="ps-4">' + escape(row.sort_order) + '</td><td>' + escape(row.domain) + '</td><td style="white-space:pre-line">' + escape(row.question) + '</td><td><span class="badge ' + (row.active === '1' ? 'badge-light-success' : 'badge-light-secondary') + '">' + (row.active === '1' ? 'Aktif' : 'Nonaktif') + '</span></td><td class="text-end"><div class="btn-group"><button type="button" class="btn btn-light-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Aksi</button><div class="dropdown-menu dropdown-menu-end"><button type="button" class="dropdown-item btn btn-sm text-primary pe-edit-question" data-id="' + escape(row.question_id) + '"><i class="bi bi-pencil text-primary me-4"></i>Edit</button></div></div></td></tr>').join('') : '<tr><td colspan="5" class="text-center text-muted">' + (!selected ? 'Belum ada instrumen. Tambahkan instrumen untuk memulai.' : all.length ? 'Tidak ada pertanyaan yang sesuai filter.' : 'Belum ada pertanyaan. Klik Tambah pertanyaan untuk memulai.') + '</td></tr>';
        $('pe-result-count').textContent = 'Menampilkan ' + rows.length + ' dari ' + all.length + ' pertanyaan pada instrumen ini.';
    }

    function openQuestion(row) {
        const selected = current();
        if (!selected) return;
        const form = $('pe-question-form');
        form.reset();
        form.querySelector('.pe-form-error').classList.add('d-none');
        const domainSelect = $('pe-domain');
        domainSelect.replaceChildren(new Option('Pilih domain', ''));
        domains.filter((domain) => domain.active === '1' || (row && row.domain_id === domain.domain_id)).forEach((domain) => {
            domainSelect.add(new Option(domain.name + (domain.active === '0' ? ' (Nonaktif)' : ''), domain.domain_id));
        });
        const data = row || {question_id: '', instrument_id: selected.instrument_id, sort_order: Math.max(0, ...instrumentQuestions().map((q) => Number(q.sort_order))) + 1, domain_id: '', question: '', active: '1'};
        Object.keys(data).forEach((key) => { if (form.elements.namedItem(key)) form.elements.namedItem(key).value = data[key]; });
        $('pe-question-title').textContent = row ? 'Edit pertanyaan' : 'Tambah pertanyaan';
        $('pe-question-instrument').textContent = selected.name;
        form.querySelectorAll('select').forEach(syncSelect);
        modal('pe-question-modal').show();
    }

    function openInstrument(row) {
        const form = $('pe-instrument-form');
        const typeSelect = $('pe-instrument-type');
        typeSelect.querySelectorAll('[data-existing-skm]').forEach(option => option.remove());
        if (row && row.type === 'SKM') {
            const option = new Option('SKM', 'SKM');
            option.dataset.existingSkm = '1';
            typeSelect.add(option);
        }
        form.reset();
        form.querySelector('.pe-form-error').classList.add('d-none');
        form.elements.namedItem('instrument_id').value = '';
        if (row) Object.keys(row).forEach((key) => { if (form.elements.namedItem(key)) form.elements.namedItem(key).value = row[key]; });
        form.querySelectorAll('select').forEach(syncSelect);
        $('pe-instrument-title').textContent = row ? 'Edit instrumen' : 'Tambah instrumen';
        modal('pe-instrument-modal').show();
    }

    function bindForm(id, action, modalId) {
        $(id).addEventListener('submit', async (event) => {
            event.preventDefault();
            if (writing) return;
            const form = event.currentTarget;
            if (!form.reportValidity()) return;
            const errorBox = form.querySelector('.pe-form-error');
            const button = form.querySelector('[type="submit"]');
            const label = button.textContent;
            errorBox.classList.add('d-none');
            writing = true; button.disabled = true; button.textContent = 'Menyimpan…';
            try {
                const result = await request(action, new FormData(form));
                if (action === 'savedomain') openDomain();
                else modal(modalId).hide();
                message(result.responseDesc, false);
                try {
                    const code = action === 'saveinstrument' ? form.elements.namedItem('code').value : null;
                    await refresh(instrumentFilter.value);
                    if (code) {
                        const saved = instruments.find((row) => row.code === code);
                        if (saved) { instrumentFilter.value = saved.instrument_id; syncSelect(instrumentFilter); render(); }
                    }
                } catch (error) { message('Data tersimpan, tetapi daftar gagal dimuat ulang. ' + error.message, true); }
            } catch (error) { errorBox.textContent = error.message; errorBox.classList.remove('d-none'); }
            finally { writing = false; button.disabled = false; button.textContent = label; }
        });
    }

    document.querySelectorAll('#pe-master select, #pe-question-modal select, #pe-instrument-modal select, #pe-domain-modal select').forEach((element) => {
        element.setAttribute('data-control', 'select2');
        element.style.width = '100%';
        const parent = element.closest('.modal');
        if (parent) element.setAttribute('data-dropdown-parent', '#' + parent.id);
        if (!jQuery(element).data('select2')) {
            const options = {width: '100%', minimumResultsForSearch: 0, dropdownCssClass: 'pe-master-select-menu'};
            if (parent) options.dropdownParent = jQuery(parent);
            jQuery(element).select2(options);
        }
    });
    jQuery(instrumentFilter).on('change', () => { $('pe-search').value = ''; $('pe-status-filter').value = ''; syncSelect($('pe-status-filter')); render(); });
    $('pe-search').addEventListener('input', render);
    jQuery($('pe-status-filter')).on('change', render);
    $('pe-add-question').addEventListener('click', () => openQuestion());
    $('pe-add-instrument').addEventListener('click', () => openInstrument());
    $('pe-edit-instrument').addEventListener('click', () => openInstrument(current()));
    $('pe-questions').addEventListener('click', (event) => {
        const button = event.target.closest('.pe-edit-question');
        if (button) openQuestion(questions.find((row) => row.question_id === button.dataset.id));
    });
    $('pe-manage-domains').addEventListener('click', () => { openDomain(); renderDomains(); modal('pe-domain-modal').show(); });
    $('pe-domain-reset').addEventListener('click', () => openDomain());
    $('pe-domain-list').addEventListener('click', (event) => {
        const button = event.target.closest('.pe-edit-domain');
        if (button) openDomain(domains.find((row) => row.domain_id === button.dataset.id));
    });
    $('pe-preview').addEventListener('click', () => {
        const selected = current();
        if (!selected) return;
        $('pe-preview-title').textContent = selected.name;
        const labels = ['Sangat tidak setuju', 'Tidak setuju', 'Ragu-ragu', 'Setuju', 'Sangat setuju'];
        $('pe-preview-content').innerHTML = instrumentQuestions().filter((row) => row.active === '1').map((row) => '<fieldset class="border rounded p-5 mb-5"><legend class="float-none w-auto fs-7 px-2 text-muted">' + escape(row.domain) + '</legend><div class="fw-bold mb-4">' + escape(row.sort_order) + '. ' + escape(row.question) + '</div>' + (row.answer_options ? JSON.parse(row.answer_options) : labels).map((label, index) => '<label class="d-flex align-items-center gap-3 mb-3"><input class="form-check-input" type="radio" name="preview-' + escape(row.question_id) + '" value="' + (index + 1) + '"><span>' + (index + 1) + ' — ' + label + '</span></label>').join('') + '</fieldset>').join('') || '<div class="text-muted text-center py-5">Belum ada pertanyaan aktif.</div>';
        modal('pe-preview-modal').show();
    });
    bindForm('pe-question-form', 'savequestion', 'pe-question-modal');
    bindForm('pe-instrument-form', 'saveinstrument', 'pe-instrument-modal');
    bindForm('pe-domain-form', 'savedomain', 'pe-domain-modal');
    refresh().catch((error) => { $('pe-questions').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Data gagal dimuat.</td></tr>'; message(error.message, true); });
})();
