(function () {
    'use strict';
    const selector = '#dataraweklaim_table';
    const nf = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const numeric = value => Number.isFinite(Number(value)) ? Number(value) : 0;
    const money = value => 'Rp ' + nf.format(numeric(value));
    const text = value => esc(value || '—');
    const codes = value => value ? esc(value).split(';').join('<br>') : '—';
    let request = null, generation = 0, tableApi = null, refreshSummary = true;
    const status = (message, error = false) => $('#ek-status').text(message).toggleClass('is-error', error);

    function summary(value) {
        $('#ek-count').text(value ? nf.format(numeric(value.episodes)) : '—');
        [['hospital','tarif_rs'],['cbg','total_tarif'],['idrg','idrg_total_tarif']].forEach(([id, key]) => $('#ek-' + id).text(value ? money(value[key]) : '—'));
    }

    function service(row) {
        return String(row.ptd) === '1' ? '<span class="ek-service ek-service-inpatient">Rawat inap</span>' : String(row.ptd) === '2' ? '<span class="ek-service ek-service-outpatient">Rawat jalan</span>' : '—';
    }

    function details(row, index) {
        const field = (label, value) => '<div class="ek-detail-field"><dt>' + label + '</dt><dd>' + value + '</dd></div>';
        return '<div class="ek-detail" id="ek-detail-' + index + '"><div class="ek-detail-heading"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i> Rincian episode · ' + text(row.nama_pasien) + '</div><dl class="ek-detail-grid">' +
            field('Nomor SEP', text(row.sep)) + field('Nomor kartu', text(row.nokartu)) + field('Nomor rekam medis', text(row.mrn)) + field('DPJP', text(row.dpjp)) +
            field('Tanggal masuk', text(row.admission_date)) + field('Tanggal pulang', text(row.discharge_date)) + field('Lama perawatan (LOS)', esc(row.los ?? '—') + ' hari') + field('Jenis layanan', service(row)) +
            field('Diagnosis', codes(row.diaglist)) + field('Prosedur', codes(row.proclist)) + '</dl></div>';
    }

    function loadPage(data, callback) {
        const current = ++generation;
        if (request) request.abort();
        const period = $('#selectperiode').val();
        const selectedService = $('#ek-service').val() || '';
        const serviceLabel = selectedService === '1' ? 'Rawat inap' : selectedService === '2' ? 'Rawat jalan' : 'Semua layanan';
        const fresh = refreshSummary;
        refreshSummary = false;
        $('#ek-refresh').prop('disabled', true);
        $(selector).attr('aria-busy', 'true');
        status('Memuat data periode ' + period + '…');
        request = $.ajax({
            url:url + 'ur/dataeklaim/dataraweklaim', type:'POST', dataType:'json', timeout:45000,
            data:Object.assign({}, data, {selectperiode:period, service:selectedService, refreshSummary:fresh ? '1' : '0'}),
            success:function(response) {
                if (current !== generation) return;
                if (!response || response.responseCode !== '00' || !Array.isArray(response.data)) {
                    fail('Respons data tidak dapat dibaca. Silakan muat ulang.'); return;
                }
                summary(response.summary);
                status('');
                callback(response);
            },
            error:function(xhr, state) {
                if (state === 'abort' || current !== generation) return;
                const message = xhr.responseJSON?.responseDesc || (state === 'timeout' ? 'Pemuatan data terlalu lama. Silakan coba muat ulang.' : xhr.status === 200 ? 'Sesi atau respons server tidak valid. Silakan masuk kembali.' : 'Gagal memuat data. Silakan coba muat ulang.');
                fail(message);
            },
            complete:function() {
                if (current !== generation) return;
                $(selector).attr('aria-busy', 'false');
                $('#ek-refresh').prop('disabled', false);
                request = null;
            }
        });
        function fail(message) {
            summary(null); refreshSummary = true;
            status(message, true);
            callback({draw:data.draw,recordsTotal:0,recordsFiltered:0,data:[]});
        }
    }

    function load() {
        refreshSummary = true;
        summary(null);
        if (tableApi) { tableApi.ajax.reload(null, true); return; }
        tableApi = initDataTable(selector, '#searchtable', 10, {
            serverSide:true, processing:false, deferRender:true, searchDelay:400,
            lengthMenu:[10,25,50,100], ordering:false,
            dom:"<'ek-datatable-scroll't><'app-table-footer'<'app-table-meta'li>p>",
            ajax:loadPage,
            initComplete:function() {
                $(this.api().table().container()).find('.ek-datatable-scroll').attr({tabindex:0,role:'region','aria-label':'Tabel e-klaim, geser untuk melihat seluruh kolom'});
            },
            columns:[
                {data:null,render:(value,type,row,meta)=>meta.settings._iDisplayStart + meta.row + 1},
                {data:'nama_pasien',render:value=>'<span class="ek-patient">' + text(value) + '</span>'},
                {data:'mrn',className:'ek-rm',render:value=>text(value)},
                {data:'sep',className:'ek-sep',render:value=>text(value)},
                {data:'admission_date',className:'ek-date',render:value=>text(value)},
                {data:'discharge_date',className:'ek-date',render:value=>text(value)},
                {data:'dpjp',className:'ek-dpjp',render:value=>text(value)},
                {data:null,render:(value,type,row)=>service(row)},
                {data:'tarif_rs',className:'text-end',render:value=>money(value)},
                {data:'total_tarif',className:'text-end',render:value=>money(value)},
                {data:'idrg_total_tarif',className:'text-end',render:value=>money(value)},
                {data:null,className:'text-end',searchable:false,orderable:false,render:(value,type,row,meta)=>'<button type="button" class="btn btn-sm btn-light-primary ek-toggle" aria-expanded="false" aria-controls="ek-detail-' + meta.row + '" aria-label="Lihat rincian ' + text(row.nama_pasien) + '"><i class="bi bi-chevron-down" aria-hidden="true"></i><span>Detail</span></button>'}
            ],
            language:{emptyTable:'Belum ada data pada periode ini.',zeroRecords:'Tidak ada data yang sesuai pencarian.',processing:'Memuat data…'}
        });
    }
    $(selector).on('click', '.ek-toggle', function () {
        if (!tableApi) return;
        const button = $(this), row = tableApi.row(button.closest('tr'));
        if (!row.data()) return;
        const opened = row.child.isShown();
        if (opened) row.child.hide();
        else row.child(details(row.data(), row.index()), 'ek-detail-row').show();
        button.attr('aria-controls', 'ek-detail-' + row.index()).attr('aria-expanded', String(!opened));
        button.find('span').text(opened ? 'Detail' : 'Tutup');
        button.find('i').toggleClass('bi-chevron-down', opened).toggleClass('bi-chevron-up', !opened);
        button.closest('tr').toggleClass('ek-row-open', !opened);
    });
    window.dataraweklaim = load;
    $('#selectperiode,#ek-service').on('change', load);
    $('#ek-refresh').on('click', load);
    load();
})();
