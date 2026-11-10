// Run: node tests/ur-severity3.test.js
const fs=require('fs'),vm=require('vm'),assert=require('assert');
const states=new Map(),pending=[];
function state(id){if(!states.has(id))states.set(id,{value:'',html:'',text:'',props:{},events:{}});return states.get(id);}
function $(ids){const items=ids.split(',').map(state);const api={val:()=>items[0].value};['html','text'].forEach(key=>api[key]=value=>{items.forEach(item=>item[key]=value);return api;});api.prop=(key,value)=>{items.forEach(item=>item.props[key]=value);return api;};api.on=(key,handler)=>{items.forEach(item=>item.events[key]=handler);return api;};return api;}
state('#selectperiode').value='2026';state('#tp-sort').value='gap';state('#tp-limit').value='25';
let mode='success',opened=0,closed=0;
const rows=[{month:1,dpjp:'<script>DEMO</script>',all_episodes:10,episodes:2,total_los:4,min_los:0,los_up_to_1:1,los_1_to_4:1,tarif_rs:100,total_tarif:80,idrg_total_tarif:90,first_discharge:'2026-01-01',last_discharge:'2026-01-20'}];
$.ajax=config=>{const cb={done(){},fail(){},always(){}};pending.push(cb);return {abort(){},done(fn){cb.done=fn;return this;},fail(fn){cb.fail=fn;return this;},always(fn){cb.always=fn;return this;}};};
function flush(){while(pending.length){const cb=pending.shift();if(mode==='error')cb.fail({},'error');else cb.done({responseCode:'00',responseResult:rows});cb.always();}}
const context={$,Intl,Date,Number,String,Set,Map,Math,console,url:'/',document:{getElementById:()=>({})},window:{print(){}},Swal:{fire(){},showLoading(){},close(){}},ApexCharts:function(){this.destroy=()=>{};this.render=()=>{};}};
vm.runInNewContext(fs.readFileSync('public/assets/js/ur/severity3.js','utf8'),context);flush();
assert(state('#s3-kpis').html.includes('20%'),'Correct denominator');
assert(state('#s3-kpis').html.includes('2 hari'),'Weighted average LOS');
assert(state('#s3-kpis').html.includes('0 hari'),'Minimum LOS preserves zero');
assert(state('#s3-dpjp').html.includes('&lt;script&gt;'),'Escaped doctor name');
state('#s3-search').value='unknown';state('#s3-search').events.input();assert(state('#s3-dpjp').html.includes('Tidak ada DPJP'));
state('#s3-month').value='12';state('#s3-month').events.change();assert.equal(state('#s3-content').props.hidden,true);
mode='error';context.window.load();flush();assert(state('#s3-status').text.includes('gagal'));assert.equal(state('#s3-refresh').props.disabled,false);
console.log('PASS: severity denominator, weighted LOS, escaped labels, search, empty month, request failure.');
