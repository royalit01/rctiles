<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../db_connect.php';

// Check user session
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$successMessage = '';
$errorMessage = '';
$selectedStorageArea = 0;

// Fetch all storage areas for dropdown
$storageAreas = [];
$res = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas ORDER BY storage_area_name ASC");
while ($row = $res->fetch_assoc()) {
    $storageAreas[] = $row;
}

// Handle storage area selection (GET or POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['storage_area_id'])) {
    $selectedStorageArea = (int)$_POST['storage_area_id'];
} elseif (isset($_GET['storage_area_id'])) {
    $selectedStorageArea = (int)$_GET['storage_area_id'];
}

// Handle Add Stock for multiple products
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_stock'])) {
    $user_id = (int)$_SESSION['user_id'];
    $successMessage = '';
    $errorMessage = '';

    foreach ($_POST['product_id'] as $index => $product_id) {
        $product_id = (int)$product_id;
        $packets = isset($_POST['packets'][$index]) ? (int)$_POST['packets'][$index] : 0;
        $pieces = isset($_POST['pieces'][$index]) ? (int)$_POST['pieces'][$index] : 0;
        $remark = trim($_POST['remark'][$index] ?? '');

        if ($packets <= 0 && $pieces <= 0) {
            continue; // Skip if nothing to add
        }

        // Fetch pieces_per_packet for this product
        $stmt = $mysqli->prepare("SELECT pieces_per_packet FROM product_stock WHERE product_id = ? AND storage_area_id = ? LIMIT 1");
        if (!$stmt) {
            $errorMessage .= "❌ DB error: " . $mysqli->error;
            continue;
        }
        $stmt->bind_param("ii", $product_id, $selectedStorageArea);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $ppp = (int)$row['pieces_per_packet'];
            $totalAdded = $packets * $ppp + $pieces;

            // Check if stock exists for this product and storage area
            $stmt = $mysqli->prepare("SELECT quantity FROM product_stock WHERE product_id = ? AND storage_area_id = ?");
            if (!$stmt) {
                $errorMessage .= "❌ DB error: " . $mysqli->error;
                continue;
            }
            $stmt->bind_param("ii", $product_id, $selectedStorageArea);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                $existing = $res->fetch_assoc()['quantity'] + $totalAdded;
                $stmt = $mysqli->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ? AND storage_area_id = ?");
                if (!$stmt) {
                    $errorMessage .= "❌ DB error: " . $mysqli->error;
                    continue;
                }
                $stmt->bind_param("iii", $existing, $product_id, $selectedStorageArea);
            } else {
                $newQty = $totalAdded;
                $stmt = $mysqli->prepare("INSERT INTO product_stock (product_id, storage_area_id, quantity, pieces_per_packet) VALUES (?, ?, ?, ?)");
                if (!$stmt) {
                    $errorMessage .= "❌ DB error: " . $mysqli->error;
                    continue;
                }
                // Fetch pieces_per_packet from products table or default to 1 if not found
                $stmt->bind_param("iiii", $product_id, $selectedStorageArea, $newQty, $ppp);
            }
            $stmt->execute();

            // Log transaction with description
            $type = 'Add';
            $description = $remark !== '' ? $remark : "Stock added";
            $stmt = $mysqli->prepare("INSERT INTO transactions (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
            if (!$stmt) {
                $errorMessage .= "❌ DB error: " . $mysqli->error;
                continue;
            }
            $stmt->bind_param("iiisis", $user_id, $product_id, $selectedStorageArea, $type, $totalAdded, $description);
            $stmt->execute();

            $successMessage .= "✅ Stock added for product ID $product_id. ";
        } else {
            $errorMessage .= "❌ No 'pieces per packet' found for product ID $product_id in selected storage area. ";
        }
    }
}

// Fetch products for selected storage area
$products = [];
if ($selectedStorageArea > 0) {
    $stmt = $mysqli->prepare("SELECT p.product_id, p.product_name FROM products p JOIN product_stock ps ON p.product_id = ps.product_id WHERE ps.storage_area_id = ? ORDER BY p.product_name ASC");
    $stmt->bind_param("i", $selectedStorageArea);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Add Stock - Single Storage Area</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
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
    </style>
</head>
<body class="sb-nav-fixed">
<?php include 'navbar.php'; ?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid my-4" style="max-width: 1000px;">
            <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto">
                <h2 class="mt-1 mb-4 fw-bold fs-2 text-center">Add Stock</h2>

                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>
                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                <?php endif; ?>

                <!-- Storage Area Selection Form -->
                <form method="POST" id="storageSelectForm">
                    <div class="mb-3">
                        <label for="storage_area_id" class="form-label fw-medium">Select Storage Area:</label>
                        <select name="storage_area_id" id="storage_area_id" class="form-select" required onchange="document.getElementById('storageSelectForm').submit()">
                            <option value="">-- Select Storage Area --</option>
                            <?php foreach ($storageAreas as $area): ?>
                                <option value="<?= $area['storage_area_id']; ?>" <?= $selectedStorageArea == $area['storage_area_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($area['storage_area_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <?php if ($selectedStorageArea > 0): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="storage_area_id" value="<?= $selectedStorageArea ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Packets to Add</th>
                                        <th>Pieces to Add</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($products) > 0): ?>
                                        <?php foreach ($products as $index => $product): ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($product['product_name']); ?>
                                                    <input type="hidden" name="product_id[]" value="<?= $product['product_id']; ?>">
                                                </td>
                                                <td><input type="number" name="packets[]" min="0" value="0" class="form-control"></td>
                                                <td><input type="number" name="pieces[]" min="0" value="0" class="form-control"></td>
                                                <td><input type="text" name="remark[]" class="form-control" placeholder="Optional remark"></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center">No products found in this storage area.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            <button type="submit" name="add_stock" class="btn gradient-btn w-25 text-white p-2 position-relative">
                                <i class="fas fa-plus text-white me-2"></i> Add Stock
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>