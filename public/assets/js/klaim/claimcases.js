(function () {
    'use strict';
    const page = document.querySelector('[data-case-module]');
    const module = page.dataset.caseModule;
    const nf = new Intl.NumberFormat('id-ID', {maximumFractionDigits:0});
    const compact = new Intl.NumberFormat('id-ID', {notation:'compact',maximumFractionDigits:2});
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const text = value => esc(value || '—');
    const money = value => value == null || value === '' ? '—' : 'Rp ' + nf.format(Number(value));
    const service = value => ({'1':'Rawat inap','2':'Rawat jalan'}[String(value).trim()] || '—');
    const status = row => String(row.status || '').trim().toUpperCase();
    const body = $('#resultdataraweklaim');
    let rows = [], request = null, generation = 0, chart = null, valueChart = null;
    function badge(row) {
        const label = status(row);
        const tone = label === 'PENDING' ? 'case-status-pending' : label === 'TIDAK LAYAK' ? 'case-status-rejected' : 'badge-light';
        return '<span class="badge ' + tone + '">' + text(label) + '</span>';
    }
    function filtered() {
        const month = $('#case-month').val(), care = $('#case-service').val(), claimStatus = $('#case-status').val();
        return rows.filter(row => (!month || Number(row.periode) === Number(month))
            && (!care || String(row.jnspel).trim() === care) && (!claimStatus || status(row) === claimStatus));
    }
    function render() {
        const table = $('#dataraweklaim_table');
        if ($.fn.DataTable.isDataTable(table[0])) table.DataTable().destroy();
        const data = filtered();
        body.html(data.map((row, index) => '<tr><td>' + nf.format(index+1) + '</td><td>' + text(row.sep) +
            '</td><td>' + text(row.nama_pasien) + '</td><td>' + service(row.jnspel) +
            '</td><td>' + text(row.discharge_date) + '</td><td>' + money(row.pengajuan) + '</td><td>' + badge(row) +
            '</td><td><button type="button" class="btn btn-sm btn-light-primary case-detail-toggle" data-index="' +
            rows.indexOf(row) + '" aria-expanded="false"><i class="bi bi-chevron-down" aria-hidden="true"></i><span>Detail</span></button></td></tr>').join(''));
        initDataTable('#dataraweklaim_table','#searchtable',10,{
            order:[],columnDefs:[{targets:[0,7],orderable:false}],
            dom:"<'app-datatable-scroll't><'app-table-footer'<'app-table-meta'li>p>",
            language:{emptyTable:'Tidak ada klaim sesuai filter.'}
        });
        if (chart) { chart.destroy(); chart = null; }
        if (valueChart) { valueChart.destroy(); valueChart = null; }
        if (typeof ApexCharts !== 'undefined') {
            const countSeries = [{name:'Klaim',data:months.map((_,index)=>data.filter(row=>Number(row.periode)===index+1).length)}];
            chart = new ApexCharts(document.getElementById('trencase'), klaimLineChartOptions(
                months, countSeries, ['#087e82'], value => nf.format(value), value => nf.format(value) + ' klaim'
            ));
            chart.render();
            const hasPengajuan = row => row.pengajuan != null && row.pengajuan !== '';
            const financial = new Map();
            data.forEach(row => {
                const key = row.periode + ':' + row.sep;
                const existing = financial.get(key);
                if (!existing || (!hasPengajuan(existing) && hasPengajuan(row))) financial.set(key, row);
            });
            const values = [...financial.values()];
            const total = month => {
                const items=values.filter(row=>Number(row.periode)===month && hasPengajuan(row));
                return items.reduce((sum,row)=>sum+Number(row.pengajuan),0);
            };
            const amountSeries = [{name:'Nilai pengajuan',data:months.map((_,index)=>total(index+1))}];
            valueChart = new ApexCharts(document.getElementById('case-value-chart'), klaimLineChartOptions(
                months, amountSeries, ['#087e82'], value => compact.format(value), money
            ));
            valueChart.render();
            $('#case-value-note').text(nf.format(values.filter(row=>!hasPengajuan(row)).length)+' SEP belum memiliki nilai pengajuan.');
        }
    }
    function detail(row) {
        const field = (label, value, wide = false) => '<div class="' + (wide ? 'case-detail-wide' : '') +
            '"><dt>' + label + '</dt><dd>' + value + '</dd></div>';
        return '<div class="case-detail"><div class="case-detail-heading"><i class="bi bi-file-earmark-medical me-2" aria-hidden="true"></i>Rincian klaim · ' +
            text(row.sep) + '</div><dl class="case-detail-grid">' +
            field('Nama pasien',text(row.nama_pasien)) + field('Nomor SEP',text(row.sep)) + field('Jenis layanan',service(row.jnspel)) +
            field('Tanggal masuk',text(row.admission_date)) + field('Tanggal pulang',text(row.discharge_date)) +
            field('Tanggal verifikasi',text(row.verification_date)) + field('Kode INA-CBG',text(row.kdinacbg)) +
            field('Severity',text(row.sl)) + field('Nilai pengajuan',money(row.pengajuan)) + field('Tagihan RS',money(row.tarif_rs)) + field('Nilai INA-CBG',money(row.nilai_inacbg)) +
            field('Status klaim',badge(row)) + field('Jenis verifikasi',text(row.algoritma_type)) +
            field('Algoritma verifikasi',text(String(row.algoritma || '').split('|').map(item=>item.trim()).filter(Boolean).join(', ')),true) +
            field('Catatan verifikator',text(row.keterangan_verifikasi),true) + '</dl></div>';
    }
    body.on('click','.case-detail-toggle',function () {
        const button = $(this), episode = rows[Number(button.attr('data-index'))];
        if (!episode) return;
        const row = $('#dataraweklaim_table').DataTable().row(button.closest('tr'));
        const opened = row.child.isShown();
        if (opened) row.child.hide();
        else row.child(detail(episode),'case-detail-row').show();
        button.attr('aria-expanded',String(!opened));
        button.find('span').text(opened?'Detail':'Tutup');
        button.find('i').toggleClass('bi-chevron-down',opened).toggleClass('bi-chevron-up',!opened);
    });
    function load() {
        const token = ++generation;
        if (request) request.abort();
        rows = []; render();
        $('#case-message').text('');
        showDataLoading();
        $('#klaim-case-refresh').prop('disabled',true);
        request = $.ajax({
            url:url+'klaim/'+module+'/dataraweklaimdetail',type:'POST',dataType:'json',
            data:{selectperiode:$('#selectperiode').val()}
        }).done(response=>{
            if(token!==generation)return;
            if(response.responseCode!=='00'){
                $('#case-message').text(response.responseDesc||'Belum ada klaim pada periode ini.');
                return;
            }
            rows=Array.isArray(response.responseResult)?response.responseResult:[];
            $('#case-message').text('');render();
        }).fail((xhr,state)=>{
            if(token===generation&&state!=='abort')$('#case-message').text(xhr.responseJSON?.responseDesc||'Data gagal dimuat. Klik Perbarui untuk mencoba kembali.');
        }).always(()=>{if(token===generation)$('#klaim-case-refresh').prop('disabled',false);});
    }
    $('#selectperiode').on('change',load);
    $('#case-month,#case-service,#case-status').on('change',render);
    $('#klaim-case-refresh').on('click',load);
    load();
})();
