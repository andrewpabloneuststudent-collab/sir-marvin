<div class="modal fade" id="edit<?= $u['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST">
                <div class="modal-header">
                    <h5>Edit Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <input name="username" class="form-control mb-2" value="<?= $u['username'] ?>" placeholder="Username">
                    <input name="firstname" class="form-control mb-2" value="<?= $u['firstname'] ?>" placeholder="Firstname">
                    <input name="middlename" class="form-control mb-2" value="<?= $u['middlename'] ?>" placeholder="Middlename">
                    <input name="lastname" class="form-control mb-2" value="<?= $u['lastname'] ?>" placeholder="Lastname">

                    <input type="number" name="age" class="form-control mb-2" value="<?= $u['age'] ?>" placeholder="Age">

                    <input name="contactnumber" class="form-control mb-2" value="<?= $u['contactnumber'] ?>" placeholder="Contact Number">
                    <input type="email" name="email" class="form-control mb-2" value="<?= $u['email'] ?>" placeholder="Email">

                    <input name="street" class="form-control mb-2" value="<?= $u['street'] ?>" placeholder="Street">
                    <input name="barangay" class="form-control mb-2" value="<?= $u['barangay'] ?>" placeholder="Barangay">
                    <input name="city" class="form-control mb-2" value="<?= $u['city'] ?>" placeholder="City">
                    <input name="province" class="form-control mb-2" value="<?= $u['province'] ?>" placeholder="Province">
                    <input name="country" class="form-control mb-2" value="<?= $u['country'] ?>" placeholder="Country">

                    <select name="position" class="form-control mb-2">
                        <option value="Admin" <?= $u['position']=='Admin'?'selected':'' ?>>Admin</option>
                        <option value="Owner" <?= $u['position']=='Owner'?'selected':'' ?>>Owner</option>
                        <option value="Staff" <?= $u['position']=='Staff'?'selected':'' ?>>Staff</option>
                    </select>

                    <input name="void_password" class="form-control mb-2" value="<?= $u['void_password'] ?>" maxlength="7" placeholder="Void Password">
                    <input name="password" class="form-control mb-2" maxlength="16" placeholder="Enter new password">
                </div>

                <div class="modal-footer">
                    <button type="submit" name="updateUser" class="btn btn-success">
                        Save
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>