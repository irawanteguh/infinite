(function () {
    'use strict';
    const months=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const nf=new Intl.NumberFormat('id-ID',{maximumFractionDigits:0});
    const cf=new Intl.NumberFormat('id-ID',{notation:'compact',maximumFractionDigits:2});
    const num=value=>Number(value)||0;
    const sum=(data,field)=>data.reduce((total,row)=>total+num(row[field]),0);
    const pct=(value,total)=>total?(value/total*100).toLocaleString('id-ID',{maximumFractionDigits:1})+'%':'—';
    const money=value=>'Rp '+cf.format(value);
    const esc=value=>String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const date=value=>value?new Date(value.slice(0,10)+'T00:00:00').toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}):'—';
    let rows=[],ready=false,request=null,generation=0,chart=null;
    function showStatus(id,text){$(id).text(text).prop('hidden',false);}
    function fetchData(endpoint,params,success){
        const token=++generation;
        if(request)request.abort();
        $('#tn-refresh').prop('disabled',true);
        showStatus('#tn-status','Memuat ringkasan tarif negatif…');
        request=$.ajax({url:url+'ur/tarifnegatif/'+endpoint,type:'POST',dataType:'json',data:{selectperiode:$('#selectperiode').val(),...params}})
            .done(function(response){if(token!==generation)return;if(response.responseCode!=='00'){showStatus('#tn-status',response.responseDesc||'Data gagal dimuat.');return;}success(response.responseResult);})
            .fail(function(xhr,state){if(token!==generation||state==='abort')return;showStatus('#tn-status',xhr.responseJSON?.responseDesc||'Data gagal dimuat. Klik Perbarui untuk mencoba kembali.');})
            .always(function(){if(token===generation){$('#tn-refresh').prop('disabled',false);}});
    }
    function renderSummary(){
        if(chart){chart.destroy();chart=null;}
        const month=$('#tn-month').val(),service=$('#tn-service').val();
        const data=rows.filter(row=>(!month||num(row.month)===num(month))&&(!service||String(row.ptd)===service));
        $('#tn-content,#tn-dpjp').prop('hidden',true);
        if(!data.length){showStatus('#tn-status','Belum ada data pada periode dan layanan terpilih.');$('#tn-coverage').text('Tidak ada data pada cakupan terpilih');return false;}
        $('#tn-status').prop('hidden',true);$('#tn-content,#tn-dpjp').prop('hidden',false);
        const count=sum(data,'negative_episodes'),all=sum(data,'all_episodes'),gap=sum(data,'negative_gap'),rs=sum(data,'tarif_rs'),ina=sum(data,'total_tarif');
        const starts=data.map(row=>row.first_discharge).filter(Boolean).sort(),ends=data.map(row=>row.last_discharge).filter(Boolean).sort();
        $('#tn-coverage').text(date(starts[0])+' — '+date(ends[ends.length-1])+' / '+(service==='1'?'Rawat inap':service==='2'?'Rawat jalan':'Semua layanan'));
        const kpis=[['Episode negatif',nf.format(count),pct(count,all)+' dari '+nf.format(all)+' episode','bi-people'],['Total selisih negatif',money(gap),'INA-CBG − tarif RS episode negatif','bi-graph-down-arrow'],['Tarif RS',money(rs),'Hanya episode berselisih negatif','bi-hospital'],['Total INA-CBG',money(ina),'TOTAL_TARIF episode negatif','bi-receipt'],['Selisih rata-rata',count?money(gap/count):'—','Per episode negatif','bi-bar-chart'],['Rasio cakupan',pct(ina,rs),'INA-CBG ÷ tarif RS episode negatif','bi-speedometer2']];
        $('#tn-kpis').html(kpis.map((k,i)=>'<article class="card card-flush ur-kpi ur-kpi-tone-'+i+'"><i class="bi '+k[3]+' ur-kpi-icon"></i><div class="ur-kpi-label">'+k[0]+'</div><div class="ur-kpi-value'+((i===1||i===4)?' ur-negative':'')+'">'+k[1]+'</div><small>'+k[2]+'</small></article>').join(''));
        const monthly=[...new Set(data.map(row=>num(row.month)))].sort((a,b)=>a-b).map(m=>{const subset=data.filter(row=>num(row.month)===m);return {month:m,rows:subset,count:sum(subset,'negative_episodes'),all:sum(subset,'all_episodes'),rs:sum(subset,'tarif_rs'),ina:sum(subset,'total_tarif'),gap:sum(subset,'negative_gap')};});
        $('#tn-monthly').html(monthly.map(r=>'<tr><td>'+months[r.month-1]+'</td><td>'+nf.format(r.all)+'</td><td>'+nf.format(r.count)+'</td><td>'+pct(r.count,r.all)+'</td><td>'+money(r.rs)+'</td><td>'+money(r.ina)+'</td><td class="ur-negative fw-bold">'+money(r.gap)+'</td></tr>').join(''));
        initDataTable('#tn-monthly-table', null, 12, {paging:false, searching:false, info:false, lengthChange:false, dom:'t'});
        $('#tn-services').html([['1','Rawat inap'],['2','Rawat jalan']].filter(([ptd])=>!service||service===ptd).map(([ptd,name])=>{const subset=data.filter(row=>String(row.ptd)===ptd),count=sum(subset,'negative_episodes'),gap=sum(subset,'negative_gap');return '<div class="tn-services-card"><h3>'+name+'</h3><div class="tn-services-stats"><div><strong>'+nf.format(count)+'</strong><small>Episode negatif</small></div><div><strong>'+money(gap)+'</strong><small>Selisih negatif</small></div><div><strong>'+pct(count,sum(subset,'all_episodes'))+'</strong><small>Proporsi negatif</small></div></div></div>';}).join(''));
        if(typeof ApexCharts!=='undefined'){
            chart=new ApexCharts(document.getElementById('tn-trend'),{chart:{type:'bar',height:285,stacked:true,fontFamily:'inherit',toolbar:{show:false},animations:{enabled:false}},series:[['1','Rawat inap'],['2','Rawat jalan']].filter(([ptd])=>!service||service===ptd).map(([ptd,name])=>({name:name,data:monthly.map(r=>sum(r.rows.filter(row=>String(row.ptd)===ptd),'negative_gap'))})),colors:[['1','#087e82'],['2','#93b6c3']].filter(([ptd])=>!service||service===ptd).map(([,color])=>color),plotOptions:{bar:{columnWidth:'45%',borderRadius:4}},dataLabels:{enabled:false},stroke:{width:0},grid:{borderColor:'#edf2f4',strokeDashArray:3},xaxis:{categories:monthly.map(r=>months[r.month-1]),labels:{rotate:-35,hideOverlappingLabels:true,style:{fontSize:'11px',colors:'#789095'}}},yaxis:{labels:{formatter:value=>cf.format(value)}},legend:{position:'top',horizontalAlign:'left'},tooltip:{y:{formatter:value=>'Rp '+nf.format(value)}}});chart.render();
        }else{$('#tn-trend').html('<p class="ur-empty">Grafik tidak tersedia. Gunakan rekap bulanan di bawah.</p>');}
        return true;
    }
    function renderDpjp(){
        const month=$('#tn-month').val(),service=$('#tn-service').val();

        ['1','2'].forEach(ptd=>{
            const term=String($('#tn-dpjp-search-'+ptd).val()||'').toLocaleLowerCase('id-ID');
            $('#tn-dpjp-service-'+ptd).prop('hidden',Boolean(service&&service!==ptd));
            const grouped=new Map();
            rows.filter(row=>String(row.ptd)===ptd&&(!month||num(row.month)===num(month))).forEach(row=>{
                const name=String(row.dpjp||'Belum terisi');
                if(!grouped.has(name))grouped.set(name,{name,all_episodes:0,negative_episodes:0,tarif_rs:0,total_tarif:0,idrg_total_tarif:0,negative_gap:0});
                const group=grouped.get(name);
                ['all_episodes','negative_episodes','tarif_rs','total_tarif','idrg_total_tarif','negative_gap'].forEach(field=>group[field]+=num(row[field]));
            });
            const groups=[...grouped.values()].filter(group=>group.negative_episodes>0&&group.name.toLocaleLowerCase('id-ID').includes(term)).sort((a,b)=>Math.abs(b.negative_gap)-Math.abs(a.negative_gap)||a.name.localeCompare(b.name));
            const cells=group=>'<td>'+nf.format(group.all_episodes)+'</td><td>'+nf.format(group.negative_episodes)+'</td><td>'+pct(group.negative_episodes,group.all_episodes)+'</td><td>'+money(group.tarif_rs)+'</td><td>'+money(group.total_tarif)+'</td><td>'+money(group.idrg_total_tarif)+'</td><td class="ur-negative fw-bold">'+money(group.negative_gap)+'</td><td>'+pct(group.total_tarif,group.tarif_rs)+'</td>';
            if ($.fn.DataTable.isDataTable('#tn-dpjp-table-'+ptd)) $('#tn-dpjp-table-'+ptd).DataTable().destroy();
            $('#tn-dpjp-rows-'+ptd).html(groups.length?groups.map(group=>'<tr><td>'+esc(group.name)+'</td>'+cells(group)+'</tr>').join(''):'<tr><td colspan="9" class="text-center py-8">Tidak ada DPJP yang sesuai filter.</td></tr>');
            const totals={};['all_episodes','negative_episodes','tarif_rs','total_tarif','idrg_total_tarif','negative_gap'].forEach(field=>totals[field]=sum(groups,field));
            $('#tn-dpjp-total-'+ptd).html(groups.length?'<tr><td>Total DPJP ditampilkan</td>'+cells(totals)+'</tr>':'');
            initDataTable('#tn-dpjp-table-'+ptd, null, 10, {searching:false, dom:"<'app-datatable-scroll't><'app-table-footer'<'app-table-meta'li>p>", language:{emptyTable:'Belum ada DPJP yang sesuai filter.'}});
        });
    }
    window.load=function(){
        ready=false;rows=[];$('#tn-content,#tn-dpjp').prop('hidden',true);$('#tn-status').prop('hidden',true);$('#tn-coverage').text('Memuat cakupan data…');
        fetchData('summary',{},function(result){rows=Array.isArray(result)?result:[];ready=true;if(renderSummary())renderDpjp();});
    };
    const scopeLink = document.getElementById('tarif-scope-link');
    function updateScopeLink() {
        const target = new URL(scopeLink.href);
        target.searchParams.set('year', $('#selectperiode').val());
        [['month', '#tn-month'], ['service', '#tn-service']].forEach(([key, selector]) => {
            const value = $(selector).val();
            if (value) target.searchParams.set(key, value);
            else target.searchParams.delete(key);
        });
        scopeLink.href = target.href;
    }
    $('#selectperiode').on('change', () => { updateScopeLink(); window.load(); });
    $('#tn-month,#tn-service').on('change',function(){
        updateScopeLink();
        if(ready&&renderSummary())renderDpjp();
    });
    updateScopeLink();
    $('#tn-dpjp-search-1,#tn-dpjp-search-2').on('input',renderDpjp);
    $('#tn-refresh').on('click',window.load);$('#tn-print').on('click',()=>window.print());
    window.load();
})();
