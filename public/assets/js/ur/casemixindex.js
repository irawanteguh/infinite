(function () {
    'use strict';
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const nf = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
    const cf = new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 2 });
    const colors = ['#009ef7','#7239ea','#f1a208','#7e8299'];
    const num = value => Number(value) || 0;
    const sum = (rows, field) => rows.reduce((value, row) => value + num(row[field]), 0);
    const pct = (value, total) => total ? (value / total * 100).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + '%' : '—';
    const money = value => 'Rp ' + cf.format(value);
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const code = value => String(value ?? '').trim() || '?';
    const date = value => new Date(value.slice(0,10) + 'T00:00:00').toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' });
    let payload = { rows:[], casegroups:[], cmgs:[] }, request = null, generation = 0;
    const charts = {};
    let tableData = null;
    function message(text) { $('#cm-status').text(text).prop('hidden', false); }
    function destroyCharts() { Object.keys(charts).forEach(id => { charts[id].destroy(); delete charts[id]; }); }
    function draw(id, series, categories, horizontal) {
        if (typeof ApexCharts === 'undefined') { $('#'+id).html('<p class="ur-empty">Grafik tidak tersedia; gunakan tabel detail di bawah.</p>'); return; }
        charts[id] = new ApexCharts(document.getElementById(id), {
            chart:{type:'bar',height:300,stacked:!horizontal,fontFamily:'inherit',toolbar:{show:false},animations:{enabled:false}},
            series:series,colors:horizontal?['#7239ea']:colors,
            plotOptions:{bar:{horizontal:horizontal,columnWidth:'46%',borderRadius:4,barHeight:'52%'}},
            dataLabels:{enabled:false},stroke:{width:0},grid:{borderColor:'#edf2f4',strokeDashArray:3},
            xaxis:{categories:categories,axisTicks:{show:false},axisBorder:{show:false},labels:{formatter:value=>horizontal?cf.format(num(value)):value}},
            yaxis:{labels:{maxWidth:horizontal?280:100,style:{fontSize:'10px'},formatter:value=>horizontal?value:cf.format(num(value))}},
            legend:{position:'top',horizontalAlign:'left',fontSize:'11px'},
            tooltip:{shared:!horizontal,intersect:false,y:{formatter:value=>nf.format(value)+' episode'}},
            noData:{text:'Tidak ada episode rawat inap'}
        });
        charts[id].render();
    }
    function groupRows(rows, field, masters, masterKey, nameKey, noteKey) {
        const groups = new Map();
        masters.forEach(master => groups.set(code(master[masterKey]), {code:code(master[masterKey]),name:master[nameKey],note:master[noteKey] || '',known:true,rows:[]}));
        rows.forEach(row => {
            const key=code(row[field]);
            if (!groups.has(key)) groups.set(key,{code:key,name:key==='?'?'Kode belum terisi':'Belum terdaftar di master',note:'Tetap termasuk dalam total episode.',known:false,rows:[]});
            groups.get(key).rows.push(row);
        });
        return [...groups.values()].map(group => ({...group,total:sum(group.rows,'episodes'),tariff:sum(group.rows,'total_tarif')})).sort((a,b)=>b.total-a.total || a.code.localeCompare(b.code,undefined,{numeric:true}));
    }
    function tables() {
        if (!tableData) return;
        const term = String($('#cm-search').val() || '').toLowerCase();
        const {cg,cmg,sl,activeMonths,total} = tableData;
        const selected=$('#cm-month').val();
        const columns=selected?[num(selected)]:Array.from({length:12},(_,i)=>i+1);
        [['cg',cg],['sl',sl],['cmg',cmg]].forEach(([type,groups])=>{
            const denominator=type==='sl'?groups.reduce((value,g)=>value+g.total,0):total;
            const visible=groups.filter(group=>(group.code+' '+group.name+' '+group.note).toLowerCase().includes(term));
            $('#cm-head-'+type).html('<tr><th>Kelompok / kode</th><th>Total episode</th><th>Proporsi</th><th>Total INA-CBG</th>'+columns.map(m=>'<th>'+months[m-1]+'</th>').join('')+'</tr>');
            $('#cm-body-'+type).html(visible.length?visible.map(group=>'<tr><td><span class="badge '+(group.known?'badge-light-primary':'badge-light-warning')+' me-2">'+esc(group.code)+'</span>'+esc(group.name)+(group.note?'<small>'+esc(group.note)+'</small>':'')+'</td><td><strong>'+nf.format(group.total)+'</strong></td><td>'+pct(group.total,denominator)+'</td><td>'+money(group.tariff)+'</td>'+columns.map(m=>{const count=sum(group.rows.filter(row=>num(row.month)===m),'episodes');return '<td class="'+(count?'cm-heat':'')+'">'+(activeMonths.has(m)?nf.format(count):'—')+'</td>';}).join('')+'</tr>').join(''):'<tr><td colspan="'+(columns.length+4)+'" class="text-center py-8">Tidak ada kelompok yang sesuai pencarian.</td></tr>');
            $('#cm-foot-'+type).html('<tr><td>Total baris ditampilkan</td><td>'+nf.format(visible.reduce((t,g)=>t+g.total,0))+'</td><td>'+pct(visible.reduce((t,g)=>t+g.total,0),denominator)+'</td><td>'+money(visible.reduce((t,g)=>t+g.tariff,0))+'</td>'+columns.map(m=>'<td>'+(activeMonths.has(m)?nf.format(visible.reduce((t,g)=>t+sum(g.rows.filter(row=>num(row.month)===m),'episodes'),0)):'—')+'</td>').join('')+'</tr>');
        });
    }
    function render() {
        destroyCharts(); tableData=null;
        const month=$('#cm-month').val(), service=$('#cm-service').val();
        const rows=payload.rows.filter(row=>(!month || num(row.month)===num(month)) && (!service || String(row.ptd)===service));
        $('#cm-content').prop('hidden',true); $('#cm-quality').prop('hidden',true);
        if (!rows.length) { message('Belum ada data untuk filter ini. Pilih tahun, bulan, atau layanan lain.'); $('#cm-coverage').text('Tidak ada data pada cakupan terpilih'); return; }
        $('#cm-status').prop('hidden',true); $('#cm-content').prop('hidden',false);
        const total=sum(rows,'episodes'), ri=rows.filter(row=>String(row.ptd)==='1'), inpatient=sum(ri,'episodes');
        const severe=sum(ri.filter(row=>['II','III'].includes(String(row.sl))),'episodes');
        const cg=groupRows(rows,'cg',payload.casegroups,'id_cg','name_case_group','notes');
        const cmg=groupRows(rows,'cmg',payload.cmgs,'group_code','group_name','description');
        const severityMasters=[{sl:'I',name:'Severity I'},{sl:'II',name:'Severity II'},{sl:'III',name:'Severity III'}];
        const sl=groupRows(ri,'sl',severityMasters,'sl','name','note');
        const alos=sum(ri,'los_cases')?(sum(ri,'total_los')/sum(ri,'los_cases')).toLocaleString('id-ID',{maximumFractionDigits:1}):'—';
        const weightCases=sum(ri,'weight_cases');
        const averageWeight=weightCases?(sum(ri,'weight_sum')/weightCases).toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:3}):'—';
        const starts=rows.map(row=>row.first_discharge).filter(Boolean).sort();
        const ends=rows.map(row=>row.last_discharge).filter(Boolean).sort();
        const last=ends[ends.length-1];
        const lastDate=new Date(last.slice(0,10)+'T00:00:00');
        const partial=lastDate.getDate()<new Date(lastDate.getFullYear(),lastDate.getMonth()+1,0).getDate();
        $('#cm-coverage').text(date(starts[0])+' — '+date(last)+' / '+(service==='1'?'Rawat inap':service==='2'?'Rawat jalan':'Semua layanan')+(partial?' / Bulan terakhir belum penuh':''));
        const kpis=[
            ['Episode pelayanan',nf.format(total),nf.format(inpatient)+' RI · '+nf.format(total-inpatient)+' layanan lainnya','bi-people'],
            ['Episode rawat inap',nf.format(inpatient),pct(inpatient,total)+' dari episode terpilih','bi-hospital'],
            ['Proporsi SL II + III',pct(severe,inpatient),nf.format(severe)+' episode rawat inap','bi-layers'],
            ['CMG aktif',nf.format(cmg.filter(g=>g.total>0).length),'Termasuk kode di luar master','bi-diagram-3'],
            ['ALOS rawat inap',alos==='—'?alos:alos+' hari','Rata-rata LOS yang terisi','bi-clock-history'],
            ['Rata-rata bobot IDRG',averageWeight,nf.format(weightCases)+' RI dengan bobot positif','bi-speedometer2']
        ];
        $('#cm-kpis').html(kpis.map((k,i)=>'<article class="card card-flush ur-kpi ur-kpi-tone-'+i+'"><i class="bi '+k[3]+' ur-kpi-icon"></i><div class="ur-kpi-label">'+k[0]+'</div><div class="ur-kpi-value">'+k[1]+'</div><small>'+k[2]+'</small></article>').join(''));
        const unknownCMG=cmg.filter(g=>!g.known && g.total>0),unknownCG=cg.filter(g=>!g.known && g.total>0);
        if (unknownCMG.length || unknownCG.length) {
            const details=[];
            if(unknownCMG.length)details.push(nf.format(unknownCMG.reduce((t,g)=>t+g.total,0))+' episode pada CMG '+unknownCMG.map(g=>g.code).join(', '));
            if(unknownCG.length)details.push(nf.format(unknownCG.reduce((t,g)=>t+g.total,0))+' episode pada kelompok kasus '+unknownCG.map(g=>g.code).join(', '));
            $('#cm-quality-text').text(details.join('; ')+'. Kode belum terdaftar/terisi pada master, tetapi seluruh episode tetap dihitung.');
            $('#cm-quality').prop('hidden',false);
        }
        const activeMonths=new Set(rows.map(row=>num(row.month)));
        const riMonths=[...new Set(ri.map(row=>num(row.month)))].sort((a,b)=>a-b);
        const slSets=[['I','SL I'],['II','SL II'],['III','SL III'],['other','Lainnya']];
        draw('cm-severity-chart',slSets.map(([key,name])=>({name:name,data:riMonths.map(m=>sum(ri.filter(row=>num(row.month)===m && (key==='other'?!['I','II','III'].includes(String(row.sl)):String(row.sl)===key)),'episodes'))})),riMonths.map(m=>months[m-1]),false);
        $('#cm-severity-mix').html(slSets.map(([key,name],i)=>{const n=sum(ri.filter(row=>key==='other'?!['I','II','III'].includes(String(row.sl)):String(row.sl)===key),'episodes');return '<span class="badge '+['badge-light-primary','badge-light-info','badge-light-warning','badge-light'][i]+'">'+name+': '+nf.format(n)+' · '+pct(n,inpatient)+'</span>';}).join(''));
        const top=cmg.filter(g=>g.total>0).slice(0,7);
        draw('cm-cmg-chart',[{name:'Episode',data:top.map(g=>g.total)}],top.map(g=>g.name),true);
        $('#cm-casegroups').html(cg.filter(g=>g.total>0).map(g=>'<article class="cm-casegroup"><span class="badge '+(g.known?'badge-light-primary':'badge-light-warning')+'">CG '+esc(g.code)+'</span><h3>'+esc(g.name)+'</h3><strong>'+nf.format(g.total)+'</strong> <span class="text-muted fs-8">episode</span><div class="ur-meter"><span style="width:'+(g.total/total*100)+'%"></span></div><small>'+pct(g.total,total)+' · '+money(g.tariff)+'</small></article>').join(''));
        tableData={cg,cmg,sl,activeMonths,total}; tables();
    }
    window.load=function () {
        const token=++generation;
        if(request)request.abort();
        destroyCharts();tableData=null;payload={rows:[],casegroups:[],cmgs:[]};
        $('#cm-content').prop('hidden',true);$('#cm-status').prop('hidden',true);$('#cm-refresh').prop('disabled',true);
        $('#cm-coverage').text('Memuat cakupan data…');
        Swal.fire({title:'Memuat data casemix',text:'Mohon tunggu, ringkasan sedang disiapkan.',allowOutsideClick:false,allowEscapeKey:false,showConfirmButton:false,didOpen:()=>Swal.showLoading()});
        request=$.ajax({url:url+'ur/casemixindex/summary',type:'POST',dataType:'json',data:{selectperiode:$('#selectperiode').val()}})
            .done(function(response){
                if(token!==generation)return;
                if(response.responseCode!=='00'){message(response.responseDesc || 'Data gagal dimuat.');return;}
                const result=response.responseResult || {};
                payload={rows:Array.isArray(result.rows)?result.rows:[],casegroups:Array.isArray(result.casegroups)?result.casegroups:[],cmgs:Array.isArray(result.cmgs)?result.cmgs:[]};render();
            })
            .fail(function(xhr,state){if(token===generation && state!=='abort'){message(xhr.responseJSON?.responseDesc || 'Data gagal dimuat. Klik Perbarui untuk mencoba kembali.');$('#cm-coverage').text('Data belum tersedia');}})
            .always(function(){if(token===generation){Swal.close();$('#cm-refresh').prop('disabled',false);}});
    };
    $('#selectperiode').on('change',window.load);
    $('#cm-month,#cm-service').on('change',render);
    $('#cm-search').on('input',tables);
    $('#cm-refresh').on('click',window.load);
    $('#cm-print').on('click',()=>window.print());
    window.load();
})();
