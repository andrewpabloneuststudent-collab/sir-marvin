$(function () {

    // 🔹 NORMAL TABLES (no buttons)
    $('.myTable').each(function () {
        sanitizeTable(this);
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable({
                responsive: true,
                autoWidth: false
            });
        }
    });

    // 🔥 TABLES WITH EXPORT BUTTONS ONLY
    $('.myTableExport').each(function () {
        sanitizeTable(this);
        if (!$.fn.DataTable.isDataTable(this)) {
            initDataTable(this);
        }
    });

    // Remove any tbody rows that don't match header column count (prevents DataTables unknown parameter errors)
    function sanitizeTable(table) {
        try {
            var $t = $(table);
            var headerCount = $t.find('thead th').length;
            if (headerCount <= 0) return;
            $t.find('tbody tr').each(function () {
                var tdCount = $(this).children('td, th').length;
                if (tdCount !== headerCount) {
                    // Move mismatched rows to a hidden container to preserve data if needed
                    $(this).addClass('dt-row-mismatched').hide();
                }
            });
        } catch (e) {
            console.warn('sanitizeTable error', e);
        }
    }

    // Focus search input on page load
    focusSearchInput();
    initUserModals();

});

function initDataTable(table) {
    return $(table).DataTable({
        responsive: true,
        autoWidth: false,
        dom: 'fBrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });
}

function focusSearchInput() {
    setTimeout(function() {
        $('input[type="search"]').first().focus();
    }, 200);
}
