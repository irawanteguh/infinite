(function () {
    'use strict';
    const nf = new Intl.NumberFormat('id-ID', {maximumFractionDigits:0});
    const num = value => Number(value) || 0;
    const money = value => value == null ? '—' : 'Rp ' + nf.format(num(value));
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const date = value => new Date(value.slice(0, 10) + 'T00:00:00').toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'});
    let details = [], request = null, generation = 0;

    function updateSummaryLink() {
        const link = document.getElementById('vent-summary-link');
        const target = new URL(link.href);
        target.searchParams.set('year', $('#selectperiode').val());
        const month = $('#vent-month').val();
        if (month) target.searchParams.set('month', month);
        else target.searchParams.delete('month');
        link.href = target.href;
    }

    function showStatus(message) {
        $('#vent-status').text(message).prop('hidden', false);
    }

    window.load = function () {
        const token = ++generation;
        if (request) request.abort();
        details = [];
        $('#vent-content').prop('hidden', true);
        $('#vent-refresh').prop('disabled', true);
        showStatus('Memuat daftar kasus ventilator…');

        request = $.ajax({
            url: url + 'ur/ventilator/cases',
            type: 'POST',
            dataType: 'json',
            data: {selectperiode: $('#selectperiode').val()}
        }).done(function (response) {
            if (token !== generation) return;
            if (response.responseCode !== '00') {
                showStatus(response.responseDesc || 'Daftar kasus ventilator gagal dimuat.');
                return;
            }
            details = Array.isArray(response.responseResult) ? response.responseResult : [];
            $('#vent-status').prop('hidden', true);
            $('#vent-content').prop('hidden', false);
            renderCases();
        }).fail(function (xhr, state) {
            if (token === generation && state !== 'abort') {
                showStatus(xhr.responseJSON?.responseDesc || 'Daftar kasus ventilator gagal dimuat. Klik Perbarui untuk mencoba kembali.');
            }
        }).always(function () {
            if (token === generation) $('#vent-refresh').prop('disabled', false);
        });
    };

    function renderCases() {
        const month = $('#vent-month').val();
        const review = $('#vent-review').val();
        const cases = details.filter(row =>
            (!month || num(row.month) === num(month)) &&
            (!review || (review === 'normal' ? !row.review_status : row.review_status === review))
        );
        if ($.fn.DataTable.isDataTable('#vent-cases-table')) $('#vent-cases-table').DataTable().destroy();
        const codes = value => esc(value || '—').split(';').join(', ');
        $('#vent-cases').html(cases.map((row, index) => {
            const status = row.review_status;
            return '<tr class="' + (status ? 'table-' + status : '') + '"><td>' + (index + 1) +
                '</td><td>' + esc(row.sep || '—') + '</td><td>' + esc(row.mrn || '—') +
                '</td><td>' + esc(row.nama_pasien || '—') + '</td><td>' + esc(row.dpjp || '—') +
                '</td><td>' + (row.admission_date ? esc(date(row.admission_date)) : '—') +
                '</td><td>' + (row.discharge_date ? esc(date(row.discharge_date)) : '—') +
                '</td><td>' + esc(row.los ?? '—') +
                '</td><td><button type="button" class="btn btn-sm btn-light-primary vent-case-detail" data-index="' +
                details.indexOf(row) + '" aria-expanded="false"><i class="bi bi-chevron-down" aria-hidden="true"></i><span>Detail</span></button></td></tr>';
        }).join(''));
        initDataTable('#vent-cases-table', null, 10, {order:[], columnDefs:[{targets:[0,8],orderable:false}], language:{emptyTable:'Tidak ada kasus pada filter ini.'}});
    }
    $('#vent-cases').on('click', '.vent-case-detail', function () {
        const button = $(this);
        const episode = details[Number(button.attr('data-index'))];
        if (!episode) return;
        const table = $('#vent-cases-table').DataTable();
        const row = table.row(button.closest('tr'));
        const opened = row.child.isShown();
        const field = (label, value) => '<div class="mal-detail-field"><dt>' + label + '</dt><dd>' + value + '</dd></div>';
        const codes = value => String(value ?? '').split(';').map(code => code.trim()).filter(Boolean).map(esc).join(', ') || '—';
        const diagnoses = String(episode.diaglist || '').split(';');
        if (opened) row.child.hide();
        else {
            const review = episode.review_status
                ? '<div class="alert alert-' + episode.review_status + ' mb-3">Periksa kesesuaian kode dan jam ventilator.</div>' : '';
            row.child('<div class="mal-detail"><div class="mal-detail-heading"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i> Rincian episode · ' +
                esc(episode.sep) + '</div>' + review + '<dl class="mal-detail-grid">' +
                field('Nama pasien', esc(episode.nama_pasien || '—')) +
                field('Nomor SEP', esc(episode.sep || '—')) +
                field('Nomor rekam medis', esc(episode.mrn || '—')) +
                field('DPJP', esc(episode.dpjp || '—')) +
                field('Tanggal masuk', episode.admission_date ? esc(date(episode.admission_date)) : '—') +
                field('Tanggal pulang', episode.discharge_date ? esc(date(episode.discharge_date)) : '—') +
                field('Lama perawatan (LOS)', esc(episode.los ?? '—') + ' hari') +
                field('Total INA-CBG', money(episode.total_tarif)) +
                field('Tarif RS', money(episode.tarif_rs)) +
                field('Jam ventilator', esc(episode.vent_hour ?? '—')) +
                '</dl><dl class="mal-detail-codes">' + field('Diagnosis utama', esc(diagnoses[0] || '—')) +
                field('Diagnosis sekunder', codes(diagnoses.slice(1).join(';'))) + field('Prosedur', codes(episode.proclist)) +
                field('Catatan telaah', 'Periksa indikasi penggunaan ventilator dan diagnosis gagal napas.') +
                '</dl></div>', 'mal-detail-row').show();
        }
        button.attr('aria-expanded', String(!opened));
        button.find('span').text(opened ? 'Detail' : 'Tutup');
        button.find('i').toggleClass('bi-chevron-down', opened).toggleClass('bi-chevron-up', !opened);
    });
    $('#selectperiode').on('change', () => { updateSummaryLink(); window.load(); });
    $('#vent-month').on('change', () => { updateSummaryLink(); renderCases(); });
    $('#vent-review').on('change', renderCases);
    $('#vent-refresh').on('click', window.load);
    $('#vent-print').on('click', () => window.print());
    updateSummaryLink();
    window.load();
})();