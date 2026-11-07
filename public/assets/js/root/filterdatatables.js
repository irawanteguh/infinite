function initDataTable(tableSelector, searchSelector = null, pageLength = 10, options = {}) {

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

    const table = $(tableSelector).DataTable(
        $.extend(true, {}, defaultOptions, options)
    );

    // Search
    if (searchSelector) {
        $(searchSelector)
            .off("keyup.datatable")
            .on("keyup.datatable", function () {
                table.search(this.value).draw();
            });
    }

    return table;
}
