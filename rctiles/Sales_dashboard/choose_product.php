<?php 
include '../db_connect.php'; 
session_start();

$detailIndex = isset($_GET['detailIndex']) ? (int)$_GET['detailIndex'] : 0;
$categoryId  = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$areaParam   = isset($_GET['area']) ? floatval($_GET['area']) : 0.0;

$products = [];
if ($categoryId > 0) {
    $sql = "SELECT product_id, product_name, description, price, product_image, area FROM products WHERE category_id = ? AND status = 'Active'";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}
$mysqli->close();
$_SESSION['selected_products'] = $_SESSION['selected_products'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Select a Product</h2>
        <?php if (empty($products)): ?>
            <p>No matching products found.</p>
        <?php else: ?>
            <?php foreach ($products as $prod): ?>
                <div class="card p-3 mb-2">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="<?php echo $prod['product_image']; ?>" class="img-fluid">
                        </div>
                        <div class="col-md-6">
                            <h5><?php echo $prod['product_name']; ?></h5>
                            <p><?php echo $prod['description']; ?></p>
                            <p><strong>Price:</strong> ₹<?php echo number_format($prod['price'], 2); ?></p>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <button class="btn btn-primary" onclick="selectProduct(<?php echo $prod['product_id']; ?>, '<?php echo addslashes($prod['product_name']); ?>')">Select</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script>
        function selectProduct(productId, productName) {
            window.opener.setChosenProduct(productId, productName);
            window.close();
        }
    </script>
</body>
</html>
