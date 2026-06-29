let dataTableInstances = {};

/**
 * Inisialisasi / Reload DataTable
 * @param {string} tableSelector   contoh "#datausers_table"
 * @param {string} tbodySelector   contoh "#resultdatausers"
 * @param {string} searchSelector  contoh "#searchtable"
 * @param {string} html            isi tbody
 * @param {object} options         opsi DataTable tambahan
 */
function loadDataTable(
    tableSelector,
    tbodySelector,
    searchSelector,
    html,
    options = {}
) {

    // Destroy jika sudah ada
    if ($.fn.DataTable.isDataTable(tableSelector)) {
        $(tableSelector).DataTable().clear().destroy();
    }

    // Kosongkan tbody
    $(tbodySelector).empty();

    // Isi data
    $(tbodySelector).html(html);

    // Default option
    const defaultOption = {

        responsive: true,

        pageLength: 10,

        autoWidth: false,

        destroy: true,

        processing: false,

        searching: true,

        ordering: true,

        info: true,

        lengthChange: true,

        order: [],

        language: {

            emptyTable: "No data available",

            search: "",

            searchPlaceholder: "Search..."

        }

    };

    // Merge option
    const setting = $.extend(true, {}, defaultOption, options);

    // Init
    const table = $(tableSelector).DataTable(setting);

    // Simpan instance
    dataTableInstances[tableSelector] = table;

    // Search
    if (searchSelector) {

        $(searchSelector)
            .off("keyup search input")
            .on("keyup search input", function () {

                table.search($(this).val()).draw();

            });

    }

    return table;

}

/**
 * Reload tanpa destroy manual
 */
function reloadDataTable(tableSelector) {

    if (dataTableInstances[tableSelector]) {

        dataTableInstances[tableSelector].ajax.reload(null, false);

    }

}

/**
 * Destroy
 */
function destroyDataTable(tableSelector) {

    if ($.fn.DataTable.isDataTable(tableSelector)) {

        $(tableSelector).DataTable().clear().destroy();

    }

}