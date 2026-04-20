$(function () {

    // 🔹 NORMAL TABLES (no buttons)
    $('.myTable').each(function () {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                responsive: true,
                autoWidth: false
            });
        }
    });

    // 🔥 TABLES WITH EXPORT BUTTONS ONLY
    $('.myTableExport').each(function () {
        if (!$.fn.DataTable.isDataTable(this)) {
            initDataTable(this);
        }
    });

});

function initDataTable(table) {
    return $(table).DataTable({
        responsive: true,
        autoWidth: false,
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });
}