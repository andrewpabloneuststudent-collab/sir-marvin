let weposCart = {};
let barcodeBuffer = '';
let barcodeTimer = null;
let currentPayMethod = 'Cash';
let pendingOverride = null;
let pendingVoid = null;           // stores cart item id pending void auth
let pendingClearCart = false;     // tracks if void auth is for clearing entire cart
let pendingDiscountIndex = null;  // stores previous discount index if user cancels verify
let weposVerified = false;        // tracks if Senior/PWD customer has been verified this session
let weposCustomerType = null;     // tracks customer type: 'senior', 'pwd', or null for regular
let weposCustomerName = null;     // tracks customer name for senior/pwd customers
let weposCustomerId = null;       // tracks customer ID linking to senior_customers/pwd_customers table
let weposLastReceiptData = null;  // stores last receipt data for printing

document.addEventListener('DOMContentLoaded', () => {
    try {
        console.log('DOMContentLoaded event firing, initializing wePOS...');
        weposSetupScanner();
        weposSetupSearch();
        weposSetupKeyboard();
        weposUpdateCart();
        console.log('wePOS initialization complete');
    } catch (error) {
        console.error('Error during wePOS initialization:', error);
    }
});

// ═════ BARCODE SCANNER ═════
function weposSetupScanner() {
    document.addEventListener('keypress', function(e) {
        const active = document.activeElement;
        const isSearch = active && active.id === 'weposSearch';
        const isModalInput = active && (active.id === 'weposTendered' || active.id === 'weposCustomer' || active.id === 'overrideReason' || active.id === 'overrideUsername' || active.id === 'overridePassword' || active.id === 'overridePercent' || active.id === 'verifyIdName' || active.id === 'verifyIdNumber' || active.id === 'voidAuthPin');

        if (isModalInput) return;

        if (e.key === 'Enter') {
            e.preventDefault();
            if (isSearch && active.value.trim().length > 0) {
                weposFindAndAdd(active.value.trim());
                active.value = '';
            } else if (barcodeBuffer.length > 2) {
                weposFindAndAdd(barcodeBuffer);
            }
            barcodeBuffer = '';
            return;
        }

        if (!isSearch) {
            barcodeBuffer += e.key;
            clearTimeout(barcodeTimer);
            barcodeTimer = setTimeout(() => { barcodeBuffer = ''; }, 80);
        }
    });
}

function weposFindAndAdd(code) {
    const cards = document.querySelectorAll('.wepos-product-card:not(.out-of-stock):not(.expired)');
    for (const card of cards) {
        if (card.dataset.barcode === code) {
            weposAddToCart(card);
            return true;
        }
    }
    const lc = code.toLowerCase();
    for (const card of cards) {
        if (card.dataset.name.toLowerCase().includes(lc)) {
            weposAddToCart(card);
            return true;
        }
    }
    alert('Product not found or out of stock: ' + code);
    return false;
}

// ═════ SEARCH & FILTER ═════
function weposSetupSearch() {
    const search = document.getElementById('weposSearch');
    if (search) {
        search.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('.wepos-product-card').forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const barcode = (card.dataset.barcode || '').toLowerCase();
                card.style.display = (!query || name.includes(query) || barcode.includes(query)) ? '' : 'none';
            });
        });
    }
}

function weposFilterCat(category) {
    document.querySelectorAll('.wepos-cat-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    document.querySelectorAll('.wepos-product-card').forEach(card => {
        if (category === 'All' || card.dataset.category === category) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
    const search = document.getElementById('weposSearch');
    if (search) search.value = '';
}

// ═════ CART LOGIC ═════
function weposAddToCart(cardEl) {
    console.log('weposAddToCart called with element:', cardEl?.dataset?.id);
    
    // Validate that cardEl is provided and is an element
    if (!cardEl || !cardEl.dataset) {
        console.error('Invalid element passed to weposAddToCart');
        alert('Error: Could not add product to cart. Please refresh the page.');
        return;
    }
    
    const id = cardEl.dataset.id;
    const stock = parseInt(cardEl.dataset.stock) || 0;
    const isExpired = cardEl.dataset.expired === '1';
    
    // Validate required data
    if (!id) {
        console.error('Product ID missing from data attributes', cardEl.dataset);
        alert('Error: Product information is incomplete. Please refresh the page.');
        return;
    }
    
    console.log('Product ID:', id, 'Stock:', stock, 'Expired:', isExpired);

    if (isExpired) {
        alert('Cannot add expired product to cart!');
        return;
    }

    if (stock <= 0) {
        alert('Item is Out of Stock!');
        return;
    }

    if (weposCart[id]) {
        if (weposCart[id].qty >= stock) {
            alert('Cannot add more. Only ' + stock + ' in stock.');
            return;
        }
        weposCart[id].qty++;
    } else {
        const price = parseFloat(cardEl.dataset.price);
        const net = parseFloat(cardEl.dataset.net || cardEl.dataset.price);
        
        // Validate prices
        if (isNaN(price) || price < 0) {
            console.error('Invalid price:', cardEl.dataset.price, 'parsed to:', price);
            alert('Error: Product price is invalid. Please refresh the page.');
            return;
        }
        
        weposCart[id] = {
            id: id,
            name: cardEl.dataset.name,
            price: price,
            net: isNaN(net) ? price : net,
            qty: 1,
            // Category-based flags (auto-apply from DB)
            hasVat: cardEl.dataset.hasVat === '1',
            senior: cardEl.dataset.senior === '1',
            pwd: cardEl.dataset.pwd === '1',
            stock: stock,
            // Override state (set during checkout)
            override: false,
            overrideRate: 0,
            overrideApprover: null
        };
        
        console.log('Added product to cart:', weposCart[id]);
    }

    // Visual feedback
    cardEl.classList.add('flash-add');
    setTimeout(() => cardEl.classList.remove('flash-add'), 300);

    weposUpdateCart();
}

function weposUpdateQty(id, delta) {
    // Ensure id and delta are proper types
    id = String(id).trim();
    delta = Number(delta);
    
    if (!weposCart[id]) {
        console.warn('Item not in cart:', id);
        return;
    }
    
    weposCart[id].qty = Number(weposCart[id].qty) + delta;
    if (weposCart[id].qty <= 0) {
        delete weposCart[id];
    } else if (weposCart[id].qty > weposCart[id].stock) {
        weposCart[id].qty = weposCart[id].stock;
        alert('Maximum stock reached');
    }
    
    weposUpdateCart();
}

function weposRemoveItem(id) {
    id = String(id).trim();
    const item = weposCart[id];
    
    if (!item) {
        console.warn('Item not found in cart:', id);
        return;
    }

    // Intercept with void authorization modal
    pendingVoid = id;
    document.getElementById('voidItemPreview').innerHTML =
        `<strong>${item.name}</strong> &times; ${item.qty} &mdash; &#8369;${(item.price * item.qty).toFixed(2)}`;
    document.getElementById('voidAuthPin').value = '';
    document.getElementById('voidAuthError').style.display = 'none';
    const btn = document.getElementById('voidAuthBtn');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Confirm Remove';
    }
    document.getElementById('voidAuthModal').style.display = 'flex';
    setTimeout(() => document.getElementById('voidAuthPin')?.focus(), 100);
}

function weposClearCart() {
    if (Object.keys(weposCart).length === 0) return;
    
    // Show void auth modal for clearing entire cart
    pendingClearCart = true;
    const cartItems = Object.values(weposCart);
    const cartTotal = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const itemCount = cartItems.length;
    
    document.getElementById('voidItemPreview').innerHTML =
        `<strong><i class="fas fa-exclamation-triangle" style="color: #dc2626; margin-right: 8px;"></i>Clear Entire Cart</strong><br><br>` +
        `<small>${itemCount} item(s) • Total: &#8369;${cartTotal.toFixed(2)}</small>`;
    document.getElementById('voidAuthPin').value = '';
    document.getElementById('voidAuthError').style.display = 'none';
    
    const btn = document.getElementById('voidAuthBtn');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt"></i> Clear Cart';
    }
    
    // Update modal title
    const modalHead = document.querySelector('#voidAuthModal .wepos-modal-head h5');
    if (modalHead) {
        modalHead.innerHTML = '<i class="fas fa-trash-alt"></i> Clear Cart Authorization';
    }
    
    // Update modal instructions
    const instructions = document.querySelector('#voidAuthModal .wepos-modal-body p');
    if (instructions) {
        instructions.textContent = 'Please enter the 7-digit Manager Void PIN to clear the entire cart.';
    }
    
    document.getElementById('voidAuthModal').style.display = 'flex';
    setTimeout(() => document.getElementById('voidAuthPin')?.focus(), 100);
}

// ═════ MATH & DISCOUNT CALCULATION ═════
function weposCalcItem(item, dRate, isVatExempt) {
    let gross = item.price * item.qty;
    let vatExempt = 0;
    let discount = 0;
    let vatAmount = 0;
    let finalPrice = gross;

    // Manual override beats everything
    if (item.override && item.overrideRate > 0) {
        let base = gross;
        if (item.hasVat) {
            const net = gross / 1.12;
            vatExempt = gross - net;
            base = net;
        }
        discount = base * item.overrideRate;
        finalPrice = gross - vatExempt - discount;
        return { gross, vatExempt, discount, vatAmount: 0, final: finalPrice };
    }

    // ═══ PHILIPPINE LAW APPLICATION ═══
    // 1. Check if item is VAT-EXEMPT per category (Medicines, medical supplies, etc.)
    if (!item.hasVat) {
        // Item is VAT-exempt (e.g., medicines per BIR regulations)
        // Price stays the same, no VAT added
        vatExempt = 0;
        vatAmount = 0;
        finalPrice = gross;
    } else {
        // Item HAS VAT (non-medicine items like food, beverages, etc.)
        // VAT amount = original price × 12% / 112%
        const netPrice = gross / 1.12;
        vatAmount = gross - netPrice;
        finalPrice = gross;  // Price already includes VAT
    }

    // 2. Apply discount only to items eligible per category configuration
    if (weposVerified && dRate > 0) {
        // Check if item is eligible for discount based on category flags
        const isSeniorEligible = item.senior === true;
        const isPWDEligible = item.pwd === true;
        const isEligible = isSeniorEligible || isPWDEligible;
        
        if (isEligible) {
            // Apply discount rate on the base price (net of VAT if applicable)
            // This respects the dRate parameter (could be 12%, 20%, or custom rate)
            const basePrice = item.hasVat ? (gross / 1.12) : gross;
            discount = basePrice * dRate;
            finalPrice = gross - discount;
        }
    }

    return { 
        gross: item.price * item.qty, 
        vatExempt: 0,  // No VAT exemption benefit shown (it's already in the price)
        discount, 
        vatAmount, 
        final: finalPrice
    };
}

function weposUpdateCart() {
    const tbody = document.getElementById('weposCartBody');
    
    // Safety check - if cart body element doesn't exist, exit
    if (!tbody) {
        console.error('Cart body element (weposCartBody) not found in DOM');
        return;
    }
    
    const entries = Object.values(weposCart);

    if (entries.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="wepos-empty-cart">
                    <i class="fas fa-shopping-cart fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                    <p>Cart is empty</p>
                    <small>Scan barcode or click products to add</small>
                </td>
            </tr>`;
        weposSetTotals(0, 0, 0, 0, 0, 0);
        document.getElementById('weposPayBtn').disabled = true;
        return;
    }

    // Get discount info
    const discountSelect = document.getElementById('weposDiscount');
    if (!discountSelect) {
        console.warn('Discount select element not found');
        weposSetTotals(0, 0, 0, 0, 0, 0);
        return;
    }
    
    const selOpt = discountSelect.options[discountSelect.selectedIndex];
    if (!selOpt) {
        console.warn('No discount option selected or available');
        weposSetTotals(0, 0, 0, 0, 0, 0);
        return;
    }
    
    const discountName = (selOpt?.text || '').toLowerCase();
    const isSpecialDiscount = discountName.includes('senior') || discountName.includes('pwd');
    
    // For Senior/PWD: use 20% if verified (RA 9994 & RA 10754), otherwise use database rate
    const dRate = isSpecialDiscount && weposVerified ? 0.20 : (parseFloat(selOpt.dataset.rate) || 0);
    const isVatExempt = selOpt.dataset.exempt === '1';

    let html = '';
    let rawSubtotal = 0;
    let totalDiscount = 0;
    let totalVatExemption = 0;
    let finalVat = 0;

    entries.forEach(item => {
        const lineTotal = item.price * item.qty;
        rawSubtotal += lineTotal;

        const c = weposCalcItem(item, dRate, isVatExempt);
        totalDiscount += c.discount;
        totalVatExemption += c.vatExempt;
        finalVat += c.vatAmount;

        const overrideBadge = item.override
            ? ` <span style="font-size:10px; color:#00a32a; font-weight:600;">✓ ${(item.overrideRate*100).toFixed(0)}% OFF</span>`
            : '';

        html += `
            <tr class="wepos-cart-row">
                <td class="wepos-col-name">
                    <div class="wepos-cart-item-name" title="${item.name}">${item.name}${overrideBadge}</div>
                </td>
                <td class="wepos-col-price text-muted">₱${item.price.toFixed(2)}</td>
                <td class="wepos-col-qty">
                    <div class="wepos-qty-ctrl">
                        <button onclick="weposUpdateQty('${item.id}', -1)"><i class="fas fa-minus"></i></button>
                        <span>${item.qty}</span>
                        <button onclick="weposUpdateQty('${item.id}', 1)"><i class="fas fa-plus"></i></button>
                    </div>
                </td>
                <td class="wepos-col-total">₱${c.final.toFixed(2)}</td>
                <td class="wepos-col-action">
                    <button class="wepos-btn-icon text-danger" onclick="weposRemoveItem('${item.id}')"><i class="fas fa-times"></i></button>
                </td>
            </tr>`;
    });

    tbody.innerHTML = html;

    // ═══ CORRECT TOTAL CALCULATION ═══
    // Final Total = Subtotal - Discounts + VAT
    const finalTotal = rawSubtotal - totalDiscount + finalVat;
    weposSetTotals(rawSubtotal, totalDiscount, dRate, totalVatExemption, finalVat, finalTotal);
    document.getElementById('weposPayBtn').disabled = false;
}

function weposSetTotals(sub, disc, dRate, vatExempt, vat, total) {
    const calcSub = document.getElementById('calcSub');
    if (calcSub) calcSub.textContent = '₱' + sub.toFixed(2);
    else console.warn('Element calcSub not found');
    
    const rowDisc = document.getElementById('rowDiscount');
    if (rowDisc) {
        if (disc > 0) {
            rowDisc.style.display = 'flex';
            const discLabel = document.getElementById('calcDiscountLabel');
            const calcDisc = document.getElementById('calcDiscount');
            if (discLabel) discLabel.textContent = (dRate * 100).toFixed(0) + '%';
            if (calcDisc) calcDisc.textContent = '-₱' + disc.toFixed(2);
        } else {
            rowDisc.style.display = 'none';
        }
    }

    const rowVat = document.getElementById('rowVat');
    if (rowVat) {
        if (vat > 0) {
            rowVat.style.display = 'flex';
            const calcVat = document.getElementById('calcVat');
            if (calcVat) calcVat.textContent = '+₱' + vat.toFixed(2);
        } else {
            rowVat.style.display = 'none';
        }
    }

    const rowVatEx = document.getElementById('rowVatExempt');
    if (rowVatEx) {
        if (vatExempt > 0) {
            rowVatEx.style.display = 'flex';
            const calcVatEx = document.getElementById('calcVatExempt');
            if (calcVatEx) calcVatEx.textContent = '-₱' + vatExempt.toFixed(2);
        } else {
            rowVatEx.style.display = 'none';
        }
    }

    const calcTotal = document.getElementById('calcTotal');
    if (calcTotal) calcTotal.textContent = '₱' + total.toFixed(2);
    
    const btnAmount = document.getElementById('btnTotalAmount');
    if (btnAmount) btnAmount.textContent = '₱' + total.toFixed(2);
}

// ═════ KEYBOARD SHORTCUTS ═════
function weposSetupKeyboard() {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F2') {
            e.preventDefault();
            document.getElementById('weposSearch')?.focus();
        }
        if (e.key === 'F8') {
            e.preventDefault();
            weposClearCart();
        }
        if (e.key === 'F12' && !document.getElementById('weposPayBtn').disabled) {
            e.preventDefault();
            weposOpenPayModal();
        }
        if (e.key === 'Escape') {
            weposClosePayModal();
            weposCancelOverride();
            weposCancelVerifyId();
            weposCancelVoidAuth();
        }
    });
}

// ═════ PAYMENT MODAL ═════
function weposOpenPayModal() {
    if (Object.keys(weposCart).length === 0) return;

    // Gate: Senior/PWD requires verification before payment
    const discountSelect = document.getElementById('weposDiscount');
    const selOpt = discountSelect.options[discountSelect.selectedIndex];
    const discountName = (selOpt?.text || '').toLowerCase();
    const needsVerify = discountName.includes('senior') || discountName.includes('pwd');

    if (needsVerify && !weposVerified) {
        // Trigger the verification modal instead of pay modal
        weposOnDiscountChange(discountSelect);
        return;
    }

    const totalText = document.getElementById('btnTotalAmount').textContent.replace('₱', '');
    const total = parseFloat(totalText);
    
    document.getElementById('modalAmountDue').textContent = '₱' + total.toFixed(2);
    document.getElementById('weposTendered').value = '';
    document.getElementById('weposChangeBox').style.display = 'none';
    document.getElementById('modalConfirmBtn').disabled = true;

    // Render checkout items with override buttons
    weposRenderCheckoutItems();

    weposGenerateQuickCash(total);
    document.getElementById('weposPayModal').style.display = 'flex';
    setTimeout(() => document.getElementById('weposTendered')?.focus(), 100);
}

function weposClosePayModal(e) {
    if (e && e.target !== e.currentTarget) return;
    document.getElementById('weposPayModal').style.display = 'none';
}

// ═════ CHECKOUT ITEMS WITH OVERRIDE ═════
function weposRenderCheckoutItems() {
    const container = document.getElementById('weposCheckoutItems');
    if (!container) return;

    const entries = Object.values(weposCart);
    const discountSelect = document.getElementById('weposDiscount');
    const selOpt = discountSelect.options[discountSelect.selectedIndex];
    const dRate = parseFloat(selOpt.dataset.rate) || 0;
    const isVatExempt = selOpt.dataset.exempt === '1';

    let html = '';
    entries.forEach(item => {
        const c = weposCalcItem(item, dRate, isVatExempt);
        const overrideLabel = item.override
            ? `<span style="color:#00a32a; font-weight:600;">${(item.overrideRate*100).toFixed(0)}% OFF (by ${item.overrideApprover})</span>`
            : '';
        
        html += `<div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; border-bottom:1px solid #f0f0f1;">
            <div style="flex:1; min-width:0;">
                <div style="font-weight:500; font-size:13px;">${item.name}</div>
                <div style="font-size:12px; color:#50575e;">${item.qty} × ₱${item.price.toFixed(2)} = ₱${c.final.toFixed(2)} ${overrideLabel}</div>
            </div>
            <button onclick="weposRequestOverride('${item.id}')" 
                    style="padding:4px 10px; font-size:11px; border-radius:3px; border:1px solid ${item.override ? '#00a32a' : '#8c8f94'}; background:${item.override ? '#f0fdf4' : '#f6f7f7'}; color:${item.override ? '#00a32a' : '#50575e'}; cursor:pointer; white-space:nowrap;">
                <i class="fas fa-tag"></i> ${item.override ? 'Remove' : 'Override'}
            </button>
        </div>`;
    });

    container.innerHTML = html;
    container.style.display = entries.length > 0 ? 'block' : 'none';
}

// ═════ OVERRIDE FLOW ═════
function weposRequestOverride(cartId) {
    const item = weposCart[cartId];
    if (!item) return;

    // If already overridden, remove it
    if (item.override) {
        if (confirm('Remove the override discount from "' + item.name + '"?')) {
            item.override = false;
            item.overrideRate = 0;
            item.overrideApprover = null;
            weposRenderCheckoutItems();
            weposUpdateCart();
            // Refresh modal amount
            const totalText = document.getElementById('calcTotal').textContent.replace('₱', '');
            document.getElementById('modalAmountDue').textContent = '₱' + parseFloat(totalText).toFixed(2);
        }
        return;
    }

    // Open the PIN modal
    pendingOverride = { cartId };
    document.getElementById('overrideItemPreview').innerHTML =
        `<strong>${item.name}</strong> — ₱${(item.price * item.qty).toFixed(2)} (${item.qty} × ₱${item.price.toFixed(2)})`;
    document.getElementById('overrideReason').value = '';
    document.getElementById('overrideUsername').value = '';
    document.getElementById('overridePassword').value = '';
    document.getElementById('overridePercent').value = 12;
    document.getElementById('overridePinError').style.display = 'none';
    document.getElementById('overridePinModal').style.display = 'flex';
}

function weposCancelOverride() {
    pendingOverride = null;
    const modal = document.getElementById('overridePinModal');
    if (modal) modal.style.display = 'none';
}

async function weposSubmitOverride() {
    const reason   = document.getElementById('overrideReason').value.trim();
    const username = document.getElementById('overrideUsername').value.trim();
    const password = document.getElementById('overridePassword').value.trim();
    const pct      = parseFloat(document.getElementById('overridePercent').value) || 0;
    const errEl    = document.getElementById('overridePinError');

    errEl.style.display = 'none';

    if (!reason)              { errEl.textContent = 'Please enter a reason.';               errEl.style.display = 'block'; return; }
    if (!username || !password){ errEl.textContent = 'Enter manager username and password.'; errEl.style.display = 'block'; return; }
    if (pct <= 0 || pct > 100){ errEl.textContent = 'Enter a valid discount % (1–100).';    errEl.style.display = 'block'; return; }

    try {
        const res = await fetch('../function/verify_override_pin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const result = await res.json();

        if (!result.success) {
            errEl.textContent = result.error || 'Invalid credentials';
            errEl.style.display = 'block';
            return;
        }

        const item = weposCart[pendingOverride.cartId];
        if (!item) { weposCancelOverride(); return; }

        item.override = true;
        item.overrideRate = pct / 100;
        item.overrideApprover = result.approver_name;

        // Log the override
        await fetch('../function/log_override.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                product_id: item.id,
                product_name: item.name,
                original_price: item.price * item.qty,
                discounted_price: (item.price * item.qty) * (1 - pct / 100),
                discount_amount: (item.price * item.qty) * (pct / 100),
                discount_percent: pct,
                reason,
                approver_id: result.approver_id,
                approver_name: result.approver_name
            })
        });

        weposCancelOverride();
        weposRenderCheckoutItems();
        weposUpdateCart();
        // Refresh modal amount
        const totalText = document.getElementById('calcTotal').textContent.replace('₱', '');
        document.getElementById('modalAmountDue').textContent = '₱' + parseFloat(totalText).toFixed(2);

    } catch (err) {
        errEl.textContent = 'Network error. Try again.';
        errEl.style.display = 'block';
    }
}

// ═════ PAYMENT METHODS & CASH ═════
function weposSelectMethod(btn, method) {
    document.querySelectorAll('.wepos-pay-method').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentPayMethod = method;

    if (method === 'Cash') {
        document.querySelector('.wepos-tendered-box').style.display = 'block';
        weposCalcChange();
    } else {
        document.querySelector('.wepos-tendered-box').style.display = 'none';
        document.getElementById('weposChangeBox').style.display = 'none';
        document.getElementById('modalConfirmBtn').disabled = false;
    }
}

function weposGenerateQuickCash(total) {
    const container = document.getElementById('weposQuickCash');
    const rounded = Math.ceil(total / 50) * 50;
    const amounts = [...new Set([total, rounded, rounded + 50, rounded + 100, 500, 1000])].filter(a => a >= total).slice(0, 4);
    
    container.innerHTML = '';
}

function weposSetCash(amt) {
    document.getElementById('weposTendered').value = amt;
    weposCalcChange();
}

function weposCalcChange() {
    if (currentPayMethod !== 'Cash') return;
    const total = parseFloat(document.getElementById('btnTotalAmount').textContent.replace('₱', ''));
    const tendered = parseFloat(document.getElementById('weposTendered').value) || 0;
    const change = tendered - total;

    const changeBox = document.getElementById('weposChangeBox');
    const confirmBtn = document.getElementById('modalConfirmBtn');

    if (tendered >= total) {
        changeBox.style.display = 'block';
        document.getElementById('modalChange').textContent = '₱' + change.toFixed(2);
        confirmBtn.disabled = false;
    } else {
        changeBox.style.display = 'none';
        confirmBtn.disabled = true;
    }
}

async function weposSubmitTransaction() {
    const btn = document.getElementById('modalConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = 'Processing...';

    const discountSelect = document.getElementById('weposDiscount');
    const selOpt = discountSelect.options[discountSelect.selectedIndex];
    const discountName = (selOpt?.text || '').toLowerCase();
    const isSpecialDiscount = discountName.includes('senior') || discountName.includes('pwd');
    const dRate = isSpecialDiscount && weposVerified ? 0.20 : (parseFloat(selOpt.dataset.rate) || 0);
    const isVatExempt = selOpt.dataset.exempt === '1';

    const items = Object.entries(weposCart).map(([id, item]) => ({
        id: parseInt(id), price: item.price, qty: item.qty
    }));

    // Calculate receipt totals before clearing cart
    let rawSubtotal = 0, totalDiscount = 0, totalVatExempt = 0, finalVat = 0;
    Object.values(weposCart).forEach(item => {
        rawSubtotal += item.price * item.qty;
        const c = weposCalcItem(item, dRate, isVatExempt);
        totalDiscount   += c.discount;
        totalVatExempt  += c.vatExempt;
        finalVat        += c.vatAmount;
    });
    const finalTotal = rawSubtotal - totalDiscount + finalVat;
    const tendered   = parseFloat(document.getElementById('weposTendered')?.value) || finalTotal;
    const change     = currentPayMethod === 'Cash' ? tendered - finalTotal : 0;

    try {
        const res = await fetch('../function/process_transaction', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                items: items,
                discount_id: discountSelect.value,
                customer_name: weposCustomerName || 'Walk-in',
                customer_id: weposCustomerId,
                customer_type: weposCustomerType,
                discount_total: totalDiscount,
                total_vat_exemption: totalVatExempt
            })
        });
        
        const result = await res.json();
        
        if (result.success) {
            // Close payment modal
            document.getElementById('weposPayModal').style.display = 'none';

            // Build receipt data
            const receiptItems = Object.values(weposCart);
            weposLastReceiptData = {
                refNo:        String(result.transaction_id).padStart(6, '0'),
                items:        receiptItems,
                dRate,
                isVatExempt,
                rawSubtotal,
                totalDiscount,
                totalVatExempt,
                finalVat,
                finalTotal,
                discountLabel: selOpt.text,
                method:       currentPayMethod,
                tendered,
                change
            };

            // Show receipt
            weposShowReceipt(weposLastReceiptData);

            // Reset state
            weposCart = {};
            weposVerified = false;
            weposCustomerType = null;
        } else {
            alert('Payment Error: ' + result.error);
            btn.disabled = false;
            btn.innerHTML = 'Confirm Payment';
        }
    } catch (err) {
        alert('Network Error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = 'Confirm Payment';
    }
}

// ═════ RECEIPT ═════
function weposShowReceipt(data) {
    const now = new Date();
    document.getElementById('receiptDateTime').textContent =
        now.toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' }) + ' ' +
        now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit' });
    document.getElementById('receiptRefNo').textContent = '#' + data.refNo;

    // Items list
    let itemsHtml = '';
    data.items.forEach(item => {
        const c = weposCalcItem(item, data.dRate, data.isVatExempt);
        itemsHtml += `<div style="display:flex; justify-content:space-between; margin-bottom:8px; align-items: flex-start;">
            <div style="flex:1; margin-right:8px;">
                <div style="font-weight:500; line-height:1.2;">${item.name}</div>
                <div style="font-size:11px; color:#64748b;">${item.qty} x &#8369;${item.price.toFixed(2)}</div>
            </div>
            <div style="font-weight:600; white-space:nowrap;">&#8369;${c.final.toFixed(2)}</div>
        </div>`;
    });
    document.getElementById('receiptItems').innerHTML = itemsHtml;

    // Totals
    document.getElementById('receiptSubtotal').textContent  = '\u20b1' + data.rawSubtotal.toFixed(2);
    document.getElementById('receiptVat').textContent       = '\u20b1' + data.finalVat.toFixed(2);
    document.getElementById('receiptTotal').textContent     = '\u20b1' + data.finalTotal.toFixed(2);
    document.getElementById('receiptMethod').textContent    = data.method;

    const discRow = document.getElementById('receiptDiscountRow');
    if (data.totalDiscount > 0) {
        discRow.style.display = 'flex';
        document.getElementById('receiptDiscLabel').textContent = data.discountLabel;
        document.getElementById('receiptDiscount').textContent  = '-\u20b1' + data.totalDiscount.toFixed(2);
    } else {
        discRow.style.display = 'none';
    }

    const vatExRow = document.getElementById('receiptVatExRow');
    if (data.totalVatExempt > 0) {
        vatExRow.style.display = 'flex';
        document.getElementById('receiptVatEx').textContent = '-\u20b1' + data.totalVatExempt.toFixed(2);
    } else {
        vatExRow.style.display = 'none';
    }

    const tenderedRow = document.getElementById('receiptTenderedRow');
    const changeRow   = document.getElementById('receiptChangeRow');
    if (data.method === 'Cash') {
        tenderedRow.style.display = 'flex';
        changeRow.style.display   = 'flex';
        document.getElementById('receiptTendered').textContent = '\u20b1' + data.tendered.toFixed(2);
        document.getElementById('receiptChange').textContent   = '\u20b1' + data.change.toFixed(2);
    } else {
        tenderedRow.style.display = 'none';
        changeRow.style.display   = 'none';
    }

    document.getElementById('weposReceiptModal').style.display = 'flex';
}

function weposCloseReceipt() {
    document.getElementById('weposReceiptModal').style.display = 'none';
    // Clear cart and reset totals instead of reloading
    weposCart = {};
    weposVerified = false;
    weposCustomerType = null;
    weposCustomerName = null;
    weposCustomerId = null;
    weposUpdateCart();
    // Refresh inventory from server
    weposRefreshInventory();
    // Focus on search for next transaction
    setTimeout(() => document.getElementById('weposSearch')?.focus(), 100);
}

// ═════ REFRESH INVENTORY ═════
function weposRefreshInventory() {
    fetch('../function/workingpos.php?action=getProducts', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(products => {
        if (!products || !Array.isArray(products)) return;
        
        // Update each product card with new inventory
        products.forEach(newProduct => {
            const card = document.querySelector(`.wepos-product-card[data-id="${newProduct.id}"]`);
            if (!card) return;
            
            const stock = parseInt(newProduct.stock || 0);
            const isExpired = newProduct.earliest_expiry_date && new Date(newProduct.earliest_expiry_date) < new Date();
            
            // Update classes
            card.classList.toggle('out-of-stock', stock <= 0);
            card.classList.toggle('expired', isExpired);
            
            // Update stock badge
            const badge = card.querySelector('.wepos-stock-badge');
            if (badge) {
                if (isExpired) {
                    badge.textContent = 'EXPIRED';
                    badge.className = 'wepos-stock-badge expired';
                } else if (stock <= 0) {
                    badge.textContent = 'Out of Stock';
                    badge.className = 'wepos-stock-badge empty';
                } else {
                    badge.textContent = stock + ' in stock';
                    badge.className = 'wepos-stock-badge' + (stock <= 10 ? ' low' : '');
                }
            }
            
            // Update data attributes
            card.setAttribute('data-stock', stock);
            card.setAttribute('data-expired', isExpired ? '1' : '0');
        });
    })
    .catch(err => console.log('Inventory refresh:', err));
}

function weposPrintReceipt() {
    const content = document.getElementById('weposReceiptPrint').innerHTML;
    const win = window.open('', '_blank', 'width=320,height=600');
    win.document.write(`
        <html><head><title>Receipt</title>
        <style>
            body { font-family: 'Courier New', monospace; font-size: 13px; width: 280px; margin: 0 auto; padding: 10px; }
            @media print { body { margin: 0; } }
        </style></head>
        <body>${content}</body></html>
    `);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); win.close(); }, 300);
}
// ═════ SENIOR / PWD DISCOUNT INTERCEPT ═════
function weposOnDiscountChange(selectEl) {
    const selOpt = selectEl.options[selectEl.selectedIndex];
    const discountName = selOpt.text.toLowerCase();

    const isSenior = discountName.includes('senior');
    const isPwd    = discountName.includes('pwd');

    // Reset verification whenever discount changes
    weposVerified = false;
    weposCustomerType = null;
    weposCustomerName = null;
    weposCustomerId = null;

    if (isSenior || isPwd) {
        // Save previous index so we can restore on cancel
        pendingDiscountIndex = selectEl.selectedIndex;

        const type = isSenior ? 'senior' : 'pwd';
        document.getElementById('verifyIdTitle').innerHTML =
            isSenior ? '<i class="fas fa-id-card"></i> Senior Citizen Verification' : '<i class="fas fa-id-card"></i> PWD Verification';
        document.getElementById('verifyIdName').value = '';
        document.getElementById('verifyIdNumber').value = '';
        document.getElementById('verifyIdError').style.display = 'none';
        document.getElementById('verifyIdNewMsg').style.display = 'none';
        document.getElementById('verifyIdFootInitial').style.display = 'flex';
        document.getElementById('verifyIdFootManual').style.display = 'none';
        document.getElementById('verifyIdBtn').disabled = false;
        document.getElementById('verifyIdBtn').innerHTML = 'Verify';
        document.getElementById('verifyIdModal').setAttribute('data-type', type);
        document.getElementById('verifyIdModal').setAttribute('data-discount-index', selectEl.selectedIndex);
        document.getElementById('verifyIdModal').style.display = 'flex';
        setTimeout(() => document.getElementById('verifyIdName')?.focus(), 100);
    } else {
        weposUpdateCart();
    }
}

function weposCancelVerifyId() {
    // Revert discount dropdown to None (index 0)
    const sel = document.getElementById('weposDiscount');
    if (sel) sel.selectedIndex = 0;
    document.getElementById('verifyIdModal').style.display = 'none';
    weposUpdateCart();
}

async function weposSubmitVerifyId() {
    const name      = document.getElementById('verifyIdName').value.trim();
    const id_number = document.getElementById('verifyIdNumber').value.trim();
    const type      = document.getElementById('verifyIdModal').getAttribute('data-type');
    const errEl     = document.getElementById('verifyIdError');
    const btn       = document.getElementById('verifyIdBtn');

    errEl.style.display = 'none';
    document.getElementById('verifyIdNewMsg').style.display = 'none';

    if (!name)      { errEl.textContent = 'Please enter the customer name.';  errEl.style.display = 'block'; return; }
    if (!id_number) { errEl.textContent = 'Please enter the ID number.'; errEl.style.display = 'block'; return; }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

    try {
        const res = await fetch('../function/verify_customer_id', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type, name, id_number })
        });
        const result = await res.json();

        if (result.error) {
            errEl.textContent = result.error;
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = 'Verify';
            return;
        }
        if (!result.exists) {
            // New customer — show warning message and switch to manual footer
            document.getElementById('verifyIdNewMsg').style.display = 'block';

            // Switch to manual footer
            document.getElementById('verifyIdFootInitial').style.display = 'none';
            document.getElementById('verifyIdFootManual').style.display = 'flex';
        } else {
            // Exists — apply discount and close modal
            weposVerified = true;
            weposCustomerType = type; // Store the customer type ('senior' or 'pwd')
            weposCustomerName = name; // Store the customer name
            weposCustomerId = result.customer_id; // Store the customer ID
            document.getElementById('verifyIdModal').style.display = 'none';
            weposUpdateCart();
            
            btn.disabled = false;
            btn.innerHTML = 'Verify';
        }

    } catch (e) {
        errEl.textContent = 'Network error. Please try again.';
        errEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = 'Verify';
    }
}

function weposDeclineVerify() {
    weposCancelVerifyId();
}

function weposOpenVerificationSite() {
    const type = document.getElementById('verifyIdModal').getAttribute('data-type');
    const verifyUrl = type === 'senior'
        ? 'https://www.ncsc.gov.ph/registration-verification'
        : 'https://pwd.doh.gov.ph/tbl_pwd_id_verificationlist.php';
    window.open(verifyUrl, '_blank', 'width=900,height=600');
}

async function weposApproveVerify() {
    const name      = document.getElementById('verifyIdName').value.trim();
    const id_number = document.getElementById('verifyIdNumber').value.trim();
    const type      = document.getElementById('verifyIdModal').getAttribute('data-type');
    const errEl     = document.getElementById('verifyIdError');

    try {
        const res = await fetch('../function/save_customer_id', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type, name, id_number })
        });
        const result = await res.json();
        
        if (result.success) {
            weposVerified = true;
            weposCustomerType = type; // Store the customer type ('senior' or 'pwd')
            weposCustomerName = name; // Store the customer name
            weposCustomerId = result.customer_id; // Store the customer ID
            document.getElementById('verifyIdModal').style.display = 'none';
            weposUpdateCart();
        } else {
            errEl.textContent = result.error || 'Failed to save customer.';
            errEl.style.display = 'block';
        }
    } catch (e) {
        errEl.textContent = 'Network error. Please try again.';
        errEl.style.display = 'block';
    }
}

// ═════ VOID AUTH MODAL (CART ITEM REMOVE) ═════
function weposCancelVoidAuth() {
    pendingVoid = null;
    pendingClearCart = false;
    document.getElementById('voidAuthModal').style.display = 'none';
}

async function weposSubmitVoidAuth() {
    const pin    = document.getElementById('voidAuthPin').value.trim();
    const errEl  = document.getElementById('voidAuthError');
    const btn    = document.getElementById('voidAuthBtn');

    errEl.style.display = 'none';

    if (!/^\d{7}$/.test(pin)) {
        errEl.textContent = 'Please enter a valid 7-digit Void PIN.';
        errEl.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authorizing...';

    try {
        const res = await fetch('../function/verify_void_pin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ void_pin: pin })
        });
        const result = await res.json();

        if (!result.success) {
            errEl.textContent = result.error || 'Invalid Void PIN.';
            errEl.style.display = 'block';
            return;
        }

        // Authorized — remove the item or clear cart
        if (pendingClearCart) {
            // Clear all cart items
            for (let key in weposCart) {
                delete weposCart[key];
            }
            // Reset verification state
            weposVerified = false;
            weposCustomerType = null;
            weposCustomerName = null;
            weposCustomerId = null;
            // Reset UI elements
            const discountSel = document.getElementById('weposDiscount');
            if (discountSel) discountSel.selectedIndex = 0;
            const customerInput = document.getElementById('weposCustomer');
            if (customerInput) customerInput.value = '';
            pendingClearCart = false;
        } else if (pendingVoid && weposCart[pendingVoid]) {
            delete weposCart[pendingVoid];
            pendingVoid = null;
        }
        
        document.getElementById('voidAuthModal').style.display = 'none';
        weposUpdateCart();

    } catch (e) {
        errEl.textContent = 'Network error. Please try again.';
        errEl.style.display = 'block';
    } finally {
        // Always reset button so it doesn't get stuck
        btn.disabled = false;
        const buttonText = pendingClearCart ? '<i class="fas fa-trash-alt"></i> Clear Cart' : '<i class="fas fa-trash"></i> Confirm Remove';
        btn.innerHTML = buttonText;
    }
}
