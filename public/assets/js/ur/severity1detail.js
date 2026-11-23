(function () {
    'use strict';
    const nf = new Intl.NumberFormat('id-ID', {maximumFractionDigits:0});
    const num = value => Number(value) || 0;
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const date = value => new Date(value.split('.').reverse().join('-') + 'T00:00:00').toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'});
    let details = [], request = null, generation = 0;

    function updateSummaryLink() {
        const link = document.getElementById('s1-summary-link');
        const target = new URL(link.href);
        target.searchParams.set('year', $('#selectperiode').val());
        const month = $('#s1-month').val();
        if (month) target.searchParams.set('month', month);
        else target.searchParams.delete('month');
        link.href = target.href;
    }

    function showStatus(message) {
        $('#s1-status').text(message).prop('hidden', false);
    }

    window.load = function () {
        const token = ++generation;
        if (request) request.abort();
        details = [];
        $('#s1-content').prop('hidden', true);
        $('#s1-refresh').prop('disabled', true);
        showStatus('Memuat daftar kasus severity I…');

        request = $.ajax({
            url: url + 'ur/severity1/cases',
            type: 'POST',
            dataType: 'json',
            data: {selectperiode: $('#selectperiode').val()}
        }).done(function (response) {
            if (token !== generation) return;
            if (response.responseCode !== '00') {
                showStatus(response.responseDesc || 'Daftar kasus severity I gagal dimuat.');
                return;
            }
            details = Array.isArray(response.responseResult) ? response.responseResult : [];
            $('#s1-status').prop('hidden', true);
            $('#s1-content').prop('hidden', false);
            renderCases();
        }).fail(function (xhr, state) {
            if (token === generation && state !== 'abort') {
                showStatus(xhr.responseJSON?.responseDesc || 'Daftar kasus severity I gagal dimuat. Klik Perbarui untuk mencoba kembali.');
            }
        }).always(function () {
            if (token === generation) $('#s1-refresh').prop('disabled', false);
        });
    };

    function caseRows(){const month=$('#s1-month').val(),term=String($('#s1-case-search').val()||'').toLocaleLowerCase('id-ID');return details.filter(r=>(!month||num(r.month)===num(month))&&[r.sep,r.mrn,r.nama_pasien,r.dpjp,r.diaglist].join(' ').toLocaleLowerCase('id-ID').includes(term));}
    function renderCases(){
        if($.fn.DataTable&&$.fn.DataTable.isDataTable('#s1-case-table'))$('#s1-case-table').DataTable().destroy();
        const data=caseRows();
        $('#s1-cases').html(data.map((r,i)=>'<tr><td>'+nf.format(i+1)+'</td><td><span class="fw-semibold">'+esc(r.sep||'-')+'</span></td><td>'+esc(r.mrn||'-')+'</td><td>'+esc(r.nama_pasien||'-')+'</td><td>'+esc(r.dpjp||'-')+'</td><td>'+esc(r.admission_date?date(r.admission_date):'-')+'</td><td>'+esc(r.discharge_date?date(r.discharge_date):'-')+'</td><td><span class="badge mal-los-badge">'+esc(nf.format(num(r.los)))+' hari</span></td><td><button type="button" class="btn btn-sm btn-light-primary s1-case-detail" data-case-index="'+details.indexOf(r)+'" aria-expanded="false" aria-controls="s1-detail-'+details.indexOf(r)+'" aria-label="Detail kasus '+esc(r.sep||r.mrn||i+1)+'"><i class="bi bi-chevron-down" aria-hidden="true"></i><span>Detail</span></button></td></tr>').join(''));
        if(typeof initDataTable==='function')initDataTable('#s1-case-table',null,10,{searching:false,order:[],columnDefs:[{targets:[0,8],orderable:false}],dom:"<'app-datatable-scroll't><'app-table-footer'<'app-table-meta'li>p>",language:{emptyTable:'Tidak ada kasus yang sesuai filter.'}});
    }
    function episodeDetail(episode, index) {
        const text = value => esc(value || '—');
        const codes = value => String(value ?? '').split(';').map(code => code.trim()).filter(Boolean).map(esc).join(', ') || '—';
        const field = (label, value) => '<div class="mal-detail-field"><dt>' + label + '</dt><dd>' + value + '</dd></div>';
        const fields = [
            ['Nama pasien', text(episode.nama_pasien)],
            ['Nomor SEP', text(episode.sep)],
            ['Nomor rekam medis', text(episode.mrn)],
            ['DPJP', text(episode.dpjp)],
            ['Tanggal masuk', episode.admission_date ? text(date(episode.admission_date)) : '—'],
            ['Tanggal pulang', episode.discharge_date ? text(date(episode.discharge_date)) : '—'],
            ['Lama perawatan (LOS)', nf.format(num(episode.los)) + ' hari'],
            ['Total INA-CBG', 'Rp ' + nf.format(num(episode.total_tarif))],
            ['Tarif RS', 'Rp ' + nf.format(num(episode.tarif_rs))]
        ];

        return '<div class="mal-detail" id="s1-detail-' + index + '">' +
            '<div class="mal-detail-heading"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i> Rincian episode · ' +
            text(episode.sep || episode.mrn) + '</div>' +
            '<dl class="mal-detail-grid">' + fields.map(([label, value]) => field(label, value)).join('') + '</dl>' +
            '<dl class="mal-detail-codes">' + field('Diagnosis', codes(episode.diaglist)) +
            field('Prosedur', codes(episode.proclist)) + '</dl></div>';
    }

    $('#s1-cases').on('click','.s1-case-detail',function(){
        const index = Number($(this).attr('data-case-index'));
        const r = details[index];
        if (!r) return;

        const button=$(this),table=$('#s1-case-table').DataTable(),row=table.row(button.closest('tr')),opened=row.child.isShown();
        if (opened) row.child.hide();
        else row.child(episodeDetail(r, index), 'mal-detail-row').show();
        button.attr('aria-expanded',String(!opened));button.find('span').text(opened?'Detail':'Tutup');
        button.find('i').toggleClass('bi-chevron-down',opened).toggleClass('bi-chevron-up',!opened);
        button.closest('tr').toggleClass('mal-row-open',!opened);
    });
    $('#s1-case-search').on('input', renderCases);
    $('#selectperiode').on('change', () => { updateSummaryLink(); window.load(); });
    $('#s1-month').on('change', () => { updateSummaryLink(); renderCases(); });
    $('#s1-refresh').on('click', window.load);
    $('#s1-print').on('click', () => window.print());
    updateSummaryLink();
    window.load();
})();
