(function () {
    'use strict';
    const locale = {
        firstDayOfWeek: 1,
        weekdays: {shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'], longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']},
        months: {shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']}
    };
    function enhance(root) {
        if (typeof flatpickr !== 'function') return;
        const inputs = root.matches && root.matches('input[type="date"]') ? [root] : [];
        if (root.querySelectorAll) inputs.push(...root.querySelectorAll('input[type="date"]'));
        inputs.forEach((input) => {
            if (input._flatpickr || input.dataset.datepicker === 'native') return;
            flatpickr(input, {
                locale, dateFormat: 'Y-m-d', altInput: true, altFormat: 'j F Y',
                disableMobile: true, allowInput: false,
                minDate: input.min || undefined, maxDate: input.max || undefined,
                onReady: (_, __, picker) => {
                    picker.calendarContainer.classList.add('app-date-calendar');
                    picker.altInput.classList.add('app-date-input');
                    const label = input.labels && input.labels[0];
                    picker.altInput.setAttribute('aria-label', input.getAttribute('aria-label') || (label ? label.textContent.trim() : input.name || 'Tanggal'));
                    if (input.id && label) {
                        picker.altInput.id = input.id + '-display';
                        label.htmlFor = picker.altInput.id;
                    }
                }
            });
        });
    }
    function start() {
        enhance(document);
        new MutationObserver((records) => {
            records.forEach((record) => record.addedNodes.forEach((node) => {
                if (node.nodeType === 1) enhance(node);
            }));
        }).observe(document.body, {childList: true, subtree: true});
        document.addEventListener('reset', (event) => {
            setTimeout(() => event.target.querySelectorAll('input').forEach((input) => {
                if (input._flatpickr) input._flatpickr.setDate(input.defaultValue || null, false);
            }), 0);
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
