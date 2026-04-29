<div class="card shadow-sm w-100">

    <div class="card-header text-center fw-bold">
        POS SUMMARY
    </div>

    <div class="card-body">

        <div class="d-flex justify-content-between">
            <span>Qty</span>
            <b id="qty">0</b>
        </div>

        <div class="d-flex justify-content-between">
            <span>Subtotal</span>
            <b id="subtotal">0.00</b>
        </div>

        <div class="d-flex justify-content-between">
            <span>Tax</span>
            <b id="tax">0.00</b>
        </div>

        <div class="d-flex justify-content-between">
            <span>Discount</span>
            <b id="discount">0.00</b>
        </div>

        <hr>

        <!-- DISCOUNT BUTTONS -->
       <div class="card shadow-sm mb-3">
    <div class="card-body">

        <div class="d-grid gap-2">

            <!-- Normal -->
            <button type="button" class="btn btn-secondary fw-semibold">
                Normal
            </button>

            <!-- Senior -->
            <details class="border rounded">
                <summary class="btn btn-primary w-100 text-start fw-semibold text-center">
                    Senior Citizen
                </summary>

                <div class="p-3 border-top bg-light">
                    <div class="mb-2">
                        <label class="form-label small">Senior ID No.</label>
                        <input type="text" class="form-control form-control-sm">
                    </div>

                    <div>
                        <label class="form-label small">Full Name</label>
                        <input type="text" class="form-control form-control-sm">
                    </div>
                </div>
            </details>

            <!-- PWD -->
            <details class="border rounded">
                <summary class="btn btn-success w-100 text-start fw-semibold text-center">
                    PWD
                </summary>

                <div class="p-3 border-top bg-light">
                    <div class="mb-2">
                        <label class="form-label small">PWD ID No.</label>
                        <input type="text" class="form-control form-control-sm">
                    </div>

                    <div>
                        <label class="form-label small">Full Name</label>
                        <input type="text" class="form-control form-control-sm">
                    </div>
                </div>
            </details>

        </div>

    </div>
</div>

        <!-- DISCOUNT INFO -->
        <div id="discountDropdown" class="d-none">
            <input class="form-control mb-2" placeholder="Name">
            <input class="form-control mb-3" placeholder="ID No">
        </div>

        <hr>

        <!-- TOTAL -->
        <div class="text-end total-text">
            ₱ <span id="total">0.00</span>
        </div>

        <hr>

        <!-- PAYMENT -->
        <input type="number" id="cash" name="cash" class="form-control mb-2" placeholder="Cash">

        <div class="text-end mb-3">
            Change: ₱<span id="change">0.00</span>
        </div>

        <button type="button" onclick="payAndPrint()" class="btn btn-success w-100 py-3">
            PAY (<span id="payTotal">0.00</span>)
        </button>

    </div>
</div>