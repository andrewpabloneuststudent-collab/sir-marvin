let weposCart = {};
let barcodeBuffer = '';
let barcodeTimer = null;
let currentPayMethod = 'Cash';

document.addEventListener('DOMContentLoaded', () => {
    weposSetupScanner();
    weposSetupSearch();
    weposSetupKeyboard();
    weposUpdateCart();
});

// ═════ BARCODE SCANNER ═════
function weposSetupScanner() {
    document.addEventListener('keypress', function(e) {
        const active = document.activeElement;
        const isSearch = active && active.id === 'weposSearch';
        const isModalInput = active && (active.id === 'weposTendered' || active.id === 'weposCustomer');

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
    const cards = document.querySelectorAll('.wepos-product-card:not(.out-of-stock)');
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
    const id = cardEl.dataset.id;
    const stock = parseInt(cardEl.dataset.stock) || 0;

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
        weposCart[id] = {
            id: id,
            name: cardEl.dataset.name,
            price: parseFloat(cardEl.dataset.price),
            net: parseFloat(cardEl.dataset.net || cardEl.dataset.price),
            qty: 1,
            discountable: cardEl.dataset.discountable === '1',
            vatable: cardEl.dataset.vatable === '1',
            stock: stock
        };
    }

    // Visual feedback
    cardEl.classList.add('flash-add');
    setTimeout(() => cardEl.classList.remove('flash-add'), 300);

    weposUpdateCart();
}

function weposUpdateQty(id, delta) {
    if (!weposCart[id]) return;
    weposCart[id].qty += delta;
    if (weposCart[id].qty <= 0) {
        delete weposCart[id];
    } else if (weposCart[id].qty > weposCart[id].stock) {
        weposCart[id].qty = weposCart[id].stock;
        alert('Maximum stock reached');
    }
    weposUpdateCart();
}

function weposRemoveItem(id) {
    delete weposCart[id];
    weposUpdateCart();
}

function weposClearCart() {
    if (Object.keys(weposCart).length === 0) return;
    if (confirm('Clear entire cart?')) {
        weposCart = {};
        document.getElementById('weposDiscount').selectedIndex = 0;
        document.getElementById('weposCustomer').value = '';
        weposUpdateCart();
    }
}

// ═════ MATH & DISCOUNT CALCULATION ═════
function weposUpdateCart() {
    const tbody = document.getElementById('weposCartBody');
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
        weposSetTotals(0, 0, 0, 0, 0);
        document.getElementById('weposPayBtn').disabled = true;
        return;
    }

    let html = '';
    let rawSubtotal = 0;

    entries.forEach(item => {
        const lineTotal = item.price * item.qty;
        rawSubtotal += lineTotal;
        html += `
            <tr class="wepos-cart-row">
                <td class="wepos-col-name">
                    <div class="wepos-cart-item-name" title="${item.name}">${item.name}</div>
                </td>
                <td class="wepos-col-price text-muted">₱${item.price.toFixed(2)}</td>
                <td class="wepos-col-qty">
                    <div class="wepos-qty-ctrl">
                        <button onclick="weposUpdateQty('${item.id}', -1)"><i class="fas fa-minus"></i></button>
                        <span>${item.qty}</span>
                        <button onclick="weposUpdateQty('${item.id}', 1)"><i class="fas fa-plus"></i></button>
                    </div>
                </td>
                <td class="wepos-col-total">₱${lineTotal.toFixed(2)}</td>
                <td class="wepos-col-action">
                    <button class="wepos-btn-icon text-danger" onclick="weposRemoveItem('${item.id}')"><i class="fas fa-times"></i></button>
                </td>
            </tr>`;
    });

    tbody.innerHTML = html;
    
    // --- DISCOUNT LOGIC (SENIOR / PWD / REGULAR) ---
    const discountSelect = document.getElementById('weposDiscount');
    const selOpt = discountSelect.options[discountSelect.selectedIndex];
    const dRate = parseFloat(selOpt.dataset.rate) || 0;
    const isVatExempt = selOpt.dataset.exempt === '1';

    let totalDiscount = 0;
    let totalVatExemption = 0;
    let finalVat = 0;
    let finalTotal = 0;

    entries.forEach(item => {
        let itemGross = item.price * item.qty;
        let itemNet = item.net * item.qty; // Assuming net is price without VAT
        
        let itemDiscount = 0;
        let itemVatExemption = 0;
        let itemVatToPay = 0;

        // If VAT Exempt (Senior/PWD)
        if (isVatExempt) {
            // Remove VAT for VATable items
            if (item.vatable) {
                // Philippine standard: Gross / 1.12 = Net
                let properNet = itemGross / 1.12;
                itemVatExemption = itemGross - properNet;
                itemGross = properNet; // Base price for discount is now the Net price
            }

            // Apply 20% discount ONLY if the item is discountable (e.g. medicine)
            if (item.discountable && dRate > 0) {
                itemDiscount = itemGross * dRate;
            }
            
            // Seniors don't pay VAT on eligible items
            itemVatToPay = 0; 
        } 
        // Regular Discount (e.g. Promo 10%)
        else {
            if (item.discountable && dRate > 0) {
                itemDiscount = itemGross * dRate;
            }
            // Calculate standard 12% VAT on the remaining amount if vatable
            if (item.vatable) {
                let discountedGross = itemGross - itemDiscount;
                itemVatToPay = discountedGross - (discountedGross / 1.12);
            }
        }

        totalDiscount += itemDiscount;
        totalVatExemption += itemVatExemption;
        finalVat += itemVatToPay;
    });

    finalTotal = rawSubtotal - totalVatExemption - totalDiscount;

    weposSetTotals(rawSubtotal, totalDiscount, dRate, totalVatExemption, finalVat, finalTotal);
    document.getElementById('weposPayBtn').disabled = false;
}

function weposSetTotals(sub, disc, dRate, vatExempt, vat, total) {
    document.getElementById('calcSub').textContent = '₱' + sub.toFixed(2);
    
    const rowDisc = document.getElementById('rowDiscount');
    if (disc > 0) {
        rowDisc.style.display = 'flex';
        document.getElementById('calcDiscountLabel').textContent = (dRate * 100).toFixed(0) + '%';
        document.getElementById('calcDiscount').textContent = '-₱' + disc.toFixed(2);
    } else {
        rowDisc.style.display = 'none';
    }

    const rowVatEx = document.getElementById('rowVatExempt');
    if (vatExempt > 0) {
        rowVatEx.style.display = 'flex';
        document.getElementById('calcVatExempt').textContent = '-₱' + vatExempt.toFixed(2);
    } else {
        rowVatEx.style.display = 'none';
    }

    document.getElementById('calcVat').textContent = '₱' + vat.toFixed(2);
    document.getElementById('calcTotal').textContent = '₱' + total.toFixed(2);
    document.getElementById('btnTotalAmount').textContent = '₱' + total.toFixed(2);
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
        }
    });
}

// ═════ PAYMENT MODAL ═════
function weposOpenPayModal() {
    if (Object.keys(weposCart).length === 0) return;
    const totalText = document.getElementById('btnTotalAmount').textContent.replace('₱', '');
    const total = parseFloat(totalText);
    
    document.getElementById('modalAmountDue').textContent = '₱' + total.toFixed(2);
    document.getElementById('weposTendered').value = '';
    document.getElementById('weposChangeBox').style.display = 'none';
    document.getElementById('modalConfirmBtn').disabled = true;

    weposGenerateQuickCash(total);
    document.getElementById('weposPayModal').style.display = 'flex';
    setTimeout(() => document.getElementById('weposTendered')?.focus(), 100);
}

function weposClosePayModal(e) {
    if (e && e.target !== e.currentTarget) return;
    document.getElementById('weposPayModal').style.display = 'none';
}

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
    
    container.innerHTML = amounts.map(a => `<button type="button" onclick="weposSetCash(${a})">₱${a.toFixed(0)}</button>`).join('');
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

    const items = Object.entries(weposCart).map(([id, item]) => ({
        id: parseInt(id), price: item.price, qty: item.qty
    }));

    try {
        const res = await fetch('../function/process_transaction.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                items: items,
                discount_id: document.getElementById('weposDiscount').value,
                customer_name: document.getElementById('weposCustomer').value || 'Walk-in'
            })
        });
        
        const result = await res.json();
        
        if (result.success) {
            alert('Transaction #' + String(result.transaction_id).padStart(6,'0') + ' completed successfully!');
            weposClosePayModal();
            location.reload(); // Quick way to refresh stock levels
        } else {
            alert('Payment Error: ' + result.error);
        }
    } catch (err) {
        alert('Network Error');
    }
    
    btn.disabled = false;
    btn.innerHTML = 'Confirm Payment';
}
