<?php
include "../db_connect.php";
session_start();
$message = '';
$categories = [];
$storage_areas = [];
$suppliers = [];

// Fetch categories
$category_query = "SELECT category_id, category_name FROM category";
$category_result = $mysqli->query($category_query);
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch storage areas
$storage_query = "SELECT storage_area_id, storage_area_name FROM storage_areas";
$storage_result = $mysqli->query($storage_query);
while ($row = $storage_result->fetch_assoc()) {
    $storage_areas[] = $row;
}

// Fetch suppliers
$supplier_query = "SELECT supplier_id, supplier_name FROM suppliers";
$supplier_result = $mysqli->query($supplier_query);
while ($row = $supplier_result->fetch_assoc()) {
    $suppliers[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productName = $_POST['productName'];
    $description = $_POST['description'];
    $category_id = $_POST['category'];
    $supplier_id = $_POST['supplier'];
    $price = $_POST['price'];
    $costPrice = $_POST['costPrice'];
    $status = $_POST['status'];
    $totalPackets = $_POST['totalPackets'];
    $piecesPerPacket = $_POST['piecesPerPacket'];
    $quantity = $_POST['quantity'];
    $storageAreaId = $_POST['storageArea'];
    $minStockLevel = $_POST['minStockLevel'];
    $uploadPath = '../uploads/default_img.png'; // Set default image path


    // Start transaction
    $mysqli->begin_transaction();

    try {

    //     // Require product image upload
    // if (!isset($_FILES['productImage']) || $_FILES['productImage']['error'] != 0) {
    //     throw new Exception("Product image is required.");
    // }

    // Handle file upload if an image is provided
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
        // ...existing file upload code...
    }

        // Handle file upload if an image is provided
        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
            $allowed = ['jpg' => 'image/jpg','jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];
            $fileExt = pathinfo($_FILES['productImage']['name'], PATHINFO_EXTENSION);
            $fileType = $_FILES['productImage']['type'];
            $fileSize = $_FILES['productImage']['size'];
        
            // Validate file extension and size
            if (!array_key_exists($fileExt, $allowed)) {
                throw new Exception("Please select a valid image file format.");
            } elseif ($fileSize > 5000000) { // Validate file size (5MB)
                throw new Exception("File size is too large. Please upload a file smaller than 5MB.");
            } else {
                // Proceed with file upload
                $newFilename = uniqid() . '.' . $fileExt;
                $uploadPath = '../uploads/' . $newFilename;
                if (!move_uploaded_file($_FILES['productImage']['tmp_name'], $uploadPath)) {
                    throw new Exception("Error uploading file.");
                }
            }
        }

        // Insert product into the `products` table
        $product_sql = "INSERT INTO products (product_name, description, category_id, supplier_id, price, cost_price,product_image, status)
                        VALUES (?, ?, ?, ?, ?, ?,?, ?)";
        $product_stmt = $mysqli->prepare($product_sql);
        $product_stmt->bind_param("ssiiidss", $productName, $description, $category_id, $supplier_id, $price, $costPrice,$uploadPath, $status);
        if (!$product_stmt->execute()) {
            throw new Exception("Error adding product: " . $mysqli->error);
        }
        $product_id = $mysqli->insert_id;

        // Insert stock with pieces per packet
        $stock_sql = "INSERT INTO product_stock (product_id, storage_area_id, pieces_per_packet, quantity, min_stock_level) VALUES (?, ?, ?, ?, ?)";
        $stock_stmt = $mysqli->prepare($stock_sql);
        $stock_stmt->bind_param("iiiii", $product_id, $storageAreaId, $piecesPerPacket, $quantity, $minStockLevel);
        if (!$stock_stmt->execute()) {
            throw new Exception("Error adding stock: " . $mysqli->error);
        }

        // Commit transaction
        $mysqli->commit();
        $message = 'Product and stock added successfully!';
        
        // Log transaction (new product added)

if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $transaction_type = null;
    $quantity_changed = 0;
    // $description = "New product '$productName' added by user ID $user_id";
    $description = "New product added '$productName'";
    
    $log_stmt = $mysqli->prepare("INSERT INTO transactions 
        (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $log_stmt->bind_param("iiisis", $user_id, $product_id, $storageAreaId, $transaction_type, $quantity_changed, $description);
    $log_stmt->execute();
    $log_stmt->close();
}
    } catch (Exception $e) {
        $mysqli->rollback();
        $message = $e->getMessage();
    } finally {
        if (isset($product_stmt)) {
            $product_stmt->close();
        }
        if (isset($stock_stmt)) {
            $stock_stmt->close();
        }
    }
}

$mysqli->close();
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
  .btn-primary {
    border-radius: 0.6rem;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.75rem;
  }

  #successAlert {
    max-width: 700px;
    margin: 0 auto 1rem auto;
  }
  .gradient-btn {
    background: linear-gradient(135deg,rgb(92, 140, 212) 0%,rgb(31, 106, 218) 100%);
    border: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.gradient-btn:hover {
    background: linear-gradient(135deg,rgb(125, 154, 198) 0%,rgb(37, 106, 210) 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.gradient-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 3px rgba(0,0,0,0.1);
}
.custom-file-input {
    border: 1px solid #ced4da;
    border-radius: 0.75rem;
    padding: 0.5rem 1rem;
    height: 48px;
    font-size: 1rem;
    display: flex;
    align-items: center;
  }

  .custom-file-input::-webkit-file-upload-button {
    background-color: #e9ecef;
    border: none;
    padding: 8px 16px;
    border-radius: 0.5rem 0 0 0.5rem;
    margin-right: 1rem;
    cursor: pointer;
  }

  .custom-file-input::file-selector-button {
    background-color: #e9ecef;
    border: none;
    padding: 9px 16px;
    border-radius: 0.5rem 0.5rem 0.5rem 0.5rem;
    margin-right: 1rem;
    cursor: pointer;
  }

  .custom-file-input:hover::file-selector-button {
    background-color: #d3d3d3;
  }
</style>

    </head>
    <body class="sb-nav-fixed">
    <?php  include 'navbar.php'; ?>

            <!-- ---------------------------- -->
            <div id="layoutSidenav_content">
            <main>
                    <div class="container-fluid px-4">
                        <!-- ---------------------------------------->
                         <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto mt-4" style="max-width: 950px;">
                        <h2 class="my-4 fw-bold fs-2 text-center">Add New Product</h2>
                        <?php if ($message): ?>
                        <div class="alert alert-info  mx-auto mb-3"  style="max-width: 700px;" id="successAlert"><?= $message ?></div>
                        <?php endif; ?>
                        <script>
                            setTimeout(function() {
                                document.getElementById('successAlert').style.display = 'none';
                            }, 3000);  // Disappears after 5 seconds
                        </script>
                        <form action="" method="post"  enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="productName" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Product Name</label>
                                <input type="text" class="form-control rounded-3 py-2" id="productName" name="productName" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Description</label>
                                <textarea class="form-control rounded-3 py-2" id="description" name="description" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Category</label>
                                <select class="form-select rounded-3 py-2" id="category" name="category" required>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="supplier" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Supplier</label>
                                <select class="form-select " id="supplier" name="supplier" required>
                                    <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['supplier_id'] ?>"><?= $supplier['supplier_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Selling price</label>
                                <input type="number" step="0.01" class="form-control rounded-3 py-2" id="price" name="price" required>
                            </div>
                            <div class="mb-3">
                                <label for="costPrice" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Purchase Price</label>
                                <input type="number" step="0.01" class="form-control rounded-3 py-2" id="costPrice" name="costPrice" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Status</label>
                                <select class="form-select " id="status" name="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Discontinued">Discontinued</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="totalPackets" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Total Packets</label>
                                <input type="number" class="form-control rounded-3 py-2" id="totalPackets" name="totalPackets" required onchange="calculateTotalPieces()">
                            </div>
                            <div class="mb-3">
                                <label for="piecesPerPacket" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Pieces Per Packet</label>
                                <input type="number" class="form-control rounded-3 py-2" id="piecesPerPacket" name="piecesPerPacket" required onchange="calculateTotalPieces()">
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Total Pieces</label>
                                <input type="number" class="form-control rounded-3 py-2" id="quantity" name="quantity" required readonly>
                            </div>
                            <div class="mb-3">
                                <label for="storageArea" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Storage Area</label>
                                <select class="form-select " id="storageArea" name="storageArea" required>
                                    <?php foreach ($storage_areas as $area): ?>
                                    <option value="<?= $area['storage_area_id'] ?>"><?= $area['storage_area_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="minStockLevel" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Minimum Stock Level</label>
                                <input type="number" class="form-control rounded-3 py-2" id="minStockLevel" name="minStockLevel" value="7" required>
                            </div>
                            <div class="mb-3">
                                <label for="productImage" class="form-label fw-medium text-secondary" style=" font-weight: 550;">Product Image  </label>
                                <input type="file" class="form-control custom-file-input" id="productImage" name="productImage" >
                            </div>
                            <div class="mb-3">
    <label for="insertArea" class="form-label fw-medium text-secondary" style="font-weight: 550;">Insert Area</label>
    <input type="text" class="form-control rounded-3 py-2" id="insertArea" name="insertArea" required>
</div>
                            <div class="text-center">
                            <button type="submit" class="btn gradient-btn btn-primary mb-4"><i class="fas fa-plus text-white me-2"></i>Add Product</button>
                            </div>
                        </form>
                        <!-- ---------------------------------------->
                                    </div>
                    </div>
                </main>

                <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            ·
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
            </div>
         <!-- ---------------------------- -->   
        </div>
        <script>
            function calculateTotalPieces() {
                var totalPackets = document.getElementById('totalPackets').value;
                var piecesPerPacket = document.getElementById('piecesPerPacket').value;
                document.getElementById('quantity').value = totalPackets * piecesPerPacket;
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>

        <script>
document.querySelector('form').addEventListener('submit', function(e) {
    const packets = document.querySelector('input[name="packets"]').value;
    const pieces = document.querySelector('input[name="pieces"]').value;
    if (!packets || !pieces || Number(packets) === 0 && Number(pieces) === 0) {
        alert('Enter amount to add (packets or pieces)');
        e.preventDefault();
    }
});
</script>



    </body>
</html>
