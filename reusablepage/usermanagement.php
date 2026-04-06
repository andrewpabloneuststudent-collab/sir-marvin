<?php
require_once __DIR__ . "/../function/usermanagement.php";
require_once __DIR__ . "/../conn/connection_links.php";


use Classes\UserManagement;
$usersmanagement = new UserManagement($db);
$users = $usersmanagement->getAllUsers();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../css/table.css">
</head>

<body class="bg-light">
    <div class="container py-5">

        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">User Management</h4>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Contact</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $member): ?>
                            <tr>
                                <td>
                                    <?php echo $member['id']; ?>
                                </td>
                                <td>
                                    <?php echo $member['username']; ?>
                                </td>
                                <td>
                                    <?php echo $member['firstname'] . ' ' . $member['middlename'] . ' ' . $member['lastname']; ?>
                                </td>
                                <td>
                                    <?php echo $member['position']; ?>
                                </td>
                                <td>
                                    <?php echo $member['contactnumber']; ?>
                                </td>
                                <td>
                                    <a href="../reusablepage/viewaccount.php<?php echo $member['id']; ?>"
                                        class="btn btn-sm btn-info">View</a>
                                    <a href="../reusablepage/editaccount.php<?php echo $member['id']; ?>"
                                        class="btn btn-sm btn-warning">Edit</a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this member?');">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>


                    </tbody>
                </table>
                </div>

            </div>
        </div>

    </div>

    <!-- Link to usersmanagement.js -->
    <script src="../js/usersmanagement.js"></script>
</body>

</html>