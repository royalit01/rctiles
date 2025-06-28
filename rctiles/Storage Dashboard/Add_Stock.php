
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

// Fetch dropdown data
$productResult = $mysqli->query("SELECT product_id, product_name FROM products");
$storageResult = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas");

// Handle Add Stock
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_stock'])) {
    $product_id = (int)$_POST['product_id'];
    $storage_area_id = (int)$_POST['storage_area_id'];
    $packets = (int)$_POST['packets'];
    $pieces = (int)$_POST['pieces'];
    $user_id = (int)$_SESSION['user_id'];

    // Fetch pieces_per_packet
    $stmt = $mysqli->prepare("SELECT pieces_per_packet FROM product_stock WHERE product_id = ? LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $ppp = (int)$row['pieces_per_packet'];
        $totalAdded = $packets * $ppp + $pieces;

        // Check if stock exists
        $stmt = $mysqli->prepare("SELECT quantity FROM product_stock WHERE product_id = ? AND storage_area_id = ?");
        $stmt->bind_param("ii", $product_id, $storage_area_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $existing = $res->fetch_assoc()['quantity'] + $totalAdded;
            $stmt = $mysqli->prepare("UPDATE product_stock SET quantity = ? WHERE product_id = ? AND storage_area_id = ?");
            $stmt->bind_param("iii", $existing, $product_id, $storage_area_id);
        } else {
            $newQty = $totalAdded;
            $stmt = $mysqli->prepare("INSERT INTO product_stock (product_id, storage_area_id, quantity, pieces_per_packet) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $product_id, $storage_area_id, $newQty, $ppp);
        }
        $stmt->execute();

        // Log transaction with description
        $type = 'Add';
        $description = "Stock added";
        $stmt = $mysqli->prepare("INSERT INTO transactions (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("iiisis", $user_id, $product_id, $storage_area_id, $type, $totalAdded, $description);
        $stmt->execute();

        $successMessage = "✅ Stock added successfully!";
    } else {
        $errorMessage = "❌ No 'pieces per packet' found for this product.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

    <div class="container-fluid mt-4">
        <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto" style="max-width: 950px;">
        <h2 class="mt-1 mb-2 fw-bold fs-2 text-center">Add Stock</h2>

        <form class="p-2" method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-medium text-secondary"style="  font-weight: 550;"">Select Product:</label>
                <select class="form-select rounded-3 py-2" name="product_id" id="productDropdown" required onchange="fetchStock()">
                    <option value=""> Choose Product </option>
                    <?php while ($row = $productResult->fetch_assoc()): ?>
                        <option value="<?= $row['product_id'] ?>"><?= $row['product_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium text-secondary"style="  font-weight: 550;"">Select Storage Area:</label>
                <select class="form-select" name="storage_area_id" id="storageDropdown" required onchange="fetchStock()">
                    <option value=""> Choose Storage </option>
                    <?php while ($row = $storageResult->fetch_assoc()): ?>
                        <option value="<?= $row['storage_area_id'] ?>"><?= $row['storage_area_name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium text-secondary"style="  font-weight: 550;"">Current Stock:</label>
                <input type="text" class="form-control" id="currentStock" readonly>
            </div>

            <div class="mb-3">
                <label>Packets to Add:</label>
                <input type="number" name="packets" class="form-control" min="0" required value="0">
            </div>

            <div class="mb-3">
                <label>Pieces to Add:</label>
                <input type="number" name="pieces" class="form-control" min="0" required value="0">
            </div>

             <div class="mb-3">
                <label class="form-label fw-medium text-secondary"style="  font-weight: 550;"">Remark:</label>
                <input type="text" class="form-control" id="remark">
            </div>

<div class="d-flex justify-content-center">
    <button type="submit" name="add_stock" class="btn gradient-btn w-20 text-white p-2  position-relative">
        <i class="fas fa-plus text-white me-2"></i> Add Stock
    </button>
</div>
        </form>
                    </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Success</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="successMessageModal"></div>
        </div>
      </div>
    </div>
      </main> 

                
            </div>
        </div>


    <script>
        function fetchStock() {
            const productId = $('#productDropdown').val();
            const storageId = $('#storageDropdown').val();

            if (productId && storageId) {
                $.post('ajax_fetch_stock.php', {
                    product_id: productId,
                    storage_area_id: storageId
                }, function(data) {
                    $('#currentStock').val(data);
                }).fail(function() {
                    $('#currentStock').val('Error fetching stock');
                });
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            const msg = "<?= $successMessage ?>";
            if (msg !== '') {
                $('#successMessageModal').text(msg);
                const modal = new bootstrap.Modal(document.getElementById('successModal'));
                modal.show();
                setTimeout(() => modal.hide(), 3000);
            }
        });
    </script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/chart-area-demo.js"></script>
        <script src="../assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>

</html>
