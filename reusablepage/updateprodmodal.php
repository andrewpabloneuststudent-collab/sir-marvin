<!-- 🔥 UPDATE STOCK MODAL -->
<div id="stockModal"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; width:400px; margin:100px auto; padding:20px; border-radius:10px;">

        <h4>Update Stock</h4>

        <form method="POST">
            <input type="hidden" name="id" id="stock_id">

            <label>Quantity</label>
            <input type="number" name="quantity" id="stock_quantity" class="form-control" required>

            <label>Expiry Date</label>
            <input type="date" name="expiry_date" id="stock_expiry" class="form-control">

            <br>

            <button class="btn btn-success" type="submit" name="updateStock">Save</button>
            <button type="button" class="btn btn-danger" onclick="closeStockModal()">Cancel</button>
        </form>

    </div>
</div>
