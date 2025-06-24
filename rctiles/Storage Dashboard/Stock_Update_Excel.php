<?php

require '../vendor/autoload.php'; // If using Composer
use PhpOffice\PhpSpreadsheet\IOFactory;


include "../db_connect.php";

// Initialize variables for alert messages
$alertMessage = null;
$alertType = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Check if a file was uploaded
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == UPLOAD_ERR_OK) {
            $uploadedFile = $_FILES['excel_file']['tmp_name']; // Temporary file path
            
            // Validate file type (optional but recommended)
            $allowedExtensions = ['xls', 'xlsx'];
            $fileExtension = pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION);

            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception("Invalid file type. Please upload an Excel file (.xls or .xlsx).");
            }

            // Load the spreadsheet
            $spreadsheet = IOFactory::load($uploadedFile);
            $sheet = $spreadsheet->getActiveSheet();

            // Start transaction
            $mysqli->begin_transaction();

            // Loop through rows
            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Include even empty cells

                $rowData = [];
                $isEmptyRow = true;

                foreach ($cellIterator as $cell) {
                    $cellValue = $cell->getFormattedValue();
                    $rowData[] = $cellValue;
                    if (!empty($cellValue)) {
                        $isEmptyRow = false; // Found data, row is not empty
                    }
                }

                if ($isEmptyRow) {
                    break; // Stop processing further rows if the current row is empty
                }

                // Validate data (example for product_name and category_id)
                if (empty($rowData[0]) || !is_string($rowData[0]) || !is_numeric($rowData[3])) {
                    $alertMessage = "Invalid data in row. Skipping.";
                    $alertType = "alert-warning";
                    continue;
                }

                // Insert data into tables
                insertDataIntoTables($rowData);
            }

            // Commit transaction
            $mysqli->commit();
            $alertMessage = "Data imported successfully.";
            $alertType = "alert-success";
        } else {
            throw new Exception("File upload failed. Please try again.");
        }
    } catch (Exception $e) {
        $mysqli->rollback();
        $alertMessage = "Error during import: " . $e->getMessage();
        $alertType = "alert-danger";
    }

    $mysqli->close();
}

function insertDataIntoTables($rowData) {
    global $mysqli;

    // Insert into the products table
    $sql1 = "INSERT INTO products (product_name, description, area, category_id, supplier_id, price, cost_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt1 = $mysqli->prepare($sql1);
    $stmt1->bind_param("ssiiiiis", $rowData[0], $rowData[1], $rowData[2], $rowData[3], $rowData[4], $rowData[5], $rowData[6], $rowData[7]);

    if (!$stmt1->execute()) {
        throw new Exception("Error inserting into products: " . $stmt1->error);
    }

    // Fetch the last inserted ID for the products table
    $productId = $mysqli->insert_id;

    $stmt1->close();

    // Insert into the product_stock table using the fetched product ID
    $sql2 = "INSERT INTO product_stock (product_id, storage_area_id, pieces_per_packet, quantity, min_stock_level) VALUES (?, ?, ?, ?, ?)";
    $stmt2 = $mysqli->prepare($sql2);
    $multipliedValue = $rowData[10] * $rowData[9];
    $stmt2->bind_param("iiiii", $productId, $rowData[8], $rowData[9], $multipliedValue, $rowData[11]);

    if (!$stmt2->execute()) {
        throw new Exception("Error inserting into product_stock: " . $stmt2->error);
    }

    $stmt2->close();
}
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
 
    </head>
    <body class="sb-nav-fixed">
    <?php  include 'navbar.php'; ?>
<div id="layoutSidenav_content">
                    <main>
                    <div class="container-fluid mt-5">
                    <div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0 text-center">Upload Excel Data</h3>
                </div>
                <div class="card-body">
                    <!-- Excel File Upload Form -->
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="excelFile" class="form-label">Choose Excel File</label>
                            <input type="file" class="form-control" id="excelFile" name="excel_file" accept=".xls,.xlsx" required>
                        </div>
                        <div class="mt-4 d-grid">
                            <button type="submit" name="upload" class="btn btn-primary btn-block">Upload</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <a href="../excel_uploads/template.xlsx" class="btn btn-success" download="Excel_Template.xlsx">
                        <i class="fas fa-download"></i> Download Excel Template
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container mt-5">
<?php
include "../db_connect.php";

// Fetch data from the database
$sql = "SELECT * FROM storage_areas"; // Replace 'products' with your table name
$result = $mysqli->query($sql);

// Check for errors
if ($mysqli->error) {
    die("Database query failed: " . $mysqli->error);
}
?>
<h2 class="mb-4">Storage Area</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Id</th>
                    <th>Storage Area</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through the data and display in table rows
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['storage_area_id']}</td>
                                <td>{$row['storage_area_name']}</td>
                                
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
</div>
<div class="container mt-5">
<?php
include "../db_connect.php";

// Fetch data from the database
$sql = "SELECT * FROM category"; // Replace 'products' with your table name
$result = $mysqli->query($sql);

// Check for errors
if ($mysqli->error) {
    die("Database query failed: " . $mysqli->error);
}
?>
<h2 class="mb-4">Category Table</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Id</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through the data and display in table rows
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['category_id']}</td>
                                <td>{$row['category_name']}</td>
                                
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
</div>
<div class="container mt-5">
<?php
include "../db_connect.php";

// Fetch data from the database
$sql = "SELECT * FROM suppliers"; // Replace 'products' with your table name
$result = $mysqli->query($sql);

// Check for errors
if ($mysqli->error) {
    die("Database query failed: " . $mysqli->error);
}
?>
<h2 class="mb-4">Suppliers Table</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Id</th>
                    <th>Suppliers</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through the data and display in table rows
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['supplier_id']}</td>
                                <td>{$row['supplier_name']}</td>
                                
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No data available</td></tr>";
                }
                ?>
            </tbody>
        </table>
</div>

                    </main> 
                    
                </div>
            <!-- ---------------------------- -->   
            </div>


 <script src="../js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>


</body>
</html>