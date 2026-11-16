const automaticDataTables = new WeakMap();

function initDataTable(tableSelector, searchSelector = null, pageLength = 10, options = {}) {

    const element = $(tableSelector)[0];
    if (!element) return null;
    automaticDataTables.delete(element);

    if ($.fn.DataTable.isDataTable(tableSelector)) {
        // Data rows may already have been replaced by an AJAX refresh. Keep
        // that current markup while removing the old DataTables instance.
        const currentRows = $(tableSelector).find("tbody").html();
        $(tableSelector).DataTable().destroy();
        $(tableSelector).find("tbody").html(currentRows);
    }

    const defaultOptions = {
        responsive: false,
        pageLength: pageLength,
        autoWidth: false,
        destroy: true,
        ordering: false,
        searching: true,
        info: true,
        language: {
            emptyTable: "No data available"
        }
    };

    // DataTables does not support colspan placeholders in tbody.
    const body = element.tBodies[0];
    let placeholder = null;
    if (body && body.rows.length === 1 && body.rows[0].cells.length === 1 && body.rows[0].cells[0].colSpan > 1) {
        placeholder = body.rows[0].textContent.trim();
        body.replaceChildren();
    }
    const settings = $.extend(true, {}, defaultOptions, options);
    if (placeholder && !(options.language && options.language.emptyTable)) settings.language.emptyTable = placeholder;

    const table = $(tableSelector).DataTable(
        settings
    );

    // Search
    if (searchSelector) {
        $(searchSelector)
            .off("keyup.datatable input.datatable search.datatable")
            .on("keyup.datatable input.datatable search.datatable", function () {
                table.search(this.value).draw();
            });
    }

    if (searchSelector) table.search($(searchSelector).val() || '').draw();
    return table;
}

// Tables with their own rendering/filtering keep those controls and use the same
// helper for consistent lifecycle handling. Explicit page initializers take precedence.
(function () {
    function start() {
        if (!window.jQuery || !$.fn.DataTable) return;
        let scheduled = false;
        const signature = (table) => Array.from(table.tBodies).map(body => body.innerHTML).join('');
        function enhance(table) {
            if (table.dataset.datatable === 'false' || !table.tHead || !table.tBodies.length) return;
            const previous = automaticDataTables.get(table);
            if ($.fn.DataTable.isDataTable(table) && !previous) return;
            if (previous && previous.signature === signature(table)) return;
            const header = table.tHead.rows[table.tHead.rows.length - 1];
            if (!header || !header.cells.length) return;
            const columns = Array.from(header.cells).reduce((sum, cell) => sum + cell.colSpan, 0);
            const rows = Array.from(table.tBodies[0].rows);
            const placeholder = columns > 1 && rows.length === 1 && rows[0].cells.length === 1 && rows[0].cells[0].colSpan === columns;
            // Matrices and grouped body rows require their dedicated page configuration.
            if (!placeholder && rows.some(row => row.cells.length !== columns ||
                Array.from(row.cells).some(cell => cell.colSpan !== 1 || cell.rowSpan !== 1))) return;
            if (previous && $.fn.DataTable.isDataTable(table)) {
                const api = $(table).DataTable();
                const currentRows = placeholder ? [] : rows;
                if (placeholder) api.settings()[0].oLanguage.sEmptyTable = rows[0].textContent.trim();
                api.clear();
                api.rows.add(currentRows).draw(false);
                previous.signature = signature(table);
                return;
            }
            initDataTable(table, null, 10, {
                paging: false, searching: false, info: false, lengthChange: false
            });
            const state = {signature: signature(table)};
            automaticDataTables.set(table, state);
            $(table).off('draw.dt.standardTables').on('draw.dt.standardTables', () => {
                if (automaticDataTables.get(table) === state) state.signature = signature(table);
            });
        }
        function scan() {
            scheduled = false;
            document.querySelectorAll('table.table').forEach(enhance);
        }
        const observer = new MutationObserver(records => {
            if (!records.some(record => {
                const node = record.target.nodeType === 1 ? record.target : record.target.parentElement;
                return node && (node.closest('table') || Array.from(record.addedNodes).some(child =>
                    child.nodeType === 1 && (child.matches('table') || child.querySelector('table'))));
            }) || scheduled) return;
            scheduled = true;
            queueMicrotask(scan);
        });
        observer.observe(document.body, {childList: true, subtree: true, characterData: true});
        scan();
        $(document).on('shown.bs.tab.standardTables shown.bs.modal.standardTables', function () {
            $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();