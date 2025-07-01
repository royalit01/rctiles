<?php
include "../db_connect.php";  // Ensure this is the correct path to your database connection script

$selected_storage_area = $_POST['storage_area'] ?? '';

// Build SQL query based on storage area selection
$query = "SELECT p.product_name, p.product_id, ps.quantity, ps.pieces_per_packet, ps.min_stock_level, sa.storage_area_name 
          FROM product_stock ps
          JOIN products p ON p.product_id = ps.product_id
          JOIN storage_areas sa ON sa.storage_area_id = ps.storage_area_id ";

if (!empty($selected_storage_area)) {
    $query .= " WHERE ps.storage_area_id = " . intval($selected_storage_area);
}

$query .= " AND (ps.quantity / ps.pieces_per_packet) < ps.min_stock_level
            ORDER BY p.product_name ASC";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
         <style>
            /* Main container styling */
.container-fluid {
    padding: 20px;
   
}

/* Card styling */
.card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border-radius: 0.75rem;
    border: none;
    margin-top: 20px;
    background-color: white;
}

.card-body {
    padding: 2rem;
}

/* Header styling */
h1 {
    color: #343a40;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
}

/* Form styling */
.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-select {
    border-radius: 0.5rem;
    border: 1px solid #ced4da;
    padding: 0.5rem 1rem;
    max-width: 300px;
    transition: all 0.3s;
}

.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Alert styling */
.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeeba;
    color: #856404;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}

/* Table styling */
.rounded-table {
    border-radius: 0.75rem;
    overflow: hidden;
    border-collapse: separate;
    border-spacing: 0;
    border: 1px solid #dee2e6;
    margin-top: 1.5rem;
}

.rounded-table thead {
    background-color: #343a40;
    color: white;
}

.rounded-table thead th {
    border-bottom: 2px solid #495057 !important;
    padding: 1rem;
    font-weight: 600;
}

.rounded-table thead th:first-child {
    border-top-left-radius: 0.75rem;
}
.rounded-table thead th:last-child {
    border-top-right-radius: 0.75rem;
}
.rounded-table tbody tr:last-child td:first-child {
    border-bottom-left-radius: 0.75rem;
}
.rounded-table tbody tr:last-child td:last-child {
    border-bottom-right-radius: 0.75rem;
}

.rounded-table th, 
.rounded-table td {
    border-left: 1px solid #dee2e6 !important;
    border-right: 1px solid #dee2e6 !important;
    padding: 0.75rem;
    vertical-align: middle;
}

.rounded-table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.rounded-table tbody tr:hover {
    background-color: #e9ecef;
}

/* Button styling */
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    border-radius: 0.5rem;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card {
        margin: 1rem;
    }
    
    .form-select {
        max-width: 100%;
    }
    
    .rounded-table {
        font-size: 0.9rem;
    }
}


         </style>
    </head>
    <body class="sb-nav-fixed">
    <?php  include 'navbar.php'; ?>
 

            <!-- ---------------------------- -->
            <div id="layoutSidenav_content">
                <main>
                <div class="container-fluid mt-3">
                        <h1 class="mt-4">Low Stock</h1>
                            <div class="card-body">
                                <form action="" method="POST" class="mb-3">
                                    <label for="storageAreaSelect" class="form-label">Storage Area:</label>
                                    <select id="storageAreaSelect" name="storage_area" class="form-select" onchange="this.form.submit();">
                                        <option value="">All Areas</option>
                                        <?php
                                        $areas = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas ORDER BY storage_area_name");
                                        while ($area = $areas->fetch_assoc()) {
                                            $selected = ($selected_storage_area == $area['storage_area_id']) ? 'selected' : '';
                                            echo "<option value='{$area['storage_area_id']}' $selected>{$area['storage_area_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </form>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    echo '<div class="table-responsive">
                                            <table class="table table-striped table-bordered mt-3" id="stockTable" >
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Product Name</th>
                                                        <th>Stock (Packets)</th>
                                                        <th>Min Stock Level (Packets)</th>
                                                        <th>Storage Area</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                                    while ($row = $result->fetch_assoc()) {
                                        $packets = floor($row['quantity'] / $row['pieces_per_packet']);
                                        echo "<tr>
                                                <td>{$row['product_name']}</td>
                                                <td>{$packets}</td>
                                                <td>{$row['min_stock_level']}</td>
                                                <td>{$row['storage_area_name']}</td>
                                              </tr>";
                                    }
                                    echo '</tbody>
                                        </table></div>';
                                } else {
                                    echo '<div class="alert alert-warning" role="alert">No products are low in stock for the selected area.</div>';
                                }
                                ?>
                            </div>   
                    </div>
                </main> 

                
            </div>
         <!-- ---------------------------- -->   
        </div>
            <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/umd.simple-datatables.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
            <script src="../js/scripts.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
            <script src="../assets/demo/chart-area-demo.js"></script>
            <script src="../assets/demo/chart-bar-demo.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
            <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
