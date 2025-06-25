<?php 
include "../db_connect.php"; // Include your DB connection settings'

session_start();

$storageAreas = $categories = $products = [];
$selectedStorageArea = $selectedCategory = '';
$productDetails = [];
$searchQuery = '';
$successMessage = '';
$errorMessage = '';

// Fetch Storage Areas
$result = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $storageAreas[] = $row;
    }
    $result->free();
} else {
    die("Error fetching storage areas: " . $mysqli->error);
}

// Check for GET parameters to restore state or display messages
if (isset($_GET['area'])) {
    $selectedStorageArea = $_GET['area'];
}
if (isset($_GET['category'])) {
    $selectedCategory = $_GET['category'];
}
if (isset($_GET['success'])) {
    $successMessage = "Stock updated successfully.";
}

// POST request processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['storage_area'])) {
        $selectedStorageArea = $_POST['storage_area'];
    }
    if (isset($_POST['category'])) {
        $selectedCategory = $_POST['category'];
    }

    if (isset($_POST['minus_stock'])) {
        $message = handleStockMinus($mysqli, $_POST, $selectedStorageArea);
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF'] . "?success=1&area=" . urlencode($selectedStorageArea) . "&category=" . urlencode($selectedCategory));
        exit();
    }
}

// Fetch categories and products if an area is selected
if (!empty($selectedStorageArea)) {
    list($categories, $products) = fetchCategoriesAndProducts($mysqli, $selectedStorageArea, $selectedCategory);
}




function handleStockMinus($mysqli, $postData, $selectedStorageArea) {
    $successMessage = "";

    if (!isset($_SESSION['user_id'])) {
        error_log("❌ User not logged in.");
        return "❌ User not logged in.";
    }

    $user_id = (int)$_SESSION['user_id'];

    foreach ($postData['product_id'] as $index => $product_id) {
        $packets = (int) $postData['packets'][$index];
        $pieces = (int) $postData['pieces'][$index];

        if ($packets <= 0 && $pieces <= 0) continue;

        $stmt = $mysqli->prepare("SELECT quantity, pieces_per_packet FROM product_stock WHERE product_id = ? AND storage_area_id = ?");
        if (!$stmt) {
            error_log("Prepare SELECT failed: " . $mysqli->error);
            continue;
        }

        $stmt->bind_param("ii", $product_id, $selectedStorageArea);
        if (!$stmt->execute()) {
            error_log("Execute SELECT failed: " . $stmt->error);
            $stmt->close();
            continue;
        }

        $stmt->bind_result($quantity, $pieces_per_packet);

        if ($stmt->fetch()) {
            error_log("✅ Product ID $product_id - Qty: $quantity, Per packet: $pieces_per_packet");
            $stmt->close();

            $totalToRemove = $packets * $pieces_per_packet + $pieces;

            if ($totalToRemove <= $quantity) {
                $newQuantity = $quantity - $totalToRemove;

                $updateStmt = $mysqli->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ? AND storage_area_id = ?");
                if (!$updateStmt) {
                    error_log("Prepare UPDATE failed: " . $mysqli->error);
                    continue;
                }

                $updateStmt->bind_param("iii", $newQuantity, $product_id, $selectedStorageArea);
                if (!$updateStmt->execute()) {
                    error_log("Execute UPDATE failed: " . $updateStmt->error);
                } else {
                    error_log("✅ Stock updated for product ID $product_id to $newQuantity");
                }
                $updateStmt->close();

                // Log transaction with description
                $transactionType = 'Subtract';
                // $description = "Subtracted $packets box and $pieces piece(s) by user ID $user_id";
                $description = "Stock Minus";
                $logStmt = $mysqli->prepare("INSERT INTO transactions (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
                if (!$logStmt) {
                    error_log("Prepare INSERT log failed: " . $mysqli->error);
                    continue;
                }

                $logStmt->bind_param("iiisis", $user_id, $product_id, $selectedStorageArea, $transactionType, $totalToRemove, $description);
                if (!$logStmt->execute()) {
                    error_log("Execute INSERT log failed: " . $logStmt->error);
                } else {
                    error_log("✅ Transaction logged for product ID $product_id");
                }
                $logStmt->close();

                $successMessage .= "✔️ Stock subtracted for product ID $product_id. ";
            } else {
                error_log("⚠️ Not enough stock for product ID $product_id: Requested $totalToRemove, Available $quantity");
                $successMessage .= "⚠️ Not enough stock for product ID $product_id. ";
            }
        } else {
            error_log("❌ No stock found for product ID $product_id in storage area $selectedStorageArea");
            $stmt->close();
            $successMessage .= "❌ Could not fetch stock info for product ID $product_id. ";
        }
    }

    return $successMessage;
}








// function handleStockMinus($mysqli, $postData, $selectedStorageArea) {
//     $successMessage = "";

//     if (!isset($_SESSION['user_id'])) {
//         error_log("❌ User not logged in.");
//         return "❌ User not logged in.";
//     }

//     $user_id = (int)$_SESSION['user_id'];

//     foreach ($postData['product_id'] as $index => $product_id) {
//         $packets = (int) $postData['packets'][$index];
//         $pieces = (int) $postData['pieces'][$index];

//         if ($packets <= 0 && $pieces <= 0) continue;

//         $stmt = $mysqli->prepare("SELECT quantity, pieces_per_packet FROM product_stock WHERE product_id = ? AND storage_area_id = ?");
//         if (!$stmt) {
//             error_log("Prepare SELECT failed: " . $mysqli->error);
//             continue;
//         }

//         $stmt->bind_param("ii", $product_id, $selectedStorageArea);
//         if (!$stmt->execute()) {
//             error_log("Execute SELECT failed: " . $stmt->error);
//             $stmt->close();
//             continue;
//         }

//         $stmt->bind_result($quantity, $pieces_per_packet);

//         if ($stmt->fetch()) {
//             error_log("✅ Product ID $product_id - Qty: $quantity, Per packet: $pieces_per_packet");
//             $stmt->close();

//             $totalToRemove = $packets * $pieces_per_packet + $pieces;

//             if ($totalToRemove <= $quantity) {
//                 $newQuantity = $quantity - $totalToRemove;

//                 $updateStmt = $mysqli->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ? AND storage_area_id = ?");
//                 if (!$updateStmt) {
//                     error_log("Prepare UPDATE failed: " . $mysqli->error);
//                     continue;
//                 }

//                 $updateStmt->bind_param("iii", $newQuantity, $product_id, $selectedStorageArea);
//                 if (!$updateStmt->execute()) {
//                     error_log("Execute UPDATE failed: " . $updateStmt->error);
//                 } else {
//                     error_log("✅ Stock updated for product ID $product_id to $newQuantity");
//                 }
//                 $updateStmt->close();

//                 // Log transaction
//                 $transactionType = 'Subtract';
//                 $logStmt = $mysqli->prepare("INSERT INTO transactions (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date) VALUES (?, ?, ?, ?, ?, NOW())");
//                 if (!$logStmt) {
//                     error_log("Prepare INSERT log failed: " . $mysqli->error);
//                     continue;
//                 }

//                 $logStmt->bind_param("iiisi", $user_id, $product_id, $selectedStorageArea, $transactionType, $totalToRemove);
//                 if (!$logStmt->execute()) {
//                     error_log("Execute INSERT log failed: " . $logStmt->error);
//                 } else {
//                     error_log("✅ Transaction logged for product ID $product_id");
//                 }
//                 $logStmt->close();

//                 $successMessage .= "✔️ Stock subtracted for product ID $product_id. ";
//             } else {
//                 error_log("⚠️ Not enough stock for product ID $product_id: Requested $totalToRemove, Available $quantity");
//                 $successMessage .= "⚠️ Not enough stock for product ID $product_id. ";
//             }
//         } else {
//             error_log("❌ No stock found for product ID $product_id in storage area $selectedStorageArea");
//             $stmt->close();
//             $successMessage .= "❌ Could not fetch stock info for product ID $product_id. ";
//         }
//     }

//     return $successMessage;
// }

function fetchCategoriesAndProducts($mysqli, $selectedStorageArea, $selectedCategory) {
    $categories = [];
    $products = [];
    $stmt = $mysqli->prepare("SELECT DISTINCT c.category_id, c.category_name FROM category c JOIN products p ON c.category_id = p.category_id JOIN product_stock ps ON p.product_id = ps.product_id WHERE ps.storage_area_id = ?");
    $stmt->bind_param("i", $selectedStorageArea);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($category_id, $category_name);
    while ($stmt->fetch()) {
        $categories[] = ['category_id' => $category_id, 'category_name' => $category_name];
    }
    $stmt->free_result();

    $query = "SELECT p.product_id, p.product_name, ps.quantity, ps.pieces_per_packet FROM products p JOIN product_stock ps ON p.product_id = ps.product_id WHERE ps.storage_area_id = ?";
    if ($selectedCategory) {
        $query .= " AND p.category_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $selectedStorageArea, $selectedCategory);
    } else {
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $selectedStorageArea);
    }
    $stmt->execute();
    $stmt->bind_result($product_id, $product_name, $quantity, $pieces_per_packet);
    while ($stmt->fetch()) {
        $products[] = ['product_id' => $product_id, 'product_name' => $product_name, 'quantity' => $quantity, 'pieces_per_packet' => $pieces_per_packet];
    }
    $stmt->close();

    return [$categories, $products];
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
  <style>
        .card-form {
            background-color: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            padding: 2rem;
        }
        .form-control, .form-select {
            border-radius: 0.75rem;
            min-height: 40px;
            margin-top: 0.3rem;
        }
        .search-box {
            max-width: 410px;
            margin-bottom: 1.5rem;
        }
        .stock-input {
            width: 100px !important;
            display: inline-block !important;
        }
        .action-btn {
            border-radius: 0.6rem;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
    </style>
    </head>
    <body class="sb-nav-fixed">
    <?php  include 'navbar.php'; ?>
 

            <!-- ---------------------------- -->
            <div id="layoutSidenav_content">
                <main>
                <div class="container-fluid  px-4  p-2">
   <div class="card border-0 shadow my-3 rounded-3 p-4 bg-white mx-auto" style="max-width: 950px; min-height: 560px;">

                    <h2 class="mb-4 text-center m-4 "><b>Minus Stock</b></h2>
                    <form action="" method="post">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="storage_area">Storage Area:</label>
                                <select id="storage_area" name="storage_area" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select Storage Area</option>
                                    <?php foreach ($storageAreas as $area): ?>
                                        <option value="<?= $area['storage_area_id']; ?>" <?= $selectedStorageArea == $area['storage_area_id'] ? 'selected' : ''; ?>>
                                            <?= $area['storage_area_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="category">Category:</label>
                                <select id="category" name="category" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['category_id']; ?>" <?= $selectedCategory == $category['category_id'] ? 'selected' : ''; ?>>
                                            <?= $category['category_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search products..." onkeyup="filterTable()" style="max-width: 410px; width: 100%;">
                                <!-- Product Table -->
               <form method="POST">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $index => $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_name']); ?></td>
                        <td><?= intdiv($product['quantity'], $product['pieces_per_packet']) . ' packets / ' . ($product['quantity'] % $product['pieces_per_packet']) . ' pieces'; ?></td>
                        <td>
                            <input type="hidden" name="product_id[<?= $index ?>]" value="<?= $product['product_id']; ?>">
                            <input type="number" name="packets[<?= $index ?>]" placeholder="Packets" min="0" class="form-control" style="width: 80px; display: inline-block;">
                            <input type="number" name="pieces[<?= $index ?>]" placeholder="Pieces" min="0" class="form-control" style="width: 80px; display: inline-block;">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <center><button type="submit" name="minus_stock" class="btn btn-danger">Update Stock</button></center>
    </div>
</form>

                            </div>
                        </div>
                    </form>
                </div>
                <!-- Success Modal -->
                <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="successModalLabel">Success</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p id="successMessage"><?= htmlspecialchars($successMessage); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                    </div>
                </main> 

                
            </div>
         <!-- ---------------------------- -->   
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="../assets/demo/chart-area-demo.js"></script>
    <script src="../assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="../js/datatables-simple-demo.js"></script>
    <script>
    


document.addEventListener("DOMContentLoaded", function() {
    if ("<?= !empty($successMessage); ?>") {
        var successModal = new bootstrap.Modal(document.getElementById("successModal"));
        successModal.show();
        setTimeout(function() {
            successModal.hide();
        }, 3000);
    }
});

           // Show success modal
            <?php if (!empty($successMessage)) { ?>
                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("successMessage").textContent = "<?php echo $successMessage; ?>";
                    const successModal = new bootstrap.Modal(document.getElementById("successModal"));
                    successModal.show();

                    // Hide the success modal after 3 seconds
                    setTimeout(function() {
                        successModal.hide();
                    }, 3000);
                });
            <?php } ?>

        function filterTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementsByTagName("table")[0];
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }
    </script>
    </body>
</html>
