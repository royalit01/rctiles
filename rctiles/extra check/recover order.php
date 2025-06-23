<?php
include '../db_connect.php'; // Ensure your DB connection is correct

// Query to fetch categories
$sql = "SELECT category_id , category_name  FROM category"; // Make sure 'categories' is your table name
$result = $conn->query($sql);

// Check if there are categories
if ($result->num_rows > 0) {
    // Create an array to store categories
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    // Output categories in JSON format
    echo json_encode($categories);
} else {
    // No categories found
    echo json_encode([]);
}

// Close connection
$conn->close();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Form Example</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .detail-entry { border: 1px solid #ccc; padding: 20px; margin-top: 10px; position: relative; border-radius: 5px; background-color: #f9f9f9; }
        .hidden { display: none; }
        .toggle-btn { position: absolute; top: 20px; right: 20px; }
    </style>
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Create New Order</h1>
        <div id="orderDetailsContainer"></div>
        <button class="btn btn-success" onclick="addDetail()">Add New Detail</button>
    </div>

    <script>
        function toggleLayout(detail) {
            const isStandardLayout = detail.classList.contains('standard-layout');
            detail.classList.toggle('standard-layout', !isStandardLayout);
            detail.classList.toggle('volume-layout', isStandardLayout);
            updateVisibility(detail);
            calculateAreas(detail);
        }

        function updateVisibility(detail) {
            const isVolumeLayout = detail.classList.contains('volume-layout');
            detail.querySelectorAll('.standard-input').forEach(input => input.classList.toggle('hidden', isVolumeLayout));
            detail.querySelectorAll('.custom-input').forEach(input => input.classList.toggle('hidden', !isVolumeLayout));
            detail.querySelector('.wall-height').classList.remove('hidden');  // Always visible
        }

        function calculateAreas(detail) { 
    const wallAreaSpan = detail.querySelector('.wall-area');
    const floorAreaSpan = detail.querySelector('.floor-area');
    const totalAreaSpan = detail.querySelector('.total-area');
    let wallArea = 0, floorArea = 0;

    if (detail.classList.contains('standard-layout')) {
        const length = parseFloat(detail.querySelector('.wall-length').value) || 0;
        const width = parseFloat(detail.querySelector('.wall-width').value) || 0;
        const height = parseFloat(detail.querySelector('.wall-height').value) || 0;
        wallArea = 2 * height * (length + width);  // Calculate wall area for standard layout
        floorArea = (parseFloat(detail.querySelector('.floor-length').value) || 0) *
                    (parseFloat(detail.querySelector('.floor-width').value) || 0);  // Calculate floor area for standard layout
    } else {
        const perimeter = parseFloat(detail.querySelector('.wall-perimeter').value) || 0;
        const height = parseFloat(detail.querySelector('.wall-height').value) || 0;
        wallArea = perimeter * height;  // Calculate wall area for custom layout

        // Get floor area from the direct input field in custom layout
        const floorAreaInput = parseFloat(detail.querySelector('.floor-area-direct').value) || 0;
        console.log("Custom Layout Floor Area Input:", floorAreaInput);  // Debugging log
        floorArea = floorAreaInput;
    }

    console.log("Wall Area Calculated:", wallArea);
    console.log("Floor Area Calculated:", floorArea);

    const totalArea = wallArea + floorArea;  // Calculate total area
    wallAreaSpan.textContent = `Wall Area: ${wallArea.toFixed(2)} m²`;
    floorAreaSpan.textContent = `Floor Area: ${floorArea.toFixed(2)} m²`;
    totalAreaSpan.textContent = `Total Area: ${totalArea.toFixed(2)} m²`;

    console.log("Total Area Updated To:", totalArea);
}

        function addDetail() {
            const container = document.getElementById('orderDetailsContainer');
            const detail = document.createElement('div');
            detail.className = 'detail-entry standard-layout';
            detail.innerHTML = `
                <div class="form-row">
                    <div class="form-group col-md-4 standard-input">
                        <label>Wall Length (m):</label>
                        <input type="number" class="form-control wall-length" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4 standard-input">
                        <label>Wall Width (m):</label>
                        <input type="number" class="form-control wall-width" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Wall Height (m):</label>
                        <input type="number" class="form-control wall-height" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4 custom-input hidden">
                        <label>Wall Perimeter (m):</label>
                        <input type="number" class="form-control wall-perimeter" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4 standard-input">
                        <label>Floor Length (m):</label>
                        <input type="number" class="form-control floor-length" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4 standard-input">
                        <label>Floor Width (m):</label>
                        <input type="number" class="form-control floor-width" oninput="calculateAreas(this.parentNode.parentNode.parentNode)">
                    </div>
                    <div class="form-group col-md-4 custom-input hidden">
                        <label>Floor Area (m²):</label>
                        <input type="number" class="form-control floor-area-direct" oninput="calculateAreas this.parentNode.parentNode.parentNode)">
                    </div>
                </div>
                <p>Wall Area: <span class="wall-area">0 m²</span></p>
                <p>Floor Area: <span class="floor-area">0 m²</span></p>
                <p>Total Area: <span class="total-area">0 m²</span></p>
                <button class="btn btn-info toggle-btn" onclick="toggleLayout(this.parentNode)">Toggle Layout</button>
                <button class="btn btn-danger remove-btn" onclick="removeDetail(this.parentNode)">Remove</button>


            `;
            container.appendChild(detail);
        }
 // Function to remove the detail div
 function removeDetail(detail) {
        detail.remove();
    }


    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="../assets/demo/chart-area-demo.js"></script>
    <script src="../assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
</body>
</html>
