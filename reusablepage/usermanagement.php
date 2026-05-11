<?php
require_once __DIR__ . "/../conn/database.php";
require_once __DIR__ . "/../function/usermanagement.php";

use Classes\UserManagement;

$usersmanagement = new UserManagement($db);

$result = null;

// CONTROLLER
if (isset($_POST['addUser'])) {
    $result = $usersmanagement->addUser($_POST);
}

if (isset($_POST['updateUser'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $result = $usersmanagement->updateUser($id, $_POST);
}

if (isset($_POST['deleteUser'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $result = $usersmanagement->deleteUser($id);
}

// FETCH
$users = $usersmanagement->getAllUsers();
?>

<!-- ✅ ALERT + REDIRECT -->
<?php if ($result): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($result['success']): ?>
                if (typeof weposAlert === 'function') {
                    weposAlert('<?= addslashes($result['message']) ?>', 'success');
                } else {
                    alert('<?= addslashes($result['message']) ?>');
                }
                setTimeout(() => { window.location.href = 'dashboard.php?tab=users'; }, 1500);
            <?php else: ?>
                if (typeof weposAlert === 'function') {
                    weposAlert('<?= addslashes($result['message']) ?>', 'error');
                } else {
                    alert('<?= addslashes($result['message']) ?>');
                }
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<!-- ✅ CUSTOM TABLE SPACING -->
<link rel="stylesheet" href="/MMBPOS/css/table.css">
<div class="container-fluid px-4 mt-3">

    <div class="card shadow-sm">
        <div class="card-body">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">User Management</h4>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adduser">
                    Add User
                </button>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle w-100 myTable userstable">

                    <thead class="table-dark">
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-name">Name</th>
                            <th class="col-position">Position</th>
                            <th class="col-action text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td class="col-id"><?= $u['id'] ?></td>

                                <td class="col-name"><?= ($u['firstname']), " ", ($u['lastname']) ?></td>

                                <td class="col-position">
                                    <?php
                                    $pos = $u['position'];

                                    if ($pos === 'Admin') {
                                        $badgeClass = 'bg-danger';
                                    } elseif ($pos === 'Owner') {
                                        $badgeClass = 'bg-dark';
                                    } elseif ($pos === 'Staff') {
                                        $badgeClass = 'bg-success';
                                    } else {
                                        $badgeClass = 'bg-secondary';
                                    }
                                    ?>

                                    <span class="badge <?= $badgeClass ?> badge-uniform">
                                        <?= htmlspecialchars($pos) ?>
                                    </span>
                                </td>

                                <td class="col-action text-center">
                                    <div class="action-btns justify-content-center">
                                        <!-- VIEW -->
                                        <button class="btn-action-edit" data-bs-toggle="modal"
                                            data-bs-target="#view<?= $u['id'] ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <!-- EDIT -->
                                        <button class="btn-action-edit" data-bs-toggle="modal"
                                            data-bs-target="#edit<?= $u['id'] ?>"
                                            style="background:#2d3f57;">
                                            <i class="fas fa-pen"></i> Edit
                                        </button>
                                        <!-- DELETE -->
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" name="deleteUser" class="btn-action-delete"
                                                onclick="return confirm('Delete this user?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <?php include 'editaccount.php'; ?>
                            <?php include 'viewaccount.php'; ?>

                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<?php include 'addaccount.php'; ?>
<script src="../js/usersmanagement.js"></script>