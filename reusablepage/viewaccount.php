<div class="modal fade" id="view<?= $u['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- BASIC INFO -->
                <h6 class="mb-3">Basic Information</h6>
                <div class="row">
                    <div class="col-md-4"><strong>ID:</strong> <?= $u['id'] ?></div>
                    <div class="col-md-4"><strong>First Name:</strong> <?= htmlspecialchars($u['firstname']) ?></div>
                    <div class="col-md-4"><strong>Middle Name:</strong> <?= htmlspecialchars($u['middlename']) ?></div>
                    <div class="col-md-4 mt-2"><strong>Last Name:</strong> <?= htmlspecialchars($u['lastname']) ?></div>
                    <div class="col-md-4 mt-2"><strong>Age:</strong> <?= $u['age'] ?></div>
                    <div class="col-md-4 mt-2"><strong>Position:</strong> <?= htmlspecialchars($u['position']) ?></div>
                </div>

                <hr>

                <!-- CONTACT -->
                <h6 class="mb-3">Contact</h6>
                <div class="row">
                    <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars($u['email']) ?></div>
                    <div class="col-md-6"><strong>Contact No:</strong> <?= htmlspecialchars($u['contactnumber']) ?></div>
                </div>

                <hr>

                <!-- ADDRESS -->
                <h6 class="mb-3">Address</h6>
                <div class="row">
                    <div class="col-md-4"><strong>Street:</strong> <?= htmlspecialchars($u['street']) ?></div>
                    <div class="col-md-4"><strong>Barangay:</strong> <?= htmlspecialchars($u['barangay']) ?></div>
                    <div class="col-md-4"><strong>City:</strong> <?= htmlspecialchars($u['city']) ?></div>
                    <div class="col-md-4 mt-2"><strong>Province:</strong> <?= htmlspecialchars($u['province']) ?></div>
                    <div class="col-md-4 mt-2"><strong>Country:</strong> <?= htmlspecialchars($u['country']) ?></div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>