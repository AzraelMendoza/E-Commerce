/**
 * BUY.JS
 * This script now only handles the "Buy Now" redirection.
 * UI logic (Quantity +/- and Variant selection) is handled by prod.js.
 */
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput = document.getElementById('qty');
    const variantInput = document.getElementById('variant_id');
    const productInput = document.getElementById('product_id');

    // handleAction is called by the "Buy Now" button in your HTML
    window.handleAction = function (type) {
        if (type === 'buy') {
            const productId = productInput.value;
            const qty = parseInt(qtyInput.value) || 1;
            const variantId = variantInput.value;

            const params = new URLSearchParams({
                buy_now: 1,
                product_id: productId,
                quantity: qty
            });

            // Only add variant_id if it's a valid value
            if (variantId && variantId !== "undefined" && variantId !== "") {
                params.append('variant_id', variantId);
            }

            // Redirect to checkout
            window.location.href = 'checkout.php?' + params.toString();
        } else {
            // This part updates the hidden form quantity if needed
            const formQty = document.getElementById('formQty');
            if (formQty) formQty.value = qtyInput.value;
        }
    };
});