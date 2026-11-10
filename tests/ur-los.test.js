// Run: node tests/ur-los.test.js
const fs=require('fs'),vm=require('vm'),assert=require('assert');
const states=new Map(),pending=[];
function state(id){if(!states.has(id))states.set(id,{value:'',html:'',text:'',props:{},events:{}});return states.get(id);}
function $(ids){const items=ids.split(',').map(state);const api={val:()=>items[0].value};['html','text'].forEach(key=>api[key]=value=>{items.forEach(item=>item[key]=value);return api;});api.prop=(key,value)=>{items.forEach(item=>item.props[key]=value);return api;};api.on=(key,handler)=>{items.forEach(item=>item.events[key]=handler);return api;};return api;}
state('#selectperiode').value='2026';
let mode='success';
const rows=[{month:1,dpjp:'<script>DEMO</script>',all_episodes:10,episodes:2,total_los:1,los_0:1,los_1:1,tarif_rs:100,total_tarif:80,idrg_total_tarif:90,first_discharge:'2026-01-01',last_discharge:'2026-01-20'}];
$.ajax=config=>{const cb={done(){},fail(){},always(){}};pending.push(cb);return {abort(){},done(fn){cb.done=fn;return this;},fail(fn){cb.fail=fn;return this;},always(fn){cb.always=fn;return this;}};};
function flush(){while(pending.length){const cb=pending.shift();if(mode==='error')cb.fail({},'error');else cb.done({responseCode:'00',responseResult:rows});cb.always();}}
const context={$,Intl,Date,Number,String,Set,Map,Math,console,url:'/',document:{getElementById:()=>({})},window:{print(){}},Swal:{fire(){},showLoading(){},close(){}},ApexCharts:function(){this.destroy=()=>{};this.render=()=>{};}};
vm.runInNewContext(fs.readFileSync('public/assets/js/ur/los.js','utf8'),context);flush();
assert(state('#los-kpis').html.includes('20%'),'Correct inpatient denominator');
assert(state('#los-kpis').html.includes('0,5 hari'),'Weighted average LOS');
assert(state('#los-dpjp').html.includes('&lt;script&gt;'),'Escaped doctor name');
state('#los-search').value='unknown';state('#los-search').events.input();assert(state('#los-dpjp').html.includes('Tidak ada DPJP'));
state('#los-month').value='12';state('#los-month').events.change();assert.equal(state('#los-content').props.hidden,true);
mode='error';context.window.load();flush();assert(state('#los-status').text.includes('gagal'));assert.equal(state('#los-refresh').props.disabled,false);
console.log('PASS: LOS denominator, weighted average, escaped labels, filters, and request failure.');
