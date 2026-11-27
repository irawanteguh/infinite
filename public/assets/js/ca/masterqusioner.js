document.addEventListener('DOMContentLoaded', () => {
    const get = (id) => document.getElementById('ca-' + id);
    const root = get('master');
    if (!root) return;

    const select = get('instrument');
    const tbody = get('questions');
    const options = get('options');
    let instruments = [];
    let status = '';
    let busy = false;

    const current = () => instruments.find(({code}) => code === select.value);
    const modal = (kind) => bootstrap.Modal.getOrCreateInstance(get(kind + '-modal'));
    const element = (tag, value, classes = '') => {
        const node = document.createElement(tag);
        node.textContent = value;
        node.className = classes;
        return node;
    };

    function message(node, value, classes) {
        node.textContent = value;
        node.className = 'alert ' + classes + (value ? '' : ' d-none');
    }

    async function request(action, body) {
        if (body) body.set(root.dataset.csrfName, root.dataset.csrfHash);
        const response = await fetch(root.dataset.endpoint + '/' + action, {
            method: body ? 'POST' : 'GET',
            body,
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            cache: 'no-store',
        });
        const payload = await response.json().catch(() => ({}));
        if (payload.csrfHash) root.dataset.csrfHash = payload.csrfHash;
        if (!response.ok || payload.responseCode !== '00') {
            throw new Error(payload.responseDesc || 'Tidak dapat memuat data. Periksa koneksi atau sesi Anda.');
        }
        return payload;
    }

    function empty(text) {
        const cell = element('td', text, 'text-center text-muted');
        cell.colSpan = 5;
        const row = element('tr', '');
        row.append(cell);
        tbody.replaceChildren(row);
    }

    function render() {
        const instrument = current();
        if (!instrument) return;
        get('initialize').classList.toggle('d-none', instrument.initialized || !instrument.writable);
        get('edit-instrument').classList.toggle('d-none', !instrument.writable);
        get('add-question').disabled = !instrument.writable;
        get('preview').disabled = false;
        get('risks').replaceChildren(...instrument.risks.map(([min, max, label], index) => {
            const range = min === null ? '≤' + max : min + '–' + max;
            return element('span', label + ' · ' + range, 'badge badge-light-' + ['success', 'warning', 'danger'][index]);
        }));

        const questions = instrument.questions.filter((q) =>
            !status || String(q.active) === status
        );
        tbody.replaceChildren();
        if (!questions.length) empty('Tidak ada pertanyaan yang cocok.');

        for (const question of questions) {
            const row = tbody.insertRow();
            row.insertCell().textContent = question.sort_order;
            row.insertCell().textContent = question.question;
            const list = element('ul', '', 'ps-5 mb-0');
            for (const [label, score, warning] of question.answer_options) {
                const item = element('li', label + ' · ');
                item.append(warning
                    ? element('span', 'Tanda peringatan', 'badge badge-light-danger')
                    : document.createTextNode(score === null ? 'Tanpa skor' : score));
                list.append(item);
            }
            row.insertCell().append(list);
            const active = String(question.active) === '1';
            row.insertCell().append(element('span', active ? 'Aktif' : 'Nonaktif', active ? 'badge badge-light-success' : 'badge badge-light-warning'));
            const actions = row.insertCell();
            actions.className = 'text-end';
            if (instrument.writable) {
                const group = element('div', '', 'btn-group');
                const toggle = element('button', 'Actions', 'btn btn-light-primary btn-sm dropdown-toggle');
                toggle.type = 'button';
                toggle.setAttribute('data-bs-toggle', 'dropdown');
                toggle.setAttribute('aria-expanded', 'false');
                const menu = element('div', '', 'dropdown-menu dropdown-menu-end');
                const edit = element('button', '', 'dropdown-item btn btn-sm text-primary');
                const icon = element('i', '', 'bi bi-pencil text-primary me-4');
                icon.setAttribute('aria-hidden', 'true');
                edit.append(icon, document.createTextNode('Edit'));
                edit.type = 'button';
                edit.addEventListener('click', () => withStoredInstrument((stored) => {
                    const savedQuestion = question.question_id ? question : stored.questions.find((item) =>
                        Number(item.sort_order) === Number(question.sort_order) && item.question === question.question
                    );
                    if (!savedQuestion) throw new Error('Daftar pertanyaan sudah berubah. Pilih pertanyaan dari daftar terbaru.');
                    editQuestion(savedQuestion);
                }));
                menu.append(edit);
                group.append(toggle, menu);
                actions.append(group);
            }
        }
    }

    async function load(selected = select.value) {
        const payload = await request('data');
        instruments = payload.responseResult;
        get('add-instrument').disabled = !instruments.some(({writable}) => writable);
        select.replaceChildren(...instruments.map((instrument) =>
            new Option(instrument.name + (String(instrument.active) === '0' ? ' (Nonaktif)' : ''), instrument.code)
        ));
        if (instruments.some(({code}) => code === selected)) select.value = selected;
        select.disabled = false;
        render();
        return payload.responseDesc;
    }

    function openForm(kind, values) {
        const form = get(kind + '-form');
        form.reset();
        message(form.querySelector('[role=alert]'), '', 'alert-danger');
        for (const [key, value] of Object.entries(values)) form.elements.namedItem(key).value = value;
        modal(kind).show();
    }

    function addOption(label = '', score = '', warning = false) {
        const row = element('div', '', 'd-flex flex-wrap gap-2 mb-2');
        const answer = element('input', '', 'form-control form-control-solid flex-grow-1 w-auto');
        answer.name = 'label[]';
        answer.defaultValue = label;
        answer.maxLength = 500;
        answer.placeholder = 'Pilihan jawaban';
        answer.setAttribute('aria-label', 'Pilihan jawaban');
        answer.required = true;

        const points = element('input', '', 'form-control form-control-solid w-100px');
        points.type = 'number';
        points.name = 'score[]';
        points.defaultValue = score ?? '';
        points.min = current().code === 'IASTO' ? 1 : 0;
        points.max = current().custom ? 999999 : (current().code === 'IASTO' ? 4 : 3);
        points.placeholder = 'Skor';
        points.setAttribute('aria-label', 'Skor');
        points.required = true;

        const remove = element('button', 'Hapus', 'btn btn-light-danger');
        remove.type = 'button';
        remove.addEventListener('click', () => {
            if (options.children.length > 2) row.remove();
        });
        row.append(answer);
        if (current().custom) {
            const type = element('select', '', 'form-select form-select-solid w-auto');
            type.name = 'answer_type[]';
            type.setAttribute('aria-label', 'Jenis penilaian jawaban');
            const currentType = warning ? 'warning' : (score === null ? 'unscored' : 'score');
            for (const [value, title] of [['score', 'Skor'], ['unscored', 'Tanpa skor'], ['warning', 'Tanda peringatan']]) {
                type.add(new Option(title, value, value === currentType, value === currentType));
            }
            const updateScore = () => {
                const usesScore = type.value === 'score';
                points.classList.toggle('d-none', !usesScore);
                points.required = usesScore;
                if (!usesScore) points.value = '';
            };
            type.addEventListener('change', updateScore);
            updateScore();
            row.append(type);
        }
        row.append(points, remove);
        options.append(row);
    }

    function editQuestion(question) {
        const instrument = current();
        const next = Math.min(9999, Math.max(0, ...instrument.questions.map((q) => Number(q.sort_order))) + 1);
        get('question-title').textContent = question ? 'Edit pertanyaan' : 'Tambah pertanyaan';
        get('answer-type-help').classList.toggle('d-none', !instrument.custom);
        options.replaceChildren();
        const answers = question ? question.answer_options : [['', ''], ['', '']];
        for (const [label, score, warning] of answers) addOption(label, score, warning);
        openForm('question', {
            code: instrument.code,
            question_id: question ? question.question_id : '',
            question: question ? question.question : '',
            sort_order: question ? question.sort_order : next,
            active: question ? question.active : '1',
        });
    }

    async function withStoredInstrument(open) {
        if (busy) return;
        const instrument = current();
        if (!instrument.writable) return;
        if (instrument.initialized) {
            open(instrument);
            return;
        }

        busy = true;
        select.disabled = true;
        message(get('message'), '', 'alert-danger');
        try {
            const body = new FormData();
            body.set('code', instrument.code);
            await request('initialize', body);
            await load();
            open(current());
        } catch (error) {
            message(get('message'), error.message, 'alert-danger');
        } finally {
            select.disabled = false;
            busy = false;
        }
    }

    async function save(action, body, button, kind = '') {
        if (busy) return;
        busy = true;
        button.disabled = true;
        const errorNode = kind ? get(kind + '-form').querySelector('[role=alert]') : get('message');
        message(errorNode, '', 'alert-danger');
        try {
            const payload = await request(action, body);
            if (kind) modal(kind).hide();
            message(get('message'), payload.responseDesc, 'alert-success');
            try {
                await load(payload.instrumentCode || select.value);
            } catch (error) {
                message(get('message'), 'Data tersimpan. ' + error.message, 'alert-warning');
            }
        } catch (error) {
            message(errorNode, error.message, 'alert-danger');
        } finally {
            button.disabled = false;
            busy = false;
        }
    }

    select.addEventListener('change', render);
    for (const button of get('status').querySelectorAll('button')) {
        button.addEventListener('click', () => {
            const previous = get('status').querySelector('.active');
            previous.classList.remove('active');
            previous.setAttribute('aria-pressed', 'false');
            button.classList.add('active');
            button.setAttribute('aria-pressed', 'true');
            status = button.dataset.status;
            render();
        });
    }

    get('initialize').addEventListener('click', (event) => {
        const body = new FormData();
        body.set('code', current().code);
        save('initialize', body, event.currentTarget);
    });
    get('add-instrument').addEventListener('click', () => {
        if (!busy) openForm('create-instrument', {});
    });
    get('edit-instrument').addEventListener('click', () => withStoredInstrument(({code, name, active, risks, custom}) => {
        const values = {code, name, active};
        const riskFields = get('instrument-risks');
        get('optional-risks').classList.toggle('d-none', !custom);
        for (const input of riskFields.querySelectorAll('input')) {
            input.required = !custom && input.name !== 'risk_min_0';
        }
        risks.forEach(([min, max], index) => {
            values['risk_min_' + index] = min ?? '';
            values['risk_max_' + index] = max;
        });
        openForm('instrument', values);
    }));
    get('add-question').addEventListener('click', () => withStoredInstrument(() => editQuestion()));
    get('add-option').addEventListener('click', () => {
        if (options.children.length < 20) addOption();
    });

    for (const [kind, action] of [['create-instrument', 'createinstrument'], ['instrument', 'saveinstrument'], ['question', 'savequestion']]) {
        const form = get(kind + '-form');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            save(action, new FormData(form), form.querySelector('[type=submit]'), kind);
        });
    }

    get('preview').addEventListener('click', () => {
        const instrument = current();
        const content = get('preview-content');
        content.replaceChildren(element('h4', instrument.name, 'mb-4'));
        const questions = instrument.questions.filter((q) => String(q.active) === '1');
        if (!questions.length) content.append(element('p', 'Tidak ada pertanyaan aktif.', 'text-muted'));
        for (const question of questions) {
            const fieldset = element('fieldset', '', 'mb-5');
            fieldset.append(element('legend', question.sort_order + '. ' + question.question, 'fs-5 fw-bold'));
            for (const [label] of question.answer_options) {
                const choice = element('label', '', 'form-check mb-2');
                const input = element('input', '', 'form-check-input');
                input.type = 'radio';
                input.name = 'ca-preview-' + question.sort_order;
                choice.append(input, element('span', label, 'form-check-label'));
                fieldset.append(choice);
            }
            content.append(fieldset);
        }
        modal('preview').show();
    });

    load().then((note) => {
        if (note) message(get('message'), note, 'alert-warning');
    }).catch((error) => {
        message(get('message'), error.message, 'alert-danger');
        empty('Data tidak tersedia.');
    });
});
