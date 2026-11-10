/* Matrix copied from the institution's PETA RISIKO 2026 workbook. */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    var matrix = [[1, 3, 5, 8, 20], [2, 7, 11, 13, 21], [4, 10, 14, 17, 22], [6, 12, 16, 19, 24], [9, 15, 18, 23, 25]];
    document.querySelectorAll('[data-risk-assessment]').forEach(function (form) {
        var preview = form.querySelector('[data-score-preview]');
        function update() {
            var likelihood = Number(form.elements.likelihood.value);
            var impact = Number(form.elements.impact_level.value);
            var score = matrix[likelihood - 1][impact - 1];
            var color = score <= 5 ? 'Biru' : score <= 11 ? 'Hijau' : score <= 15 ? 'Kuning' : score <= 19 ? 'Oranye' : 'Merah';
            var text = 'Skor: ' + score + ' · ' + color + ' · ' + (score >= 12 ? 'Perlu penanganan' : 'Di bawah ambang penanganan');
            if (preview.dataset.baseline) {
                var baseline = Number(preview.dataset.baseline);
                text += '. Skor awal: ' + baseline + '. Tren: ' + (score < baseline ? 'Menurun' : score > baseline ? 'Meningkat' : 'Tetap');
            }
            preview.textContent = text;
            form.querySelectorAll('[data-action-required]').forEach(function (field) { field.required = score >= 12; });
        }
        form.elements.likelihood.addEventListener('change', update);
        form.elements.impact_level.addEventListener('change', update);
        form.addEventListener('submit', function () {
            var button = form.querySelector('button[type="submit"], button:not([type])');
            if (button) { button.disabled = true; button.textContent = 'Menyimpan…'; }
        });
        update();
    });
    var print = document.getElementById('risk-print');
    if (print) { print.addEventListener('click', function () { window.print(); }); }
    function openProfile() {
        if (window.location.hash === '#risk-profile') {
            var profile = document.getElementById('risk-profile');
            if (profile) { profile.open = true; }
        }
    }
    window.addEventListener('hashchange', openProfile);
    openProfile();
});
