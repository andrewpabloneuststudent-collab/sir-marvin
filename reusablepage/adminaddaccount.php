<?php 
// connection_links.php is already loaded by parent dashboard
?>

<div class="container mt-4">

    <div class="card shadow-lg border-0">
        
        <!-- HEADER -->
        <div class="card-header text-white" style="background:linear-gradient(135deg,#c0392b,#e74c3c);">
            <h4 class="mb-0">Add User</h4>
        </div>

        <div class="card-body">

            <form method="POST" action="">

                <!-- 🔐 ACCOUNT -->
                <h6 style="color:#c0392b; font-weight:700;">Account Information</h6>
                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control mb-3" required minlength="4" maxlength="20">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control mb-3" required minlength="8">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <select name="position" class="form-select" required>
                        <option value="">Select Position</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>

                <!-- 👤 PERSONAL -->
                <h6 class="mt-4" style="color:#c0392b; font-weight:700;">Personal Information</h6>
                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">First Name</label>
                        <input type="text" name="firstname" class="form-control mb-3" required maxlength="50">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middlename" class="form-control mb-3" maxlength="50">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="lastname" class="form-control mb-3" required maxlength="50">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Age</label>
                    <input type="number" name="age" class="form-control" required min="18" max="100">
                </div>

                <!-- 📧 CONTACT -->
                <h6 class="mt-4" style="color:#c0392b; font-weight:700;">Contact Information</h6>
                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control mb-3" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contactnumber" class="form-control mb-3" required minlength="11" maxlength="11" pattern="09[0-9]{9}">
                    </div>
                </div>

                <!-- 📍 ADDRESS -->
                <h6 class="mt-4" style="color:#c0392b; font-weight:700;">Address</h6>
                <hr>

                <div class="mb-2">
                    <input type="text" name="street" class="form-control" placeholder="Street">
                </div>

                <div class="mb-2">
                    <input type="text" name="barangay" class="form-control" placeholder="Barangay">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="city" class="form-control mb-2" placeholder="City">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="province" class="form-control mb-2" placeholder="Province">
                    </div>
                </div>

                <div class="mb-3">
                    <input type="text" name="country" class="form-control" placeholder="Country">
                </div>

                <!-- ACTION BUTTONS -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" name="pre_addUser" class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i> Save User
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
