<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}
if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
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
let selectedProductsData = {};
let selectedProductsWall = [];
let selectedProductsFloor = [];
let currentSelectionType = 'wall';

        // let nextStep = currentStep.nextElementSibling;
        // if (nextStep && nextStep.classList.contains('form-step')) {
        //     currentStep.classList.remove('active-step');
        //     nextStep.classList.add('active-step');
        //     if (nextStep.id === 'step3') updateSummary();
        // }

    function addDetail() {
        const container = document.getElementById('orderDetailsContainer');
        const detailIndex = document.querySelectorAll('.dimension-group').length;

        const detailDiv = document.createElement('div');
        detailDiv.className = 'dimension-group';
        detailDiv.setAttribute("data-section-id", detailIndex); // Assign unique ID to track selections

        detailDiv.innerHTML = `
            <h6 class="fw-bold">Detail ${detailIndex + 1}</h6>
            
            
            <div class="mb-2">
                <label class="form-label">Title (e.g., Washroom 1, Kitchen, etc.) </label>
                <input type="text" class="form-control" name="titles[]" placeholder="Enter location name">
            </div>

   
<!-- REMOVE the following block (Wall/Floor input row): -->

<div class="row">
    <div class="col-md-4">
        <label class="form-label">Wall Length (ft²) </label>
        <input type="number" class="form-control" name="wall_lengths[]" step="0.1" min="0" oninput="calculateAreas(this)">
    </div>
    <div class="col-md-4">
        <label class="form-label">Wall Width (ft²) </label>
        <input type="number" class="form-control" name="wall_widths[]" step="0.1" min="0" oninput="calculateAreas(this)">
    </div>
    <div class="col-md-4">
        <label class="form-label">Wall Height (ft²) </label>
        <input type="number" class="form-control" name="wall_heights[]" step="0.1" min="0" oninput="calculateAreas(this)">
    </div>
    <div class="col-md-4">
        <label class="form-label">Door Area (ft²) </label>
        <input type="number" class="form-control" name="door_areas[]" step="0.1" min="0" oninput="calculateAreas(this)">
    </div>
    <div class="col-md-4">
        <label class="form-label">Window Area (ft²) </label>
        <input type="number" class="form-control" name="window_areas[]" step="0.1" min="0" oninput="calculateAreas(this)">
    </div>
</div>

        

            <p class="mt-2"><strong>Wall Area:</strong> <span class="wall-area">0.00 ft²</span></p>

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
                    <label class="form-label">Floor Length (ft²)</label>
                    <input type="number" class="form-control" name="floor_lengths[]" step="0.1" min="0" oninput="calculateAreas(this)">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Floor Width (ft²)</label>
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

            <p class="mt-2"><strong>Floor Area:</strong> <span class="floor-area">0.00 ft²</span></p>

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
        // Allow area to be zero or missing, but default to 1 if not a number
        let totalArea = type === 'wall' 
            ? parseFloat(detailGroup.querySelector('.wall-area')?.textContent) 
            : parseFloat(detailGroup.querySelector('.floor-area')?.textContent);
        if (isNaN(totalArea) || totalArea < 0) totalArea = 1;

      

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
        fetch(`fetch_products.php?category_id=${categoryId}&total_area=100`)
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
                        <!--<td>${product.description}</td> -->
                        <td>${product.area_per_unit !== "N/A" ? product.area_per_unit : "Not Specified"} m²</td>
                        <td>
                            <input type="number" class="form-control product-quantity" min="1" 
                                value="${quantity}" data-product-id="${product.id}" 
                                data-unit-price="${product.price !== 'N/A' ? parseFloat(product.price):0}">
                        </td>
                        <td>
                            <input type="checkbox" class="product-checkbox" data-product-id="${product.id}"
                        onchange="toggleProductSelection(this, ${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price !== 'N/A' ? parseFloat(product.price) : 0}, ${product.area_per_unit})"
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(error => console.error('❌ Error fetching products:', error));
    }

    function toggleProductSelection(checkbox, productId, productName, unitPrice, areaPerUnit) {
        let quantityInput = checkbox.closest("tr").querySelector(".product-quantity");
        let quantity = parseInt(quantityInput.value) || 1;
        let totalPrice = quantity * unitPrice;
        let totalArea = quantity * (areaPerUnit || 0);

        let selectedList = currentSelectionType === 'wall' ? selectedProductsWall : selectedProductsFloor;
        console.log(selectedList)
        let existingProductIndex = selectedList.findIndex(p => p.id === productId);
        
        if (checkbox.checked) {
            if (existingProductIndex === -1) {
                selectedList.push({
                    id: productId,
                    name: productName,
                    unitPrice: unitPrice,
                    quantity: quantity,
                    totalPrice: totalPrice,
                    customPrice: unitPrice,
                    area : areaPerUnit,
                    totalArea: totalArea
                  
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
        console.log("🔄 Updating selected products UI...", selectedList);
        if (selectedList.length === 0) {
            container.innerHTML = `<p class="text-muted">No products selected yet.</p>`;
            return;
        }
        console.log("🔄 Updating UI with selected products:", selectedList);
        selectedList.forEach(product => {
            let productRow = document.createElement("div");
            productRow.classList.add("selected-product-item", "border", "p-2", "rounded", "mb-2", "d-flex", "justify-content-between");

            productRow.innerHTML = `
                <span><strong>${product.name}</strong> - ${product.quantity} pcs </span>
                <!--<span><strong>${product.name}</strong> - ${product.quantity} pcs @ ${product.area.toFixed(2)} per unit</span>-->
                <span class="badge bg-success">Total: ${product.totalArea.toFixed(2)}</span>
            `;
            container.appendChild(productRow);
        });
        let totalAreaSum = selectedList.reduce((sum, product) => sum + (product.totalArea || 0), 0);
     let totalAreaRow = document.createElement("div");
totalAreaRow.classList.add("selected-product-total-area", "mt-2", "fw-bold", "text-end");

// Get the required area from the modal (removing " m²" and parsing as float)
let requiredArea = 0;
const selectedTotalAreaElem = document.getElementById("selectedTotalArea");
if (selectedTotalAreaElem) {
    requiredArea = parseFloat(selectedTotalAreaElem.textContent) || 0;
}

// Decide color: red if totalAreaSum >= requiredArea, else blue
let areaColor = totalAreaSum >= requiredArea ? 'blue' : 'red';

totalAreaRow.innerHTML = `Total Area: <span style="color:${areaColor}">${totalAreaSum.toFixed(2)} m²</span>`;
container.appendChild(totalAreaRow);



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
//by agrima same.new start
document.addEventListener("DOMContentLoaded", function() {
    addDetail(); // Add the first detail section automatically

    // Use the correct form ID ("Form" not "orderForm")
    const form = document.getElementById("Form");
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            console.log("Form submission initiated");

            // Validate final amount only if the field exists
            const finalAmountInput = document.getElementById("finalAmountPaid");
            if (finalAmountInput) {
                let finalAmount = parseFloat(finalAmountInput.value) || 0;
                if (finalAmount <= 0) {
                    alert("Final amount must be greater than 0");
                    return;
                }
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

                // Submit the form programmatically
                console.log("Submitting form...");
                this.submit();

            } catch (error) {
                console.error("Error during form submission:", error);
                alert("An error occurred while preparing the order data. Please try again.");
            }
        });
    } else {
        console.error('Form with id "Form" not found.');
    }
});
 

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
        .summary-table tfoot tr {
    border-bottom: none; /* Remove only the bottom border */
}
/* Always show number input controls */
.product-quantity::-webkit-outer-spin-button,
.product-quantity::-webkit-inner-spin-button {
    opacity: 1;
    margin: 0;
}

/* For Firefox */
.product-quantity {
    -moz-appearance: textfield;
}
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include "../templates/admin_header.php"; ?>
        <div id="layoutSidenav_content">
            <main>
            <div class="container ">
                <div class="container-box mt-4">
                    <h2 class="text-center mb-3">Create New Order</h2>
                        <form id="Form" action="submit_order.php" method="post">

                            <div class="form">
                                <h5>Order Details</h5>
            
                                <div class="mb-3">
                                    <label class="form-label">Customer Name <span style="color:red">*</span> </label>
                                    <input type="text" class="form-control" name="customer_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone Number <span style="color:red">*</span> </label>
                                    <input type="tel" class="form-control" name="phone_no" pattern="\d{10}" title="Please enter exactly 10 digits" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address <span style="color:red">*</span> </label>
                                    <input type="text" class="form-control" name="address" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">City <span style="color:red">*</span> </label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>
                                <!-- ✅ This will contain dynamically added sections -->
                                <div id="orderDetailsContainer"></div>
                                    <!-- ✅ Keep the buttons outside this container -->
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <button type="button" class="btn btn-success" onclick="addDetail()">Add New</button>
                                        
                                    </div>

                                </div>
                                                               <br>  <br> <button type="submit" class="btn btn-success">Submit Order</button>

                    </form>
                </div>
            </div>
        </main>
    </div>


<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" >

    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Select Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Selected Category:</strong> <span id="selectedCategoryName"></span></p>
<p><strong>Total Area:</strong> <span id="selectedTotalArea" class="area-value">0.00 m²</span></p>
                <!-- Warning Message -->
                <div id="quantityWarning" class="alert alert-warning d-none" role="alert"></div>

                <!-- Product List -->
                <div id="productListContainer" class="table-responsive">
                    <table class="table table-bordered product-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <!-- <th>Description</th> -->
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

