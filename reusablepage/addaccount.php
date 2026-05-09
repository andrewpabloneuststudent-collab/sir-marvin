<!-- Modal -->
<div class="modal fade" id="adduser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="post">
                <div class="modal-header">
                    <h5>Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input name="username" class="form-control mb-2" placeholder="Username" required>
                    <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>

                    <input name="firstname" class="form-control mb-2" placeholder="First Name">
                    <input name="middlename" class="form-control mb-2" placeholder="Middle Name">
                    <input name="lastname" class="form-control mb-2" placeholder="Last Name">

                    <input type="number" name="age" class="form-control mb-2" placeholder="Age">

                    <input type="email" name="email" class="form-control mb-2" placeholder="Email">
                    <input name="contactnumber" class="form-control mb-2" placeholder="Contact Number ex 09xxxxxxxxx" minlength="11" maxlength="11">

                    <input name="street" class="form-control mb-2" placeholder="Street">
                    <input name="barangay" class="form-control mb-2" placeholder="Barangay">
                    <input name="city" class="form-control mb-2" placeholder="City">
                    <input name="province" class="form-control mb-2" placeholder="Province">
                    <input name="country" class="form-control mb-2" placeholder="Country">

                    <select name="position" class="form-control mb-2">
                        <option>Admin</option>
                        <option>Owner</option>
                        <option selected>Staff</option>
                    </select>

                    <input name="void_password" class="form-control mb-2" minlength="7" maxlength="7" placeholder="Enter 7 number void password">
                    <input name="password" class="form-control mb-2" maxlength="16" placeholder="Enter 8 to 16 alpanumeric password ex Qwerty123!" minlength="8" maxlength="16" required>
                </div>

                <div class="modal-footer">
                    <button type="submit" name="addUser" class="btn btn-primary">Save</button>
                </div>
            </form>

        </div>
    </div>
</div>