function getCsrfToken() {
    const field = document.querySelector('input[name="_csrf_token"]');
    return field ? field.value : '';
}

function showToast(message, type) {
    type = type || 'success';
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = {
        success: '&#10003;',
        danger: '&#9888;',
        warning: '&#9888;',
        info: '&#8505;',
    };

    const toast = document.createElement('div');
    toast.style.cssText =
        'padding:14px 20px;border-radius:16px;font-weight:600;font-size:14px;' +
        'animation:toastIn 0.3s ease;display:flex;align-items:center;gap:10px;' +
        (type === 'success' ? 'background:#002a16;color:#b4ffd8;border:1px solid rgba(255,255,255,0.12);' :
        type === 'danger' ? 'background:#2a0000;color:#ffb4b4;border:1px solid rgba(255,255,255,0.12);' :
        type === 'warning' ? 'background:#2a2a00;color:#ffe484;border:1px solid rgba(255,255,255,0.12);' :
        'background:#002a3a;color:#b4e0ff;border:1px solid rgba(255,255,255,0.12);');
    toast.innerHTML = (icons[type] || '') + ' ' + message;
    container.appendChild(toast);

    setTimeout(function () {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
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

// Scroll-reveal animation
const revealElements = document.querySelectorAll(
    ".page-section, .vps-card, .blog-card, .package-card, .auth-card, .cart-item, .cart-summary, .detail-card, .admin-card, .admin-table, .chart-card, .history-card, .checkout-form, .checkout-summary, .glass-panel",
);
const observer = new IntersectionObserver(
    (entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("show");
            }
        });
    },
    { threshold: 0.12 },
);
revealElements.forEach((el) => {
    el.classList.add("reveal");
    observer.observe(el);
});

// 3D tilt on cards
const tiltSelectors = [
    ".vps-card", ".blog-card", ".package-card", ".admin-card",
    ".cart-item", ".detail-card", ".history-card",
];
const tiltElements = document.querySelectorAll(tiltSelectors.join(", "));
const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
if (!prefersReducedMotion && tiltElements.length) {
    tiltElements.forEach((el) => {
        el.style.transformStyle = "preserve-3d";
        el.addEventListener("mousemove", (e) => {
            const rect = el.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width;
            const py = (e.clientY - rect.top) / rect.height;
            const rotateY = (px - 0.5) * 16;
            const rotateX = (0.5 - py) * 12;
            el.style.setProperty("--rx", `${rotateX}deg`);
            el.style.setProperty("--ry", `${rotateY}deg`);
        });
        el.addEventListener("mouseleave", () => {
            el.style.setProperty("--rx", `0deg`);
            el.style.setProperty("--ry", `0deg`);
        });
    });
}

// Toast animation keyframes
const styleSheet = document.createElement("style");
styleSheet.textContent = `
    @keyframes toastIn { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
`;
document.head.appendChild(styleSheet);

document.addEventListener('DOMContentLoaded', function () {
    // Initialize cart badge count on page load
    fetch('/cart/count', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (typeof data.count === 'number') {
            updateCartBadge(data.count);
        }
    })
    .catch(function () {});

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
            var discountCodeEl = document.getElementById('discount-code');

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

                var hiddenVoucher = document.getElementById('hidden-voucher');

                if (data.success && data.code) {
                    if (hiddenVoucher) hiddenVoucher.value = data.code;
                    if (successEl) {
                        successEl.textContent = data.message;
                        successEl.style.display = 'block';
                    }
                    if (errorEl) errorEl.style.display = 'none';
                    if (discountRow && data.discount > 0) {
                        discountRow.style.display = 'flex';
                        if (discountEl) discountEl.textContent = '- ' + formatCurrency(data.discount);
                        if (discountCodeEl) discountCodeEl.textContent = data.code;
                    } else if (discountRow) {
                        discountRow.style.display = 'none';
                    }
                    if (totalEl) totalEl.textContent = formatCurrency(data.total);
                } else if (!data.success) {
                    if (hiddenVoucher) hiddenVoucher.value = '';
                    if (errorEl) {
                        errorEl.textContent = data.message;
                        errorEl.style.display = 'block';
                    }
                    if (successEl) successEl.style.display = 'none';
                    if (discountRow) discountRow.style.display = 'none';
                    if (totalEl) totalEl.textContent = formatCurrency(data.subtotal || 0);
                } else {
                    if (hiddenVoucher) hiddenVoucher.value = '';
                    if (errorEl) errorEl.style.display = 'none';
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
