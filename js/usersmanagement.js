$(document).ready(function () {
    $('#usersTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
    });
});
$('#usersTable').DataTable({
    autoWidth: false,
    columnDefs: [
        { width: "20%", targets: 0 },
        { width: "30%", targets: 1 },
        { width: "50%", targets: 2 }
    ]
});