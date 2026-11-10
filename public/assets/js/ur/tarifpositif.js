(function () {
    'use strict';
    const months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const nf=new Intl.NumberFormat('id-ID',{maximumFractionDigits:0});
    const cf=new Intl.NumberFormat('id-ID',{notation:'compact',maximumFractionDigits:2});
    const num=value=>Number(value)||0;
    const sum=(data,field)=>data.reduce((total,row)=>total+num(row[field]),0);
    const pct=(value,total)=>total?(value/total*100).toLocaleString('id-ID',{maximumFractionDigits:1})+'%':'—';
    const money=value=>'Rp '+cf.format(value);
    const esc=value=>String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const date=value=>new Date(value.slice(0,10)+'T00:00:00').toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'});
    let rows=[],ready=false,request=null,generation=0,chart=null;
    function showStatus(id,text){$(id).text(text).prop('hidden',false);}
    function fetchData(endpoint,params,success){
        const token=++generation;
        if(request)request.abort();
        $('#tp-refresh').prop('disabled',true);
        Swal.fire({title:'Memuat data',text:'Mohon tunggu, data tarif positif sedang disiapkan.',allowOutsideClick:false,allowEscapeKey:false,showConfirmButton:false,didOpen:()=>Swal.showLoading()});
        request=$.ajax({url:url+'ur/tarifpositif/'+endpoint,type:'POST',dataType:'json',data:{selectperiode:$('#selectperiode').val(),...params}})
            .done(function(response){if(token!==generation)return;if(response.responseCode!=='00'){showStatus('#tp-status',response.responseDesc||'Data gagal dimuat.');return;}success(response.responseResult);})
            .fail(function(xhr,state){if(token!==generation||state==='abort')return;showStatus('#tp-status',xhr.responseJSON?.responseDesc||'Data gagal dimuat. Klik Perbarui untuk mencoba kembali.');})
            .always(function(){if(token===generation){Swal.close();$('#tp-refresh').prop('disabled',false);}});
    }
    function renderSummary(){
        if(chart){chart.destroy();chart=null;}
        const month=$('#tp-month').val(),service=$('#tp-service').val();
        const data=rows.filter(row=>(!month||num(row.month)===num(month))&&(!service||String(row.ptd)===service));
        $('#tp-content,#tp-dpjp').prop('hidden',true);
        if(!data.length){showStatus('#tp-status','Belum ada data pada periode dan layanan terpilih.');$('#tp-coverage').text('Tidak ada data pada cakupan terpilih');return false;}
        $('#tp-status').prop('hidden',true);$('#tp-content,#tp-dpjp').prop('hidden',false);
        const count=sum(data,'positive_episodes'),all=sum(data,'all_episodes'),gap=sum(data,'positive_gap'),rs=sum(data,'tarif_rs'),ina=sum(data,'total_tarif');
        const starts=data.map(row=>row.first_discharge).filter(Boolean).sort(),ends=data.map(row=>row.last_discharge).filter(Boolean).sort();
        $('#tp-coverage').text(date(starts[0])+' — '+date(ends[ends.length-1])+' / '+(service==='1'?'Rawat inap':service==='2'?'Rawat jalan':'Semua layanan'));
        const kpis=[['Episode positif',nf.format(count),pct(count,all)+' dari '+nf.format(all)+' episode','bi-people'],['Total selisih positif',money(gap),'INA-CBG − tarif RS episode positif','bi-graph-up-arrow'],['Tarif RS',money(rs),'Hanya episode berselisih positif','bi-hospital'],['Total INA-CBG',money(ina),'TOTAL_TARIF episode positif','bi-receipt'],['Selisih rata-rata',count?money(gap/count):'—','Per episode positif','bi-bar-chart'],['Rasio cakupan',pct(ina,rs),'INA-CBG ÷ tarif RS episode positif','bi-speedometer2']];
        $('#tp-kpis').html(kpis.map((k,i)=>'<article class="card card-flush ur-kpi ur-kpi-tone-'+i+'"><i class="bi '+k[3]+' ur-kpi-icon"></i><div class="ur-kpi-label">'+k[0]+'</div><div class="ur-kpi-value">'+k[1]+'</div><small>'+k[2]+'</small></article>').join(''));
        const monthly=[...new Set(data.map(row=>num(row.month)))].sort((a,b)=>a-b).map(m=>{const subset=data.filter(row=>num(row.month)===m);return {month:m,rows:subset,count:sum(subset,'positive_episodes'),all:sum(subset,'all_episodes'),rs:sum(subset,'tarif_rs'),ina:sum(subset,'total_tarif'),gap:sum(subset,'positive_gap')};});
        $('#tp-monthly').html(monthly.map(r=>'<tr><td>'+months[r.month-1]+'</td><td>'+nf.format(r.all)+'</td><td>'+nf.format(r.count)+'</td><td>'+pct(r.count,r.all)+'</td><td>'+money(r.rs)+'</td><td>'+money(r.ina)+'</td><td class="ur-positive fw-bold">'+money(r.gap)+'</td></tr>').join(''));
        $('#tp-services').html([['1','Rawat inap'],['2','Rawat jalan']].filter(([ptd])=>!service||service===ptd).map(([ptd,name])=>{const subset=data.filter(row=>String(row.ptd)===ptd),count=sum(subset,'positive_episodes'),gap=sum(subset,'positive_gap');return '<div class="tp-services-card"><h3>'+name+'</h3><div class="tp-services-stats"><div><strong>'+nf.format(count)+'</strong><small>Episode positif</small></div><div><strong>'+money(gap)+'</strong><small>Selisih positif</small></div><div><strong>'+pct(count,sum(subset,'all_episodes'))+'</strong><small>Proporsi positif</small></div></div></div>';}).join(''));
        if(typeof ApexCharts!=='undefined'){
            chart=new ApexCharts(document.getElementById('tp-trend'),{chart:{type:'bar',height:285,stacked:true,fontFamily:'inherit',toolbar:{show:false},animations:{enabled:false}},series:[['1','Rawat inap'],['2','Rawat jalan']].filter(([ptd])=>!service||service===ptd).map(([ptd,name])=>({name:name,data:monthly.map(r=>sum(r.rows.filter(row=>String(row.ptd)===ptd),'positive_gap'))})),colors:['#009ef7','#50cd89'],plotOptions:{bar:{columnWidth:'45%',borderRadius:4}},dataLabels:{enabled:false},stroke:{width:0},grid:{borderColor:'#edf2f4',strokeDashArray:3},xaxis:{categories:monthly.map(r=>months[r.month-1])},yaxis:{labels:{formatter:value=>cf.format(value)}},legend:{position:'top',horizontalAlign:'left'},tooltip:{y:{formatter:value=>'Rp '+nf.format(value)}}});chart.render();
        }else{$('#tp-trend').html('<p class="ur-empty">Grafik tidak tersedia. Gunakan rekap bulanan di bawah.</p>');}
        return true;
    }
    function renderDpjp(){
        const month=$('#tp-month').val(),service=$('#tp-service').val();
        const term=String($('#tp-dpjp-search').val()||'').toLocaleLowerCase('id-ID');
        ['1','2'].forEach(ptd=>{
            $('#tp-dpjp-service-'+ptd).prop('hidden',Boolean(service&&service!==ptd));
            const grouped=new Map();
            rows.filter(row=>String(row.ptd)===ptd&&(!month||num(row.month)===num(month))).forEach(row=>{
                const name=String(row.dpjp||'Belum terisi');
                if(!grouped.has(name))grouped.set(name,{name,all_episodes:0,positive_episodes:0,tarif_rs:0,total_tarif:0,idrg_total_tarif:0,positive_gap:0});
                const group=grouped.get(name);
                ['all_episodes','positive_episodes','tarif_rs','total_tarif','idrg_total_tarif','positive_gap'].forEach(field=>group[field]+=num(row[field]));
            });
            const groups=[...grouped.values()].filter(group=>group.positive_episodes>0&&group.name.toLocaleLowerCase('id-ID').includes(term)).sort((a,b)=>Math.abs(b.positive_gap)-Math.abs(a.positive_gap)||a.name.localeCompare(b.name));
            const cells=group=>'<td>'+nf.format(group.positive_episodes)+'</td><td>'+pct(group.positive_episodes,group.all_episodes)+'</td><td>'+money(group.tarif_rs)+'</td><td>'+money(group.total_tarif)+'</td><td>'+money(group.idrg_total_tarif)+'</td><td class="ur-positive fw-bold">'+money(group.positive_gap)+'</td><td>'+pct(group.total_tarif,group.tarif_rs)+'</td>';
            $('#tp-dpjp-rows-'+ptd).html(groups.length?groups.map(group=>'<tr><td>'+esc(group.name)+'</td>'+cells(group)+'</tr>').join(''):'<tr><td colspan="8" class="text-center py-8">Tidak ada DPJP yang sesuai filter.</td></tr>');
            const totals={};['all_episodes','positive_episodes','tarif_rs','total_tarif','idrg_total_tarif','positive_gap'].forEach(field=>totals[field]=sum(groups,field));
            $('#tp-dpjp-total-'+ptd).html(groups.length?'<tr><td>Total DPJP ditampilkan</td>'+cells(totals)+'</tr>':'');
        });
    }
    window.load=function(){
        ready=false;rows=[];$('#tp-content,#tp-dpjp').prop('hidden',true);$('#tp-status').prop('hidden',true);$('#tp-coverage').text('Memuat cakupan data…');
        fetchData('summary',{},function(result){rows=Array.isArray(result)?result:[];ready=true;if(renderSummary())renderDpjp();});
    };
    $('#selectperiode').on('change',window.load);
    $('#tp-month,#tp-service').on('change',function(){if(ready&&renderSummary())renderDpjp();});
    $('#tp-dpjp-search').on('input',renderDpjp);
    $('#tp-refresh').on('click',window.load);$('#tp-print').on('click',()=>window.print());
    window.load();
})();
