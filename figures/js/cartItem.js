document.addEventListener('DOMContentLoaded', function() {
        const shippingFee = 100;
        const cartForm = document.getElementById('cartForm');

        // Validation for Checkout
        if(cartForm) {
            cartForm.addEventListener('submit', function(e) {
                const checkedItems = document.querySelectorAll('.cart-checkbox:checked');
                if (checkedItems.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one item to checkout.');
                }
            });
        }

        function updateTotals() {
            let total = 0;
            let itemsSelected = false;

            document.querySelectorAll('.cart-item-row').forEach(row => {
                const checkbox = row.querySelector('.cart-checkbox');
                const price = parseFloat(row.dataset.price);
                const qtyInput = row.querySelector('.qty-input');
                const subtotalDisplay = row.querySelector('.subtotal-display');

                const subtotal = price * parseInt(qtyInput.value);
                subtotalDisplay.textContent = `₱${subtotal.toLocaleString('en-PH', {minimumFractionDigits:2})}`;

                if (checkbox.checked) {
                    total += subtotal;
                    itemsSelected = true;
                }
            });

            if (itemsSelected) total += shippingFee;
            document.getElementById('estimatedTotal').textContent = `₱${total.toLocaleString('en-PH', {minimumFractionDigits:2})}`;
        }

        // Qty and Delete logic
        document.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('.cart-item-row');
                const action = this.dataset.action;
                const input = row.querySelector('.qty-input');
                let currentQty = parseInt(input.value);

                if (action === 'minus' && currentQty <= 1) return;

                const formData = new FormData();
                formData.append('cart_id', row.dataset.cartId);
                formData.append('action', action);

                fetch('update_cart_qty.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            input.value = (action === 'plus') ? currentQty + 1 : currentQty - 1;
                            updateTotals();
                        }
                    });
            });
        });

        document.querySelectorAll('.delete-item').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!confirm('Remove this item?')) return;
                const row = this.closest('.cart-item-row');
                const formData = new FormData();
                formData.append('cart_id', row.dataset.cartId);

                fetch('remove_cart_item.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            row.remove();
                            updateTotals();
                            if (document.querySelectorAll('.cart-item-row').length === 0) location.reload();
                        }
                    });
            });
        });

        document.querySelectorAll('.cart-checkbox').forEach(cb => {
            cb.addEventListener('change', updateTotals);
        });

        updateTotals();
    });