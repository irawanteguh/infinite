(function(){
'use strict';
const nf=new Intl.NumberFormat('id-ID'), months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
const num=v=>Number(v)||0, sum=(r,k)=>r.reduce((n,x)=>n+num(x[k]),0), money=v=>'Rp '+nf.format(v);
const esc=v=>String(v??'—').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let rows=[], charts=[],request, generation=0;
function link(){const a=document.getElementById('pn-detail-link'),u=new URL(a.href);u.searchParams.set('year',$('#selectperiode').val());const m=$('#pn-month').val();if(m)u.searchParams.set('month',m);else u.searchParams.delete('month');a.href=u.href;}
function render(){
charts.forEach(c=>c.destroy());charts=[];
const m=$('#pn-month').val(),data=rows.filter(r=>!m||num(r.month)===num(m));
$('#pn-content').prop('hidden',!data.length);$('#pn-status').text(data.length?'':'Tidak ada kasus pada filter ini.').prop('hidden',!!data.length);
$('#pn-coverage').text(nf.format(data.length)+' episode rawat inap');
if(!data.length)return;
const rs=sum(data,'tarif_rs'),ina=sum(data,'total_tarif');
const compactMoney = value => 'Rp ' + new Intl.NumberFormat('id-ID', {
    notation:'compact', maximumFractionDigits:2
}).format(value);
const metrics = [
    ['Episode pneumonia', nf.format(data.length), 'Episode rawat inap pada periode terpilih', 'bi-lungs'],
    ['Rata-rata LOS', (sum(data,'los') / data.length).toLocaleString('id-ID', {maximumFractionDigits:1}) + ' hari', 'Rata-rata lama rawat episode pneumonia', 'bi-clock-history'],
    ['Total tarif RS', compactMoney(rs), 'Akumulasi tarif rumah sakit', 'bi-hospital'],
    ['Total INA-CBG', compactMoney(ina), 'Akumulasi TOTAL_TARIF', 'bi-receipt'],
    ['Total IDRG', compactMoney(sum(data,'idrg_total_tarif')), 'Nilai IDRG dari data impor', 'bi-file-earmark-medical'],
    ['Selisih INA-CBG − RS', compactMoney(ina-rs), 'Total INA-CBG dikurangi tarif RS', 'bi-arrow-left-right']
];
$('#pn-kpis').html(metrics.map(([label,value,description,icon],i) =>
    '<article class="card card-flush ur-kpi ur-kpi-tone-' + i + '">' +
    '<i class="bi ' + icon + ' ur-kpi-icon" aria-hidden="true"></i>' +
    '<div class="ur-kpi-label">' + label + '</div>' +
    '<div class="ur-kpi-value ' + (i === 5 && ina < rs ? 'ur-negative' : '') + '">' + value + '</div>' +
    '<small>' + description + '</small></article>'
).join(''));
if(typeof ApexCharts!=='undefined'){
const active=[...new Set(data.map(r=>num(r.month)))].sort((a,b)=>a-b);
const options=[
['pn-trend',{chart:{type:'bar',height:285,toolbar:{show:false},animations:{enabled:false}},series:[{name:'Episode',data:active.map(m=>data.filter(r=>num(r.month)===m).length)}],xaxis:{categories:active.map(m=>months[m-1])},plotOptions:{bar:{columnWidth:'48%',borderRadius:4}},dataLabels:{enabled:false}}],
['pn-severity',{chart:{type:'donut',height:285,animations:{enabled:false}},series:['I','II','III','Lainnya'].map(s=>data.filter(r=>s==='Lainnya'?!['I','II','III'].includes(r.sl):r.sl===s).length),labels:['SL I','SL II','SL III','Lainnya'],legend:{position:'bottom'}}]];
options.forEach(([id,opt])=>{const c=new ApexCharts(document.getElementById(id),{...opt,colors:['#087e82','#93b6c3','#64748b','#d3ad69']});charts.push(c);c.render();});
}
const groups=new Map();data.forEach(r=>{const name=r.dpjp||'Belum terisi';if(!groups.has(name))groups.set(name,[]);groups.get(name).push(r);});
if($.fn.DataTable.isDataTable('#pn-table'))$('#pn-table').DataTable().destroy();
const term=String($('#pn-search').val()||'').toLowerCase();
$('#pn-doctors').html([...groups].filter(([name,items])=>{
    if(!name.toLowerCase().includes(term))return false;
    const selected=$('.pn-gap-option[aria-pressed="true"]').attr('data-gap');
    const gap=sum(items,'total_tarif')-sum(items,'tarif_rs');
    return !selected||(selected==='positive'&&gap>0)||(selected==='negative'&&gap<0);
}).sort((a,b)=>b[1].length-a[1].length).map(([name,r])=>{const gap=sum(r,'total_tarif')-sum(r,'tarif_rs');return '<tr><td>'+esc(name)+'</td><td>'+nf.format(r.length)+'</td><td>'+(sum(r,'los')/r.length).toLocaleString('id-ID',{maximumFractionDigits:1})+' hari</td><td>'+money(sum(r,'tarif_rs'))+'</td><td>'+money(sum(r,'total_tarif'))+'</td><td class="'+(gap<0?'ur-negative':'ur-positive')+'">'+money(gap)+'</td></tr>';}).join(''));
initDataTable('#pn-table',null,10,{searching:false,order:[]});
}
window.load=function(){const token=++generation;if(request)request.abort();$('#pn-content').prop('hidden',true);$('#pn-status').text('Memuat ringkasan pneumonia…').prop('hidden',false);$('#pn-refresh').prop('disabled',true);
request=$.ajax({url:url+'ur/pneumonia/summary',type:'POST',dataType:'json',data:{selectperiode:$('#selectperiode').val()}}).done(r=>{if(token!==generation)return;if(r.responseCode!=='00'){$('#pn-status').text(r.responseDesc||'Data gagal dimuat.');return;}rows=Array.isArray(r.responseResult)?r.responseResult:[];render();}).fail((xhr,state)=>{if(token===generation&&state!=='abort')$('#pn-status').text('Data gagal dimuat. Klik Perbarui untuk mencoba kembali.');}).always(()=>{if(token===generation)$('#pn-refresh').prop('disabled',false);});};
$('#selectperiode').on('change',()=>{link();window.load();});$('#pn-month').on('change',()=>{link();render();});$('#pn-search').on('input',render);$('.pn-gap-option').on('click',function(){
    $('.pn-gap-option').attr('aria-pressed','false');
    $(this).attr('aria-pressed','true');
    render();
});$('#pn-refresh').on('click',window.load);$('#pn-print').on('click',()=>window.print());link();window.load();
})();