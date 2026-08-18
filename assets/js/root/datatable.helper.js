function initDataTable(tableSelector, searchSelector = null, pageLength = 10, options = {}) {

    if ($.fn.DataTable.isDataTable(tableSelector)) {
        $(tableSelector).DataTable().clear().destroy();
    }

    const table = $(tableSelector).DataTable($.extend(true, {
        responsive: false,
        pageLength: pageLength,
        autoWidth: false,
        destroy: true,
        ordering: false,
        searching: true,
        info: true,
        language: {
            emptyTable: "No data available",
            search: "",
            searchPlaceholder: "Search..."
        }
    }, options));

    if (searchSelector) {
        $(document)
            .off("input.datatable", searchSelector)
            .on("input.datatable", searchSelector, function (e) {
                e.stopPropagation();
                table.search($(this).val()).draw();
            });
    }

    return table;
}