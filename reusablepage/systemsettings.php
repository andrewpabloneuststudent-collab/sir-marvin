<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../conn/database.php";
require_once __DIR__ . "/../function/usermanagement.php";

use Classes\UserManagement;

$usersmanagement = new UserManagement($db);

$userId = $_SESSION['user_id'] ?? 0;
$result = null;

// ✅ HANDLE UPDATE
if (isset($_POST['updateUserSystem'])) {
    $result = $usersmanagement->updateUserSystem(
        $_POST['user_id'],
        $_POST
    );
}

$currentUser = $usersmanagement->getUserById($userId);
?>
<?php if ($result): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($result['success']): ?>
                showNotif('<?= addslashes($result['message']) ?>', 'success');
                setTimeout(() => { window.location.href = 'dashboard.php?tab=system'; }, 1500);
            <?php else: ?>
                showNotif('<?= addslashes($result['message']) ?>', 'error');
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<style>
.ss-wrapper {
    max-width: 960px;
    margin: 0 auto;
    padding: 32px 16px;
    font-family: 'Inter', sans-serif;
}
.ss-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
    overflow: hidden;
    display: flex;
    min-height: 480px;
}
.ss-left {
    width: 240px;
    min-width: 240px;
    background: linear-gradient(160deg, #c0392b 0%, #e74c3c 60%, #922b21 100%);
    padding: 40px 28px;
    display: flex;
    flex-direction: column;
    color: #fff;
}
.ss-left-icon {
    width: 52px;
    height: 52px;
    background: rgba(255,255,255,.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
}
.ss-left h3 {
    font-size: 1.4rem;
    font-weight: 800;
    margin: 0 0 10px;
    letter-spacing: -.3px;
}
.ss-left p {
    font-size: .82rem;
    opacity: .8;
    line-height: 1.6;
    margin-bottom: 28px;
}
.ss-left hr { border-color: rgba(255,255,255,.25); margin-bottom: 20px; }
.ss-left-nav { list-style: none; padding: 0; margin: 0; }
.ss-left-nav li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 0;
    font-size: .83rem;
    font-weight: 500;
    opacity: .85;
    border-bottom: 1px solid rgba(255,255,255,.12);
}
.ss-left-nav li:last-child { border-bottom: none; }
.ss-left-nav li i { width: 18px; text-align: center; opacity: .7; }

.ss-right {
    flex: 1;
    padding: 40px 40px 32px;
    overflow-y: auto;
}
.ss-right h4 {
    font-size: 1.25rem;
    font-weight: 800;
    color: #1a2535;
    margin-bottom: 6px;
    letter-spacing: -.3px;
}
.ss-right .ss-subtitle {
    font-size: .82rem;
    color: #94a3b8;
    margin-bottom: 28px;
}
.ss-section-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: #c0392b;
    margin: 20px 0 10px;
    padding-bottom: 6px;
    border-bottom: 2px solid #fff5f5;
}
.ss-form-control {
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: .9rem;
    width: 100%;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
    background: #fafafa;
}
.ss-form-control:focus {
    border-color: #c0392b;
    box-shadow: 0 0 0 3px rgba(192,57,43,.1);
    background: #fff;
}
.ss-label {
    font-size: .78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 5px;
    display: block;
}
.ss-save-btn {
    background: linear-gradient(135deg, #c0392b, #e74c3c);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 11px 28px;
    font-size: .9rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    box-shadow: 0 4px 12px rgba(192,57,43,.3);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.ss-save-btn:hover {
    background: linear-gradient(135deg, #a93226, #c0392b);
    box-shadow: 0 6px 18px rgba(192,57,43,.4);
    transform: translateY(-1px);
}
</style>

<div class="ss-wrapper">
    <div class="ss-card">

        <!-- LEFT PANEL -->
        <div class="ss-left">
            <div class="ss-left-icon"><i class="fas fa-gear"></i></div>
            <h3>Settings</h3>
            <p>Update your account details, contact information, and security settings.</p>
            <hr>
            <ul class="ss-left-nav">
                <li><i class="fas fa-user"></i> Account Info</li>
                <li><i class="fas fa-id-card"></i> Personal Info</li>
                <li><i class="fas fa-phone"></i> Contact</li>
                <li><i class="fas fa-shield-halved"></i> Security</li>
            </ul>
        </div>

        <!-- RIGHT PANEL -->
        <div class="ss-right">
            <h4>System Settings</h4>
            <div class="ss-subtitle">Manage your personal account details and security credentials.</div>

            <form method="POST">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">

                <!-- Account -->
                <div class="ss-section-label"><i class="fas fa-user me-1"></i> Account Info</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="ss-label">Username</label>
                        <input type="text" name="username" class="ss-form-control"
                            value="<?= htmlspecialchars($currentUser['username'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="ss-label">Email</label>
                        <input type="email" name="email" class="ss-form-control"
                            value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>">
                    </div>
                </div>

                <!-- Personal -->
                <div class="ss-section-label"><i class="fas fa-id-card me-1"></i> Personal Info</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="ss-label">First Name</label>
                        <input type="text" name="firstname" class="ss-form-control"
                            value="<?= htmlspecialchars($currentUser['firstname'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="ss-label">Middle Name</label>
                        <input type="text" name="middlename" class="ss-form-control"
                            value="<?= htmlspecialchars($currentUser['middlename'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="ss-label">Last Name</label>
                        <input type="text" name="lastname" class="ss-form-control"
                            value="<?= htmlspecialchars($currentUser['lastname'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Contact & Security -->
                <div class="ss-section-label"><i class="fas fa-phone me-1"></i> Contact & Security</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="ss-label">Contact Number</label>
                        <input type="text" name="contactnumber" class="ss-form-control"
                            minlength="11" maxlength="11" pattern="09[0-9]{9}"
                            placeholder="09XXXXXXXXX" required
                            value="<?= htmlspecialchars($currentUser['contactnumber'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="ss-label">Void Password <small style="color:#94a3b8;">(7 digits)</small></label>
                        <input type="text" name="void_password" class="ss-form-control"
                            minlength="7" maxlength="7" pattern="[0-9]{7}" required
                            placeholder="0000000"
                            value="<?= htmlspecialchars($currentUser['void_password'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="ss-label">New Password <small style="color:#94a3b8;">(leave blank to keep)</small></label>
                        <input type="password" name="password" class="ss-form-control"
                            minlength="8" maxlength="16"
                            placeholder="Min 8 characters">
                    </div>
                </div>

                <!-- Save -->
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" name="updateUserSystem" class="ss-save-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
