// <?php
// include "../db_connect.php";

// $storageAreas = $products = [];
// $selectedSource = $selectedProduct = $selectedDestination = '';
// $productDetails = $transferMessage = '';
// $success = false;

// // Fetch storage areas
// $result = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas");
// while ($row = $result->fetch_assoc()) {
//     $storageAreas[] = $row;
// }

// $successMessage = $errorMessage = '';

// // Handle source area selection and product fetch
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['source_area'])) {
//     $selectedSource = $_POST['source_area'];
//     $stmt = $mysqli->prepare("SELECT ps.product_id, p.product_name, ps.quantity, ps.pieces_per_packet FROM product_stock ps JOIN products p ON ps.product_id = p.product_id WHERE storage_area_id = ?");
//     $stmt->bind_param("i", $selectedSource);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     while ($row = $result->fetch_assoc()) {
//         $products[] = $row;
//     }
//     $stmt->close();
// }

// // Handle product selection
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
//     $selectedProduct = $_POST['product_id'];
//     foreach ($products as $product) {
//         if ($product['product_id'] == $selectedProduct) {
//             $productDetails = $product;
//             break;
//         }
//     }
// }

// // Handle stock transfer logic
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transfer'])) {
//     $selectedDestination = $_POST['destination_area'];
//     $packets = $_POST['packets'];
//     $pieces = $_POST['pieces'];
//     $totalPieces = ($packets * $productDetails['pieces_per_packet']) + $pieces;


//     if ($totalPieces > $productDetails['quantity']) {
//         $errorMessage = "Insufficient stock. Cannot transfer more than available.";
//     } else {
//         $mysqli->begin_transaction();
//     try {
//         // Update source quantity
//         $newQuantitySource = $productDetails['quantity'] - $totalPieces;
//         $stmtUpdateSource = $mysqli->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ? AND storage_area_id = ?");
//         $stmtUpdateSource->bind_param("iii", $newQuantitySource, $selectedProduct, $selectedSource);
//         $stmtUpdateSource->execute();

//         // Check if the destination already has this product
//         $stmtCheckDest = $mysqli->prepare("SELECT quantity FROM product_stock WHERE product_id = ? AND storage_area_id = ?");
//         $stmtCheckDest->bind_param("ii", $selectedProduct, $selectedDestination);
//         $stmtCheckDest->execute();
//         $resultDest = $stmtCheckDest->get_result();

//         if ($rowDest = $resultDest->fetch_assoc()) {
//             // Update destination stock
//             $newQuantityDest = $rowDest['quantity'] + $totalPieces;
//             $stmtUpdateDest = $mysqli->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ? AND storage_area_id = ?");
//             $stmtUpdateDest->bind_param("iii", $newQuantityDest, $selectedProduct, $selectedDestination);
//             $stmtUpdateDest->execute();
//         } else {
//             // If product does not exist at the destination, insert a new record
//             // Fetch pieces_per_packet for the current product from an existing entry
//             $stmtGetPieces = $mysqli->prepare("SELECT pieces_per_packet FROM product_stock WHERE product_id = ?");
//             $stmtGetPieces->bind_param("i", $selectedProduct);
//             $stmtGetPieces->execute();
//             $resultPieces = $stmtGetPieces->get_result();
//             $piecesPerPacket = $resultPieces->fetch_assoc()['pieces_per_packet']; // Assuming there's at least one entry elsewhere

//             // Insert new record at the destination with the pieces_per_packet value
//             $stmtInsertDest = $mysqli->prepare("INSERT INTO product_stock (product_id, storage_area_id, quantity, pieces_per_packet) VALUES (?, ?, ?, ?)");
//             $stmtInsertDest->bind_param("iiii", $selectedProduct, $selectedDestination, $totalPieces, $piecesPerPacket);
//             $stmtInsertDest->execute();
//         }

//         // Commit transaction if all operations succeed
//         $mysqli->commit();
//         $successMessage = "Stock transferred successfully!";
//         } catch (Exception $e) {
//             // Rollback transaction if any operation fails
//             $mysqli->rollback();
//             $errorMessage = "Transaction failed: " . $e->getMessage();
//         }

//     }

// }

// ?>


<?php
include "../db_connect.php";

$storageAreas = $products = [];
$selectedSource = $selectedProduct = $selectedDestination = '';
$productDetails = [];
$successMessage = $errorMessage = '';

// Fetch storage areas
$result = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas");
while ($row = $result->fetch_assoc()) {
    $storageAreas[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selectedSource = $_POST['source_area'] ?? '';

    if (!empty($selectedSource)) {
        $stmt = $mysqli->prepare("SELECT ps.product_id, p.product_name, ps.quantity, ps.pieces_per_packet FROM product_stock ps JOIN products p ON ps.product_id = p.product_id WHERE storage_area_id = ?");
        $stmt->bind_param("i", $selectedSource);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($productId, $productName, $quantity, $piecesPerPacket);
        while ($stmt->fetch()) {
            $products[] = ['product_id' => $productId, 'product_name' => $productName, 'quantity' => $quantity, 'pieces_per_packet' => $piecesPerPacket];
        }
        $stmt->close();
    }

    $selectedProduct = $_POST['product_id'] ?? '';
    $selectedDestination = $_POST['destination_area'] ?? '';

    if (!empty($selectedProduct)) {
        foreach ($products as $product) {
            if ($product['product_id'] == $selectedProduct) {
                $productDetails = $product;
                break;
            }
        }
    }

    if (isset($_POST['transfer'])) {
        $packets = $_POST['packets'] ?? 0;
        $pieces = $_POST['pieces'] ?? 0;
        $totalPieces = ($packets * $productDetails['pieces_per_packet']) + $pieces;

        if ($totalPieces > $productDetails['quantity']) {
            $errorMessage = "Insufficient stock. Cannot transfer more than available.";
        } else {
            $mysqli->begin_transaction();
            try {
                $newQuantitySource = $productDetails['quantity'] - $totalPieces;
                $stmtUpdateSource = $mysqli->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ? AND storage_area_id = ?");
                $stmtUpdateSource->bind_param("iii", $newQuantitySource, $selectedProduct, $selectedSource);
                $stmtUpdateSource->execute();

                $stmtCheckDest = $mysqli->prepare("SELECT quantity FROM product_stock WHERE product_id = ? AND storage_area_id = ?");
                $stmtCheckDest->bind_param("ii", $selectedProduct, $selectedDestination);
                $stmtCheckDest->execute();
                $stmtCheckDest->store_result();
                if ($stmtCheckDest->num_rows() > 0) {
                    $stmtCheckDest->bind_result($destQuantity);
                    $stmtCheckDest->fetch();
                    $newQuantityDest = $destQuantity + $totalPieces;
                    $stmtUpdateDest = $mysqli->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ? AND storage_area_id = ?");
                    $stmtUpdateDest->bind_param("iii", $newQuantityDest, $selectedProduct, $selectedDestination);
                    $stmtUpdateDest->execute();
                } else {
                    $stmtInsertDest = $mysqli->prepare("INSERT INTO product_stock (product_id, storage_area_id, quantity, pieces_per_packet) VALUES (?, ?, ?, ?)");
                    $stmtInsertDest->bind_param("iiii", $selectedProduct, $selectedDestination, $totalPieces, $productDetails['pieces_per_packet']);
                    $stmtInsertDest->execute();
                }
                
                
               // === Transaction Logging ===
session_start();
// === Transaction Logging with Names ===
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];

    // Fetch user name
    $stmtUser = $mysqli->prepare("SELECT name FROM users WHERE user_id = ?");
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $stmtUser->bind_result($user_name);
    $stmtUser->fetch();
    $stmtUser->close();

    // Fetch source area name
    $stmtArea1 = $mysqli->prepare("SELECT storage_area_name FROM storage_areas WHERE storage_area_id = ?");
    $stmtArea1->bind_param("i", $selectedSource);
    $stmtArea1->execute();
    $stmtArea1->bind_result($sourceAreaName);
    $stmtArea1->fetch();
    $stmtArea1->close();

    // Fetch destination area name
    $stmtArea2 = $mysqli->prepare("SELECT storage_area_name FROM storage_areas WHERE storage_area_id = ?");
    $stmtArea2->bind_param("i", $selectedDestination);
    $stmtArea2->execute();
    $stmtArea2->bind_result($destAreaName);
    $stmtArea2->fetch();
    $stmtArea2->close();

    // Prepare descriptions
    $descSource = "Transferred $packets box and $pieces piece(s) from $sourceAreaName to $destAreaName by $user_name";
    $descDest = "Received $packets box and $pieces piece(s) in $destAreaName from $sourceAreaName by $user_name";

    // Log SUBTRACT from source
    $typeSubtract = 'Subtract';
    $stmtLog1 = $mysqli->prepare("INSERT INTO transactions (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $stmtLog1->bind_param("iiisis", $user_id, $selectedProduct, $selectedSource, $typeSubtract, $totalPieces, $descSource);
    $stmtLog1->execute();
    $stmtLog1->close();

    // Log ADD to destination
    $typeAdd = 'Add';
    $stmtLog2 = $mysqli->prepare("INSERT INTO transactions (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
    $stmtLog2->bind_param("iiisis", $user_id, $selectedProduct, $selectedDestination, $typeAdd, $totalPieces, $descDest);
    $stmtLog2->execute();
    $stmtLog2->close();
}

// Commit after logging
$mysqli->commit();
$successMessage = "Stock transferred successfully!";
                
                
                
                
            } catch (Exception $e) {
                $mysqli->rollback();
                $errorMessage = "Transaction failed: " . $e->getMessage();
            }
        }
    }
}
?>

<!-- Remaining HTML and script tags should be included here. -->

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
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.3.0/css/all.css">
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
 
    </head>
    <body class="sb-nav-fixed">
    <?php  include 'navbar.php'; ?>
 

            <!-- ---------------------------- -->
            <div id="layoutSidenav_content">
                <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-2 mb-4 text-center">Stock Transfer</h1>
                    <form action="" method="post">
                    <div class="mb-3">
                    <label for="source_area" class="form-label">
                    Source Storage Area:
                    <span class="badge rounded-circle bg-danger text-white">
                        <i class="fas fa-minus"></i>
                    </span>
                    </label>
                    <select id="source_area" name="source_area" class="form-select shadow" onchange="this.form.submit()">
                        <option value="">Select Area</option>
                        <?php foreach ($storageAreas as $area): ?>
                            <option value="<?= $area['storage_area_id']; ?>" <?= $selectedSource == $area['storage_area_id'] ? 'selected' : ''; ?>>
                                <?= $area['storage_area_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="product_id" class="form-label">Product:</label>
                    <select id="product_id" name="product_id" class="form-select shadow" onchange="this.form.submit()" <?= empty($products) ? 'disabled' : ''; ?>>
                        <option value="">Select Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['product_id']; ?>" <?= $selectedProduct == $product['product_id'] ? 'selected' : ''; ?>>
                                <?= $product['product_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="destination_area" class="form-label">
                        Destination Storage Area:
                        <span class="badge rounded-circle bg-success text-white">
                            <i class="fas fa-plus"></i>
                        </span>
                    </label>
                    <select id="destination_area" name="destination_area" class="form-select shadow" <?= empty($selectedSource) ? 'disabled' : ''; ?>>
                        <option value="">Select Destination Area</option>
                        <?php foreach ($storageAreas as $area): ?>
                            <option value="<?= $area['storage_area_id']; ?>">
                                <?= $area['storage_area_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Current Stock:</label>
                    <input type="text" class="form-control shadow" value="<?= isset($productDetails['quantity']) ? intdiv($productDetails['quantity'], $productDetails['pieces_per_packet']) . ' packets / ' . ($productDetails['quantity'] % $productDetails['pieces_per_packet']) . ' pieces' : ''; ?>" disabled>
                </div>
            
            <div class="mb-3">
                    <label for="packets" class="form-label">Packets:</label>
                    <input type="number" class="form-control shadow" id="packets" name="packets" required min="0">
                </div>
                <div class="mb-3">
                    <label for="pieces" class="form-label">Pieces:</label>
                    <input type="number" class="form-control shadow" id="pieces" name="pieces" required min="0">
                </div>
               
            <div class="text-center">
                <button type="submit" name="transfer" class="btn btn-primary mt-4">Transfer Stock</button>
            </div>
                    </form>
                </div>

                </main> 
                <!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Transaction Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage"></p>
            </div>
        </div>
    </div>
</div>


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
document.addEventListener("DOMContentLoaded", function() {
    var message = '';
    var successModal = new bootstrap.Modal(document.getElementById('successModal'));

    <?php if (!empty($successMessage)): ?>
        message = "<?php echo $successMessage; ?>";
    <?php elseif (!empty($errorMessage)): ?>
        message = "<?php echo $errorMessage; ?>";
    <?php endif; ?>

    if (message) {
        document.getElementById('successMessage').textContent = message;
        successModal.show();

        // Optionally, hide the success modal after a delay
        setTimeout(function() {
            successModal.hide();
        }, 3000);
    }
});
</script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
