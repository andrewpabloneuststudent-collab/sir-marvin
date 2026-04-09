<?php
// Clear any buffered output from includes
@ob_end_clean();
ob_start();

require_once "../conn/database.php";
require_once "../function/usermanagement.php";

use Classes\UserManagement;

// Handle POST request - Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean();
    // Set JSON header FIRST before any output
    header('Content-Type: application/json; charset=utf-8');
    
    $userManagement = new UserManagement($db);
    $userId = intval($_POST['userId'] ?? 0);
    
    // Debug: Log the incoming data
    error_log('Edit POST received - UserId: ' . $userId . ', POST data: ' . json_encode($_POST));
    
    // Use the reusable updateUserAccount method
    $response = $userManagement->updateUserAccount($userId, $_POST);
    
    // Debug: Log the response
    error_log('Update response: ' . json_encode($response));
    
    echo json_encode($response);
    exit;
}

// GET request - Load user data and show the form
ob_end_clean();

$userManagement = new UserManagement($db);
$data = null;

// Load user data if ID is provided
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $data = $userManagement->getUserById($userId);
}
?>

<!-- Edit Account Modal Content -->
<div class="modal-header bg-primary text-white border-0">
    <h5 class="modal-title">
        <i class="fas fa-edit"></i> Edit Account
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4">
    <!-- Alert Box -->
    <div id="editAlert" class="alert d-none" role="alert"></div>

    <form id="editAccountForm" novalidate>
        <input type="hidden" id="editUserId" name="userId" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">

        <!-- Personal Information Section -->
        <h6 class="fw-bold mb-3 pb-2 border-bottom">
            <i class="fas fa-user"></i> Personal Information
        </h6>

        <div class="row g-3 mb-4">
            <!-- First Name -->
            <div class="col-md-6">
                <label for="editFirstname" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="editFirstname" name="firstname" value="<?php echo $data['firstname'] ?? ''; ?>" required>
            </div>

            <!-- Middle Name -->
            <div class="col-md-6">
                <label for="editMiddlename" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="editMiddlename" name="middlename" value="<?php echo $data['middlename'] ?? ''; ?>">
            </div>

            <!-- Last Name -->
            <div class="col-md-6">
                <label for="editLastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="editLastname" name="lastname" value="<?php echo $data['lastname'] ?? ''; ?>" required>
            </div>

            <!-- Age -->
            <div class="col-md-6">
                <label for="editAge" class="form-label">Age <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="editAge" name="age" min="1" max="150" value="<?php echo $data['age'] ?? ''; ?>" required>
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="editEmail" name="email" value="<?php echo $data['email'] ?? ''; ?>" required>
            </div>

            <!-- Position -->
            <div class="col-md-6">
                <label for="editPosition" class="form-label">Position <span class="text-danger">*</span></label>
                <select class="form-select" id="editPosition" name="position" required>
                    <option value="">Select Position</option>
                    <option value="Admin" <?php echo (isset($data['position']) && $data['position'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="Owner" <?php echo (isset($data['position']) && $data['position'] === 'Owner') ? 'selected' : ''; ?>>Owner</option>
                    <option value="Staff" <?php echo (isset($data['position']) && $data['position'] === 'Staff') ? 'selected' : ''; ?>>Staff</option>
                </select>
            </div>

            <!-- Contact Number -->
            <div class="col-md-6">
                <label for="editContactnumber" class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="editContactnumber" name="contactnumber" value="<?php echo $data['contactnumber'] ?? ''; ?>" required>
            </div>
        </div>

        <!-- Address Information Section -->
        <h6 class="fw-bold mb-3 pb-2 border-bottom">
            <i class="fas fa-map-marker-alt"></i> Address Information
        </h6>

        <div class="row g-3 mb-4">
            <!-- Street -->
            <div class="col-md-6">
                <label for="editStreet" class="form-label">Street <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="editStreet" name="street" value="<?php echo $data['street'] ?? ''; ?>" required>
            </div>

            <!-- Barangay -->
            <div class="col-md-6">
                <label for="editBarangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="editBarangay" name="barangay" value="<?php echo $data['barangay'] ?? ''; ?>" required>
            </div>

            <!-- City -->
            <div class="col-md-6">
                <label for="editCity" class="form-label">City <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="editCity" name="city" value="<?php echo $data['city'] ?? ''; ?>" required>
            </div>

            <!-- Province -->
            <div class="col-md-6">
                <label for="editProvince" class="form-label">Province <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="editProvince" name="province" value="<?php echo $data['province'] ?? ''; ?>" required>
            </div>

            <!-- Country -->
            <div class="col-md-6">
                <label for="editCountry" class="form-label">Country <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="editCountry" name="country" value="<?php echo $data['country'] ?? ''; ?>" required>
            </div>
        </div>
    </form>
</div>

<div class="modal-footer border-top">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times"></i> Cancel
    </button>
    <button type="button" class="btn btn-primary" id="editSaveBtn">
        <i class="fas fa-save"></i> Save Changes
    </button>
</div>