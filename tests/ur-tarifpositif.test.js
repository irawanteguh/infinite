// Run: node tests/ur-tarifpositif.test.js
const fs=require('fs'),vm=require('vm'),assert=require('assert');
const states=new Map(),pending=[];
function state(id){if(!states.has(id))states.set(id,{value:'',html:'',text:'',props:{},events:{}});return states.get(id);}
function $(ids){const items=ids.split(',').map(state);const api={val:()=>items[0].value};['html','text'].forEach(key=>api[key]=value=>{items.forEach(item=>item[key]=value);return api;});api.prop=(key,value)=>{items.forEach(item=>item.props[key]=value);return api;};api.on=(key,handler)=>{items.forEach(item=>item.events[key]=handler);return api;};return api;}
state('#selectperiode').value='2026';state('#tp-sort').value='gap';state('#tp-limit').value='25';
let mode='success',opened=0,closed=0;
const rows=[{month:1,ptd:1,dpjp:'<script>DEMO</script>',all_episodes:10,positive_episodes:4,tarif_rs:100,total_tarif:200,positive_gap:100,first_discharge:'2026-01-01',last_discharge:'2026-01-20'},{month:2,ptd:2,dpjp:'DOKTER DEMO',all_episodes:20,positive_episodes:5,tarif_rs:200,total_tarif:400,positive_gap:200,first_discharge:'2026-02-01',last_discharge:'2026-02-20'}];
$.ajax=config=>{const callbacks={config,done(){},fail(){},always(){}};pending.push(callbacks);return {abort(){callbacks.aborted=true;},done(fn){callbacks.done=fn;return this;},fail(fn){callbacks.fail=fn;return this;},always(fn){callbacks.always=fn;return this;}};};
function flush(){while(pending.length){const r=pending.shift();if(r.aborted)continue;if(mode==='error'){r.fail({},'error');}else{const summary=r.config.url.endsWith('/summary');r.done({responseCode:'00',responseResult:summary?rows:{rows:[{ptd:1,sep:'TEST',nama_pasien:'<script>x</script>',mrn:'DEMO',diaglist:'A;B',total_tarif:200,tarif_rs:100,positive_gap:100,idrg_total_tarif:null}],total:30,offset:r.config.data.offset,limit:25}});}r.always();}}
const context={$,Intl,Date,Number,String,Set,Math,console,clearTimeout,setTimeout,url:'/',document:{getElementById:()=>({})},window:{print(){}},Swal:{fire(){opened++;},showLoading(){},close(){closed++;}},ApexCharts:function(){this.destroy=()=>{};this.render=()=>{};}};
vm.runInNewContext(fs.readFileSync('public/assets/js/ur/tarifpositif.js','utf8'),context);flush();
assert(state('#tp-kpis').html.includes('>9<'),'Positive episodes aggregated');
assert(state('#tp-kpis').html.includes('30%'),'Denominator includes all episodes');
assert(state('#tp-dpjp-rows-1').html.includes('&lt;script&gt;DEMO&lt;/script&gt;'),'DPJP safely escaped');
assert(state('#tp-dpjp-rows-2').html.includes('DOKTER DEMO'),'Outpatient DPJP');
assert(state('#tp-dpjp-total-1').html.includes('>4<'),'Inpatient DPJP totals');
state('#tp-dpjp-search').value='no match';state('#tp-dpjp-search').events.input();
assert(state('#tp-dpjp-rows-1').html.includes('Tidak ada DPJP'),'DPJP search empty');
state('#tp-dpjp-search').value='';state('#tp-dpjp-search').events.input();
state('#tp-service').value='1';state('#tp-service').events.change();flush();assert(state('#tp-kpis').html.includes('>4<'));
state('#tp-month').value='12';state('#tp-month').events.change();assert.equal(state('#tp-content').props.hidden,true);assert(state('#tp-status').text.includes('Belum ada data'));
mode='error';context.window.load();flush();assert(state('#tp-status').text.includes('gagal'));assert.equal(state('#tp-refresh').props.disabled,false);
console.log('PASS: summary totals, proportions, escaping, DPJP aggregates/search, service/month filters, empty data, request failure.');
