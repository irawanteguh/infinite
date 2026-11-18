(function () {
    'use strict';
    const nf = new Intl.NumberFormat('id-ID');
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const text = value => esc(value || '—');
    const money = value => value == null ? '—' : 'Rp ' + nf.format(Number(value));
    const services = {'1':'Rawat inap', '2':'Rawat jalan'};
    const tariffType = document.getElementById('ur-executive').dataset.tariffType;
    const positive = tariffType === 'positif';
    const toolbarPrefix = positive ? 'tp' : 'tn';
    const gapField = positive ? 'positive_gap' : 'negative_gap';
    const gapClass = positive ? 'ur-positive' : 'ur-negative';
    const refreshButton = $('#' + toolbarPrefix + '-refresh');
    const scopeLink = document.getElementById('tarif-scope-link');
    const body = $('#tp-cases-rows');
    let rows = [], offset = 0, request = null, generation = 0, searchTimer;

    function scope() {
        return {year:$('#selectperiode').val(), month:$('#tp-month').val(), service:$('#tp-service').val()};
    }

    function updateScopeLink() {
        const target = new URL(scopeLink.href);
        Object.entries(scope()).forEach(([key, value]) => {
            if (value) target.searchParams.set(key, value);
            else target.searchParams.delete(key);
        });
        scopeLink.href = target.href;
    }

    function showStatus(message) {
        $('#tp-cases-status').text(message).prop('hidden', !message);
    }

    function renderPagination(total, limit) {
        const last = Math.ceil(total / limit);
        const current = Math.floor(offset / limit) + 1;
        let pages;
        if (last <= 7) {
            pages = Array.from({length:last}, (_, index) => index + 1);
        } else if (current <= 4) {
            pages = [1, 2, 3, 4, 5, null, last];
        } else if (current >= last - 3) {
            pages = [1, null, last - 4, last - 3, last - 2, last - 1, last];
        } else {
            pages = [1, null, current - 1, current, current + 1, null, last];
        }

        $('#tp-case-prev-item').siblings('.tp-case-page-item').remove();
        $('#tp-case-prev-item').after(pages.map(page => {
            if (page === null) {
                return '<li class="tp-case-page-item page-item disabled" aria-hidden="true"><span class="page-link">…</span></li>';
            }
            if (page === current) {
                return '<li class="tp-case-page-item page-item active"><span class="page-link" aria-current="page">' + page + '</span></li>';
            }
            return '<li class="tp-case-page-item page-item"><button type="button" class="page-link" data-page="' +
                page + '" aria-label="Halaman ' + page + '">' + page + '</button></li>';
        }).join(''));
        $('#tp-case-prev').prop('disabled', current <= 1);
        $('#tp-case-next').prop('disabled', current >= last);
    }

    function renderCases(result) {
        rows = Array.isArray(result.rows) ? result.rows : [];
        offset = Number(result.offset);
        const total = Number(result.total);
        const limit = Number(result.limit);

        body.html(rows.map((row, index) => '<tr>' +
            '<td>' + nf.format(offset + index + 1) + '</td><td class="fw-semibold">' + text(row.nama_pasien) + '</td>' +
            '<td>' + text(row.sep) + '<br><small class="text-muted">' + text(row.mrn) + '</small></td>' +
            '<td>' + text(row.dpjp) + '</td><td>' + text(services[row.ptd]) + '</td><td>' + text(row.discharge_date) + '</td>' +
            '<td class="text-end text-nowrap">' + money(row.tarif_rs) + '</td><td class="text-end text-nowrap">' + money(row.total_tarif) + '</td>' +
            '<td class="text-end text-nowrap ' + gapClass + ' fw-bold">' + money(row[gapField]) + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-light-primary tp-case-detail" data-index="' + index +
            '" aria-expanded="false"><i class="bi bi-chevron-down" aria-hidden="true"></i><span>Detail</span></button></td></tr>').join(''));

        showStatus(total ? '' : 'Tidak ada kasus tarif ' + tariffType + ' pada filter ini.');
        const first = total ? offset + 1 : 0;
        $('#tp-case-count').text('Showing ' + first + ' to ' + (offset + rows.length) + ' of ' + total + ' records');
        renderPagination(total, limit);
    }

    function loadCases(start = 0) {
        clearTimeout(searchTimer);
        const token = ++generation;
        if (request) request.abort();
        body.empty();
        $('#tp-case-count').empty();
        $('#tp-case-prev-item').siblings('.tp-case-page-item').remove();
        refreshButton.prop('disabled', true);
        $('#tp-case-prev,#tp-case-next').prop('disabled', true);
        showStatus('Memuat daftar kasus tarif ' + tariffType + '…');
        const {year, month, service} = scope();

        request = $.ajax({
            url: url + 'ur/tarif' + tariffType + '/cases',
            type: 'POST',
            dataType: 'json',
            data: {
                selectperiode: year,
                month,
                service,
                search: $('#tp-cases-search').val().trim(),
                sort: $('#tp-sort').val(),
                offset: start,
                limit: $('#tp-case-limit').val()
            }
        }).done(function (response) {
            if (token !== generation) return;
            if (response.responseCode === '00') renderCases(response.responseResult);
            else showStatus(response.responseDesc || 'Daftar kasus gagal dimuat.');
        }).fail(function (xhr, state) {
            if (token === generation && state !== 'abort') {
                showStatus(xhr.responseJSON?.responseDesc || 'Daftar kasus gagal dimuat. Klik Perbarui untuk mencoba kembali.');
            }
        }).always(function () {
            if (token === generation) refreshButton.prop('disabled', false);
        });
    }

    function episodeDetail(row) {
        const field = (label, value) => '<div><dt>' + label + '</dt><dd>' + value + '</dd></div>';
        const codes = value => String(value ?? '').split(';').map(code => code.trim()).filter(Boolean).map(esc).join(', ') || '—';
        return '<div class="tp-episode"><div class="fw-semibold mb-3"><i class="bi bi-file-earmark-medical me-2" aria-hidden="true"></i>Rincian episode · ' + text(row.sep) + '</div>' +
            '<dl class="tp-episode-grid">' + field('Tanggal masuk', text(row.admission_date)) +
            field('Tanggal pulang', text(row.discharge_date)) + field('IDRG', money(row.idrg_total_tarif)) + '</dl>' +
            '<dl class="tp-episode-codes">' + field('Diagnosis', codes(row.diaglist)) +
            field('Prosedur', codes(row.proclist)) + '</dl></div>';
    }

    body.on('click', '.tp-case-detail', function () {
        const button = $(this);
        const index = Number(button.attr('data-index'));
        const row = button.closest('tr');
        const expanded = button.attr('aria-expanded') === 'true';
        if (!rows[index]) return;

        if (expanded) row.next('.tp-episode-row').remove();
        else row.after('<tr class="tp-episode-row"><td colspan="10">' + episodeDetail(rows[index]) + '</td></tr>');
        button.attr('aria-expanded', String(!expanded));
        button.find('span').text(expanded ? 'Detail' : 'Tutup');
        button.find('i').toggleClass('bi-chevron-down', expanded).toggleClass('bi-chevron-up', !expanded);
    });

    $('#selectperiode,#tp-month,#tp-service').on('change', () => { updateScopeLink(); loadCases(); });
    $('#tp-sort,#tp-case-limit').on('change', () => loadCases());
    $('#tp-cases-search').on('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadCases(), 250);
    });
    $('#tp-case-prev-item').parent().on('click', '[data-page]', function () {
        loadCases((Number(this.dataset.page) - 1) * Number($('#tp-case-limit').val()));
    });
    $('#tp-case-prev').on('click', () => loadCases(Math.max(0, offset - Number($('#tp-case-limit').val()))));
    $('#tp-case-next').on('click', () => loadCases(offset + Number($('#tp-case-limit').val())));
    refreshButton.on('click', () => loadCases(offset));
    $('#' + toolbarPrefix + '-print').on('click', () => window.print());
    window.load = () => loadCases();
    updateScopeLink();
    window.load();
})();
