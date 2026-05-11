<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../conn/database.php";
require_once __DIR__ . "/../function/workingpos.php";

$product = new Product($db);
$products = $product->getProducts();
$categories = $product->getCategories();
$discounts = $product->getDiscounts();

$userRole = strtolower($_SESSION['position'] ?? 'staff');
$isManager = in_array($userRole, ['owner', 'admin']);
?>

<!-- wePOS Inspired CSS -->
<link rel="stylesheet" href="../css/pos_wepos.css?v=1.4">

<div class="wepos-wrapper" id="weposApp">
    
    <!-- ═══════════════ LEFT PANEL: PRODUCT GRID ═══════════════ -->
    <div class="wepos-left">
        
        <!-- Header & Search -->
        <div class="wepos-header">
            <div class="wepos-search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="weposSearch" placeholder="Scan barcode or search products..." autocomplete="off">
                <kbd>F2</kbd>
            </div>
            <button type="button" class="wepos-btn wepos-btn-outline" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>

        <!-- Categories -->
        <div class="wepos-categories">
            <button class="wepos-cat-btn active" onclick="weposFilterCat('All')">All</button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="wepos-cat-btn" onclick="weposFilterCat('<?= htmlspecialchars($cat['category_name']) ?>')">
                    <?= htmlspecialchars($cat['category_name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Products -->
        <div class="wepos-products-area">
            <div class="wepos-products-grid" id="weposGrid">
                <?php foreach ($products as $row): ?>
                    <?php 
                        $stock = (int)($row['stock'] ?? 0);
                        $isOut = $stock <= 0;
                        $image = !empty($row['imageproduct']) ? "../uploads/products/" . $row['imageproduct'] : "";
                    ?>
                    <div class="wepos-product-card <?= ($row['is_expired'] == 1) ? 'expired-product' : ($isOut ? 'out-of-stock' : '') ?>"
                         data-id="<?= $row['id'] ?>"
                         data-name="<?= htmlspecialchars($row['product_name']) ?>"
                         data-price="<?= $row['total_price'] ?>"
                         data-net="<?= $row['net_price'] ?>"
                         data-barcode="<?= htmlspecialchars($row['barcode'] ?? '') ?>"
                         data-category="<?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?>"
                         data-has-vat="<?= (int)($row['has_vat'] ?? 0) ?>"
                         data-senior="<?= (int)($row['senior_discount'] ?? 0) ?>"
                         data-pwd="<?= (int)($row['pwd_discount'] ?? 0) ?>"
                         data-stock="<?= $stock ?>"
                         data-is-expired="<?= (int)$row['is_expired'] ?>"
                         onclick="weposAddToCart(this)">
                        
                        <div class="wepos-card-img">

                            
                            <?php if ($image): ?>
                                <img src="<?= $image ?>" alt="">
                            <?php else: ?>
                                <i class="fas fa-box" style="font-size: 2rem; color: #cbd5e1;"></i>
                            <?php endif; ?>
                            
                            <!-- Stock Indicator -->
                            <?php if ($row['is_expired'] == 1): ?>
                                <span class="wepos-stock-badge empty">Expired</span>
                            <?php elseif ($isOut): ?>
                                <span class="wepos-stock-badge empty">Out of Stock</span>
                            <?php else: ?>
                                <span class="wepos-stock-badge <?= $stock <= 10 ? 'low' : '' ?>"><?= $stock ?> in stock</span>
                            <?php endif; ?>
                            
                            <!-- Hover Add Overlay -->
                            <div class="wepos-card-add-overlay">
                                <span class="wepos-add-btn-fake"><i class="fas fa-plus"></i> Add to Cart</span>
                            </div>
                        </div>
                        
                        <div class="wepos-card-info">
                            <div class="wepos-card-price">₱<?= number_format($row['total_price'], 2) ?></div>
                            <div class="wepos-card-name" title="<?= htmlspecialchars($row['product_name']) ?>">
                                <?= htmlspecialchars($row['product_name']) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- ═══════════════ RIGHT PANEL: CART & CHECKOUT ═══════════════ -->
    <div class="wepos-right">
        
        <!-- Cart Header -->
        <div class="wepos-cart-header">
            <span style="font-size:13px; font-weight:600; color:#50575e;"><i class="fas fa-shopping-cart me-1"></i> Current Order</span>
            <div class="wepos-cart-actions">
                <button type="button" class="wepos-btn wepos-btn-icon text-danger" title="Clear Cart (F8)" onclick="weposClearCart()">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>

        <!-- Cart Items List -->
        <div class="wepos-cart-items">
            <table class="wepos-cart-table">
                <thead>
                    <tr>
                        <th class="wepos-col-name">Product</th>
                        <th class="wepos-col-price">Price</th>
                        <th class="wepos-col-qty">Qty</th>
                        <th class="wepos-col-total">Total</th>
                        <th class="wepos-col-action"></th>
                    </tr>
                </thead>
                <tbody id="weposCartBody">
                    <tr>
                        <td colspan="5" class="wepos-empty-cart">
                            <i class="fas fa-shopping-cart fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                            <p>Cart is empty</p>
                            <small>Scan barcode or click products to add</small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Calculations & Checkout -->
        <div class="wepos-checkout-area">
            
            <!-- Discount Selection -->
            <div class="wepos-discount-row">
                <div class="wepos-discount-label"><i class="fas fa-tags" style="color:#c0392b;"></i> Apply Discount</div>
                <select id="weposDiscount" class="wepos-select" onchange="weposOnDiscountChange(this)">
                    <?php foreach ($discounts as $d): ?>
                        <option value="<?= $d['id'] ?>" 
                                data-rate="<?= $d['discount_rate'] ?>" 
                                data-exempt="<?= $d['is_vat_exempt'] ?>"
                                <?= $d['discount_rate'] == 0 ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['discount_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Math Breakdown -->
            <div class="wepos-calc-box">
                <div class="wepos-calc-row">
                    <span>Subtotal</span>
                    <span id="calcSub">₱0.00</span>
                </div>
                <div class="wepos-calc-row text-danger" id="rowDiscount" style="display:none;">
                    <span>Discount (<span id="calcDiscountLabel">0%</span>)</span>
                    <span id="calcDiscount">-₱0.00</span>
                </div>
                <div class="wepos-calc-row text-muted" id="rowVatExempt" style="display:none;">
                    <span>VAT Exemption</span>
                    <span id="calcVatExempt">-₱0.00</span>
                </div>
                <div class="wepos-calc-row text-muted" id="rowVat">
                    <span>VAT (12%)</span>
                    <span id="calcVat">₱0.00</span>
                </div>

                <div class="wepos-calc-row total-row">
                    <span>Total Due</span>
                    <span class="wepos-grand-total" id="calcTotal">₱0.00</span>
                </div>
            </div>

            <!-- Pay Button -->
            <button type="button" class="wepos-pay-btn" id="weposPayBtn" onclick="weposOpenPayModal()" disabled>
                <span>Pay Now</span>
                <span class="wepos-pay-amount" id="btnTotalAmount">₱0.00</span>
            </button>
        </div>

    </div>

</div>

<!-- ═══════════════ PAYMENT MODAL ═══════════════ -->
<div class="wepos-modal-overlay" id="weposPayModal" style="display:none;" onclick="weposClosePayModal(event)">
    <div class="wepos-modal" onclick="event.stopPropagation()">
        <div class="wepos-modal-head">
            <h5>Complete Payment</h5>
            <button onclick="weposClosePayModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="wepos-modal-body">
            
            <div class="wepos-modal-totals">
                <div class="wepos-modal-amt">Amount Due: <strong style="color:#c0392b;" id="modalAmountDue">₱0.00</strong></div>
            </div>

            <!-- Checkout Items with Override -->
            <div id="weposCheckoutItems" style="max-height: 200px; overflow-y: auto; margin-bottom: 15px; border: 1px solid #e0e0e0; border-radius: 4px; display: none;"></div>



            <div class="wepos-tendered-box">
                <label>Amount Tendered (₱)</label>
                <input type="number" id="weposTendered" class="wepos-input-lg" placeholder="" oninput="weposCalcChange()" onblur="weposCheckTendered()" autofocus>
                
                <div class="wepos-quick-cash" id="weposQuickCash"></div>
            </div>

            <div class="wepos-change-box" id="weposChangeBox" style="display:none;">
                <span>Change:</span>
                <strong id="modalChange">₱0.00</strong>
            </div>

        </div>
        <div class="wepos-modal-foot">
            <button type="button" class="wepos-btn wepos-btn-outline" onclick="weposClosePayModal()">Cancel</button>
            <button type="button" class="wepos-btn wepos-btn-primary" id="weposModalConfirmBtn" onclick="weposSubmitTransaction()" disabled>Confirm Payment</button>
        </div>
    </div>
</div>

<!-- ═══════════════ OVERRIDE PIN MODAL ═══════════════ -->
<div class="wepos-modal-overlay" id="overridePinModal" style="display:none;" onclick="event.stopPropagation()">
    <div class="wepos-modal" onclick="event.stopPropagation()">
        <div class="wepos-modal-head" style="background: #fff3cd;">
            <h5 style="color: #856404;"><i class="fas fa-shield-alt"></i> Manager Approval Required</h5>
            <button type="button" onclick="weposCancelOverride()"><i class="fas fa-times"></i></button>
        </div>
        <div class="wepos-modal-body">
            <p class="text-muted" style="font-size:0.9rem; margin-bottom:1rem;">
                Discount override requires manager authorization.<br>
                A log of this action will be recorded.
            </p>
            <div id="overrideItemPreview" style="background:#f8f9fa; border:1px solid #dcdcde; padding:10px 14px; border-radius:4px; font-size:13px; margin-bottom:14px;"></div>
            <div style="margin-bottom: 0.75rem;">
                <label style="font-size:0.85rem; font-weight:600; margin-bottom:0.25rem; display:block;">Reason for Discount</label>
                <textarea id="overrideReason" rows="2" placeholder="e.g. Regular customer, damaged packaging..." style="width:100%; border-radius:4px; border:1px solid #8c8f94; padding:0.5rem; font-size:0.9rem; resize:none;"></textarea>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.85rem; font-weight:600; margin-bottom:0.25rem; display:block;">Override Discount %</label>
                <input type="number" id="overridePercent" min="1" max="100" value="12" step="1" style="width:100%; border-radius:4px; border:1px solid #8c8f94; padding:0.5rem; font-size:1rem;">
            </div>
            <hr>
            <p style="font-size:0.85rem; font-weight:600; margin-bottom:0.5rem;">Manager Login</p>
            <input type="text" id="overrideUsername" placeholder="Manager username" autocomplete="off" style="width:100%; border-radius:4px; border:1px solid #8c8f94; padding:0.5rem; font-size:0.9rem; margin-bottom:0.5rem;">
            <input type="password" id="overridePassword" placeholder="Manager password" style="width:100%; border-radius:4px; border:1px solid #8c8f94; padding:0.5rem; font-size:0.9rem; margin-bottom:1rem;">
            <div id="overridePinError" class="text-danger" style="font-size:0.85rem; display:none; margin-bottom:0.5rem;"></div>
        </div>
        <div class="wepos-modal-foot">
            <button class="wepos-btn wepos-btn-outline" onclick="weposCancelOverride()">Cancel</button>
            <button class="wepos-btn wepos-btn-primary" onclick="weposSubmitOverride()"><i class="fas fa-unlock"></i> Authorize Override</button>
        </div>
    </div>
</div>





<!-- ═══════════════ VOID AUTH MODAL ═══════════════ -->
<div class="wepos-modal-overlay" id="voidAuthModal" style="display:none;" onclick="event.stopPropagation()">
    <div class="wepos-modal" onclick="event.stopPropagation()">
        <div class="wepos-modal-head" style="background: #fee2e2; border-bottom: 1px solid #fecaca;">
            <h5 style="color: #991b1b;"><i class="fas fa-trash-alt"></i> Manager Void Required</h5>
            <button onclick="weposCancelVoidAuth()"><i class="fas fa-times"></i></button>
        </div>
        <div class="wepos-modal-body">
            <div id="voidItemPreview" style="background:#f8f9fa; border:1px solid #dcdcde; padding:10px 14px; border-radius:4px; font-size:13px; margin-bottom:14px;"></div>
            <p class="text-muted" style="font-size:0.9rem; margin-bottom:1rem;">
                Please enter the 7-digit Manager Void PIN to remove this item from the cart.
            </p>
            <div style="margin-bottom:0.75rem;">
                <input type="password" id="voidAuthPin" placeholder="7-Digit Void PIN" style="width:100%; border-radius:4px; border:1px solid #8c8f94; padding:0.5rem; font-size:1.2rem; text-align:center; letter-spacing:4px;" autocomplete="off" maxlength="7">
            </div>
            <div id="voidAuthError" class="text-danger" style="font-size:0.85rem; display:none; margin-bottom:0.5rem; text-align:center;"></div>
        </div>
        <div class="wepos-modal-foot">
            <button class="wepos-btn wepos-btn-outline" onclick="weposCancelVoidAuth()">Cancel</button>
            <button class="wepos-btn wepos-btn-primary" style="background-color: #dc2626; border-color: #dc2626;" id="voidAuthBtn" onclick="weposSubmitVoidAuth()"><i class="fas fa-trash"></i> Confirm Remove</button>
        </div>
    </div>
</div>

<script>
    const WEPOS_ROLE = '<?= $userRole ?>';
    const WEPOS_IS_MANAGER = <?= $isManager ? 'true' : 'false' ?>;
</script>

<!-- ═══════════════ RECEIPT MODAL ═══════════════ -->
<div class="wepos-modal-overlay" id="weposReceiptModal" style="display:none;" onclick="event.stopPropagation()">
    <div class="wepos-modal" onclick="event.stopPropagation()" style="max-width:400px; border-radius:8px;">
        <div class="wepos-modal-head" style="background:#f0fdf4; border-bottom:1px solid #bbf7d0;">
            <h5 style="color:#15803d;"><i class="fas fa-receipt"></i> Payment Successful</h5>
            <button type="button" onclick="weposCloseReceipt()"><i class="fas fa-times"></i></button>
        </div>
        <div class="wepos-modal-body" id="weposReceiptBody" style="padding:0;">

            <!-- Printable Receipt -->
            <div id="weposReceiptPrint" style="padding:20px; font-family:'Courier New',monospace; font-size:13px;">
                <div style="text-align:center; margin-bottom:12px;">
                    <div style="font-size:16px; font-weight:700;">MMB'S DRUGSTORE</div>
                    <div style="font-size:11px; color:#64748b;">Official Receipt</div>
                    <div style="font-size:11px; color:#64748b;" id="receiptDateTime"></div>
                </div>
                <div style="border-top:1px dashed #cbd5e1; border-bottom:1px dashed #cbd5e1; padding:6px 0; margin-bottom:10px;">
                    <span style="color:#64748b;">Ref #:</span> <strong id="receiptRefNo"></strong>
                </div>
                <div id="receiptItems" style="margin-bottom:10px;"></div>
                <div style="border-top:1px dashed #cbd5e1; padding-top:8px; font-size:12px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                        <span>Subtotal</span><span id="receiptSubtotal"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#dc2626;" id="receiptDiscountRow">
                        <span>Discount (<span id="receiptDiscLabel"></span>)</span><span id="receiptDiscount"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#64748b;" id="receiptVatExRow">
                        <span>VAT Exempt</span><span id="receiptVatEx"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#64748b;">
                        <span>VAT (12%)</span><span id="receiptVat"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:700; border-top:1px solid #e2e8f0; padding-top:6px; margin-top:4px;">
                        <span>TOTAL</span><span id="receiptTotal" style="color:#15803d;"></span>
                    </div>
                </div>
                <div style="border-top:1px dashed #cbd5e1; margin-top:10px; padding-top:8px; font-size:12px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                        <span>Payment Method</span><span id="receiptMethod"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:3px;" id="receiptTenderedRow">
                        <span>Cash Tendered</span><span id="receiptTendered"></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-weight:700;" id="receiptChangeRow">
                        <span>Change</span><span id="receiptChange"></span>
                    </div>
                </div>
                <div style="text-align:center; margin-top:14px; font-size:11px; color:#94a3b8;">
                    Thank you for your purchase!<br>
                    Please come again.
                </div>
            </div>

        </div>
        <div class="wepos-modal-foot" style="gap:10px;">
            <button type="button" class="wepos-btn wepos-btn-outline" onclick="weposPrintReceipt()">
                <i class="fas fa-print"></i> Print Receipt
            </button>
            <button type="button" class="wepos-btn wepos-btn-primary" onclick="weposCloseReceipt()">
                Done
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════ SENIOR/PWD VERIFY MODAL ═══════════════ -->
<div class="wepos-modal-overlay" id="verifyIdModal" style="display:none;" onclick="event.stopPropagation()">
    <div class="wepos-modal" onclick="event.stopPropagation()">
        <div class="wepos-modal-head" style="background: #eef2ff; border-bottom: 1px solid #c7d2fe;">
            <h5 style="color: #3730a3;" id="verifyIdTitle"><i class="fas fa-id-card"></i> ID Verification</h5>
            <button type="button" onclick="weposCancelVerifyId()"><i class="fas fa-times"></i></button>
        </div>
        <div class="wepos-modal-body">
            <p class="text-muted" style="font-size:0.9rem; margin-bottom:1rem;">
                Please enter the customer's details to verify their ID.
            </p>
            
            <div style="margin-bottom: 0.75rem;">
                <label style="font-size:0.85rem; font-weight:600; margin-bottom:0.25rem; display:block;">Full Name</label>
                <input type="text" id="verifyIdName" placeholder="Juan Dela Cruz" style="width:100%; border-radius:4px; border:1px solid #8c8f94; padding:0.5rem; font-size:1rem;" autocomplete="off">
            </div>
            
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.85rem; font-weight:600; margin-bottom:0.25rem; display:block;">ID Number</label>
                <input type="text" id="verifyIdNumber" placeholder="XXXX-XXXX-XXXX" style="width:100%; border-radius:4px; border:1px solid #8c8f94; padding:0.5rem; font-size:1rem;" autocomplete="off">
            </div>
            
            <div id="verifyIdError" class="text-danger" style="font-size:0.85rem; display:none; margin-bottom:0.5rem;"></div>
            
            <div id="verifyIdNewMsg" class="text-warning" style="font-size:0.85rem; display:none; margin-bottom:0.5rem; color: #d97706; background: #fef3c7; padding: 8px; border-radius: 4px; border-left: 3px solid #f59e0b;">
                <i class="fas fa-exclamation-triangle"></i> This ID has not been verified yet for a discount. Please check the website.
            </div>
        </div>
        <div class="wepos-modal-foot" id="verifyIdFootInitial">
            <button type="button" class="wepos-btn wepos-btn-outline" onclick="weposCancelVerifyId()">Cancel</button>
            <button type="button" class="wepos-btn wepos-btn-primary" id="verifyIdBtn" onclick="weposSubmitVerifyId()">Verify</button>
        </div>
        <div class="wepos-modal-foot" id="verifyIdFootManual" style="display:none; justify-content: space-between;">
            <button class="wepos-btn wepos-btn-outline" style="color: #dc2626; border-color: #dc2626;" onclick="weposDeclineVerify()">Decline</button>
            <div style="display:flex; gap: 8px;">
                <button class="wepos-btn wepos-btn-primary" style="background-color: #3b82f6; border-color: #3b82f6;" onclick="weposOpenVerificationSite()"><i class="fas fa-external-link-alt"></i> Check Website</button>
                <button class="wepos-btn wepos-btn-primary" style="background-color: #16a34a; border-color: #16a34a;" onclick="weposApproveVerify()"><i class="fas fa-check"></i> Accept</button>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════ CUSTOM CONFIRM MODAL ═══════════════ -->
<div class="wepos-modal-overlay" id="weposConfirmModal" style="display:none;" onclick="event.stopPropagation()">
    <div class="wepos-modal" style="max-width: 400px;" onclick="event.stopPropagation()">
        <div class="wepos-modal-body" style="text-align:center; padding: 2rem;">
            <div id="weposConfirmIcon" style="font-size: 3.5rem; margin-bottom: 1.2rem; color: #f59e0b;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h4 id="weposConfirmTitle" style="font-weight:800; margin-bottom:0.7rem; color: #1e293b;">Are you sure?</h4>
            <p id="weposConfirmMsg" class="text-muted" style="font-size: 1rem; line-height: 1.5;">Do you really want to clear the entire cart? This action cannot be undone.</p>
            
            <div class="d-flex justify-content-center" style="gap:12px; margin-top:2rem;">
                <button type="button" class="wepos-btn wepos-btn-outline" style="min-width: 100px;" id="weposConfirmCancelBtn">Cancel</button>
                <button type="button" class="wepos-btn wepos-btn-danger" style="min-width: 100px; background: #dc2626;" id="weposConfirmOkBtn">Yes, Clear</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════ NOTIFICATION MODAL ═══════════════ -->
<div class="wepos-modal-overlay" id="weposNotifModal" style="display:none; z-index:99999;" onclick="document.getElementById('weposNotifModal').style.display='none'">
    <div class="wepos-modal" style="max-width:380px; text-align:center;" onclick="event.stopPropagation()">
        <div class="wepos-modal-body" style="padding:2rem 2rem 1rem;">
            <div id="weposNotifIcon" style="font-size:3rem; margin-bottom:0.75rem;"></div>
            <h5 id="weposNotifTitle" style="font-weight:700; margin-bottom:0.5rem; color:#1e293b;"></h5>
            <p id="weposNotifMsg" style="color:#50575e; margin:0; font-size:0.95rem; line-height:1.5;"></p>
        </div>
        <div class="wepos-modal-foot">
            <button type="button" class="wepos-btn wepos-btn-primary" style="min-width:100px;" onclick="document.getElementById('weposNotifModal').style.display='none'">OK</button>
        </div>
    </div>
</div>

<script>
    const WEPOS_ROLE = '<?= $userRole ?>';
    const WEPOS_IS_MANAGER = <?= $isManager ? 'true' : 'false' ?>;
</script>
<script src="../js/pos_wepos.js?v=2.7"></script>
