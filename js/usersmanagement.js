$(document).ready(function () {
    $('#usersTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
    });
});