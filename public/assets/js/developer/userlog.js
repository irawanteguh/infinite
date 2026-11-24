(function () {
    const search = document.getElementById('userlog-search');
    const table = document.getElementById('userlog-daily-table');
    if (!search || !table) return;

    const searchControl = document.getElementById('userlog-search-control');
    const empty = document.getElementById('userlog-search-empty');
    const normalize = value => value.toLocaleLowerCase('id').trim().replace(/\s+/g, ' ');
    const entries = Array.from(table.tBodies[0].rows, row => ({
        row,
        text: normalize(Array.from(row.cells).slice(0, -1).map(cell => cell.textContent).join(' '))
    }));

    function filterActivities() {
        const terms = normalize(search.value).split(' ');
        let hasMatch = false;
        for (const { row, text } of entries) {
            const matches = terms.every(term => text.includes(term));
            row.hidden = !matches;
            hasMatch = hasMatch || matches;
        }
        empty.hidden = hasMatch;
    }

    search.addEventListener('input', filterActivities);
    searchControl.hidden = false;
    filterActivities();
})();
