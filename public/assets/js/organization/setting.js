(function () {
    const mode = document.getElementById('deadline-mode');
    const fields = document.getElementById('deadline-day-fields');
    const day = document.getElementById('deadline-day');
    if (!mode || !fields || !day) return;

    function updateDeadlineFields() {
        const limited = mode.value === 'limited';
        fields.hidden = !limited;
        day.disabled = !limited;
        day.required = limited;
    }

    $(mode).on('change', updateDeadlineFields);
    updateDeadlineFields();

    $('#grant-history-search').on('input', function () {
        const keyword = this.value.toLocaleLowerCase();
        $('#grant-history-table tbody tr').each(function () {
            this.hidden = !this.textContent.toLocaleLowerCase().includes(keyword);
        });
    });
})();
