(function () {
    'use strict';
    const root=document.getElementById('pending-dashboard');if(!root)return;
    const byId=id=>document.getElementById(id),months=['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const nf=new Intl.NumberFormat('id-ID',{maximumFractionDigits:0}),compact=new Intl.NumberFormat('id-ID',{notation:'compact',maximumFractionDigits:2});
    const money=v=>v==null?'—':'Rp '+compact.format(v),fullMoney=v=>v==null?'Belum terisi':'Rp '+nf.format(v);
    const pct=(part,total)=>total? (part/total*100).toLocaleString('id-ID',{maximumFractionDigits:1})+'%':'—';
    const escape=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const statusName=s=>s||'Status belum terisi',serviceName=s=>s==='1'?'Rawat inap':s==='2'?'Rawat jalan':(s?'Kode '+s:'Pelayanan belum terisi');
    const date=v=>v?new Date(v.slice(0,10)+'T00:00:00').toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}):'—';
    const signed=v=>(v>0?'+':v<0?'−':'')+nf.format(Math.abs(v));
    let summaryRequest=null,detailRequest=null,table=null,searchTimer=null,sequence=0;const charts={};
    function filters(){return {selectperiode:String($('#selectperiode').val()||''),month:byId('pending-month').value,service:byId('pending-service').value,status:byId('pending-status').value};}
    function message(text){byId('pending-message').textContent=text;byId('pending-message').className='alert alert-warning mt-6';}
    function destroyChart(id){if(charts[id]){charts[id].destroy();delete charts[id];}byId(id).replaceChildren();}
    function chart(id,options){
        destroyChart(id);
        if(typeof ApexCharts==='undefined'){byId(id).innerHTML='<p class="pending-empty">Grafik belum tersedia. Angka lengkap dapat dibaca pada tabel.</p>';return;}
        charts[id]=new ApexCharts(byId(id),Object.assign({chart:{fontFamily:'Poppins, sans-serif',height:285,toolbar:{show:false},animations:{enabled:false}},dataLabels:{enabled:false},grid:{borderColor:'#edf2f3',strokeDashArray:4},legend:{position:'top',horizontalAlign:'left',fontSize:'10px'},noData:{text:'Belum ada data'}},options));charts[id].render();
    }
    function line(id,s,field){
        const metric = (month, status) => month?.statuses[status]?.[field] ?? 0;
        const keys=Object.keys(s.statuses),colors=keys.map((key,i)=>key==='PENDING'?'#087e82':key==='TIDAK LAYAK'?'#c9878e':['#93b6c3','#d5bc82','#9fbaa9'][i%3]);
        const series = keys.map(key => ({name:statusName(key),data:Object.values(s.months).map(m=>metric(m,key))}));
        chart(id, klaimLineChartOptions(months, series, colors,
            field === 'count' ? value => nf.format(value) : value => compact.format(value),
            field === 'count' ? value => nf.format(value) + ' klaim' : fullMoney));
    }
    function contributors(id,rows,labelField,countField,total,unit){
        const colors=['#087e82','#6bb5aa','#93b6c3','#d5bc82','#dba990','#ca8989'];
        if(!rows.length){byId(id).innerHTML='<div class="pending-empty">Belum ada data pada filter ini.</div>';return;}
        const shown=rows.reduce((sum,r)=>sum+Number(r[countField]),0);
        byId(id).innerHTML=rows.map((r,i)=>{
            const count=Number(r[countField]),percent=total?Math.max(0,Math.min(100,count/total*100)):0;
            return '<div class="ur-meter-row" role="listitem"><div class="ur-meter-label"><span>'+escape(r[labelField]||'Belum terisi')+'</span><strong>'+nf.format(count)+'</strong></div><div class="ur-meter" aria-hidden="true"><span style="width:'+percent+'%;background:'+colors[i%colors.length]+'"></span></div><div class="pending-contributor-share">'+pct(count,total)+' dari seluruh '+escape(unit)+'</div></div>';
        }).join('')+'<div class="pending-distribution-note">Proporsi dari '+nf.format(total)+' '+escape(unit)+'.'+(shown<total?' '+nf.format(total-shown)+' '+escape(unit)+' lainnya tidak ditampilkan.':'')+'</div>';
    }
    function insight(label,value,text){return '<article class="pending-insight"><span>'+escape(label)+'</span><strong>'+escape(value)+'</strong><p>'+escape(text)+'</p></article>';}
    function render(s){
        const total=s.total.count;byId('pending-content').hidden=!total;
        byId('pending-scope').querySelector('span').textContent=total?date(s.firstDate)+' – '+date(s.lastDate)+' · '+nf.format(total)+' klaim · '+s.monthsWithData+' bulan memiliki data · Diperbarui '+s.generatedAt:'Belum ada klaim pada filter terpilih';
        if(!total){Object.keys(charts).forEach(destroyChart);message('Belum ada data untuk filter ini. Ubah tahun, bulan, pelayanan, atau status.');return;}
        const other=Object.entries(s.statuses).filter(([key])=>!['PENDING','TIDAK LAYAK'].includes(key));
        if(other.length)message('Status lain tetap tercatat: '+other.map(([key,v])=>statusName(key)+' ('+nf.format(v.count)+')').join(', ')+'. Tidak digabungkan ke Tidak Layak.');
        const cards=[];
        [['PENDING','Pending','bi-hourglass-split'],['TIDAK LAYAK','Tidak layak','bi-file-earmark-x']].forEach(([key,label,icon])=>{const m=s.statuses[key];cards.push({label:'Klaim '+label.toLowerCase(),value:nf.format(m.count),note:pct(m.count,total)+' dari klaim tercatat pada filter ini',icon});});
        [['PENDING','pending'],['TIDAK LAYAK','tidak layak']].forEach(([key,label])=>{const m=s.statuses[key];cards.push({label:'Nilai pengajuan '+label,value:money(m.count===0?0:m.amount),title:fullMoney(m.count===0?0:m.amount),note:nf.format(m.valued)+' dari '+nf.format(m.count)+' klaim memiliki nilai'+(m.count?' ('+pct(m.valued,m.count)+')':''),icon:'bi-cash-stack'});});
        byId('pending-kpis').innerHTML=cards.map((c,i)=>'<article class="prem-card pe-kpi col-12 col-md-6 col-xl-3 '+(i===2?'pe-kpi-highlight':'')+'"><div class="pe-kpi-top"><span>'+escape(c.label)+'</span><i class="bi '+c.icon+'" aria-hidden="true"></i></div><strong class="'+(i>=2?'pending-amount-warning':'')+'" title="'+escape(c.title||c.value)+'">'+escape(c.value)+'</strong><p>'+escape(c.note)+'</p></article>').join('');
        const missing=total-s.total.valued;
        byId('pending-value-note').className='pending-value-note'+(missing?'':' is-complete');
        byId('pending-value-note').innerHTML='<i class="bi bi-info-circle" aria-hidden="true"></i><span>'+escape(missing?nf.format(missing)+' klaim belum memiliki nilai pengajuan. Total rupiah hanya mencakup '+nf.format(s.total.valued)+' dari '+nf.format(total)+' klaim ('+pct(s.total.valued,total)+').':'Nilai pengajuan tersedia pada seluruh '+nf.format(total)+' klaim dalam filter ini.')+'</span>';
        const latest=s.months[s.latest],previous=s.previous!==null?s.months[s.previous]:null;
        byId('pending-progress-period').textContent=months[s.latest-1]+' '+filters().selectperiode;
        let insights=insight('Jumlah klaim bulan terakhir',nf.format(latest.total.count)+' klaim',months[s.latest-1]+': '+nf.format(latest.statuses.PENDING.count)+' pending dan '+nf.format(latest.statuses['TIDAK LAYAK'].count)+' tidak layak'+(latest.total.count-latest.statuses.PENDING.count-latest.statuses['TIDAK LAYAK'].count?' serta status lain.':'.'));
        if(previous){const delta=s.change;insights+=insight('Perubahan dibanding '+months[s.previous-1],signed(delta.difference)+' klaim',(delta.percent===null?'Persentase perubahan belum dapat dihitung.':(delta.difference===0?'Tidak berubah':(delta.difference>0?'Naik ':'Turun ')+Math.abs(delta.percent).toLocaleString('id-ID',{maximumFractionDigits:1})+'%')+' dari '+nf.format(previous.total.count)+' menjadi '+nf.format(latest.total.count)+' klaim.')+' Penilaian berdasarkan klaim yang tercatat.');}
        else insights+=insight('Perubahan antarbulan','Belum dapat dibandingkan','Bulan kalender sebelumnya tidak memiliki data pada filter ini. Bulan kosong tidak dianggap nol.');
        insights+=insight('Nilai tercatat bulan terakhir',money(latest.total.amount),nf.format(latest.total.valued)+' dari '+nf.format(latest.total.count)+' klaim memiliki nilai ('+pct(latest.total.valued,latest.total.count)+').'+(previous?' Nilai terisi '+months[s.previous-1]+': '+money(previous.total.amount)+'; kelengkapan '+pct(previous.total.valued,previous.total.count)+'.':''));
        byId('pending-progress').innerHTML=insights;
        line('pending-count-chart',s,'count');line('pending-value-chart',s,'amount');
        byId('pending-monthly').innerHTML=Object.entries(s.months).map(([month,m])=>{
            const label=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][Number(month)-1];
            if(!m)return '<tr><td>'+label+'</td><td>—</td><td>—</td><td>—</td><td><small>Belum ada data</small></td><td>—</td><td>—</td></tr>';
            const prior=s.months[Number(month)-1];const delta=prior?m.total.count-prior.total.count:null;
            return '<tr><td>'+label+'</td><td>'+nf.format(m.statuses.PENDING.count)+'</td><td>'+nf.format(m.statuses['TIDAK LAYAK'].count)+'</td><td>'+nf.format(m.total.count)+'</td><td>'+(delta===null?'—<small>Data bulan sebelumnya belum tersedia</small>':signed(delta)+'<small>'+(prior.total.count?(delta/prior.total.count*100).toLocaleString('id-ID',{maximumFractionDigits:1})+'%':'Jumlah bulan sebelumnya 0')+'</small>')+'</td><td title="'+escape(fullMoney(m.total.amount))+'">'+escape(money(m.total.amount))+'</td><td>'+pct(m.total.valued,m.total.count)+'<small>'+nf.format(m.total.valued)+' / '+nf.format(m.total.count)+' klaim</small></td></tr>';
        }).join('');
        // Use the shared helper; all twelve months stay visible in calendar order.
        initDataTable('#pending-monthly-table',null,12,{paging:false,searching:false,info:false,lengthChange:false,ordering:false});
        const serviceRows=Object.entries(s.services).map(([key,count])=>({label:serviceName(key),count:Number(count)}));
        const serviceColors=['#087e82','#75bdb5','#c0d8e3','#d6dde0'];
        chart('pending-service-chart',{chart:{type:'donut',height:265,fontFamily:'Poppins, sans-serif',animations:{enabled:false}},series:serviceRows.map(r=>r.count),labels:serviceRows.map(r=>r.label),colors:serviceColors,plotOptions:{pie:{donut:{size:'72%',labels:{show:true,total:{show:true,showAlways:true,label:'Total klaim',fontSize:'10px',color:'#81979d',formatter:()=>nf.format(total)},value:{fontSize:'25px',fontWeight:600,color:'#15373e'}}}}},stroke:{width:3,colors:['#fff']},legend:{show:false},dataLabels:{enabled:true,formatter:v=>v.toLocaleString('id-ID',{maximumFractionDigits:1})+'%',style:{fontSize:'11px',fontWeight:500},dropShadow:{enabled:false}},tooltip:{y:{formatter:v=>nf.format(v)+' klaim'}}});
        byId('pending-service-legend').innerHTML=serviceRows.map((r,i)=>'<div><span class="pending-service-label"><i style="background:'+serviceColors[i%serviceColors.length]+'"></i>'+escape(r.label)+'</span><strong>'+nf.format(r.count)+'<small>'+pct(r.count,total)+'</small></strong></div>').join('');
        contributors('pending-type-chart',s.types.slice(0,6),'algoritma_type','jumlah_kasus',total,'klaim');
        const mentions=s.algorithms.reduce((sum,row)=>sum+Number(row.jumlah),0);
        contributors('pending-algorithm-chart',s.algorithms.slice(0,8),'algoritma','jumlah',mentions,'kemunculan algoritma');
    }
    function badge(status){const cls=status==='PENDING'?'pending-pill-warm':status==='TIDAK LAYAK'?'pending-pill-red':'';return '<span class="pending-pill '+cls+'">'+escape(statusName(status))+'</span>';}
    function initDetails(){
        if(table){table.ajax.reload(null,true);return;}
        table=initDataTable('#dataraweklaim_table',null,10,{serverSide:true,processing:true,ordering:false,searchDelay:350,
            dom:'r<"table-responsive pending-detail-scroll"t><"app-table-footer"<"app-table-meta"li>p>',
            pagingType:'simple_numbers',lengthMenu:[10,25,50,100],
            ajax:function(data,callback){
            if(detailRequest)detailRequest.abort();byId('pending-detail-message').classList.add('d-none');
            detailRequest=$.ajax({url:root.dataset.endpoint+'/dataraweklaimdetail',type:'POST',dataType:'json',data:Object.assign({},data,filters())})
                .done(result=>callback(result)).fail((xhr,state)=>{if(state==='abort')return;byId('pending-detail-message').textContent=xhr.responseJSON?.responseDesc||'Rincian klaim gagal dimuat. Klik Perbarui untuk mencoba lagi.';byId('pending-detail-message').classList.remove('d-none');callback({draw:data.draw,recordsTotal:0,recordsFiltered:0,data:[]});});
        },columns:[
            {data:null,render:(v,type,row,meta)=>nf.format(meta.settings._iDisplayStart+meta.row+1)},
            {data:null,className:'pending-patient',render:r=>'<strong>'+escape(r.nama_pasien||'Nama belum terisi')+'</strong><small>SEP '+escape(r.sep||'—')+'</small>'},
            {data:'jnspel',render:v=>escape(serviceName(String(v??'')))},
            {data:null,className:'pending-date',render:r=>'Masuk '+escape(r.admission_date||'—')+'<br>Pulang '+escape(r.discharge_date||'—')},
            {data:'verification_date',className:'pending-date',render:v=>escape(v||'—')},
            {data:null,render:r=>escape(r.kdinacbg||'—')+'<small class="pending-cell-muted">SL '+escape(r.sl||'—')+'</small>'},
            {data:'status',render:badge},
            {data:'pengajuan',className:'pending-cell-money',render:v=>escape(fullMoney(v))},
            {data:'algoritma_type',render:v=>escape(v||'Belum terisi')},
            {data:null,className:'pending-notes',render:r=>r.algoritma||r.keterangan_verifikasi?'<details><summary>Lihat verifikasi</summary><div><b>Algoritma</b>\n'+escape((r.algoritma||'—').split('|').join('\n'))+'\n\n<b>Catatan</b>\n'+escape(r.keterangan_verifikasi||'—')+'</div></details>':'<span class="pending-cell-muted">Belum terisi</span>'}
        ],language:{emptyTable:'Belum ada rincian klaim pada filter ini.',zeroRecords:'Tidak ada klaim yang sesuai pencarian.',processing:'Memuat rincian…',search:'Cari',lengthMenu:'_MENU_',info:'Menampilkan _START_–_END_ dari _TOTAL_ klaim',infoEmpty:'Belum ada klaim',infoFiltered:'(disaring dari _MAX_ klaim)',paginate:{next:'<i class="next" aria-hidden="true"></i><span class="visually-hidden">Berikutnya</span>',previous:'<i class="previous" aria-hidden="true"></i><span class="visually-hidden">Sebelumnya</span>'}}});
    }
    function load(){
        const current=++sequence;if(summaryRequest)summaryRequest.abort();if(detailRequest)detailRequest.abort();
        root.setAttribute('aria-busy','true');byId('pending-loading').hidden=false;byId('pending-content').hidden=true;byId('pending-refresh').disabled=true;byId('pending-message').classList.add('d-none');
        summaryRequest=$.ajax({url:root.dataset.endpoint+'/summary',type:'POST',dataType:'json',data:filters()}).done(result=>{if(current!==sequence)return;if(result.responseCode!=='00'){message(result.responseDesc||'Ringkasan belum dapat dimuat.');return;}render(result.summary);}).fail((xhr,state)=>{if(state==='abort'||current!==sequence)return;Object.keys(charts).forEach(destroyChart);byId('pending-scope').querySelector('span').textContent='Cakupan belum dapat dimuat';message(xhr.responseJSON?.responseDesc||'Ringkasan gagal dimuat. Klik Perbarui untuk mencoba kembali.');}).always(()=>{if(current!==sequence)return;root.setAttribute('aria-busy','false');byId('pending-loading').hidden=true;byId('pending-refresh').disabled=false;});
        initDetails();
    }
    $('#selectperiode').on('change',load);$('#pending-month,#pending-service,#pending-status').on('change',load);byId('pending-refresh').addEventListener('click',load);
    byId('pending-reset').addEventListener('click',()=>{['pending-month','pending-service','pending-status'].forEach(id=>{byId(id).value='';});byId('searchtable').value='';if(table)table.search('');load();});
    byId('searchtable').addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>{if(table)table.search(byId('searchtable').value).draw();},350);});
    // The shared BAHV import modal calls load() after a successful import.
    window.load=load;load();
})();
