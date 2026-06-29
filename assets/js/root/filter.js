function initTableSearch(table, inputSelector) {
    const $table = $(table);
    const dataTable = $table.DataTable();

    document.querySelector(inputSelector).addEventListener('keyup', function () {
        dataTable.search(this.value).draw();
    });

    return dataTable;
}