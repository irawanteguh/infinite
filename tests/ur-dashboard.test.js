// Run with: node tests/ur-dashboard.test.js
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const elements = new Map();
function element(id) {
    if (!elements.has(id)) elements.set(id, { value: '', html: '', text: '', props: {}, events: {} });
    return elements.get(id);
}
element('#selectperiode').value = '2026';
let mode = 'success';
const fixture = [
    { periode: '01', ptd: '1', sl: 'I', jumlah_kasus: 10, tarif_rs: 1000, total_tarif: 800, idrg_total_tarif: 900, negative_cases: 6, negative_gap: 300, total_los: 30, los_cases: 10, short_stay: 1, long_sl1: 2, short_sl3: 0, first_discharge: '2026-01-01 00:00:00', last_discharge: '2026-01-15 00:00:00', obat: 400 },
    { periode: '02', ptd: '2', sl: '0', jumlah_kasus: 20, tarif_rs: 2000, total_tarif: 2200, idrg_total_tarif: 2300, first_discharge: '2026-02-01 00:00:00', last_discharge: '2026-02-28 00:00:00', obat: 500 }
];
function $(id) {
    const state = element(id);
    return {
        val: () => state.value,
        html: value => { state.html = value; return $(id); },
        text: value => { state.text = value; return $(id); },
        prop: (key, value) => { state.props[key] = value; return $(id); },
        attr: () => $(id),
        on: (event, handler) => { state.events[event] = handler; return $(id); }
    };
}
$.ajax = () => ({
    abort() {},
    done(handler) { if (mode === 'success') handler({ responseResult: fixture }); return this; },
    fail(handler) { if (mode === 'error') handler({}, 'error'); return this; },
    always(handler) { handler(); return this; }
});
const context = {
    $, Intl, Date, Number, String, Set, Math, console, url: '/',
    Swal: { fire() {}, showLoading() {}, close() {} },
    document: { getElementById: () => ({}) },
    ApexCharts: function () { this.render = () => {}; this.destroy = () => {}; },
    window: { print() {} }
};
vm.runInNewContext(fs.readFileSync('public/assets/js/ur/dashboard.js', 'utf8'), context);
assert(element('#ur-kpis').html.includes('>30<'), 'All service totals');
element('#ur-service').value = '1';
element('#ur-month,#ur-service').events.change();
assert(element('#ur-kpis').html.includes('>10<'), 'Inpatient filter');
assert(element('#ur-clinical').html.includes('3 hari'), 'ALOS denominator');
element('#ur-month').value = '12';
element('#ur-month,#ur-service').events.change();
assert.equal(element('#ur-content').props.hidden, true);
assert(element('#ur-status').text.includes('Belum ada data'), 'Empty period');
mode = 'error';
context.window.dataraweklaim();
assert(element('#ur-status').text.includes('gagal'), 'Request error');
assert.equal(element('#ur-refresh').props.disabled, false);
console.log('PASS: totals, service filter, ALOS, empty period, request failure.');
