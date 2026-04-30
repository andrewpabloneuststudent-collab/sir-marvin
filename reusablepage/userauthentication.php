<?php
require_once __DIR__ . "/../conn/Database.php";
require_once __DIR__ . "/../function/usermanagement.php";

use Classes\UserManagement;

$usersmanagement = new UserManagement($db);
$voidPinResult = null;

// Handle Void PIN update
if (isset($_POST['updateVoidPin'])) {
    $uid = (int)($_POST['void_user_id'] ?? 0);
    $pin = trim($_POST['void_pin'] ?? '');
    $voidPinResult = $usersmanagement->updateVoidPin($uid, $pin);
}

// Fetch only Owner and Admin accounts
$stmt = $db->query("SELECT u.id, u.username, u.void_password, u.position, ui.firstname, ui.lastname 
                    FROM users u 
                    LEFT JOIN users_info ui ON u.id = ui.user_id
                    WHERE u.position IN ('Owner', 'Admin')
                    ORDER BY u.position ASC, ui.firstname ASC");
$authUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($voidPinResult): ?>
<script>
    alert("<?= $voidPinResult['message'] ?>");
</script>
<?php endif; ?>

<div class="container-fluid px-4 mt-3">

    <!-- PENDING APPROVAL SECTION -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold"><i class="fas fa-user-check me-2"></i>Pending Account Approvals</div>
        <div class="card-body p-0">
            <?php include "ownerapprovalpage.php"; ?>
        </div>
    </div>

    <!-- VOID PIN MANAGEMENT -->
    <div class="card shadow-sm">
        <div class="card-header fw-bold"><i class="fas fa-key me-2"></i>Void PIN Management</div>
        <div class="card-body">
            <p class="text-muted" style="font-size:0.9rem;">
                Each <strong>Owner</strong> and <strong>Admin</strong> account has a separate 8-digit Void PIN
                used to authorize removing items from the POS cart. This is <strong>different from their login password</strong>.
                Staff accounts do not have a Void PIN.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Position</th>
                            <th>Current Void PIN</th>
                            <th class="text-center">Update PIN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($authUsers)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No Owner or Admin accounts found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($authUsers as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars(($u['firstname'] ?? '') . ' ' . ($u['lastname'] ?? '')) ?></td>
                                    <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                                    <td>
                                        <span class="badge <?= $u['position'] === 'Owner' ? 'bg-dark' : 'bg-danger' ?>">
                                            <?= htmlspecialchars($u['position']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['void_password']): ?>
                                            <span class="badge bg-secondary font-monospace" style="font-size:0.95rem; letter-spacing:2px;">
                                                <?= htmlspecialchars($u['void_password']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" class="d-flex justify-content-center align-items-center gap-2"
                                              onsubmit="return validateVoidPin(this)">
                                            <input type="hidden" name="void_user_id" value="<?= $u['id'] ?>">
                                            <input type="text"
                                                   name="void_pin"
                                                   class="form-control form-control-sm text-center font-monospace"
                                                   placeholder="8-digit PIN"
                                                   maxlength="8"
                                                   pattern="\d{8}"
                                                   inputmode="numeric"
                                                   style="width:130px; letter-spacing:2px;"
                                                   autocomplete="off">
                                            <button type="submit" name="updateVoidPin"
                                                    class="btn btn-sm btn-primary">
                                                <i class="fas fa-save"></i> Save
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<script>
function validateVoidPin(form) {
    const input = form.querySelector('input[name="void_pin"]');
    const val = input.value.trim();
    if (!/^\d{8}$/.test(val)) {
        alert('Void PIN must be exactly 8 digits (numbers only).');
        input.focus();
        return false;
    }
    return true;
}

// Only allow numeric input in Void PIN fields
document.querySelectorAll('input[name="void_pin"]').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 8);
    });
});
</script>