function getCsrfToken() {
    const field = document.querySelector('input[name="_csrf_token"]');
    return field ? field.value : '';
}

function showToast(message, type) {
    type = type || 'success';
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = {
        success: 'bi-check-circle-fill',
        danger: 'bi-exclamation-triangle-fill',
        warning: 'bi-exclamation-circle-fill',
        info: 'bi-info-circle-fill',
    };

    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-bg-' + type + ' border-0 show';
    toast.role = 'alert';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi ${icons[type] || icons.info} me-2"></i> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    container.appendChild(toast);

    setTimeout(function () {
        toast.classList.remove('show');
        setTimeout(function () { toast.remove(); }, 300);
    }, 3500);
}

function updateCartBadge(count) {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline';
    } else {
        badge.style.display = 'none';
    }
}

function formatCurrency(amount) {
    return amount.toLocaleString('vi-VN') + ' VND';
}

document.addEventListener('DOMContentLoaded', function () {
    // Voucher AJAX on checkout
    var voucherForm = document.getElementById('voucher-form');
    if (voucherForm) {
        voucherForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var input = document.getElementById('voucher-input');
            var code = input ? input.value.trim() : '';
            var btn = voucherForm.querySelector('button[type="submit"]');
            var errorEl = document.getElementById('voucher-error');
            var successEl = document.getElementById('voucher-success');
            var discountRow = document.getElementById('discount-row');
            var discountEl = document.getElementById('discount-amount');
            var totalEl = document.getElementById('checkout-total');

            var formData = new FormData();
            formData.append('_csrf_token', getCsrfToken());
            formData.append('voucher', code);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            if (errorEl) errorEl.style.display = 'none';
            if (successEl) successEl.style.display = 'none';

            fetch('/checkout/validate-voucher', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;
                btn.innerHTML = 'Apply';

                if (data.success && data.code) {
                    var hiddenVoucher = document.getElementById('hidden-voucher');
                    if (hiddenVoucher) hiddenVoucher.value = data.code;
                    if (successEl) {
                        successEl.textContent = data.message;
                        successEl.style.display = 'block';
                    }
                    if (errorEl) errorEl.style.display = 'none';
                    if (discountRow && data.discount > 0) {
                        discountRow.style.display = 'flex';
                        if (discountEl) discountEl.textContent = '- ' + formatCurrency(data.discount);
                    } else if (discountRow) {
                        discountRow.style.display = 'none';
                    }
                    if (totalEl) totalEl.textContent = formatCurrency(data.total);
                } else if (!data.success) {
                    var hiddenVoucher = document.getElementById('hidden-voucher');
                    if (hiddenVoucher) hiddenVoucher.value = '';
                    if (errorEl) {
                        errorEl.textContent = data.message;
                        errorEl.style.display = 'block';
                    }
                    if (successEl) successEl.style.display = 'none';
                    if (discountRow) discountRow.style.display = 'none';
                    if (totalEl) totalEl.textContent = formatCurrency(data.subtotal || 0);
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = 'Apply';
                showToast('Network error.', 'danger');
            });
        });
    }
});
