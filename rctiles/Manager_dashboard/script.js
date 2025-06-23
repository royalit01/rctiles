// <script>
     
//     //by divya
//     // function    updateSummary() {
//     //     const summaryBody = document.getElementById("summaryBody");
//     //     summaryBody.innerHTML = "";

//     //     let totalAmount = 0;
//     //     let allProductsSummary = {};

//     //     // ✅ Iterate through selected products stored in `selectedProductsData`
//     //     Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
//     //         let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
//     //         let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1; // ✅ Fetch multiplier (default 1)

//     //         [...section.wall, ...section.floor].forEach(product => {
//     //             let adjustedQuantity = product.quantity * multiplier; // ✅ Multiply quantity
//     //             let adjustedTotalPrice = product.totalPrice * multiplier; // ✅ Multiply total price

//     //             if (!allProductsSummary[product.id]) {
//     //                 allProductsSummary[product.id] = { 
//     //                     ...product, 
//     //                     quantity: 0, 
//     //                     totalPrice: 0 
//     //                 };
//     //             }

//     //             allProductsSummary[product.id].quantity += adjustedQuantity;
//     //             allProductsSummary[product.id].totalPrice += adjustedTotalPrice;
//     //         });
//     //     });

//     //     let allProducts = Object.values(allProductsSummary);
//     //     totalAmount = allProducts.reduce((sum, product) => sum + product.totalPrice, 0);

//     //     allProducts.forEach(product => {
//     //         let adjustedUnitPrice = product.totalPrice / product.quantity; // ✅ Corrected unit price

//     //         summaryBody.insertAdjacentHTML("beforeend", `
//     //             <tr>
//     //                 <td>${product.name}</td>
//     //                 <td>${product.quantity} pcs</td>
//     //                 <td>₹${adjustedUnitPrice.toFixed(2)}</td> 
//     //                 <td class="final-price" data-id="${product.id}" data-original-price="${product.totalPrice.toFixed(2)}">₹${product.totalPrice.toFixed(2)}</td>
//     //             </tr>
//     //         `);
//     //     });

//     //     document.getElementById("totalAmount").textContent = `₹${totalAmount.toFixed(2)}`;
//     //     applyFinalPrice(); // ✅ Apply discount if applicable

//     //     console.log("🔄 Summary updated with multipliers applied.");
//     // }

//     //by agrima
//     function updateSummary() {
//         const summaryBody = document.getElementById("summaryBody");
//         summaryBody.innerHTML = "";

//         let totalAmount = 0;
//         let allProductsSummary = {};

//         // Iterate through selected products
//         Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
//             let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
//             let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1;

//             [...section.wall, ...section.floor].forEach(product => {
//                 let adjustedQuantity = product.quantity * multiplier;
//                 let adjustedTotalPrice = product.totalPrice * multiplier;

//                 if (!allProductsSummary[product.id]) {
//                     allProductsSummary[product.id] = { 
//                         ...product, 
//                         quantity: adjustedQuantity,
//                         totalPrice: adjustedTotalPrice,
//                         unitPrice: product.unitPrice,
//                         originalUnitPrice: product.unitPrice // Store original unit price
//                     };
//                 } else {
//                     allProductsSummary[product.id].quantity += adjustedQuantity;
//                     allProductsSummary[product.id].totalPrice += adjustedTotalPrice;
//                 }
//             });
//         });

//         let allProducts = Object.values(allProductsSummary);
//         totalAmount = allProducts.reduce((sum, product) => sum + product.totalPrice, 0);

//         allProducts.forEach(product => {
//             const finalPrice = product.quantity * product.unitPrice;

//             summaryBody.insertAdjacentHTML("beforeend", `
//                 <tr>
//                     <td>${product.name}</td>
//                     <td>${product.quantity} pcs</td>
//                     <td>
//                         <input type="number" class="form-control original-price-input" 
//                             value="${product.unitPrice.toFixed(2)}" step="0.01" min="0"
//                             data-id="${product.id}" 
//                             data-quantity="${product.quantity}"
//                             onchange="updateProductPrice(this)">
//                     </td>
//                     <td class="final-price" data-id="${product.id}">
//                         ₹${finalPrice.toFixed(2)}
//                     </td>
//                 </tr>
//             `);
//         });

//         document.getElementById("totalAmount").textContent = `₹${totalAmount.toFixed(2)}`;
//         document.getElementById("finalAmountPaid").value = totalAmount.toFixed(2);
//         document.getElementById("final_price").value = totalAmount.toFixed(2);
//     }


//     //by agrima
//     function updateProductPrice(input) {
//         const productId = input.getAttribute("data-id");
//         const newUnitPrice = parseFloat(input.value) || 0;
//         const quantity = parseFloat(input.getAttribute("data-quantity")) || 0;
        
//         // Update the final price immediately
//         const row = input.closest('tr');
//         const finalPriceCell = row.querySelector('.final-price');
//         const newFinalPrice = quantity * newUnitPrice;
//         finalPriceCell.textContent = `₹${newFinalPrice.toFixed(2)}`;
        
//         // Update all instances of this product in selectedProductsData
//         Object.values(selectedProductsData).forEach(section => {
//             [...section.wall, ...section.floor].forEach(product => {
//                 if (product.id === productId) {
//                     product.unitPrice = newUnitPrice;
//                     product.totalPrice = product.quantity * newUnitPrice;
//                 }
//             });
//         });

//         // Update the total amount
//         updateTotalAmount();
        
//         // Reapply any existing discount
//         const finalAmount = document.getElementById("finalAmountPaid").value;
//         if (finalAmount && parseFloat(finalAmount) > 0) {
//             applyFinalPrice();
//         }
//     }


//     //by agrima
//     function updateTotalAmount() {
//         let total = 0;
//         document.querySelectorAll('.final-price').forEach(cell => {
//             total += parseFloat(cell.textContent.replace('₹', '')) || 0;
//         });
//         document.getElementById("totalAmount").textContent = `₹${total.toFixed(2)}`;
//         document.getElementById("finalAmountPaid").value = total.toFixed(2);
//         document.getElementById("final_price").value = total.toFixed(2);
//     }

//     //by divya
//     // function applyFinalPrice() {
//     //     let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
//     //     document.getElementById("final_price").value = finalAmount;
//     //     let totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;

//     //     if (finalAmount > totalAmount || finalAmount <= 0) {
//     //         console.warn("Invalid final amount. Discount not applied.");
//     //         return;
//     //     }

//     //     let discount = totalAmount - finalAmount;
//     //     let allProducts = document.querySelectorAll(".final-price");
//     //     let prices = Array.from(allProducts).map(row => parseFloat(row.getAttribute("data-original-price")) || 0);
//     //     let totalOriginalAmount = prices.reduce((sum, price) => sum + price, 0);

//     //     let remainingDiscount = discount;
//     //     let lastIndex = allProducts.length - 1;

//     //     allProducts.forEach((row, index) => {
//     //         let originalPrice = prices[index];
//     //         let discountShare = (originalPrice / totalOriginalAmount) * discount;

//     //         if (index === lastIndex) {
//     //             discountShare = remainingDiscount; // Adjust the last item's discount
//     //         }

//     //         let newPrice = Math.max(originalPrice - discountShare, 0);
//     //         row.textContent = `₹${newPrice.toFixed(2)}`;
//     //         row.setAttribute("data-final-price", newPrice.toFixed(2));
//     //         remainingDiscount -= discountShare;
//     //     });

//     //     console.log(`✅ Discount of ₹${discount.toFixed(2)} applied across products.`);
//     // }

//     //by agrima
//     function applyFinalPrice() {
//         let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
//         document.getElementById("final_price").value = finalAmount;
//         let totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;

//         if (finalAmount > totalAmount || finalAmount <= 0) {
//             console.warn("Invalid final amount. Discount not applied.");
//             return;
//         }

//         let discount = totalAmount - finalAmount;
//         let allProducts = document.querySelectorAll(".final-price");
//         let prices = Array.from(allProducts).map(row => parseFloat(row.textContent.replace('₹', '')) || 0;
//         let totalOriginalAmount = prices.reduce((sum, price) => sum + price, 0);

//         let remainingDiscount = discount;
//         let lastIndex = allProducts.length - 1;

//         allProducts.forEach((row, index) => {
//             let originalPrice = prices[index];
//             let discountShare = (originalPrice / totalOriginalAmount) * discount;

//             if (index === lastIndex) {
//                 discountShare = remainingDiscount;
//             }

//             let newPrice = Math.max(originalPrice - discountShare, 0);
//             row.textContent = `₹${newPrice.toFixed(2)}`;
//             remainingDiscount -= discountShare;
//         });
//     }

//     function updateMultiplication(input) {
//         const detailGroup = input.closest(".dimension-group");
//         let multiplier = parseInt(input.value) || 1;

//         if (multiplier < 1) {
//             alert("Multiplier must be at least 1.");
//             input.value = 1;
//             multiplier = 1;
//         }

//         let sectionId = detailGroup.getAttribute("data-section-id");
//         if (!selectedProductsData[sectionId]) {
//             console.warn("⚠️ No selected products found for this section.");
//             return;
//         }

//         let selectedProducts = selectedProductsData[sectionId][currentSelectionType];

//         if (selectedProducts.length > 0) {
//             selectedProducts.forEach(product => {
//                 if (!product.baseQuantity) product.baseQuantity = product.quantity;  // Store original quantity
//                 product.quantity = product.baseQuantity * multiplier;
//                 product.totalPrice = product.quantity * product.unitPrice;
//             });

//             const selectedProductsContainer = currentSelectionType === 'wall' 
//                 ? detailGroup.querySelector('.selected-products-wall') 
//                 : detailGroup.querySelector('.selected-products-floor');

//             updateSelectedProductsUI(selectedProductsContainer, selectedProducts);
//         }

//         console.log(`🔄 Updated product quantities with multiplier (${multiplier}x)`);
//     }
    
//      // ✅ Store selected products globally
//      // Store the current detail group for selection
//         let selectedProductsData = {}; // Store selected products per detail group
//         let selectedProducts = []; // Store selected products
//         let selectedProductsWall = [];
//         let selectedProductsFloor = [];
//         let currentSelectionType = ''; // 'wall' or 'floor'

//     function enableEditSelection(button) {
//         console.log("🛠 Enabling Edit Mode...");

//         const detailGroup = button.closest(".dimension-group");

//         if (!detailGroup) {
//             console.error("❌ Error: No active detail group found!");
//             return;
//         }

//         let sectionId = detailGroup.getAttribute("data-section-id"); // ✅ Get section ID
//         let isWallSelection = button.previousElementSibling.textContent.includes("Wall");
//         currentSelectionType = isWallSelection ? 'wall' : 'floor';

//         if (!selectedProductsData[sectionId] || !selectedProductsData[sectionId][currentSelectionType]) {
//             alert(`⚠️ No ${currentSelectionType} tiles selected for editing. Please select some tiles first.`);
//             return;
//         }

//         let selectedProducts = selectedProductsData[sectionId][currentSelectionType]; // ✅ Fetch correctly

//         const categorySelect = isWallSelection
//             ? detailGroup.querySelector('.wall-category-select')
//             : detailGroup.querySelector('.floor-category-select');

//         const selectedCategory = categorySelect.value;
//         const totalArea = isWallSelection
//             ? parseFloat(detailGroup.querySelector('.wall-area').textContent) || 0
//             : parseFloat(detailGroup.querySelector('.floor-area').textContent) || 0;

//         if (!selectedCategory) {
//             alert(`Please select a ${currentSelectionType} category before editing.`);
//             return;
//         }

//         if (totalArea <= 0) {
//             alert(`Total ${currentSelectionType} area must be greater than 0 to choose a product.`);
//             return;
//         }

//         document.getElementById("selectedCategoryName").textContent = categorySelect.options[categorySelect.selectedIndex].text;
//         document.getElementById("selectedTotalArea").textContent = totalArea.toFixed(2) + " m²";

//         fetchProducts(selectedCategory, totalArea, selectedProducts); // ✅ Fetch products for editing

//         const productModal = new bootstrap.Modal(document.getElementById('productModal'));
//         productModal.show();
//     }

//     function saveEditedSelection(button) {
//         console.log("✅ Saving Edited Selection...");

//         let updatedSelectedList = [];
//         document.querySelectorAll(".product-checkbox:checked").forEach(checkbox => {
//             let row = checkbox.closest("tr");
//             let productId = parseInt(checkbox.dataset.productId);
//             let productName = row.cells[1].textContent;
//             let unitPrice = parseFloat(row.cells[3].textContent) || 0;
//             let quantity = parseInt(row.querySelector(".product-quantity").value) || 1;
//             let totalPrice = unitPrice * quantity;

//             updatedSelectedList.push({
//                 id: productId,
//                 name: productName,
//                 unitPrice: unitPrice,
//                 quantity: quantity,
//                 totalPrice: totalPrice
//             });
//         });

//         console.log("🔹 Updated Selected Products:", updatedSelectedList);

//         if (currentSelectionType === 'wall') {
//             selectedProductsWall = updatedSelectedList;
//         } else {
//             selectedProductsFloor = updatedSelectedList;
//         }

//         // Change button text back to Edit Selection
//         button.textContent = "Edit Selection";
//         button.classList.remove("btn-success");
//         button.classList.add("btn-warning");
//         button.setAttribute("onclick", "enableEditSelection(this)");

//         // Disable inputs again
//         document.querySelectorAll(".product-checkbox, .product-quantity").forEach(element => {
//             element.disabled = true;
//         });

//         console.log("✅ Edit Mode Disabled. Selections Saved.");
//     }

//     document.getElementById("orderForm").addEventListener("submit", function(event) {
//         let form = this;

//         // ✅ Remove any previously added hidden inputs
//         document.querySelectorAll(".hidden-product-input").forEach(input => input.remove());

//         let productsArray = [];

//         Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
//             let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
//             let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1; // ✅ Fetch multiplier (default 1)

//             [...section.wall, ...section.floor].forEach(product => {
//                 productsArray.push({
//                     id: product.id,
//                     name: product.name,
//                     quantity: product.quantity, // ✅ Keep base quantity
//                     unitPrice: product.unitPrice,
//                     totalPrice: product.totalPrice,
//                     multiplier: multiplier // ✅ Ensure multiplier is included
//                 });
//             });
//         });

//         // ✅ Debugging: Check if multiplier is included before submitting
//         console.log("📌 Products being sent with multiplier:", productsArray);

//         // ✅ Add a single hidden field with all products JSON
//         let input = document.createElement("input");
//         input.type = "hidden";
//         input.className = "hidden-product-input";
//         input.name = "products";
//         input.value = JSON.stringify(productsArray);
//         form.appendChild(input);

//         console.log("🚀 Form Submitted with Products:", productsArray);
//     });

// </script>