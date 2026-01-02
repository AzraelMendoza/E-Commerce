document.addEventListener("DOMContentLoaded", function () {
    const cartForm = document.getElementById('cartForm');
    const checkboxes = document.querySelectorAll('.cart-checkbox');
    const totalDisplay = document.getElementById('estimatedTotal');
    const shippingFee = 100;

    // 1. UPDATE TOTAL CALCULATION
    function updateTotal() {
        let total = 0;
        let anyChecked = false;
        checkboxes.forEach(cb => {
            if (cb.checked) {
                anyChecked = true;
                const subtotalEl = cb.closest('.card-body').querySelector('.subtotal-amount');
                const subtotal = parseFloat(subtotalEl.textContent.replace('₱', '').replace(/,/g, ''));
                total += subtotal + shippingFee;
            }
        });
        totalDisplay.textContent = anyChecked ? '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2 }) : '₱0.00';
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));

    // 2. CHECKOUT VALIDATION
    if (cartForm) {
        cartForm.addEventListener('submit', function (e) {
            const checkedCount = document.querySelectorAll('.cart-checkbox:checked').length;
            if (checkedCount === 0) {
                e.preventDefault(); // Stop the form from submitting
                alert("⚠️ Please select at least one product to checkout.");
            }
        });
    }

    // 3. QUANTITY UPDATES
    document.addEventListener("click", function (e) {
        if (!e.target.classList.contains("qty-btn")) return;
        const cartId = e.target.dataset.id;
        const action = e.target.dataset.action;
        fetch("update_cart_qty.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `cart_id=${cartId}&action=${action}`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message || "Failed to update quantity");
            });
    });

    // 4. REMOVE ITEM
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const cartId = this.dataset.id;
            if (!confirm('Are you sure you want to remove this item?')) return;
            fetch('remove_cart_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `cart_id=${cartId}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.card').remove();
                        updateTotal();
                    } else {
                        alert(data.message || 'Failed to remove item.');
                    }
                });
        });
    });
});