// Run with: node tests/ur-casemixindex.test.js
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const elements = new Map();
function element(id) {
    if (!elements.has(id)) elements.set(id, { value:'', html:'', text:'', props:{}, events:{} });
    return elements.get(id);
}
function $(id) {
    const state = element(id);
    return {
        val: () => state.value,
        html: value => { state.html=value; return $(id); },
        text: value => { state.text=value; return $(id); },
        prop: (key,value) => { state.props[key]=value; return $(id); },
        on: (event,handler) => { state.events[event]=handler; return $(id); }
    };
}
element('#selectperiode').value='2026';
let mode='success', open=0, closed=0;
const result = {
    rows:[
        {month:1,ptd:1,cg:'1',cmg:'A',sl:'I',episodes:10,total_tarif:800,tarif_rs:1000,total_los:30,los_cases:10,weight_sum:12,weight_cases:10,first_discharge:'2026-01-01',last_discharge:'2026-01-15'},
        {month:2,ptd:2,cg:'5',cmg:'Q',sl:'0',episodes:20,total_tarif:2000,tarif_rs:2200,total_los:0,los_cases:0,weight_sum:0,weight_cases:0,first_discharge:'2026-02-01',last_discharge:'2026-02-20'}
    ],
    casegroups:[{id_cg:'1',name_case_group:'<script>bad</script>',notes:'Review'},{id_cg:'5',name_case_group:'Medical',notes:''}],
    cmgs:[{group_code:'A',group_name:'Known CMG',description:''}]
};
$.ajax=()=>({
    abort(){},
    done(handler){ if(mode==='success') handler({responseCode:'00',responseResult:result}); return this; },
    fail(handler){ if(mode==='error') handler({},'error'); return this; },
    always(handler){ handler(); return this; }
});
const context={
    $,Intl,Date,Number,String,Set,Map,Math,console,url:'/',
    document:{getElementById:()=>({})},
    ApexCharts:function(){this.render=()=>{};this.destroy=()=>{};},
    Swal:{fire(){open++;},showLoading(){},close(){closed++;}},
    window:{print(){}}
};
vm.runInNewContext(fs.readFileSync('public/assets/js/ur/casemixindex.js','utf8'),context);
assert(element('#cm-kpis').html.includes('>30<'), 'All episodes retained');
assert(element('#cm-quality-text').text.includes('20 episode pada CMG Q'), 'Unmapped CMG counted');
assert.equal(element('#cm-quality').props.hidden,false);
assert(element('#cm-body-cmg').html.includes('Q'), 'Unknown CMG in monthly details');
assert(element('#cm-casegroups').html.includes('&lt;script&gt;'), 'Untrusted master label escaped');
assert(!element('#cm-casegroups').html.includes('<script>'), 'No injected script');
assert(element('#cm-foot-cmg').html.includes('>30<'), 'CMG totals reconcile');
element('#cm-service').value='1';element('#cm-month,#cm-service').events.change();
assert(element('#cm-kpis').html.includes('>10<'), 'Service filter');
assert(element('#cm-kpis').html.includes('3 hari'), 'ALOS');
assert(element('#cm-kpis').html.includes('1,20'), 'Average valid imported weight');
assert.equal(element('#cm-quality').props.hidden,true);
element('#cm-search').value='no match';element('#cm-search').events.input();
assert(element('#cm-body-cg').html.includes('Tidak ada kelompok'), 'Empty search result');
element('#cm-month').value='12';element('#cm-month,#cm-service').events.change();
assert.equal(element('#cm-content').props.hidden,true);
assert(element('#cm-status').text.includes('Belum ada data'), 'Empty month');
mode='error';context.window.load();
assert(element('#cm-status').text.includes('gagal'), 'Request error');
assert.equal(element('#cm-refresh').props.disabled,false);
assert.equal(open,closed,'Loading closed on success and failure');
console.log('PASS: totals, unknown CMG, escaping, filters, ALOS, weight average, search, empty period, request error, Swal lifecycle.');
