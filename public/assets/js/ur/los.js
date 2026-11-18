(function(){
    'use strict';
    const months=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const nf=new Intl.NumberFormat('id-ID',{maximumFractionDigits:0}),cf=new Intl.NumberFormat('id-ID',{notation:'compact',maximumFractionDigits:2});
    const num=v=>Number(v)||0,sum=(rows,key)=>rows.reduce((n,r)=>n+num(r[key]),0);
    const money=v=>'Rp '+cf.format(v),pct=(n,d)=>d?(n/d*100).toLocaleString('id-ID',{maximumFractionDigits:1})+'%':'—';
    const avg=(n,d)=>d?(n/d).toLocaleString('id-ID',{maximumFractionDigits:1})+' hari':'—';
    const esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const date=v=>v?new Date(v.slice(0,10)+'T00:00:00').toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}):'—';
    const fields=['all_episodes','episodes','total_los','los_0','los_1','tarif_rs','total_tarif','idrg_total_tarif'];
    let rows=[],request=null,generation=0;
    const charts={};
    function aggregate(data){const total={};fields.forEach(key=>total[key]=sum(data,key));return total;}
    function destroy(){Object.keys(charts).forEach(id=>{charts[id].destroy();delete charts[id];});}
    function draw(id,series,labels,currency){
        if(typeof ApexCharts==='undefined'){$('#'+id).html('<p class="ur-empty">Grafik tidak tersedia. Gunakan tabel bulanan.</p>');return;}
        charts[id]=new ApexCharts(document.getElementById(id),{chart:{type:currency?'line':'bar',height:285,stacked:!currency,fontFamily:'inherit',toolbar:{show:false},animations:{enabled:false}},series:series,colors:currency?['#93aab3','#087e82']:['#087e82','#93b6c3'],stroke:{width:currency?3:0,curve:'straight'},plotOptions:{bar:{columnWidth:'45%',borderRadius:4}},dataLabels:{enabled:false},grid:{borderColor:'#edf2f4',strokeDashArray:3},xaxis:{categories:labels,labels:{rotate:-35,hideOverlappingLabels:true,style:{fontSize:'11px',colors:'#789095'}}},yaxis:{labels:{formatter:v=>cf.format(v)}},legend:{position:'top',horizontalAlign:'left'},tooltip:{shared:true,intersect:false,y:{formatter:v=>currency?'Rp '+nf.format(v):nf.format(v)+' episode'}}});charts[id].render();
    }
    function filtered(){const month=$('#los-month').val();return rows.filter(r=>!month||num(r.month)===num(month));}
    function renderDpjp(){
        const groups=new Map(),term=String($('#los-search').val()||'').toLocaleLowerCase('id-ID');
        filtered().forEach(r=>{const name=String(r.dpjp||'Belum terisi');if(!groups.has(name))groups.set(name,[]);groups.get(name).push(r);});
        const result=[...groups.entries()].map(([name,data])=>({name,...aggregate(data)})).filter(g=>g.episodes>0&&g.name.toLocaleLowerCase('id-ID').includes(term)).sort((a,b)=>b.episodes-a.episodes||b.total_los-a.total_los||a.name.localeCompare(b.name));
        const cells=g=>{const gap=g.total_tarif-g.tarif_rs;return '<td>'+nf.format(g.all_episodes)+'</td><td>'+nf.format(g.episodes)+'</td><td>'+pct(g.episodes,g.all_episodes)+'</td><td>'+nf.format(g.los_0)+'</td><td>'+nf.format(g.los_1)+'</td><td>'+avg(g.total_los,g.episodes)+'</td><td>'+nf.format(g.total_los)+' hari</td><td>'+money(g.tarif_rs)+'</td><td>'+money(g.total_tarif)+'</td><td>'+money(g.idrg_total_tarif)+'</td><td class="'+(gap<0?'ur-negative':'ur-positive')+'">'+money(gap)+'</td>';};
        if ($.fn.DataTable.isDataTable('#los-dpjp-table')) $('#los-dpjp-table').DataTable().destroy();
        $('#los-dpjp').html(result.length?result.map(g=>'<tr><td>'+esc(g.name)+'</td>'+cells(g)+'</tr>').join(''):'<tr><td colspan="12">Tidak ada DPJP yang sesuai filter.</td></tr>');
        $('#los-dpjp-total').html(result.length?'<tr><td>Total DPJP ditampilkan</td>'+cells(aggregate(result))+'</tr>':'');
        initDataTable('#los-dpjp-table', null, 10, {searching:false, dom:"<'app-datatable-scroll't><'app-table-footer'<'app-table-meta'li>p>", language:{emptyTable:'Belum ada DPJP yang sesuai filter.'}});
    }
    function render(){
        destroy();const data=filtered();$('#los-content').prop('hidden',true);
        if(!data.length){$('#los-status').text('Belum ada data rawat inap pada cakupan terpilih.').prop('hidden',false);$('#los-coverage').text('Tidak ada data pada cakupan terpilih');return;}
        $('#los-status').prop('hidden',true);$('#los-content').prop('hidden',false);
        const total=aggregate(data),starts=data.map(r=>r.first_discharge).filter(Boolean).sort(),ends=data.map(r=>r.last_discharge).filter(Boolean).sort();
        $('#los-coverage').text(date(starts[0])+' — '+date(ends[ends.length-1])+' / Rawat inap');
        const kpis=[['Episode LOS < 2 hari',nf.format(total.episodes),pct(total.episodes,total.all_episodes)+' dari '+nf.format(total.all_episodes)+' rawat inap','bi-people'],['Rata-rata LOS',avg(total.total_los,total.episodes),'Episode rawat inap dengan LOS < 2','bi-clock-history'],['LOS 0 hari',nf.format(total.los_0),pct(total.los_0,total.episodes)+' dari short stay','bi-stopwatch'],['LOS > 0–< 2 hari',nf.format(total.los_1),pct(total.los_1,total.episodes)+' dari short stay','bi-calendar-week'],['Total tarif RS',money(total.tarif_rs),'Hanya rawat inap LOS < 2','bi-hospital'],['Selisih INA-CBG − RS',money(total.total_tarif-total.tarif_rs),'Total INA-CBG '+money(total.total_tarif),'bi-arrow-left-right']];
        $('#los-kpis').html(kpis.map((k,i)=>'<article class="card card-flush ur-kpi ur-kpi-tone-'+i+'"><i class="bi '+k[3]+' ur-kpi-icon"></i><div class="ur-kpi-label">'+k[0]+'</div><div class="ur-kpi-value">'+k[1]+'</div><small>'+k[2]+'</small></article>').join(''));
        const monthly=[...new Set(data.map(r=>num(r.month)))].sort((a,b)=>a-b).map(month=>({month,...aggregate(data.filter(r=>num(r.month)===month))}));
        $('#los-monthly').html(monthly.map(r=>{const gap=r.total_tarif-r.tarif_rs;return '<tr><td>'+months[r.month-1]+'</td><td>'+nf.format(r.all_episodes)+'</td><td>'+nf.format(r.episodes)+'</td><td>'+pct(r.episodes,r.all_episodes)+'</td><td>'+avg(r.total_los,r.episodes)+'</td><td>'+money(r.tarif_rs)+'</td><td>'+money(r.total_tarif)+'</td><td class="'+(gap<0?'ur-negative':'ur-positive')+'">'+money(gap)+'</td></tr>';}).join(''));
        initDataTable('#los-monthly-table', null, 12, {paging:false, searching:false, info:false, lengthChange:false, dom:'t'});
        draw('los-trend',[{name:'LOS 0 hari',data:monthly.map(r=>r.los_0)},{name:'LOS > 0–< 2 hari',data:monthly.map(r=>r.los_1)}],monthly.map(r=>months[r.month-1]),false);
        draw('los-finance',[{name:'Tarif RS',data:monthly.map(r=>r.tarif_rs)},{name:'Total INA-CBG',data:monthly.map(r=>r.total_tarif)}],monthly.map(r=>months[r.month-1]),true);renderDpjp();
    }
    window.load=function(){
        const token=++generation;if(request)request.abort();destroy();rows=[];$('#los-content').prop('hidden',true);$('#los-status').prop('hidden',true);$('#los-coverage').text('Memuat cakupan data…');$('#los-refresh').prop('disabled',true);
        $('#los-status').text('Memuat ringkasan lama rawat…').prop('hidden',false);
        request=$.ajax({url:url+'ur/los/summary',type:'POST',dataType:'json',data:{selectperiode:$('#selectperiode').val()}})
            .done(function(response){if(token!==generation)return;if(response.responseCode!=='00'){$('#los-status').text(response.responseDesc||'Data gagal dimuat.').prop('hidden',false);return;}rows=Array.isArray(response.responseResult)?response.responseResult:[];render();})
            .fail(function(xhr,state){if(token===generation&&state!=='abort'){$('#los-status').text(xhr.responseJSON?.responseDesc||'Data gagal dimuat. Klik Perbarui untuk mencoba kembali.').prop('hidden',false);$('#los-coverage').text('Data belum tersedia');}})
            .always(function(){if(token===generation){$('#los-refresh').prop('disabled',false);}});
    };
    $('#selectperiode').on('change',window.load);$('#los-month').on('change',render);$('#los-search').on('input',renderDpjp);$('#los-refresh').on('click',window.load);$('#los-print').on('click',()=>window.print());function updateCaseLink() {
        const link = document.getElementById('los-detail-link');
        const target = new URL(link.href);
        target.searchParams.set('year', $('#selectperiode').val());
        const month = $('#los-month').val();
        if (month) target.searchParams.set('month', month);
        else target.searchParams.delete('month');
        link.href = target.href;
    }
    $('#selectperiode,#los-month').on('change', updateCaseLink);
    updateCaseLink();
    window.load();
})();
