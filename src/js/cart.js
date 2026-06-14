document.addEventListener('DOMContentLoaded', function () {

    // Add to cart
    document.querySelectorAll('.js-add-cart').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var btn = form.querySelector('.btn') || form.querySelector('button[type="submit"]');
            if (!btn) return;

            e.preventDefault();

            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Adding...';

            var formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    btn.innerHTML = 'Added!';
                    btn.disabled = false;
                    updateCartBadge(data.count);
                    showToast(data.message, 'success');
                } else {
                    form.submit();
                }
            })
            .catch(function () {
                form.submit();
            });
        });
    });

    // Remove from cart
    document.querySelectorAll('.js-remove-cart').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!confirm('Do you want to remove this package from your cart?')) return;

            var row = form.closest('tr');
            var formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(function () { row.remove(); updateCartTotals(); }, 300);
                    }
                    updateCartBadge(data.count);
                    showToast(data.message, 'warning');
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(function () {
                showToast('Network error.', 'danger');
            });
        });
    });

    // Quantity update
    document.querySelectorAll('.js-qty-minus, .js-qty-plus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('tr');
            var input = row.querySelector('.js-qty-input');
            var productId = btn.getAttribute('data-product-id');
            var current = parseInt(input.value, 10);
            var delta = btn.classList.contains('js-qty-plus') ? 1 : -1;
            var newQty = current + delta;

            if (newQty < 0) return;
            if (newQty === 0) {
                // Remove via the remove form in this row
                var removeForm = row.querySelector('.js-remove-cart');
                if (removeForm) {
                    removeForm.dispatchEvent(new Event('submit', { cancelable: true }));
                }
                return;
            }

            updateQuantity(productId, newQty, row);
        });
    });

    function updateQuantity(productId, qty, row) {
        var formData = new FormData();
        formData.append('_csrf_token', getCsrfToken());
        formData.append('product_id', productId);
        formData.append('quantity', qty);

        fetch('/cart/update', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                row.querySelector('.js-qty-input').value = data.quantity;
                var itemTotalCell = row.querySelector('.js-item-total');
                if (itemTotalCell) itemTotalCell.textContent = formatCurrency(data.item_total);
                updateCartBadge(data.count);
                updateCartTotals();
                showToast('Quantity updated.', 'info');
            } else {
                showToast(data.message || 'Update failed.', 'danger');
            }
        })
        .catch(function () {
            showToast('Network error.', 'danger');
        });
    }

    function updateCartTotals() {
        var rows = document.querySelectorAll('.js-cart-row');
        var total = 0;
        rows.forEach(function (r) {
            var totalEl = r.querySelector('.js-item-total');
            if (totalEl) {
                var val = totalEl.textContent.replace(/[^0-9]/g, '');
                total += parseInt(val, 10) || 0;
            }
        });

        var totalDisplay = document.getElementById('cart-total-amount');
        var subtotalDisplay = document.getElementById('cart-subtotal-amount');
        if (totalDisplay) totalDisplay.textContent = formatCurrency(total);
        if (subtotalDisplay) subtotalDisplay.textContent = formatCurrency(total);

        // Show empty state if cart is now empty
        var tbody = document.querySelector('.js-cart-body');
        var emptyState = document.getElementById('cart-empty-state');
        var cartContent = document.getElementById('cart-content');
        if (tbody && tbody.querySelectorAll('tr').length === 0) {
            if (cartContent) cartContent.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
        }
    }
});
