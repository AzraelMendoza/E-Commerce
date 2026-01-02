// js/prod.js

if (typeof window.prodLoaded === 'undefined') {
    window.prodLoaded = true;

    // --- 1. HANDLE TYPING DIRECTLY (Manual Input) ---
    document.addEventListener("input", function(e) {
        if (e.target.id === "qty") {
            const qtyInput = e.target;
            const hiddenQtyInput = document.getElementById("formQty");
            const stockCount = document.getElementById("stockCount");
            
            if (!stockCount) return;

            let val = parseInt(qtyInput.value);
            let maxStock = parseInt(stockCount.textContent) || 0;

            if (isNaN(val) || val < 1) {
                val = 1; 
            } else if (val > maxStock) {
                val = maxStock; 
            }

            qtyInput.value = val;
            if (hiddenQtyInput) hiddenQtyInput.value = val;
        }
    });

    // --- 2. HANDLE CLICKS (Buttons, Variants, Gallery, Add to Cart) ---
    document.addEventListener("click", function(e) {
        
        // A. IMAGE GALLERY (Thumbnails)
        const thumbLabel = e.target.closest("label");
        if (thumbLabel) {
            const radio = thumbLabel.querySelector('input[name="imgSelect"]');
            const mainImg = document.getElementById("mainImage");
            if (radio && mainImg) {
                mainImg.src = radio.value;
            }
        }

        // B. QUANTITY BUTTONS (+ / -)
        const plusBtn = e.target.closest("#plus");
        const minusBtn = e.target.closest("#minus");

        if (plusBtn || minusBtn) {
            e.preventDefault();
            const qtyInput = document.getElementById("qty");
            const hiddenQtyInput = document.getElementById("formQty");
            const stockCount = document.getElementById("stockCount");
            
            if (!qtyInput || !stockCount) return;

            let currentQty = parseInt(qtyInput.value) || 1;
            let maxStock = parseInt(stockCount.textContent) || 0;

            if (plusBtn && currentQty < maxStock) {
                qtyInput.value = currentQty + 1;
            } 
            else if (minusBtn && currentQty > 1) {
                qtyInput.value = currentQty - 1;
            }
            if (hiddenQtyInput) hiddenQtyInput.value = qtyInput.value;
        }

        // C. VARIANT BUTTONS
        const optBtn = e.target.closest(".option-btn");
        if (optBtn) {
            e.preventDefault();
            document.querySelectorAll(".option-btn").forEach(b => b.classList.remove("active"));
            optBtn.classList.add("active");

            const price = optBtn.dataset.price;
            const variantId = optBtn.dataset.variantId;
            const newStock = parseInt(optBtn.dataset.stock) || 0;

            if (document.getElementById("price")) document.getElementById("price").textContent = `₱${price}`;
            if (document.getElementById("variant_id")) document.getElementById("variant_id").value = variantId;
            if (document.getElementById("stockCount")) document.getElementById("stockCount").textContent = newStock;

            const qtyInput = document.getElementById("qty");
            const hiddenQtyInput = document.getElementById("formQty");
            const addBtn = document.querySelector(".btn-add");
            const buyBtn = document.querySelector(".btn-buy");
            
            let currentQty = parseInt(qtyInput.value) || 1;

            // Logic to disable/enable Action Buttons based on stock
            if (newStock <= 0) {
                qtyInput.value = 0;
                if (addBtn) {
                    addBtn.disabled = true;
                    addBtn.style.background = "gray";
                    addBtn.style.cursor = "not-allowed";
                }
                if (buyBtn) {
                    buyBtn.disabled = true;
                    buyBtn.style.background = "#ccc";
                    buyBtn.style.cursor = "not-allowed";
                }
            } else {
                // If switching back to an in-stock variant
                if (currentQty <= 0) qtyInput.value = 1;
                if (currentQty > newStock) qtyInput.value = newStock;
                
                if (addBtn) {
                    addBtn.disabled = false;
                    addBtn.style.background = ""; // Resets to CSS file styling
                    addBtn.style.cursor = "pointer";
                }
                if (buyBtn) {
                    buyBtn.disabled = false;
                    buyBtn.style.background = ""; // Resets to CSS file styling
                    buyBtn.style.cursor = "pointer";
                }
            }
            
            if (hiddenQtyInput) hiddenQtyInput.value = qtyInput.value;
        }

        // D. ADD TO CART
        const addBtn = e.target.closest(".btn-add");
        if (addBtn && !addBtn.disabled) {
            e.preventDefault();
            
            const product_id = document.getElementById("product_id").value;
            const variant_id = document.getElementById("variant_id").value;
            const quantity = document.getElementById("qty").value;

            const params = new URLSearchParams({ product_id, variant_id, quantity });

            fetch("add_to_cart.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: params.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const popup = document.getElementById("cartPopup");
                    if (popup) {
                        popup.classList.add("show");
                        // Auto-remove show class after 3s
                        setTimeout(() => popup.classList.remove("show"), 3000);
                    }
                } else {
                    console.error("Cart error:", data.message);
                }
            })
            .catch(err => console.error("Fetch error:", err));
        }
    });
}