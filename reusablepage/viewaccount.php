<?php
require_once "../conn/database.php";
require_once "../function/usermanagement.php";

use Classes\UserManagement;

$userManagement = new UserManagement($db);
$data = null;

// Load user data if GET request with ID
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    $data = $userManagement->getUserById($userId);
    
    // Output only modal body content for AJAX loading
    ?>
    <!-- Modal Body Content -->
    <div class="modal-header bg-dark text-white border-0">
        <h5 class="modal-title">
            <i class="fas fa-user-circle"></i> User Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body p-4">
        <div class="row mb-4">
            <div class="col-md-4 text-center">
                <div class="bg-primary text-white rounded-circle p-4 d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                    <i class="fas fa-user fa-3x"></i>
                </div>
                <h5 class="mt-3 fw-bold"><?php echo $data['firstname'] . ' ' . $data['lastname']; ?></h5>
                <p class="text-muted mb-0"><?php echo $data['position']; ?></p>
            </div>
            <div class="col-md-8">
                <h6 class="fw-bold mb-3 pb-2 border-bottom">
                    <i class="fas fa-info-circle text-primary"></i> Personal Information
                </h6>
                <div class="row g-2">
                    <div class="col-12">
                        <small class="text-muted fw-bold">First Name</small>
                        <p class="mb-2"><?php echo $data['firstname']; ?></p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted fw-bold">Middle Name</small>
                        <p class="mb-2"><?php echo $data['middlename']; ?></p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted fw-bold">Last Name</small>
                        <p class="mb-2"><?php echo $data['lastname']; ?></p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted fw-bold">Username</small>
                        <p class="mb-2">@<?php echo $data['username']; ?></p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted fw-bold">Email</small>
                        <p class="mb-2"><?php echo $data['email']; ?></p>
                    </div>
                    <div class="col-12">
                        <small class="text-muted fw-bold">Age</small>
                        <p class="mb-2"><?php echo $data['age']; ?> years old</p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3">

        <h6 class="fw-bold mb-3 pb-2 border-bottom">
            <i class="fas fa-map-marker-alt text-success"></i> Address Information
        </h6>
        <div class="row g-3">
            <div class="col-md-6">
                <small class="text-muted fw-bold">Street</small>
                <p><?php echo $data['street']; ?></p>
            </div>
            <div class="col-md-6">
                <small class="text-muted fw-bold">Barangay</small>
                <p><?php echo $data['barangay']; ?></p>
            </div>
            <div class="col-md-6">
                <small class="text-muted fw-bold">City</small>
                <p><?php echo $data['city']; ?></p>
            </div>
            <div class="col-md-6">
                <small class="text-muted fw-bold">Province</small>
                <p><?php echo $data['province']; ?></p>
            </div>
            <div class="col-md-6">
                <small class="text-muted fw-bold">Country</small>
                <p><?php echo $data['country']; ?></p>
            </div>
            <div class="col-md-6">
                <small class="text-muted fw-bold">Contact Number</small>
                <p><?php echo $data['contactnumber']; ?></p>
            </div>
        </div>
    </div>

    <div class="modal-footer border-top">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times"></i> Close
        </button>
        <button type="button" class="btn btn-warning" id="viewEditBtn" onclick="openEditFromView(<?php echo $data['id']; ?>)">
            <i class="fas fa-edit"></i> Edit
        </button>
    </div>
    <?php
    exit;
}
?>

    <!-- Floating Modal for User Details -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0" id="viewModalContent">
                <!-- View modal content will be loaded here via AJAX -->
            </div>
        </div>
    </div>
