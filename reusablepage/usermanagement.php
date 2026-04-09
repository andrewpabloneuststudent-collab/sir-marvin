<?php
use Classes\UserManagement;
require_once "../conn/database.php";
require_once "../function/usermanagement.php";

$usersmanagement = new UserManagement($db);

// Handle AJAX delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteUser'])) {
    header('Content-Type: application/json');
    
    $userId = intval($_POST['deleteUser']);
    
    if ($usersmanagement->deleteUser($userId)) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
    }
    exit;
}

$users = $usersmanagement->getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <?php include_once __DIR__ . "/../conn/connection_links.php"; ?>
</head>

<body class="bg-light">
    <?php include_once __DIR__ . "/header.php"; ?>
    <div class="container py-5">
        <div class="card shadow rounded-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">User Management</h4>
                <button type="button" class="btn btn-success btn-sm" id="addAccountBtn">
                    <i class="fas fa-user-plus"></i> Add Account
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th width="200">Name</th>
                                <th width="200">Position</th>
                                <th width="200">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $member): ?>
                                <tr>
                                    <td>
                                        <?php echo $member['id']; ?>
                                    </td>
                                    <td>
                                        <?php echo $member['firstname'] . ' ' . $member['middlename'] . ' ' . $member['lastname']; ?>
                                    </td>
                                    <td>
                                        <?php echo $member['position']; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info text-white view-btn"
                                            data-usedata='<?php echo json_encode($member); ?>'
                                            onclick="showUserDetails(this)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-warning btn-sm edit-btn" data-user='<?= json_encode($member) ?>'>
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                            data-userid="<?php echo $member['id']; ?>"
                                            data-username="<?php echo $member['firstname'] . ' ' . $member['lastname']; ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <?php include_once __DIR__ . "/viewaccount.php"; ?>

    <!-- Floating Modal for Edit Account -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" id="editModalContent">
                <!-- editaccount.php content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Floating Modal for Add Account -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" id="addModalContent">
                <!-- addaccount.php content will be loaded here -->
            </div>
        </div>
    </div>

    <script src="../js/usersmanagement.js"></script>
</body>

</html>