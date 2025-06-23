<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Form Example</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <style>
        .detail-entry { 
            border: 1px solid #ccc; 
            padding: 20px; 
            margin-top: 10px; 
            position: relative; 
            border-radius: 5px; 
            background-color: #f9f9f9; 
        }
        .hidden { display: none; }
        .toggle-btn { position: absolute; top: 20px; right: 20px; }
        .volume-layout, .standard-layout {
            background-color: #f0f0f0;
            padding: 10px;  
        }
        .modal-dialog {
            max-width: 95%;
            width: auto;
            margin: 0.5rem;
        }
        .product-table th, .product-table td {
            vertical-align: middle;
        }
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
        }
        .quantity-controls input {
            width: 50px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-3">
        <h1 class="mb-4">Create New Order</h1>
        <div id="orderDetailsContainer"></div>
        <button class="btn btn-success" id="addDetailButton">Add New Detail</button>
    </div>

    <script>
        document.getElementById('addDetailButton').addEventListener('click', function() {
            const container = document.getElementById('orderDetailsContainer');
            const detail = document.createElement('div');
            detail.className = 'detail-entry';
            const uniqueId = new Date().getTime(); // Unique ID based on the timestamp

            detail.innerHTML = `
                <div class="form-group">
                    <label>Category:</label>
                    <select class="form-control categorySelect">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category) {
                            echo '<option value="' . htmlspecialchars($category['category_id']) . '">' . htmlspecialchars($category['category_name']) . '</option>';
                        } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Max Area (m²):</label>
                    <input type="number" class="form-control max-area" placeholder="Enter the total required area">
                </div>
                <button class="btn btn-primary choose-products">Choose Products</button>
                <button class="btn btn-danger remove-detail">Remove</button>
            `;

            container.appendChild(detail);

            // Add event listeners to new detail
            detail.querySelector('.remove-detail').addEventListener('click', function() {
                container.removeChild(detail);
            });

            detail.querySelector('.choose-products').addEventListener('click', function() {
                // Implement the function to show products based on the selected category
                console.log('Show products for category: ', detail.querySelector('.categorySelect').value);
                // You need to implement this part based on your existing code
            });
        });
    </script>
</body>
</html>
