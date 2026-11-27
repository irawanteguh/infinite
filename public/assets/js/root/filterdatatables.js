// Count leaf columns, including cells carried from an earlier header row by rowspan.
function dataTableColumnCount(head) {
    const occupied = [];
    let columns = 0;
    Array.from(head.rows).forEach((row, rowIndex) => {
        occupied[rowIndex] = occupied[rowIndex] || [];
        let column = 0;
        Array.from(row.cells).forEach(cell => {
            while (occupied[rowIndex][column]) column++;
            for (let r = rowIndex; r < rowIndex + cell.rowSpan; r++) {
                occupied[r] = occupied[r] || [];
                for (let c = column; c < column + cell.colSpan; c++) occupied[r][c] = true;
            }
            column += cell.colSpan;
            columns = Math.max(columns, column);
        });
    });
    return columns;
}

// Shared card presentation; existing IDs and search handlers remain intact.
let standardTableSearchId = 0;
function prepareTableCard(element, searchSelector, options) {
    const card = element.closest('.card, .prem-card');
    if (!card || !card.closest('#kt_content, .modal')) return searchSelector;
    card.classList.add('app-table-card');
    // Tabbed/multi-table cards retain their shared controls and section headings.
    if (Array.from(card.querySelectorAll('table')).filter(table => table.closest('.card, .prem-card') === card).length !== 1) {
        card.querySelectorAll('.pe-master-table-heading, .cm-detail-head').forEach(header => {
            header.classList.add('app-table-heading');
            header.querySelectorAll('h2, h3').forEach(title => title.classList.add('app-table-title'));
            header.querySelectorAll('p').forEach(description => description.classList.add('app-table-description'));
            header.querySelectorAll('.cm-search, .pe-master-search').forEach(search => search.classList.add('app-table-search'));
        });
        return searchSelector;
    }
    let header = Array.from(card.querySelectorAll('.card-header, .ek-table-heading, .pe-panel-heading, .ur-panel-head, .pe-master-table-heading, .app-table-heading')).find(node => node.closest('.card, .prem-card') === card);
    if (!header) {
        header = document.createElement('div'); header.classList.add('card-header');
        card.prepend(header);
    }
    header.classList.add('app-table-heading');
    let title = Array.from(header.querySelectorAll('h2, h3, .card-title')).find(node => !node.querySelector('input, select'));
    if (!title) {
        const copy = document.createElement('div');
        copy.className = 'app-table-heading-copy';
        title = document.createElement('h2');
        const pageTitle = document.querySelector('#kt_content h1, #kt_toolbar h1');
        title.textContent = element.caption?.textContent.trim() || pageTitle?.textContent.trim() || 'Daftar data';
        copy.append(title);
        header.prepend(copy);
    }
    title.classList.add('app-table-title');
    let description = header.querySelector('p');
    if (!description) {
        description = document.createElement('p');
        description.textContent = 'Telusuri data dan rincian pada tabel berikut.';
        if (title.parentElement === header) {
            const copy = document.createElement('div');
            copy.className = 'app-table-heading-copy';
            title.before(copy); copy.append(title, description);
        } else title.after(description);
    }
    description.classList.add('app-table-description');
    let search = searchSelector ? $(searchSelector)[0] : header.querySelector('input[type="search"], input[data-kt-customer-table-filter="search"]');
    if (!search && options.searching !== false) {
        search = document.createElement('input');
        search.type = 'search'; search.className = 'form-control form-control-solid';
        search.id = 'app-table-search-' + (++standardTableSearchId);
        search.placeholder = 'Cari data…';
    }
    if (search) {
        // Keep controls outside this card (for example a shared tab search) in place.
        if (!search.isConnected || card.contains(search)) {
            let wrap = search.closest('.ek-search, .cm-search, .pe-master-search, .app-table-search, .position-relative');
            if (!wrap || !header.contains(wrap)) {
                wrap = document.createElement('div');
                wrap.append(search);
            }
            wrap.classList.add('app-table-search');
            header.append(wrap);
            search.setAttribute('aria-label', search.getAttribute('aria-label') || 'Cari ' + title.textContent.trim());
            header.querySelectorAll('.card-title').forEach(node => { if (!node.textContent.trim() && !node.querySelector('input, button, a')) node.remove(); });
        }
        if (!search.id) search.id = 'app-table-search-' + (++standardTableSearchId);
        return options.searching === false ? searchSelector : (searchSelector || '#' + search.id);
    }
    return searchSelector;
}

// Keep empty tables consistent while retaining actionable loading/error messages.
function dataTableEmptyState(message = '', searching = false) {
    const escape = value => String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const operational = /memuat|loading|processing|gagal|failed|error|tidak dapat|belum berhasil|tidak berhasil/i.test(message);
    const title = operational ? 'Informasi data' : searching ? 'Tidak ada data yang sesuai' : 'Belum ada data tersedia';
    const description = operational ? message : searching ? 'Ubah kata pencarian atau filter untuk melihat data yang tersedia.' : 'Ubah periode atau filter untuk melihat data yang tersedia.';
    return '<div class="app-table-empty" role="status"><svg class="app-table-empty-icon" width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4h12l6 6v18H7z"/><path d="M19 4v7h6M12 16h8M12 21h5"/></svg><strong class="app-table-empty-title">' + escape(title) + '</strong><span class="app-table-empty-description">' + escape(description) + '</span></div>';
}

const automaticDataTables = new WeakMap();

function initDataTable(tableSelector, searchSelector = null, pageLength = 10, options = {}) {

    const element = $(tableSelector)[0];
    if (!element) return null;
    element.classList.add('fs-8', 'gy-2');
    searchSelector = prepareTableCard(element, searchSelector, options);
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
        dom: (searchSelector ? '' : "<'app-table-tools'f>") + "<'app-datatable-scroll'tr><'app-table-footer'<'app-table-meta'li>p>",
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
    // One footer presentation across modules; preserve page-specific empty/error messages.
    if (settings.paging !== false) {
        settings.info = true;
        settings.lengthChange = true;
        settings.pagingType = 'simple_numbers';
        Object.assign(settings.language, {
            lengthMenu: '_MENU_',
            info: 'Showing _START_ to _END_ of _TOTAL_ records',
            infoEmpty: 'Showing 0 to 0 of 0 records',
            infoFiltered: '(filtered from _MAX_ total records)',
            paginate: {previous: 'Sebelumnya', next: 'Berikutnya'}
        });
    }
    settings.language.emptyTable = dataTableEmptyState(options.language?.emptyTable || placeholder || '');
    settings.language.zeroRecords = dataTableEmptyState('', true);

    const table = $(tableSelector).DataTable(
        settings
    );

    // Render page-size options outside scroll/card containers.
    if ($.fn.select2) {
        const container = table.table().container();
        const pageSize = $(container).find('.dataTables_length select');
        const dropdownParent = $(element.closest('.modal') || document.body);
        pageSize.attr('aria-label', 'Jumlah baris per halaman').select2({
            minimumResultsForSearch: Infinity,
            width: '74px',
            dropdownParent,
        });
        $(element).one('destroy.dt.pageSize', () => {
            if (pageSize.hasClass('select2-hidden-accessible')) pageSize.select2('destroy');
        });
    }

    // Debounce external search too; DataTables searchDelay only covers its own input.
    if (searchSelector) {
        let searchTimer = null;
        $(searchSelector).off('keyup.datatable input.datatable search.datatable')
            .on('keyup.datatable input.datatable search.datatable', function () {
                clearTimeout(searchTimer);
                const value = this.value;
                const apply = () => { if (table.search() !== value) table.search(value).draw(); };
                if (settings.serverSide) searchTimer = setTimeout(apply, settings.searchDelay || 400);
                else apply();
            });
        $(tableSelector).on('destroy.dt.externalSearch', () => clearTimeout(searchTimer));
        const initialSearch = $(searchSelector).val() || '';
        if (initialSearch) table.search(initialSearch).draw();
    }
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
            if (['false', 'manual'].includes(table.dataset.datatable) || !table.tHead || !table.tBodies.length) return;
            const previous = automaticDataTables.get(table);
            if ($.fn.DataTable.isDataTable(table) && !previous) return;
            if (previous && previous.signature === signature(table)) return;
            const columns = dataTableColumnCount(table.tHead);
            if (!columns) return;
            const rows = Array.from(table.tBodies[0].rows);
            const placeholder = columns > 1 && rows.length === 1 && rows[0].cells.length === 1 && rows[0].cells[0].colSpan === columns;
            // Matrices and grouped body rows require their dedicated page configuration.
            if (!placeholder && rows.some(row => row.cells.length !== columns ||
                Array.from(row.cells).some(cell => cell.colSpan !== 1 || cell.rowSpan !== 1))) return;
            if (previous && $.fn.DataTable.isDataTable(table)) {
                const api = $(table).DataTable();
                const currentRows = placeholder ? [] : rows;
                if (placeholder) api.settings()[0].oLanguage.sEmptyTable = dataTableEmptyState(rows[0].textContent.trim());
                api.clear();
                api.rows.add(currentRows).draw(false);
                previous.signature = signature(table);
                return;
            }
            initDataTable(table, null, 10);
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
