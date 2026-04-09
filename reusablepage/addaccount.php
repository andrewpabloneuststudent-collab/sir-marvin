<?php
// Clear any buffered output from includes
@ob_end_clean();
ob_start();

require_once "../conn/database.php";
require_once "../function/usermanagement.php";

use Classes\UserManagement;

// Handle POST request - Add new user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean();
    // Set JSON header FIRST before any output
    header('Content-Type: application/json; charset=utf-8');
    
    $userManagement = new UserManagement($db);
    
    // Use the reusable createUserAccount method
    $response = $userManagement->createUserAccount($_POST);
    
    echo json_encode($response);
    exit;
}

// GET request - show the form
ob_end_clean();
?>

<!-- Add Account Modal Content -->
<div class="modal-header bg-success text-white border-0">
    <h5 class="modal-title">
        <i class="fas fa-user-plus"></i> Add New Account
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4">
    <!-- Alert Box -->
    <div id="addAlert" class="alert d-none" role="alert"></div>

    <form id="addAccountForm" novalidate>
        <div class="row g-3 mb-4">
            <!-- Username -->
            <div class="col-md-6">
                <label for="addUsername" class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="addUsername" name="username" required>
            </div>

            <!-- Password -->
            <div class="col-md-6">
                <label for="addPassword" class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="addPassword" name="password" required>
            </div>

            <!-- First Name -->
            <div class="col-md-6">
                <label for="addFirstname" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="addFirstname" name="firstname" required>
            </div>

            <!-- Middle Name -->
            <div class="col-md-6">
                <label for="addMiddlename" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="addMiddlename" name="middlename">
            </div>

            <!-- Last Name -->
            <div class="col-md-6">
                <label for="addLastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="addLastname" name="lastname" required>
            </div>

            <!-- Age -->
            <div class="col-md-6">
                <label for="addAge" class="form-label">Age</label>
                <input type="number" class="form-control" id="addAge" name="age" min="1" max="150">
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label for="addEmail" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="addEmail" name="email" required>
            </div>

            <!-- Position -->
            <div class="col-md-6">
                <label for="addPosition" class="form-label">Position <span class="text-danger">*</span></label>
                <select class="form-select" id="addPosition" name="position" required>
                    <option value="">Select Position</option>
                    <option value="Admin">Admin</option>
                    <option value="Owner">Owner</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>

            <!-- Contact Number -->
            <div class="col-md-6">
                <label for="addContactnumber" class="form-label">Contact Number</label>
                <input type="tel" class="form-control" id="addContactnumber" name="contactnumber">
            </div>

            <!-- Street -->
            <div class="col-md-6">
                <label for="addStreet" class="form-label">Street</label>
                <input type="text" class="form-control" id="addStreet" name="street">
            </div>

            <!-- Barangay -->
            <div class="col-md-6">
                <label for="addBarangay" class="form-label">Barangay</label>
                <input type="text" class="form-control" id="addBarangay" name="barangay">
            </div>

            <!-- City -->
            <div class="col-md-6">
                <label for="addCity" class="form-label">City</label>
                <input type="text" class="form-control" id="addCity" name="city">
            </div>

            <!-- Province -->
            <div class="col-md-6">
                <label for="addProvince" class="form-label">Province</label>
                <input type="text" class="form-control" id="addProvince" name="province">
            </div>

            <!-- Country -->
            <div class="col-md-6">
                <label for="addCountry" class="form-label">Country</label>
                <input type="text" class="form-control" id="addCountry" name="country">
            </div>
        </div>
    </form>
</div>

<div class="modal-footer border-top">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        <i class="fas fa-times"></i> Cancel
    </button>
    <button type="button" class="btn btn-success" id="addSaveBtn">
        <i class="fas fa-save"></i> Create Account
    </button>
</div>
