(function(){
'use strict';
const nf=new Intl.NumberFormat('id-ID'), months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
const num=v=>Number(v)||0, sum=(r,k)=>r.reduce((n,x)=>n+num(x[k]),0), money=v=>'Rp '+nf.format(v);
const esc=v=>String(v??'—').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let rows=[], charts=[],request, generation=0;
function link(){const a=document.getElementById('biaya-detail-link'),u=new URL(a.href);u.searchParams.set('year',$('#selectperiode').val());const m=$('#biaya-month').val();if(m)u.searchParams.set('month',m);else u.searchParams.delete('month');a.href=u.href;}
function render(){
charts.forEach(c=>c.destroy());charts=[];
const m=$('#biaya-month').val(),data=rows.filter(r=>!m||num(r.month)===num(m));
$('#biaya-content').prop('hidden',!data.length);$('#biaya-status').text(data.length?'':'Tidak ada kasus pada filter ini.').prop('hidden',!!data.length);
$('#biaya-coverage').text(nf.format(sum(data,'episodes'))+' episode pelayanan');
if(!data.length)return;
const episodeCount=sum(data,'episodes');
const rs=sum(data,'tarif_rs'),ina=sum(data,'total_tarif');
const compactMoney = value => 'Rp ' + new Intl.NumberFormat('id-ID', {
    notation:'compact', maximumFractionDigits:2
}).format(value);
const metrics = [
    ['Episode biaya', nf.format(sum(data,'episodes')), 'Episode pelayanan pada periode terpilih', 'bi-clipboard2-pulse'],
    ['Rata-rata LOS', (sum(data,'los') / episodeCount).toLocaleString('id-ID', {maximumFractionDigits:1}) + ' hari', 'Rata-rata lama rawat episode biaya', 'bi-clock-history'],
    ['Total tarif RS', compactMoney(rs), 'Akumulasi tarif rumah sakit', 'bi-hospital'],
    ['Total INA-CBG', compactMoney(ina), 'Akumulasi TOTAL_TARIF', 'bi-receipt'],
    ['Total IDRG', compactMoney(sum(data,'idrg_total_tarif')), 'Nilai IDRG dari data impor', 'bi-file-earmark-medical'],
    ['Selisih INA-CBG − RS', compactMoney(ina-rs), 'Total INA-CBG dikurangi tarif RS', 'bi-arrow-left-right']
];
$('#biaya-kpis').html(metrics.map(([label,value,description,icon],i) =>
    '<article class="card card-flush ur-kpi ur-kpi-tone-' + i + '">' +
    '<i class="bi ' + icon + ' ur-kpi-icon" aria-hidden="true"></i>' +
    '<div class="ur-kpi-label">' + label + '</div>' +
    '<div class="ur-kpi-value ' + (i === 5 && ina < rs ? 'ur-negative' : '') + '">' + value + '</div>' +
    '<small>' + description + '</small></article>'
).join(''));
if(typeof ApexCharts!=='undefined'){
const active=[...new Set(data.map(r=>num(r.month)))].sort((a,b)=>a-b);
const options=[
['biaya-trend',{chart:{type:'bar',height:285,toolbar:{show:false},animations:{enabled:false}},series:[{name:'Episode',data:active.map(m=>sum(data.filter(r=>num(r.month)===m),'episodes'))}],xaxis:{categories:active.map(m=>months[m-1])},plotOptions:{bar:{columnWidth:'48%',borderRadius:4}},dataLabels:{enabled:false}}],
['biaya-severity',{chart:{type:'donut',height:285,animations:{enabled:false}},series:['I','II','III','Lainnya'].map(s=>sum(data.filter(r=>s==='Lainnya'?!['I','II','III'].includes(r.sl):r.sl===s),'episodes')),labels:['SL I','SL II','SL III','Lainnya'],legend:{position:'bottom'}}]];
options.forEach(([id,opt])=>{const c=new ApexCharts(document.getElementById(id),{...opt,colors:['#087e82','#93b6c3','#64748b','#d3ad69']});charts.push(c);c.render();});
}
const groups=new Map();data.forEach(r=>{const name=r.dpjp||'Belum terisi';if(!groups.has(name))groups.set(name,[]);groups.get(name).push(r);});
if($.fn.DataTable.isDataTable('#biaya-table'))$('#biaya-table').DataTable().destroy();
const term=String($('#biaya-search').val()||'').toLowerCase();
$('#biaya-doctors').html([...groups].filter(([name,items])=>{
    if(!name.toLowerCase().includes(term))return false;
    const selected=$('.biaya-gap-option[aria-pressed="true"]').attr('data-gap');
    const gap=sum(items,'total_tarif')-sum(items,'tarif_rs');
    return !selected||(selected==='positive'&&gap>0)||(selected==='negative'&&gap<0);
}).sort((a,b)=>sum(b[1],'episodes')-sum(a[1],'episodes')).map(([name,r])=>{const gap=sum(r,'total_tarif')-sum(r,'tarif_rs');return '<tr><td>'+esc(name)+'</td><td>'+nf.format(sum(r,'episodes'))+'</td><td>'+(sum(r,'los')/sum(r,'episodes')).toLocaleString('id-ID',{maximumFractionDigits:1})+' hari</td><td>'+money(sum(r,'tarif_rs'))+'</td><td>'+money(sum(r,'total_tarif'))+'</td><td class="'+(gap<0?'ur-negative':'ur-positive')+'">'+money(gap)+'</td></tr>';}).join(''));
initDataTable('#biaya-table',null,10,{searching:false,order:[]});
}
window.load=function(){const token=++generation;if(request)request.abort();$('#biaya-content').prop('hidden',true);$('#biaya-status').text('Memuat ringkasan biaya…').prop('hidden',false);$('#biaya-refresh').prop('disabled',true);
request=$.ajax({url:url+'ur/biaya/summary',type:'POST',dataType:'json',data:{selectperiode:$('#selectperiode').val(),field:$('#cost-field').val(),group:$('#cost-group').val()}}).done(r=>{if(token!==generation)return;if(r.responseCode!=='00'){$('#biaya-status').text(r.responseDesc||'Data gagal dimuat.');return;}rows=Array.isArray(r.responseResult)?r.responseResult:[];render();}).fail((xhr,state)=>{if(token===generation&&state!=='abort')$('#biaya-status').text('Data gagal dimuat. Klik Perbarui untuk mencoba kembali.');}).always(()=>{if(token===generation)$('#biaya-refresh').prop('disabled',false);});};
$('#selectperiode').on('change',()=>{link();window.load();});$('#biaya-month').on('change',()=>{link();render();});$('#biaya-search').on('input',render);$('.biaya-gap-option').on('click',function(){
    $('.biaya-gap-option').attr('aria-pressed','false');
    $(this).attr('aria-pressed','true');
    render();
});$('#biaya-refresh').on('click',window.load);$('#biaya-print').on('click',()=>window.print());link();window.load();
})();
(function(){
    'use strict';
    let rows=[], request=null, generation=0;
    const nf=new Intl.NumberFormat('id-ID',{maximumFractionDigits:2});
    const esc=value=>String(value??'—').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    function render(){
        const field=$('#cost-field').val(), key=$('#cost-group').val(), month=$('#biaya-month').val();
        const groups=new Map();
        rows.filter(r=>!month||Number(r.month)===Number(month)).forEach(r=>{
            const code=r[key]||'Belum terisi';
            if(!groups.has(code))groups.set(code,{n:0,sum:0,squares:0,los:0,min:Infinity,max:-Infinity});
            const g=groups.get(code),n=Number(r.episodes),avg=Number(r[field+'_avg']),sd=Number(r[field+'_stdev']);
            g.n+=n;g.sum+=n*avg;g.squares+=n*(sd*sd+avg*avg);g.los+=n*Number(r.alos);
            g.min=Math.min(g.min,Number(r[field+'_min']));g.max=Math.max(g.max,Number(r[field+'_max']));
        });
        if($.fn.DataTable.isDataTable('#cost-table'))$('#cost-table').DataTable().destroy();
        $('#cost-body').html([...groups].sort((a,b)=>b[1].n-a[1].n).map(([code,g])=>{
            const avg=g.sum/g.n,sd=Math.sqrt(Math.max(0,g.squares/g.n-avg*avg));
            return '<tr><td>'+esc(code)+'</td>'+[g.n,g.los/g.n,g.min,g.max,avg,sd].map(v=>'<td>'+nf.format(v)+'</td>').join('')+'<td>'+nf.format(avg?sd/avg*100:0)+'%</td></tr>';
        }).join(''));
        initDataTable('#cost-table',null,10,{order:[]});
    }
    function load(){
        const token=++generation;if(request)request.abort();rows=[];
        $('#cost-status').text('Memuat variasi komponen tarif…');
        request=$.ajax({url:url+'ur/biaya/components',type:'POST',dataType:'json',data:{selectperiode:$('#selectperiode').val(),field:$('#cost-field').val(),group:$('#cost-group').val()}})
        .done(r=>{if(token!==generation)return;if(r.responseCode!=='00'){$('#cost-status').text(r.responseDesc||'Data gagal dimuat.');return;}rows=Array.isArray(r.responseResult)?r.responseResult:[];$('#cost-status').text('');render();})
        .fail((xhr,state)=>{if(token===generation&&state!=='abort')$('#cost-status').text('Variasi komponen tarif gagal dimuat.');});
    }
    $('#selectperiode,#biaya-refresh').on('change click',function(e){if((this.id==='selectperiode'&&e.type==='change')||(this.id==='biaya-refresh'&&e.type==='click'))load();});
    $('#cost-group,#cost-field').on('change',load);$('#biaya-month').on('change',render);
    load();
})();