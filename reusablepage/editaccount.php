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

                    <input name="firstname" class="form-control mb-2" value="<?= $u['firstname'] ?>">
                    <input name="middlename" class="form-control mb-2" value="<?= $u['middlename'] ?>">
                    <input name="lastname" class="form-control mb-2" value="<?= $u['lastname'] ?>">

                    <input type="number" name="age" class="form-control mb-2" value="<?= $u['age'] ?>">

                    <input name="contactnumber" class="form-control mb-2" value="<?= $u['contactnumber'] ?>">
                    <input type="email" name="email" class="form-control mb-2" value="<?= $u['email'] ?>">

                    <input name="street" class="form-control mb-2" value="<?= $u['street'] ?>">
                    <input name="barangay" class="form-control mb-2" value="<?= $u['barangay'] ?>">
                    <input name="city" class="form-control mb-2" value="<?= $u['city'] ?>">
                    <input name="province" class="form-control mb-2" value="<?= $u['province'] ?>">
                    <input name="country" class="form-control mb-2" value="<?= $u['country'] ?>">

                    <select name="position" class="form-control mb-2">
                        <option value="Admin" <?= $u['position']=='Admin'?'selected':'' ?>>Admin</option>
                        <option value="Owner" <?= $u['position']=='Owner'?'selected':'' ?>>Owner</option>
                        <option value="Staff" <?= $u['position']=='Staff'?'selected':'' ?>>Staff</option>
                    </select>

                    <input name="void_password" class="form-control mb-2" value="<?= $u['void_password'] ?>">
                    <input name="password" class="form-control mb-2" placeholder="Enter new password">
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