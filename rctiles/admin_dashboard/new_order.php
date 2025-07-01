<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
include '../db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>New Order</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- jQuery (Required for Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

   function nextStep() {
    let currentStep = document.querySelector('.form-step.active-step');
    if (!currentStep) return console.warn("⚠️ No active step found!");

    // Step 1: Customer Details validation
    if (currentStep.id === 'step1') {
        let isValid = true;
        const requiredInputs = currentStep.querySelectorAll('input[required]');
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
            // Special validation for phone number
            if (input.name === 'phone_no' && input.value.length !== 10) {
                input.classList.add('is-invalid');
                isValid = false;
            }
        });
        if (!isValid) {
            alert("Please fill all customer details correctly before proceeding.");
            return;
        }
        // Move to next step if valid
        let nextStep = currentStep.nextElementSibling;
        if (nextStep && nextStep.classList.contains('form-step')) {
            currentStep.classList.remove('active-step');
            nextStep.classList.add('active-step');
        }
        return;
    }

    // Step 2: Order Details validation
    if (currentStep.id === 'step2') {
        let allValid = true;
        document.querySelectorAll('.dimension-group').forEach(group => {
            // --- Wall area fields ---
            const wallLength = parseFloat(group.querySelector('input[name="wall_lengths[]"]').value) || 0;
            const wallWidth = parseFloat(group.querySelector('input[name="wall_widths[]"]').value) || 0;
            const wallHeight = parseFloat(group.querySelector('input[name="wall_heights[]"]').value) || 0;
            const wallCategory = group.querySelector('.wall-category-select').value;

            // --- Floor area fields ---
            const floorLength = parseFloat(group.querySelector('input[name="floor_lengths[]"]').value) || 0;
            const floorWidth = parseFloat(group.querySelector('input[name="floor_widths[]"]').value) || 0;
            const floorCategory = group.querySelector('.floor-category-select').value;

            // --- Check if wall area is filled properly ---
            const wallFilled = (wallLength > 0 && wallWidth > 0 && wallHeight > 0 && wallCategory);

            // --- Check if floor area is filled properly ---
            const floorFilled = (floorLength > 0 && floorWidth > 0 && floorCategory);

            // --- At least one (wall or floor) must be filled properly ---
            if (!wallFilled && !floorFilled) {
                allValid = false;
                // Highlight missing fields (optional)
                if (!wallFilled) {
                    group.querySelectorAll('input[name="wall_lengths[]"], input[name="wall_widths[]"], input[name="wall_heights[]"]').forEach(inp => inp.classList.add('is-invalid'));
                    group.querySelector('.wall-category-select').classList.add('is-invalid');
                }
                if (!floorFilled) {
                    group.querySelectorAll('input[name="floor_lengths[]"], input[name="floor_widths[]"]').forEach(inp => inp.classList.add('is-invalid'));
                    group.querySelector('.floor-category-select').classList.add('is-invalid');
                }
            } else {
                // Remove highlights if valid
                group.querySelectorAll('input, select').forEach(inp => inp.classList.remove('is-invalid'));
            }
        });

        if (!allValid) {
            alert("(Items must have complete wall OR floor specifications with their category.");
            return;
        }

        // Move to next step if valid
        let nextStep = currentStep.nextElementSibling;
        if (nextStep && nextStep.classList.contains('form-step')) {
            currentStep.classList.remove('active-step');
            nextStep.classList.add('active-step');
            if (nextStep.id === 'step3') updateSummary();
        }
    }
}

    function prevStep() {
        let currentStep = document.querySelector('.form-step.active-step');
        if (!currentStep) return console.warn("⚠️ No active step found!");

        let prevStep = currentStep.previousElementSibling;
        if (prevStep && prevStep.classList.contains('form-step')) {
            currentStep.classList.remove('active-step');
            prevStep.classList.add('active-step');
        }
    }

    function addDetail() {
        const container = document.getElementById('orderDetailsContainer');
        const detailIndex = document.querySelectorAll('.dimension-group').length;

        const detailDiv = document.createElement('div');
        detailDiv.className = 'dimension-group';
        detailDiv.setAttribute("data-section-id", detailIndex); // Assign unique ID to track selections

        detailDiv.innerHTML = `
            <h6>Detail ${detailIndex + 1}</h6>
            
            
            <div class="mb-2">
                <label class="form-label">Title (e.g., Washroom 1, Kitchen, etc.) </label>
                <input type="text" class="form-control" name="titles[]" placeholder="Enter location name" required>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Wall Length (m) </label>
                    <input type="number" class="form-control" name="wall_lengths[]" step="0.1" min="0" oninput="calculateAreas(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Wall Width (m) </label>
                    <input type="number" class="form-control" name="wall_widths[]" step="0.1" min="0" oninput="calculateAreas(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Wall Height (m) </label>
                    <input type="number" class="form-control" name="wall_heights[]" step="0.1" min="0" oninput="calculateAreas(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Door Area (m²) </label>
                    <input type="number" class="form-control" name="door_areas[]" step="0.1" min="0" oninput="calculateAreas(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Window Area (m²) </label>
                    <input type="number" class="form-control" name="window_areas[]" step="0.1" min="0" oninput="calculateAreas(this)">
                </div>
            
            </div>
        

            <p class="mt-2"><strong>Wall Area:</strong> <span class="wall-area">0.00 m²</span></p>

            <div class="mb-2">
                <label class="form-label">Select Wall Category</label>
                <select class="form-control wall-category-select" name="wall_category_ids[]" required>
                    <option value="">Select Wall Category</option>
                </select>
            </div>

            <button type="button" class="btn btn-primary mt-2" onclick="openProductModal(this, 'wall')">Choose Wall Tile</button>
            <!-- <button type="button" class="btn btn-warning mt-2" onclick="enableEditSelection(this)">Edit Selection</button> -->

            <!-- ✅ Separate div for wall tile selection -->
            <div class="selected-products-wall mt-2 text-muted"><p>No wall tiles selected yet.</p></div>

            <div class="row mt-2">
                <div class="col-md-4">
                    <label class="form-label">Floor Length (m)</label>
                    <input type="number" class="form-control" name="floor_lengths[]" step="0.1" min="0" oninput="calculateAreas(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Floor Width (m)</label>
                    <input type="number" class="form-control" name="floor_widths[]" step="0.1" min="0" oninput="calculateAreas(this)">
                </div>
            </div>

            <div class="row mt-2">
                

                
            

            
                <div class="mb-2">
                    <label class="form-label">Select Floor Category</label>
                    <select class="form-control floor-category-select" name="floor_category_ids[]" >
                        <option value="">Select Floor Category</option>
                    </select>
                </div>
            </div>

            <p class="mt-2"><strong>Floor Area:</strong> <span class="floor-area">0.00 m²</span></p>

            <button type="button" class="btn btn-primary mt-2" onclick="openProductModal(this, 'floor')">Choose Floor Tile</button>
            <!-- <button type="button" class="btn btn-warning mt-2" onclick="enableEditSelection(this)">Edit Selection</button> -->

            <!-- ✅ Separate div for floor tile selection -->
            <div class="selected-products-floor mt-2 text-muted"><p>No floor tiles selected yet.</p></div>

            <div class="mb-2">
                <label class="form-label">Number of Copies</label>
                <input type="number" class="form-control multiply-order" name="multipliers[]" min="1" value="1" oninput="updateMultiplication(this)">
            </div>

            <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeDetail(this)">Remove</button>
        `;

        // ✅ Store selected products in each section separately
        detailDiv.selectedProductsWall = [];
        detailDiv.selectedProductsFloor = [];

        container.appendChild(detailDiv);

        // ✅ Fetch categories separately for each new section
        fetchCategories(detailDiv.querySelector('.wall-category-select'), 'wall');
        fetchCategories(detailDiv.querySelector('.floor-category-select'), 'floor');
    }

    function fetchCategories(selectElement) {
        console.log("Fetching categories..."); // ✅ Check if function is called

        fetch('fetch_category.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("Fetched Categories:", data); // ✅ Log the fetched categories

                selectElement.innerHTML = '<option value="">Select Category</option>'; // Reset options

                data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.category_id;
                    option.textContent = category.category_name;
                    selectElement.appendChild(option);
                });

                console.log("Updated category dropdown:", selectElement.innerHTML); // ✅ Debug dropdown update
            })
            .catch(error => console.error('Error fetching categories:', error));
    }

    function calculateAreas(input) {
        const detailGroup = input.closest('.dimension-group');

        const wallLength = parseFloat(detailGroup.querySelector('input[name="wall_lengths[]"]').value) || 0;
        const wallWidth = parseFloat(detailGroup.querySelector('input[name="wall_widths[]"]').value) || 0;
        const wallHeight = parseFloat(detailGroup.querySelector('input[name="wall_heights[]"]').value) || 0;
        const windowArea = parseFloat(detailGroup.querySelector('input[name="window_areas[]"]').value) || 0;
        
        const floorLength = parseFloat(detailGroup.querySelector('input[name="floor_lengths[]"]').value) || 0;
        const floorWidth = parseFloat(detailGroup.querySelector('input[name="floor_widths[]"]').value) || 0;
        const doorArea = parseFloat(detailGroup.querySelector('input[name="door_areas[]"]').value) || 0;
        
        // const totalWallArea = Math.max(0, (2 * wallHeight * (wallLength + wallWidth)) - windowArea);
        const totalWallArea = Math.max(0, (2 * wallHeight * (wallLength + wallWidth)) - windowArea - doorArea);
        const totalFloorArea = Math.max(0, (floorLength * floorWidth) - doorArea);
        
        detailGroup.querySelector('.wall-area').textContent = totalWallArea.toFixed(2) + " m²";
        detailGroup.querySelector('.floor-area').textContent = totalFloorArea.toFixed(2) + " m²";
    }

    function openProductModal(button, type) {
        const detailGroup = button.closest('.dimension-group');

        // Remove "active" class from all dimension-groups and set the active one
        document.querySelectorAll('.dimension-group').forEach(el => el.classList.remove('active'));
        detailGroup.classList.add('active');

        currentSelectionType = type;

        let categorySelect = type === 'wall' 
            ? detailGroup.querySelector('.wall-category-select') 
            : detailGroup.querySelector('.floor-category-select');

        if (!categorySelect) {
            console.error(`❌ Missing ${type} category select element`);
            return;
        }

        const selectedCategory = categorySelect.value;
        const totalArea = type === 'wall' 
            ? parseFloat(detailGroup.querySelector('.wall-area').textContent) || 0 
            : parseFloat(detailGroup.querySelector('.floor-area').textContent) || 0;

        if (!selectedCategory) {
            alert(`Please select a ${type} category before choosing a product.`);
            return;
        }

        if (totalArea <= 0) {
            alert(`Total ${type} area must be greater than 0 to choose a product.`);
            return;
        }

        document.getElementById("selectedCategoryName").textContent = categorySelect.options[categorySelect.selectedIndex].text;
        document.getElementById("selectedTotalArea").textContent = totalArea.toFixed(2) + " m²";

        // ✅ Reset selected products for new sections
        let sectionId = detailGroup.getAttribute("data-section-id");
        if (!selectedProductsData[sectionId]) {
            selectedProductsData[sectionId] = { wall: [], floor: [] };
        }

        // ✅ Load previously selected products OR reset if new section
        let preSelectedProducts = selectedProductsData[sectionId][type] || [];
        fetchProducts(selectedCategory, totalArea, preSelectedProducts);

        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        productModal.show();
    }

    function fetchProducts(categoryId, totalArea, preSelectedProducts = []) {
        fetch(`fetch_products.php?category_id=${categoryId}&total_area=${totalArea}`)
            .then(response => response.json())
            .then(data => {
                console.log("📦 Fetched Products:", data);

                const tbody = document.querySelector("#productListContainer tbody");
                tbody.innerHTML = ''; // Clear previous products

                if (!data.products || data.products.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">No products available</td></tr>';
                    return;
                }

                data.products.forEach(product => {
                    let productImage = product.image && product.image !== "null"
                        ? product.image
                        : "../assets/img/default.jpg";

                    let isSelected = preSelectedProducts.some(p => p.id === product.id);
                    let preSelectedProduct = preSelectedProducts.find(p => p.id === product.id);
                    let quantity = isSelected ? preSelectedProduct.quantity : 1;

                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td><img src="${productImage}" alt="Product Image" width="50" onerror="this.src='../assets/img/default_img.jpg';"></td>
                        <td>${product.name}</td>
                        <td>${product.description}</td>
                        <td>${product.area_per_unit !== "N/A" ? product.area_per_unit : "Not Specified"} m²</td>
                        <td>
                            <input type="number" class="form-control product-quantity" min="1" 
                                value="${quantity}" data-product-id="${product.id}" 
                                data-unit-price="${product.price !== 'N/A' ? parseFloat(product.price):0}">
                        </td>
                        <td>
                            <input type="checkbox" class="product-checkbox" data-product-id="${product.id}"
                            onchange="toggleProductSelection(this, ${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price !== 'N/A' ? parseFloat(product.price) : 0})">
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => console.error('❌ Error fetching products:', error));
    }

    function toggleProductSelection(checkbox, productId, productName, unitPrice) {
        let quantityInput = checkbox.closest("tr").querySelector(".product-quantity");
        let quantity = parseInt(quantityInput.value) || 1;
        let totalPrice = quantity * unitPrice;

        let selectedList = currentSelectionType === 'wall' ? selectedProductsWall : selectedProductsFloor;
        let existingProductIndex = selectedList.findIndex(p => p.id === productId);

        if (checkbox.checked) {
            if (existingProductIndex === -1) {
                selectedList.push({
                    id: productId,
                    name: productName,
                    unitPrice: unitPrice,
                    quantity: quantity,
                    totalPrice: totalPrice,
                    customPrice: unitPrice
                });
            } else {
                selectedList[existingProductIndex].quantity = quantity;
                selectedList[existingProductIndex].totalPrice = totalPrice;
            }
        } else {
            if (existingProductIndex !== -1) {
                selectedList.splice(existingProductIndex, 1);
            }
        }

        console.log(`✅ Selected ${currentSelectionType} Products:`, selectedList);
    }

    function saveSelectedProducts() {
        const modal = document.getElementById("productModal");
        const detailGroup = document.querySelector(".dimension-group.active");

        if (!detailGroup) {
            console.error("❌ Error: No active detail group found!");
            return;
        }

        let selectedProductsContainer = currentSelectionType === 'wall'
            ? detailGroup.querySelector('.selected-products-wall')
            : detailGroup.querySelector('.selected-products-floor');

        let selectedList = currentSelectionType === 'wall' ? selectedProductsWall : selectedProductsFloor;

        if (selectedList.length === 0) {
            alert("⚠️ No products selected! Please select at least one product.");
            return;
        }

        let sectionId = detailGroup.getAttribute("data-section-id");
        if (!selectedProductsData[sectionId]) {
            selectedProductsData[sectionId] = { wall: [], floor: [] };
        }

        if (currentSelectionType === 'wall') {
            selectedProductsData[sectionId].wall = [...selectedList];
        } else {
            selectedProductsData[sectionId].floor = [...selectedList];
        }

        updateSelectedProductsUI(selectedProductsContainer, selectedList);

        // ✅ Reset global product selections to prevent UI carry-over to other sections
        selectedProductsWall = [];
        selectedProductsFloor = [];

        const productModalInstance = bootstrap.Modal.getInstance(modal);
        productModalInstance.hide();
    }
    
    function updateSelectedProductsUI(container, selectedList) {
        container.innerHTML = ""; // Clear previous content

        if (selectedList.length === 0) {
            container.innerHTML = `<p class="text-muted">No products selected yet.</p>`;
            return;
        }

        selectedList.forEach(product => {
            let productRow = document.createElement("div");
            productRow.classList.add("selected-product-item", "border", "p-2", "rounded", "mb-2", "d-flex", "justify-content-between");

            productRow.innerHTML = `
                <span><strong>${product.name}</strong> - ${product.quantity} pcs @ ${product.unitPrice.toFixed(2)} per unit</span>
                <span class="badge bg-success">Total: ${product.totalPrice.toFixed(2)}</span>
            `;
            container.appendChild(productRow);
        });

        console.log("✅ UI Updated with selected products:", selectedList);
    }

    function updateMultiplication(input) {
        const detailGroup = input.closest(".dimension-group");
        let multiplier = parseInt(input.value) || 1;

        if (multiplier < 1) {
            alert("Multiplier must be at least 1.");
            input.value = 1;
            multiplier = 1;
        }

        let selectedProducts = currentSelectionType === 'wall' ? selectedProductsWall : selectedProductsFloor;

        if (selectedProducts.length > 0) {
            selectedProducts.forEach(product => {
                product.quantity = product.baseQuantity * multiplier;
                product.totalPrice = product.quantity * product.unitPrice;
            });

            const selectedProductsContainer = currentSelectionType === 'wall' 
                ? detailGroup.querySelector('.selected-products-wall') 
                : detailGroup.querySelector('.selected-products-floor');

            updateSelectedProductsUI(selectedProductsContainer, selectedProducts);
        }

        console.log(`🔄 Updated product quantities with multiplier (${multiplier}x)`);
    }

     //by divya
    // function    updateSummary() {
    //     const summaryBody = document.getElementById("summaryBody");
    //     summaryBody.innerHTML = "";

    //     let totalAmount = 0;
    //     let allProductsSummary = {};

    //     // ✅ Iterate through selected products stored in `selectedProductsData`
    //     Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
    //         let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
    //         let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1; // ✅ Fetch multiplier (default 1)

    //         [...section.wall, ...section.floor].forEach(product => {
    //             let adjustedQuantity = product.quantity * multiplier; // ✅ Multiply quantity
    //             let adjustedTotalPrice = product.totalPrice * multiplier; // ✅ Multiply total price

    //             if (!allProductsSummary[product.id]) {
    //                 allProductsSummary[product.id] = { 
    //                     ...product, 
    //                     quantity: 0, 
    //                     totalPrice: 0 
    //                 };
    //             }

    //             allProductsSummary[product.id].quantity += adjustedQuantity;
    //             allProductsSummary[product.id].totalPrice += adjustedTotalPrice;
    //         });
    //     });

    //     let allProducts = Object.values(allProductsSummary);
    //     totalAmount = allProducts.reduce((sum, product) => sum + product.totalPrice, 0);

    //     allProducts.forEach(product => {
    //         let adjustedUnitPrice = product.totalPrice / product.quantity; // ✅ Corrected unit price

    //         summaryBody.insertAdjacentHTML("beforeend", `
    //             <tr>
    //                 <td>${product.name}</td>
    //                 <td>${product.quantity} pcs</td>
    //                 <td>₹${adjustedUnitPrice.toFixed(2)}</td> 
    //                 <td class="final-price" data-id="${product.id}" data-original-price="${product.totalPrice.toFixed(2)}">₹${product.totalPrice.toFixed(2)}</td>
    //             </tr>
    //         `);
    //     });

    //     document.getElementById("totalAmount").textContent = `₹${totalAmount.toFixed(2)}`;
    //     applyFinalPrice(); // ✅ Apply discount if applicable

    //     console.log("🔄 Summary updated with multipliers applied.");
    // }

    //by agrima
    // function updateSummary() {
    //     const summaryBody = document.getElementById("summaryBody");
    //     summaryBody.innerHTML = "";

    //     let totalAmount = 0;
    //     let allProductsSummary = {};

    //     // Iterate through selected products
    //     Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
    //         let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
    //         let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1;

    //         [...section.wall, ...section.floor].forEach(product => {
    //             let adjustedQuantity = product.quantity * multiplier;
    //             let adjustedTotalPrice = product.totalPrice * multiplier;

    //             if (!allProductsSummary[product.id]) {
    //                 allProductsSummary[product.id] = { 
    //                     ...product, 
    //                     quantity: adjustedQuantity,
    //                     totalPrice: adjustedTotalPrice,
    //                     unitPrice: product.unitPrice,
    //                     originalUnitPrice: product.unitPrice // Store original unit price
    //                 };
    //             } else {
    //                 allProductsSummary[product.id].quantity += adjustedQuantity;
    //                 allProductsSummary[product.id].totalPrice += adjustedTotalPrice;
    //             }
    //         });
    //     });

    //     let allProducts = Object.values(allProductsSummary);
    //     totalAmount = allProducts.reduce((sum, product) => sum + product.totalPrice, 0);

    //     allProducts.forEach(product => {
    //         const finalPrice = product.quantity * product.unitPrice;

    //         summaryBody.insertAdjacentHTML("beforeend", `
    //             <tr>
    //                 <td>${product.name}</td>
    //                 <td>${product.quantity} pcs</td>
    //                 <td>
    //                     <input type="number" class="form-control original-price-input" 
    //                         value="${product.unitPrice.toFixed(2)}" step="0.01" min="0"
    //                         data-id="${product.id}" 
    //                         data-quantity="${product.quantity}"
    //                         onchange="updateProductPrice(this)">
    //                 </td>
    //                 <td class="final-price" data-id="${product.id}">
    //                     ₹${finalPrice.toFixed(2)}
    //                 </td>
    //             </tr>
    //         `);
    //     });

    //     document.getElementById("totalAmount").textContent = `₹${totalAmount.toFixed(2)}`;
    //     document.getElementById("finalAmountPaid").value = totalAmount.toFixed(2);
    //     document.getElementById("final_price").value = totalAmount.toFixed(2);
    // }

    //by agrima deepseek start
    // Modified updateSummary function to properly track original and current prices
    
    // function updateSummary() {
    //     const summaryBody = document.getElementById("summaryBody");
    //     summaryBody.innerHTML = "";

    //     let totalAmount = 0;
    //     let allProductsSummary = {};

    //     // Iterate through selected products
    //     Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
    //         let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
    //         let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1;

    //         [...section.wall, ...section.floor].forEach(product => {
    //             let adjustedQuantity = product.quantity * multiplier;
    //             let adjustedTotalPrice = product.totalPrice * multiplier;

    //             if (!allProductsSummary[product.id]) {
    //                 allProductsSummary[product.id] = { 
    //                     ...product, 
    //                     quantity: adjustedQuantity,
    //                     totalPrice: adjustedTotalPrice,
    //                     unitPrice: product.unitPrice,
    //                     originalUnitPrice: product.unitPrice,
    //                     originalTotalPrice: adjustedTotalPrice,
    //                     currentTotalPrice: adjustedTotalPrice // Track current price (after edits)
    //                 };
    //             } else {
    //                 allProductsSummary[product.id].quantity += adjustedQuantity;
    //                 allProductsSummary[product.id].totalPrice += adjustedTotalPrice;
    //                 allProductsSummary[product.id].originalTotalPrice += adjustedTotalPrice;
    //                 allProductsSummary[product.id].currentTotalPrice += adjustedTotalPrice;
    //             }
    //         });
    //     });

    //     let allProducts = Object.values(allProductsSummary);
    //     totalAmount = allProducts.reduce((sum, product) => sum + product.currentTotalPrice, 0);

    //     allProducts.forEach(product => {
    //         summaryBody.insertAdjacentHTML("beforeend", `
    //             <tr>
    //                 <td>${product.name}</td>
    //                 <td>${product.quantity} pcs</td>
    //                 <td>
    //                     <input type="number" class="form-control original-price-input" 
    //                         value="${product.unitPrice.toFixed(2)}" step="0.01" min="0"
    //                         data-id="${product.id}" 
    //                         data-quantity="${product.quantity}"
    //                         onchange="updateProductPrice(this)">
    //                 </td>
    //                 <td class="final-price" data-id="${product.id}" 
    //                     data-original-price="${product.originalTotalPrice.toFixed(2)}"
    //                     data-current-price="${product.currentTotalPrice.toFixed(2)}">
    //                     ₹${product.currentTotalPrice.toFixed(2)}
    //                 </td>
    //             </tr>
    //         `);
    //     });

    //     document.getElementById("totalAmount").textContent = `₹${totalAmount.toFixed(2)}`;
    //     document.getElementById("finalAmountPaid").value = totalAmount.toFixed(2);
    //     document.getElementById("final_price").value = totalAmount.toFixed(2);
    // }

    // function updateProductPrice(input) {
    //     const productId = input.getAttribute("data-id");
    //     const newUnitPrice = parseFloat(input.value) || 0;
    //     const quantity = parseFloat(input.getAttribute("data-quantity")) || 0;
    //     const newTotalPrice = quantity * newUnitPrice;
        
    //     // Find the row and final price cell
    //     const row = input.closest('tr');
    //     const finalPriceCell = row.querySelector('.final-price');
        
    //     // Get the current discount ratio if it exists
    //     const originalPrice = parseFloat(finalPriceCell.getAttribute("data-original-price")) || newTotalPrice;
    //     const currentFinalPrice = parseFloat(finalPriceCell.textContent.replace('₹', '')) || newTotalPrice;
    //     const discountRatio = (originalPrice - currentFinalPrice) / originalPrice;
        
    //     // Calculate new final price maintaining the same discount ratio
    //     const newFinalPrice = newTotalPrice * (1 - discountRatio);
        
    //     // Update the display and data attributes
    //     finalPriceCell.textContent = `₹${newFinalPrice.toFixed(2)}`;
    //     finalPriceCell.setAttribute("data-original-price", newTotalPrice.toFixed(2));
    //     finalPriceCell.setAttribute("data-current-price", newFinalPrice.toFixed(2));
        
    //     // Update all instances of this product in selectedProductsData
    //     Object.values(selectedProductsData).forEach(section => {
    //         [...section.wall, ...section.floor].forEach(product => {
    //             if (product.id === productId) {
    //                 product.unitPrice = newUnitPrice;
    //                 product.totalPrice = newTotalPrice;
    //                 product.currentTotalPrice = newFinalPrice;
    //                 product.originalTotalPrice = newTotalPrice;
    //             }
    //         });
    //     });

    //     // Update the total amount
    //     updateTotalAmount();
        
    //     // Update the final amount paid to maintain the same overall discount
    //     const totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;
    //     const currentFinalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || totalAmount;
    //     const overallDiscountRatio = (totalAmount - currentFinalAmount) / totalAmount;
    //     const newFinalAmount = totalAmount * (1 - overallDiscountRatio);
        
    //     document.getElementById("finalAmountPaid").value = newFinalAmount.toFixed(2);
    //     document.getElementById("final_price").value = newFinalAmount.toFixed(2);
    // }

    // function updateTotalAmount() {
    //     let total = 0;
    //     document.querySelectorAll('.final-price').forEach(cell => {
    //         total += parseFloat(cell.getAttribute("data-original-price")) || 
    //                 parseFloat(cell.textContent.replace('₹', '')) || 0;
    //     });
        
    //     document.getElementById("totalAmount").textContent = `₹${total.toFixed(2)}`;
    // }

    // function applyFinalPrice() {
    //     let finalAmountInput = document.getElementById("finalAmountPaid");
    //     let finalAmount = parseFloat(finalAmountInput.value);
        
    //     if (isNaN(finalAmount)) {
    //         return;
    //     }

    //     document.getElementById("final_price").value = finalAmount;
    //     let totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;

    //     if (finalAmount > totalAmount || finalAmount <= 0) {
    //         console.warn("Invalid final amount. Discount not applied.");
    //         return;
    //     }

    //     let discount = totalAmount - finalAmount;
    //     let allProducts = document.querySelectorAll(".final-price");
        
    //     // Calculate total original amount from data attributes
    //     let totalOriginalAmount = 0;
    //     allProducts.forEach(row => {
    //         totalOriginalAmount += parseFloat(row.getAttribute("data-original-price")) || 0;
    //     });

    //     if (allProducts.length === 0 || totalOriginalAmount <= 0) {
    //         return;
    //     }

    //     let remainingDiscount = discount;
    //     let lastIndex = allProducts.length - 1;

    //     allProducts.forEach((row, index) => {
    //         let originalPrice = parseFloat(row.getAttribute("data-original-price")) || 0;
    //         let discountShare = (originalPrice / totalOriginalAmount) * discount;

    //         if (index === lastIndex) {
    //             discountShare = remainingDiscount;
    //         }

    //         let newPrice = Math.max(originalPrice - discountShare, 0);
    //         row.textContent = `₹${newPrice.toFixed(2)}`;
    //         row.setAttribute("data-current-price", newPrice.toFixed(2));
    //         remainingDiscount -= discountShare;
    //     });
    // }

    //by agrima deepseek end

    //by agrima same.new start
// ... existing code ...
let selectedProductsData = {}; // Store selected products per detail group
let selectedProducts = []; // Store selected products
let selectedProductsWall = [];
let selectedProductsFloor = [];
let currentSelectionType = ''; // 'wall' or 'floor'

function updateProductPrice(input) {
    const productId = input.getAttribute("data-id");
    const newUnitPrice = parseFloat(input.value) || 0;
    const quantity = parseFloat(input.getAttribute("data-quantity")) || 0;
    const newTotalPrice = quantity * newUnitPrice;

    // Update the final price display
    const row = input.closest('tr');
    const finalPriceCell = row.querySelector('.final-price');

    // Update both the text content and the data attributes
    finalPriceCell.textContent = `₹${newTotalPrice.toFixed(2)}`;
    finalPriceCell.setAttribute("data-original-price", newTotalPrice.toFixed(2));
    finalPriceCell.setAttribute("data-current-price", newTotalPrice.toFixed(2));

    // Update all instances of this product in selectedProductsData
    Object.values(selectedProductsData).forEach(section => {
        [...section.wall, ...section.floor].forEach(product => {
            if (product.id === productId) {
                product.unitPrice = newUnitPrice;
                product.totalPrice = quantity * newUnitPrice;
                product.currentTotalPrice = product.totalPrice;
                product.originalTotalPrice = product.totalPrice;
            }
        });
    });

    // Update the total amount
    updateTotalAmount();

    // Check if there's a final amount set (discount applied)
    const finalAmount = document.getElementById("finalAmountPaid").value;


    if (finalAmount && parseFloat(finalAmount) > 0 &&
        parseFloat(finalAmount) < parseFloat(document.getElementById("totalAmount").textContent.replace('₹', ''))) {
        // Re-apply the discount with the new prices
        applyFinalPrice();
    }
}

function updateTotalAmount() {
    let total = 0;
    document.querySelectorAll('.final-price').forEach(cell => {
        // Extract the price from the text content (removing the ₹ symbol)
        total += parseFloat(cell.getAttribute("data-current-price")) || 0;
    });
    document.getElementById("totalAmount").textContent = `₹${total.toFixed(2)}`;
    document.getElementById("finalAmountPaid").value = total.toFixed(2);
    document.getElementById("final_price").value = total.toFixed(2);
}

function applyFinalPrice() {
    let finalAmountInput = document.getElementById("finalAmountPaid");
    let finalAmount = parseFloat(finalAmountInput.value) || 0;

    document.getElementById("final_price").value = finalAmount;
    let totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;

    if (finalAmount > totalAmount || finalAmount <= 0) {
        console.warn("Invalid final amount. Discount not applied.");
        return;
    }

    let discount = totalAmount - finalAmount;
    let allProducts = document.querySelectorAll(".final-price");

    // Calculate total original amount from data attributes
    let totalOriginalAmount = 0;
    allProducts.forEach(row => {
        totalOriginalAmount += parseFloat(row.getAttribute("data-original-price")) || 0;
    });

    if (allProducts.length === 0 || totalOriginalAmount <= 0) {
        return;
    }

    let remainingDiscount = discount;
    let lastIndex = allProducts.length - 1;

    allProducts.forEach((row, index) => {
        let originalPrice = parseFloat(row.getAttribute("data-original-price")) || 0;
        let discountShare = (originalPrice / totalOriginalAmount) * discount;

        if (index === lastIndex) {
            discountShare = remainingDiscount;
        }

        let newPrice = Math.max(originalPrice - discountShare, 0);
        row.textContent = `₹${newPrice.toFixed(2)}`;
        row.setAttribute("data-current-price", newPrice.toFixed(2));

        // Update product data in the data structure if needed
        const productId = row.getAttribute("data-id");
        if (productId) {
            Object.values(selectedProductsData).forEach(section => {
                [...section.wall, ...section.floor].forEach(product => {
                    if (product.id == productId) {
                        product.currentTotalPrice = newPrice;
                    }
                });
            });
        }

        remainingDiscount -= discountShare;
    });

    // Don't update the finalAmountPaid input, but do update the hidden field
    document.getElementById("final_price").value = finalAmount;
    updateGrandAmount();
}

function updateSummary() {
    const summaryBody = document.getElementById("summaryBody");
    summaryBody.innerHTML = "";

    let totalAmount = 0;
    let allProductsSummary = {};

    // Iterate through selected products
    Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
        let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
        let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1;

        [...section.wall, ...section.floor].forEach(product => {
            let adjustedQuantity = product.quantity * multiplier;
            let adjustedTotalPrice = product.totalPrice * multiplier;

            if (!allProductsSummary[product.id]) {
                allProductsSummary[product.id] = {
                    ...product,
                    quantity: adjustedQuantity,
                    totalPrice: adjustedTotalPrice,
                    unitPrice: product.unitPrice,
                    originalUnitPrice: product.unitPrice,
                    originalTotalPrice: adjustedTotalPrice,
                    currentTotalPrice: adjustedTotalPrice // Initialize current price same as original
                };
            } else {
                allProductsSummary[product.id].quantity += adjustedQuantity;
                allProductsSummary[product.id].totalPrice += adjustedTotalPrice;
                allProductsSummary[product.id].originalTotalPrice += adjustedTotalPrice;
                allProductsSummary[product.id].currentTotalPrice += adjustedTotalPrice;
            }
        });
    });

    let allProducts = Object.values(allProductsSummary);
    totalAmount = allProducts.reduce((sum, product) => sum + product.currentTotalPrice, 0);

    console.log("🔄 Summary Data:", allProducts);
    console.log("🔄 Total Amount:");

    allProducts.forEach(product => {
        summaryBody.insertAdjacentHTML("beforeend", `
            <tr>
                <td>${product.name}</td>
                <td>${product.quantity} pcs</td>
                <td>
                    <input type="number" class="form-control original-price-input"
                        value="${product.unitPrice.toFixed(2)}" step="0.01" min="0"
                        data-id="${product.id}"
                        data-quantity="${product.quantity}"
                        onchange="updateProductPrice(this)">
                </td>
                <td class="final-price" data-id="${product.id}"
                    data-original-price="${product.originalTotalPrice.toFixed(2)}"
                    data-current-price="${product.currentTotalPrice.toFixed(2)}">
                    ₹${product.currentTotalPrice.toFixed(2)}
                </td>
            </tr>
        `);
    });

    document.getElementById("totalAmount").textContent = `₹${totalAmount.toFixed(2)}`;
    document.getElementById("finalAmountPaid").value = totalAmount.toFixed(2);
    document.getElementById("final_price").value = totalAmount.toFixed(2);
    updateGrandAmount();   
}

function removeDetail(button) {
    const detailGroup = button.closest('.dimension-group');
    if (detailGroup) {
        // Get the section ID being removed
        const sectionId = detailGroup.getAttribute("data-section-id");

        // Remove the section data from our tracking object
        if (selectedProductsData[sectionId]) {
            delete selectedProductsData[sectionId];
        }

        // Remove the DOM element
        detailGroup.remove();

        // Renumber the remaining details
        document.querySelectorAll('.dimension-group').forEach((group, index) => {
            group.querySelector('h6').textContent = `Detail ${index + 1}`;
            group.setAttribute("data-section-id", index);
        });
    }
}
//by agrima same.new end

//by divya start
    function updateMultiplication(input) {
        const detailGroup = input.closest(".dimension-group");
        let multiplier = parseInt(input.value) || 1;

        if (multiplier < 1) {
            alert("Multiplier must be at least 1.");
            input.value = 1;
            multiplier = 1;
        }

        let sectionId = detailGroup.getAttribute("data-section-id");
        if (!selectedProductsData[sectionId]) {
            console.warn("⚠️ No selected products found for this section.");
            return;
        }

        let selectedProducts = selectedProductsData[sectionId][currentSelectionType];

        if (selectedProducts.length > 0) {
            selectedProducts.forEach(product => {
                if (!product.baseQuantity) product.baseQuantity = product.quantity;  // Store original quantity
                product.quantity = product.baseQuantity * multiplier;
                product.totalPrice = product.quantity * product.unitPrice;
            });

            const selectedProductsContainer = currentSelectionType === 'wall' 
                ? detailGroup.querySelector('.selected-products-wall') 
                : detailGroup.querySelector('.selected-products-floor');

            updateSelectedProductsUI(selectedProductsContainer, selectedProducts);
        }

        console.log(`🔄 Updated product quantities with multiplier (${multiplier}x)`);
    }
    
     
    function enableEditSelection(button) {
        console.log("🛠 Enabling Edit Mode...");

        const detailGroup = button.closest(".dimension-group");

        if (!detailGroup) {
            console.error("❌ Error: No active detail group found!");
            return;
        }

        let sectionId = detailGroup.getAttribute("data-section-id"); // ✅ Get section ID
        let isWallSelection = button.previousElementSibling.textContent.includes("Wall");
        currentSelectionType = isWallSelection ? 'wall' : 'floor';

        if (!selectedProductsData[sectionId] || !selectedProductsData[sectionId][currentSelectionType]) {
            alert(`⚠️ No ${currentSelectionType} tiles selected for editing. Please select some tiles first.`);
            return;
        }

        let selectedProducts = selectedProductsData[sectionId][currentSelectionType]; // ✅ Fetch correctly

        const categorySelect = isWallSelection
            ? detailGroup.querySelector('.wall-category-select')
            : detailGroup.querySelector('.floor-category-select');

        const selectedCategory = categorySelect.value;
        const totalArea = isWallSelection
            ? parseFloat(detailGroup.querySelector('.wall-area').textContent) || 0
            : parseFloat(detailGroup.querySelector('.floor-area').textContent) || 0;

        if (!selectedCategory) {
            alert(`Please select a ${currentSelectionType} category before editing.`);
            return;
        }

        if (totalArea <= 0) {
            alert(`Total ${currentSelectionType} area must be greater than 0 to choose a product.`);
            return;
        }

        document.getElementById("selectedCategoryName").textContent = categorySelect.options[categorySelect.selectedIndex].text;
        document.getElementById("selectedTotalArea").textContent = totalArea.toFixed(2) + " m²";

        fetchProducts(selectedCategory, totalArea, selectedProducts); // ✅ Fetch products for editing

        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        productModal.show();
    }

    function updateGrandAmount() {
    let finalAmount = parseFloat(document.getElementById('finalAmountPaid').value) || 0;
    let rentAmount = parseFloat(document.getElementById('RentAmount').value) || 0;
    let grandAmount = finalAmount + rentAmount;
    document.getElementById('grandAmountPaid').value = grandAmount.toFixed(2);
    }

    function saveEditedSelection(button) {
        console.log("✅ Saving Edited Selection...");

        let updatedSelectedList = [];
        document.querySelectorAll(".product-checkbox:checked").forEach(checkbox => {
            let row = checkbox.closest("tr");
            let productId = parseInt(checkbox.dataset.productId);
            let productName = row.cells[1].textContent;
            let unitPrice = parseFloat(row.cells[3].textContent) || 0;
            let quantity = parseInt(row.querySelector(".product-quantity").value) || 1;
            let totalPrice = unitPrice * quantity;

            updatedSelectedList.push({
                id: productId,
                name: productName,
                unitPrice: unitPrice,
                quantity: quantity,
                totalPrice: totalPrice
            });
        });

        console.log("🔹 Updated Selected Products:", updatedSelectedList);

        if (currentSelectionType === 'wall') {
            selectedProductsWall = updatedSelectedList;
        } else {
            selectedProductsFloor = updatedSelectedList;
        }

        // Change button text back to Edit Selection
        // button.textContent = "Edit Selection";
        button.classList.remove("btn-success");
        button.classList.add("btn-warning");
        button.setAttribute("onclick", "enableEditSelection(this)");

        // Disable inputs again
        document.querySelectorAll(".product-checkbox, .product-quantity").forEach(element => {
            element.disabled = true;
        });

        console.log("✅ Edit Mode Disabled. Selections Saved.");
    }
//by divya end

//by agrima same.new start
document.addEventListener("DOMContentLoaded", function() {
    addDetail(); // Add the first detail section automatically
});

// document.getElementById("orderForm").addEventListener("submit", function(event) {
//     let form = this;

//     // Validate final amount
//     let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
//     let totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;

//     if (finalAmount <= 0) {
//         alert("Final amount must be greater than 0");
//         event.preventDefault();
//         return;
//     }

//     // Check if we have any products selected
//     let hasProducts = false;
//     Object.values(selectedProductsData).forEach(section => {
//         if (section.wall.length > 0 || section.floor.length > 0) {
//             hasProducts = true;
//         }
//     });

//     if (!hasProducts) {
//         alert("No products selected. Please add at least one product.");
//         event.preventDefault();
//         return;
//     }

//     // Remove any previously added hidden inputs
//     document.querySelectorAll(".hidden-product-input").forEach(input => input.remove());

//     let productsArray = [];

//     // Create a map of product IDs to their final prices from the summary table
//     let productFinalPrices = {};
//     document.querySelectorAll('.final-price').forEach(cell => {
//         const productId = cell.getAttribute('data-id');
//         const finalPrice = parseFloat(cell.textContent.replace('₹', '')) || 0;
//         productFinalPrices[productId] = finalPrice;
//     });

//     Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
//         let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
//         let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1;

//         [...section.wall, ...section.floor].forEach(product => {
//             // Use the current price from the table if available, otherwise use product's total price
//             const finalPrice = productFinalPrices[product.id] !== undefined
//                 ? productFinalPrices[product.id]
//                 : product.totalPrice;

//             productsArray.push({
//                 id: product.id,
//                 name: product.name,
//                 quantity: product.quantity,
//                 unitPrice: product.unitPrice,
//                 totalPrice: finalPrice, // Use the final price after any discounts
//                 multiplier: multiplier
//             });
//         });
//     });

//     // Debugging: Log what's being sent
//     console.log("📌 Products being sent:", productsArray);
//     console.log("📌 Final amount being sent:", finalAmount);

//     if (productsArray.length === 0) {
//         alert("No products found to submit. Please add at least one product.");
//         event.preventDefault();
//         return;
//     }

//     // Add a single hidden field with all products JSON
//     let input = document.createElement("input");
//     input.type = "hidden";
//     input.className = "hidden-product-input";
//     input.name = "products";
//     input.value = JSON.stringify(productsArray);
//     form.appendChild(input);

//     // Make sure the final_price field is set correctly
//     document.getElementById("final_price").value = finalAmount;

//     console.log("Form submitted with products:", productsArray);
// });

document.addEventListener("DOMContentLoaded", () => {

/* -------------------------------------------------
   ALL your existing code that starts with
   document.getElementById("orderForm").addEventListener…
   paste it here unchanged
-------------------------------------------------- */
    document.getElementById("orderForm").addEventListener("submit", function(event) {
        // Prevent default form submission
        event.preventDefault();
        
        console.log("Form submission initiated");

        // Validate final amount
        let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
        if (finalAmount <= 0) {
            alert("Final amount must be greater than 0");
            return;
        }

        // Check if we have any products selected
        let hasProducts = false;
        let productsArray = [];

        // Build products array from all detail sections
        Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
            let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
            let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1;

            // Process both wall and floor products
            ['wall', 'floor'].forEach(type => {
                if (section[type] && section[type].length > 0) {
                    hasProducts = true;
                    
                    section[type].forEach(product => {
                        // Find the corresponding final price from the summary table
                        let finalPrice = 0;
                        document.querySelectorAll('.final-price').forEach(cell => {
                            if (cell.getAttribute('data-id') == product.id) {
                                finalPrice = parseFloat(cell.textContent.replace('₹', '')) || product.totalPrice;
                            }
                        });

                        productsArray.push({
                            id: product.id,
                            name: product.name,
                            quantity: product.quantity,
                            unitPrice: product.unitPrice,
                            totalPrice: finalPrice,
                            multiplier: multiplier
                        });
                    });
                }
            });
        });

        if (!hasProducts) {
            alert("No products selected. Please add at least one product.");
            return;
        }

        // Debug output
        console.log("Products to submit:", productsArray);
        console.log("Final amount:", finalAmount);

        try {
            // Remove any previously added hidden inputs
            document.querySelectorAll(".hidden-product-input").forEach(input => input.remove());

            // Add products as a hidden input
            let productsInput = document.createElement("input");
            productsInput.type = "hidden";
            productsInput.name = "products";
            productsInput.className = "hidden-product-input";
            productsInput.value = JSON.stringify(productsArray);
            this.appendChild(productsInput);

            // Verify form data before submission
            const formData = new FormData(this);
            console.log("FormData contents:");
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            // Submit the form programmatically
            console.log("Submitting form...");
            this.submit();
            
        } catch (error) {
            console.error("Error during form submission:", error);
            alert("An error occurred while preparing the order data. Please try again.");
        }
    });

}); 


    //by agrima same.new end


    //by agrima
    // function updateProductPrice(input) {
    //     const productId = input.getAttribute("data-id");
    //     const newUnitPrice = parseFloat(input.value) || 0;
    //     const quantity = parseFloat(input.getAttribute("data-quantity")) || 0;
        
    //     // Update the final price immediately
    //     const row = input.closest('tr');
    //     const finalPriceCell = row.querySelector('.final-price');
    //     const newFinalPrice = quantity * newUnitPrice;
    //     finalPriceCell.textContent = `₹${newFinalPrice.toFixed(2)}`;
        
    //     // Update all instances of this product in selectedProductsData
    //     Object.values(selectedProductsData).forEach(section => {
    //         [...section.wall, ...section.floor].forEach(product => {
    //             if (product.id === productId) {
    //                 product.unitPrice = newUnitPrice;
    //                 product.totalPrice = product.quantity * newUnitPrice;
    //             }
    //         });
    //     });

    //     // Update the total amount
    //     updateTotalAmount();
        
    //     // Reapply any existing discount
    //     const finalAmount = document.getElementById("finalAmountPaid").value;
    //     if (finalAmount && parseFloat(finalAmount) > 0) {
    //         applyFinalPrice();
    //     }
    // }


    //by agrima
    // function updateTotalAmount() {
    //     let total = 0;
    //     document.querySelectorAll('.final-price').forEach(cell => {
    //         total += parseFloat(cell.textContent.replace('₹', '')) || 0;
    //     });
    //     document.getElementById("totalAmount").textContent = `₹${total.toFixed(2)}`;
    //     document.getElementById("finalAmountPaid").value = total.toFixed(2);
    //     document.getElementById("final_price").value = total.toFixed(2);
    // }

    //by divya
    // function applyFinalPrice() {
    //     let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
    //     document.getElementById("final_price").value = finalAmount;
    //     let totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;

    //     if (finalAmount > totalAmount || finalAmount <= 0) {
    //         console.warn("Invalid final amount. Discount not applied.");
    //         return;
    //     }

    //     let discount = totalAmount - finalAmount;
    //     let allProducts = document.querySelectorAll(".final-price");
    //     let prices = Array.from(allProducts).map(row => parseFloat(row.getAttribute("data-original-price")) || 0);
    //     let totalOriginalAmount = prices.reduce((sum, price) => sum + price, 0);

    //     let remainingDiscount = discount;
    //     let lastIndex = allProducts.length - 1;

    //     allProducts.forEach((row, index) => {
    //         let originalPrice = prices[index];
    //         let discountShare = (originalPrice / totalOriginalAmount) * discount;

    //         if (index === lastIndex) {
    //             discountShare = remainingDiscount; // Adjust the last item's discount
    //         }

    //         let newPrice = Math.max(originalPrice - discountShare, 0);
    //         row.textContent = `₹${newPrice.toFixed(2)}`;
    //         row.setAttribute("data-final-price", newPrice.toFixed(2));
    //         remainingDiscount -= discountShare;
    //     });

    //     console.log(`✅ Discount of ₹${discount.toFixed(2)} applied across products.`);
    // }

    //by agrima
    // function applyFinalPrice() {
    //     let finalAmount = parseFloat(document.getElementById("finalAmountPaid").value) || 0;
    //     document.getElementById("final_price").value = finalAmount;
    //     let totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace("₹", "")) || 0;

    //     if (finalAmount > totalAmount || finalAmount <= 0) {
    //         console.warn("Invalid final amount. Discount not applied.");
    //         return;
    //     }

    //     let discount = totalAmount - finalAmount;
    //     let allProducts = document.querySelectorAll(".final-price");
    //     let prices = Array.from(allProducts).map(row => parseFloat(row.textContent.replace('₹', '')) || 0);
    //     let totalOriginalAmount = prices.reduce((sum, price) => sum + price, 0);

    //     let remainingDiscount = discount;
    //     let lastIndex = allProducts.length - 1;

    //     allProducts.forEach((row, index) => {
    //         let originalPrice = prices[index];
    //         let discountShare = (originalPrice / totalOriginalAmount) * discount;

    //         if (index === lastIndex) {
    //             discountShare = remainingDiscount;
    //         }

    //         let newPrice = Math.max(originalPrice - discountShare, 0);
    //         row.textContent = `₹${newPrice.toFixed(2)}`;
    //         remainingDiscount -= discountShare;
    //     });
    // }

    



    

    //by divya
    // ✅ Store selected products globally
     // Store the current detail group for selection
        // let selectedProductsData = {}; // Store selected products per detail group
        // let selectedProducts = []; // Store selected products
        // let selectedProductsWall = [];
        // let selectedProductsFloor = [];
        // let currentSelectionType = ''; // 'wall' or 'floor'

    // by divya
    // document.getElementById("orderForm").addEventListener("submit", function(event) {
    //     let form = this;
       
    //     // ✅ Remove any previously added hidden inputs
    //     document.querySelectorAll(".hidden-product-input").forEach(input => input.remove());

    //     let productsArray = [];

    //     Object.entries(selectedProductsData).forEach(([sectionId, section]) => {
    //         let detailGroup = document.querySelector(`[data-section-id="${sectionId}"]`);
    //         let multiplier = parseInt(detailGroup?.querySelector(".multiply-order")?.value) || 1; // ✅ Fetch multiplier (default 1)

    //         [...section.wall, ...section.floor].forEach(product => {
    //             productsArray.push({
    //                 id: product.id,
    //                 name: product.name,
    //                 quantity: product.quantity, // ✅ Keep base quantity
    //                 unitPrice: product.unitPrice,
    //                 totalPrice: product.totalPrice,
    //                 multiplier: multiplier // ✅ Ensure multiplier is included
    //             });
    //         });
    //     });

    //     // ✅ Debugging: Check if multiplier is included before submitting
    //     console.log("📌 Products being sent with multiplier:", productsArray);

    //     // ✅ Add a single hidden field with all products JSON
    //     let input = document.createElement("input");
    //     input.type = "hidden";
    //     input.className = "hidden-product-input";
    //     input.name = "products";
    //     input.value = JSON.stringify(productsArray);
    //     form.appendChild(input);

    //     console.log("🚀 Form Submitted with Products:", productsArray);
    // });

    </script>
    <style>
        @media (max-width: 768px) {
    .modal-fullscreen .modal-content {
        padding: 10px;
    }
    .table {
        font-size: 14px;
    }
    .custom-price {
        width: 100%;
    }
}

        body {
            background-color: #f8f9fa;
        }

        .container-box {
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }

        .form-step {
            display: none;
        }

        .active-step {
            display: block;
        }

        .dimension-group {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            margin-bottom: 10px;
        }

        .summary-table {
            width: 100%;
            margin-top: 20px;
        }

        .summary-table th,
        .summary-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include "admin_header.php"; ?>
        <div id="layoutSidenav_content">
            <main>
            <div class="container ">
                <div class="container-box mt-4">
                    <h2 class="text-center mb-3">Create New Order</h2>
                        <form id="orderForm" action="submit_order.php" method="post">

                            <!-- Step 1: Customer Details -->
                            <div class="form-step active-step" id="step1">
                                <h5>Customer Details</h5>
                                <div class="mb-3">
                                    <label class="form-label">Customer Name <span style="color:red">*</span> </label>
                                    <input type="text" class="form-control" name="customer_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone Number <span style="color:red">*</span> </label>
                                    <!-- <input type="tel" class="form-control" name="phone_no" pattern="\d{10}" title="Please enter exactly 10 digits" required> -->
                                    <input type="tel" class="form-control" name="phone_no" pattern="\d{10}" title="Please enter exactly 10 digits" required oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address <span style="color:red">*</span> </label>
                                    <input type="text" class="form-control" name="address" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">City <span style="color:red">*</span> </label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
                            </div>

                            <!-- Step 2: Order Details -->
                            <div class="form-step" id="step2">
                                <h5>Order Details</h5>
                                <!-- ✅ This will contain dynamically added sections -->
                                <div id="orderDetailsContainer"></div>
                                    <!-- ✅ Keep the buttons outside this container -->
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <button type="button" class="btn btn-success" onclick="addDetail()">Add New</button>
                                        <div>
                                            <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                                            <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Confirmation -->
                                <!-- Step 3: Summary Page -->
                                <div class="form-step" id="step3">
                                    <h5>Selected Products Summary</h5>
                                    <table class="table table-bordered summary-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Original Price</th>
                                                <th>Final Price</th>
                                            </tr>
                                        </thead>
                                        <tbody id="summaryBody"></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total Amount:</th>
                                                <th id="totalAmount">₹0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <!-- ✅ Custom Final Price Input (Placed Correctly Below Table) -->
                                    <!-- <div class="mb-3">
                                        <label for="finalAmountPaid" class="form-label"><strong>Final Amount Paid (₹):</strong></label>
                                        <input type="number" class="form-control" id="finalAmountPaid" placeholder="Enter final amount" oninput="applyFinalPrice()">
                                    </div>
                                    <input type="hidden" name="final_price" id="final_price"> -->
                                    <div class="mb-3">
                                    <label for="finalAmountPaid" class="form-label"><strong>Final Amount Paid (₹):</strong></label>
                                    <input type="text" class="form-control" id="finalAmountPaid" name="final_amount_paid"
                                        placeholder="Enter final amount"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, ''); applyFinalPrice(); updateGrandAmount();">

                                    <label for="RentAmount" class="form-label"><strong>Rent Paid (₹):</strong></label>
                                    <input type="text" class="form-control" id="RentAmount" name="rent_amount"
                                     placeholder="Enter rent amount" value="0"
                                     oninput="this.value = this.value.replace(/[^0-9.]/g, ''); applyFinalPrice(); updateGrandAmount();">
 
<label for="grandAmountPaid" class="form-label"><strong>Grand Amount Paid (₹):</strong></label>
<input type="text" class="form-control" id="grandAmountPaid" name="grand_amount_paid"
    placeholder="Grand amount" readonly>                                    </div>
                                    <input type="hidden" name="final_price" id="final_price">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                                    <button type="submit" class="btn btn-success">Submit Order</button>
                                </div>
                    </form>
                </div>
            </div>
        </main>
    </div>



<!-- Product Selection Modal -->
<!-- Product Selection Modal -->
<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" >

    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Select Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Selected Category:</strong> <span id="selectedCategoryName"></span></p>
                <p><strong>Total Area:</strong> <span id="selectedTotalArea">0.00 m²</span></p>

                <!-- Warning Message -->
                <div id="quantityWarning" class="alert alert-warning d-none" role="alert"></div>

                <!-- Product List -->
                <div id="productListContainer" class="table-responsive">
                    <table class="table table-bordered product-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Description</th>
                                <th>Area per Unit</th>
                                <th>Quantity</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSelectedProducts()">Confirm Selection</button>
            </div>
        </div>
    </div>
</div>


<!-- 🔹 Bill Container (Hidden Initially) -->
<!-- Full-Screen Bill Modal -->
<script src="../js/scripts.js"></script>
<!-- ✅ Ensure the modal is placed before closing body tag -->
</body>
</html>

              <!-- <div>
                                            <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                                            <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
                                        </div> -->
                                    </div>
                                </div>

                                <!-- Step 3: Confirmation -->
                                <!-- Step 3: Summary Page -->
                                <div class="form-step" id="step3">
                                    <h5>Selected Products Summary</h5>
                                    <table class="table table-bordered summary-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Original Price</th>
                                                <th>Final Price</th>
                                            </tr>
                                        </thead>
                                        <tbody id="summaryBody"></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total Amount:</th>
                                                <th id="totalAmount">₹0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>

                                    <!-- ✅ Custom Final Price Input (Placed Correctly Below Table) -->
                                    <!-- <div class="mb-3">
                                        <label for="finalAmountPaid" class="form-label"><strong>Final Amount Paid (₹):</strong></label>
                                        <input type="number" class="form-control" id="finalAmountPaid" placeholder="Enter final amount" oninput="applyFinalPrice()">
                                    </div>
                                    <input type="hidden" name="final_price" id="final_price"> -->
                                    <div class="mb-3">
                                    <label for="finalAmountPaid" class="form-label"><strong>Final Amount Paid (₹):</strong></label>
                                    <input type="text" class="form-control" id="finalAmountPaid" name="final_amount_paid"
                                        placeholder="Enter final amount"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, ''); applyFinalPrice(); updateGrandAmount();">

                                    <label for="RentAmount" class="form-label"><strong>Rent Paid (₹):</strong></label>
                                    <input type="text" class="form-control" id="RentAmount" name="rent_amount"
                                     placeholder="Enter rent amount" value="0"
                                     oninput="this.value = this.value.replace(/[^0-9.]/g, ''); applyFinalPrice(); updateGrandAmount();">
 
<label for="grandAmountPaid" class="form-label"><strong>Grand Amount Paid (₹):</strong></label>
<input type="text" class="form-control" id="grandAmountPaid" name="grand_amount_paid"
    placeholder="Grand amount" readonly>                                    </div>
                                    <input type="hidden" name="final_price" id="final_price">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                                    <button type="submit" class="btn btn-success">Submit Order</button>
                                </div>
                    </form>
                </div>
            </div>
        </main>
    </div>



<!-- Product Selection Modal -->
<!-- Product Selection Modal -->
<!-- Product Selection Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" >

    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Select Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Selected Category:</strong> <span id="selectedCategoryName"></span></p>
                <p><strong>Total Area:</strong> <span id="selectedTotalArea">0.00 m²</span></p>

                <!-- Warning Message -->
                <div id="quantityWarning" class="alert alert-warning d-none" role="alert"></div>

                <!-- Product List -->
                <div id="productListContainer" class="table-responsive">
                    <table class="table table-bordered product-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Description</th>
                                <th>Area per Unit</th>
                                <th>Quantity</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSelectedProducts()">Confirm Selection</button>
            </div>
        </div>
    </div>
</div>


<!-- 🔹 Bill Container (Hidden Initially) -->
<!-- Full-Screen Bill Modal -->
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>




<!-- ✅ Ensure the modal is placed before closing body tag -->
</body>
</html>


