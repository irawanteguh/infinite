/* Executive UR dashboard: all calculations use server-side monthly aggregates. */
(function () {
    'use strict';
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const nf = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
    const compact = new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 2 });
    const money = value => 'Rp ' + compact.format(value);
    const percent = (part, whole) => whole ? (part / whole * 100).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + '%' : '—';
    const number = value => Number(value) || 0;
    const fields = ['jumlah_kasus','tarif_rs','total_tarif','idrg_total_tarif','negative_cases','negative_gap','total_los','los_cases','short_stay','long_sl1','short_sl3'];
    const components = {prosedur_non_bedah:'Prosedur nonbedah',prosedur_bedah:'Prosedur bedah',konsultasi:'Konsultasi',tenaga_ahli:'Tenaga ahli',keperawatan:'Keperawatan',penunjang:'Penunjang',radiologi:'Radiologi',laboratorium:'Laboratorium',pelayanan_darah:'Pelayanan darah',rehabilitasi:'Rehabilitasi',kamar_akomodasi:'Kamar & akomodasi',rawat_intensif:'Rawat intensif',obat:'Obat',alkes:'Alat kesehatan',bmhp:'BMHP',sewa_alat:'Sewa alat',obat_kronis:'Obat kronis',obat_kemo:'Obat kemoterapi'};
    let rows = [], request = null;
    const charts = {};
    function sum(data, field) { return data.reduce((total, row) => total + number(row[field]), 0); }
    function status(message) { $('#ur-status').text(message).prop('hidden', false); }
    function date(value) { return value ? new Date(value.slice(0,10) + 'T00:00:00').toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'}) : '—'; }
    function meter(label, value, total, color) {
        return '<div class="ur-meter-row"><div class="ur-meter-label"><span>' + label + '</span><strong>' + nf.format(value) + ' · ' + percent(value,total) + '</strong></div><div class="ur-meter"><span style="width:' + (total ? Math.max(0, Math.min(100,value/total*100)) : 0) + '%;background:' + color + '"></span></div></div>';
    }
    function chart(id, series, labels, currency) {
        const el = document.getElementById(id);
        if (charts[id]) { charts[id].destroy(); delete charts[id]; }
        if (typeof ApexCharts === 'undefined') { el.innerHTML = '<p class="ur-empty">Grafik tidak tersedia. Ringkasan tetap dapat dibaca pada tabel.</p>'; return; }
        charts[id] = new ApexCharts(el, {
            chart:{type:currency?'line':'bar',height:285,fontFamily:'inherit',toolbar:{show:false},animations:{enabled:false},stacked:!currency},
            series:series,colors:currency?['#93aab3','#087e82','#75b5a6']:['#087e82','#93b6c3'],
            stroke:{width:currency?[2,3,3]:0,curve:'straight',dashArray:currency?[4,0,0]:0},
            plotOptions:{bar:{columnWidth:'45%',borderRadius:3}},dataLabels:{enabled:false},
            grid:{borderColor:'#edf2f4',strokeDashArray:3,padding:{left:8,right:12}},xaxis:{categories:labels,axisBorder:{show:false},axisTicks:{show:false},labels:{style:{colors:'#7b8a9e',fontSize:'11px'}}},
            yaxis:{labels:{formatter:value=>compact.format(value),style:{colors:'#7b8a9e',fontSize:'10px'}}},legend:{position:'top',horizontalAlign:'left',fontSize:'11px',markers:{radius:3}},
            tooltip:{shared:true,intersect:false,y:{formatter:value=>currency?'Rp '+nf.format(value):nf.format(value)+' episode'}},
            noData:{text:'Tidak ada data'},markers:{size:currency?3:0},responsive:[{breakpoint:600,options:{chart:{height:260},legend:{fontSize:'10px'}}}]
        });
        charts[id].render();
    }
    function render() {
        const month = $('#ur-month').val(), service = $('#ur-service').val();
        const data = rows.filter(row => (!month || number(row.periode) === number(month)) && (!service || String(row.ptd) === service));
        $('#ur-content').prop('hidden', true);
        if (!data.length) { status('Belum ada data untuk filter ini. Ubah periode, bulan, atau jenis layanan.'); $('#ur-coverage').text('Tidak ada data pada cakupan terpilih'); return; }
        $('#ur-status').prop('hidden', true); $('#ur-content').prop('hidden', false);
        const totals = {};
        fields.forEach(field => { totals[field] = sum(data,field); });
        const inpatient = data.filter(row => String(row.ptd)==='1'), outpatient = data.filter(row => String(row.ptd)==='2');
        const gap = totals.total_tarif - totals.tarif_rs;
        const dates = data.map(row=>row.last_discharge).filter(Boolean).sort();
        const starts = data.map(row=>row.first_discharge).filter(Boolean).sort();
        const last = dates[dates.length-1];
        $('#ur-coverage').text(date(starts[0]) + ' — ' + date(last) + '  /  ' + (service==='1'?'Rawat inap':service==='2'?'Rawat jalan':'Semua layanan') + '  /  ' + nf.format(totals.jumlah_kasus) + ' episode');
        const kpis = [
            ['Episode pelayanan',nf.format(totals.jumlah_kasus),nf.format(sum(inpatient,'jumlah_kasus'))+' RI · '+nf.format(sum(outpatient,'jumlah_kasus'))+' RJ','bi-people',''],
            ['Total tarif RS',money(totals.tarif_rs),'Rata-rata '+money(totals.tarif_rs/totals.jumlah_kasus)+' / episode','bi-hospital',''],
            ['Total INA-CBG',money(totals.total_tarif),'Termasuk komponen tambahan tercatat','bi-receipt','accent'],
            ['Selisih INA-CBG − RS',money(gap),'Rasio cakupan '+percent(totals.total_tarif,totals.tarif_rs),'bi-arrow-left-right',gap<0?'ur-negative':'ur-positive'],
            ['Nilai IDRG',money(totals.idrg_total_tarif),'Selisih vs RS '+money(totals.idrg_total_tarif-totals.tarif_rs),'bi-bar-chart',''],
            ['Episode selisih negatif',nf.format(totals.negative_cases),percent(totals.negative_cases,totals.jumlah_kasus)+' dari episode terpilih','bi-flag','ur-negative']
        ];
        $('#ur-kpis').html(kpis.map(k=>'<article class="prem-card pe-kpi ur-kpi '+(k[4]==='accent'?'pe-kpi-highlight':'')+'"><div class="pe-kpi-top"><span>'+k[0]+'</span><i class="bi '+k[3]+'" aria-hidden="true"></i></div><strong class="'+(k[4]==='accent'?'':k[4])+'">'+k[1]+'</strong><p>'+k[2]+'</p></article>').join(''));
        const monthly = [...new Set(data.map(row=>number(row.periode)))].sort((a,b)=>a-b).map(m=>{
            const subset=data.filter(row=>number(row.periode)===m), item={month:m};
            fields.forEach(field=>item[field]=sum(subset,field));
            item.ri=sum(subset.filter(row=>String(row.ptd)==='1'),'jumlah_kasus'); item.rj=sum(subset.filter(row=>String(row.ptd)==='2'),'jumlah_kasus'); return item;
        });
        const labels=monthly.map(row=>months[row.month-1]);
        chart('ur-finance-chart',[{name:'Tarif RS',data:monthly.map(r=>r.tarif_rs)},{name:'Total INA-CBG',data:monthly.map(r=>r.total_tarif)},{name:'IDRG',data:monthly.map(r=>r.idrg_total_tarif)}],labels,true);
        chart('ur-visit-chart',[{name:'Rawat inap',data:monthly.map(r=>r.ri)},{name:'Rawat jalan',data:monthly.map(r=>r.rj)}],labels,false);
        const ri=sum(inpatient,'jumlah_kasus');
        $('#ur-severity').html(ri ? ['I','II','III'].map((sl,i)=>meter('Severity '+sl,sum(inpatient.filter(row=>String(row.sl)===sl),'jumlah_kasus'),ri,['#087e82','#75bdb5','#c0d8e3'][i])).join('') : '<p class="ur-empty">Tidak ada episode rawat inap pada filter ini.</p>');
        const alos=totals.los_cases?(totals.total_los/totals.los_cases).toLocaleString('id-ID',{maximumFractionDigits:1}):'—';
        $('#ur-clinical').html([['ALOS rawat inap',alos==='—'?alos:alos+' hari'],['LOS < 2 hari',nf.format(totals.short_stay)],['SL III · LOS < 4',nf.format(totals.short_sl3)]].map(item=>'<div class="ur-mini"><strong>'+item[1]+'</strong>'+item[0]+'</div>').join(''));
        $('#ur-monthly').html(monthly.map(r=>{const diff=r.total_tarif-r.tarif_rs;return '<tr><td>'+months[r.month-1]+' '+$('#selectperiode').val()+'</td><td>'+nf.format(r.jumlah_kasus)+'</td><td>'+money(r.tarif_rs)+'</td><td>'+money(r.total_tarif)+'</td><td class="'+(diff<0?'ur-negative':'ur-positive')+'">'+money(diff)+'</td><td>'+percent(r.total_tarif,r.tarif_rs)+'</td></tr>';}).join(''));
        const costs=Object.keys(components).map(key=>({name:components[key],value:sum(data,key)})).filter(c=>c.value>0).sort((a,b)=>b.value-a.value);
        const costTotal=costs.reduce((total,c)=>total+c.value,0);
        $('#ur-components').html(costs.length?costs.slice(0,6).map(c=>'<div class="ur-meter-row"><div class="ur-meter-label"><span>'+c.name+'</span><strong>'+money(c.value)+'</strong></div><div class="ur-meter"><span style="width:'+(c.value/costTotal*100)+'%"></span></div><div class="text-muted mt-1" style="font-size:10px">'+percent(c.value,costTotal)+' dari jumlah komponen tercatat</div></div>').join(''):'<p class="ur-empty">Komponen tarif belum tersedia.</p>');
    }
    window.dataraweklaim = function () {
        if(request) request.abort();
        rows=[]; $('#ur-content').prop('hidden',true); $('#ur-coverage').text('Memuat cakupan data…'); $('#ur-status').prop('hidden',true);
        $('#ur-executive').attr('aria-busy','true'); $('#ur-refresh').prop('disabled',true);
        Swal.fire({
            title: 'Memuat data',
            text: 'Mohon tunggu, ringkasan UR sedang disiapkan.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); }
        });
        request=$.ajax({url:url+'ur/dashboard/dataraweklaim',type:'POST',dataType:'json',data:{selectperiode:$('#selectperiode').val()}})
            .done(function(response){rows=Array.isArray(response.responseResult)?response.responseResult:[];render();})
            .fail(function(xhr,state){if(state!=='abort'){status(xhr.responseJSON?.responseDesc || 'Data gagal dimuat. Klik Perbarui untuk mencoba kembali.');$('#ur-coverage').text('Data belum tersedia');}})
            .always(function(){ Swal.close(); $('#ur-executive').attr('aria-busy','false');$('#ur-refresh').prop('disabled',false); });
    };
    $('#selectperiode').on('change',window.dataraweklaim);
    $('#ur-month,#ur-service').on('change',render);
    $('#ur-refresh').on('click',window.dataraweklaim);
    $('#ur-print').on('click',function(){window.print();});
    window.dataraweklaim();
})();
