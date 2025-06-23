<?php
// include "../db_connect.php"; // Include your database connection

// // Initialize arrays for categories and suppliers
// $categories = [];
// $suppliers = [];

// // Fetch categories
// $category_query = "SELECT category_id, category_name FROM category ORDER BY category_name";
// $category_result = $mysqli->query($category_query);
// if ($category_result) {
//     while ($row = $category_result->fetch_assoc()) {
//         $categories[] = $row;
//     }
// } else {
//     echo "Error fetching categories: " . $mysqli->error;
// }

// // Fetch suppliers
// $supplier_query = "SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name";
// $supplier_result = $mysqli->query($supplier_query);
// if ($supplier_result) {
//     while ($row = $supplier_result->fetch_assoc()) {
//         $suppliers[] = $row;
//     }
// } else {
//     echo "Error fetching suppliers: " . $mysqli->error;
// }

// $product = null; // Initialize the product variable

// // Check if the product ID is provided
// if (isset($_GET['product_id'])) {
//     $product_id = $_GET['product_id'];

//     // Fetch the current product details including the product image
//     $stmt = $mysqli->prepare("SELECT p.product_name, p.area, p.price, p.cost_price, 
//                                      p.category_id, p.supplier_id, p.description, 
//                                      p.product_image, ps.storage_area_id
//                               FROM products p 
//                               JOIN product_stock ps ON p.product_id = ps.product_id 
//                               WHERE p.product_id = ?");
//     $stmt->bind_param("i", $product_id);

//     if ($stmt->execute()) {
//         $stmt->bind_result(
//             $productName,
//             $area,
//             $price,
//             $costPrice,
//             $category_id,
//             $supplier_id,
//             $description,
//             $productImage,
//             $storageAreaId
//         );

//         if ($stmt->fetch()) {
//             $product = [
//                 'product_name' => $productName,
//                 'area' => $area,
//                 'price' => $price,
//                 'cost_price' => $costPrice,
//                 'category_id' => $category_id,
//                 'supplier_id' => $supplier_id,
//                 'description' => $description,
//                 'product_image' => $productImage,
//                 'storage_area_id' => $storageAreaId
//             ];
//         } else {
//             echo "No product found with the given ID.";
//         }
//     } else {
//         echo "Error executing query: " . $mysqli->error;
//     }
//     $stmt->close();
// } else {
//     echo "Product ID is missing.";
// }

// // Handling form submission for updating the product
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
//     // Extract posted data
//     $productName = $_POST['productName'] ?? '';
//     $area = $_POST['area'] ?? '';
//     $price = $_POST['price'] ?? 0;
//     $costPrice = $_POST['costPrice'] ?? 0;
//     $category_id = $_POST['category_id'] ?? null;
//     $supplier_id = $_POST['supplier_id'] ?? null;
//     $description = $_POST['description'] ?? '';
//     $productImage = $_FILES['productImage']['name'] ?? '';

//     // Handle file upload
//     if (!empty($productImage)) {
//         $target_directory = "../uploads/";
//         $target_file = $target_directory . basename($_FILES['productImage']['name']);
//         move_uploaded_file($_FILES['productImage']['tmp_name'], $target_file);
//     } else {
//         $target_file = $product['product_image']; // Use existing image if new one is not uploaded
//     }

//     // Update the product
//     $update_stmt = $mysqli->prepare("UPDATE products p 
//                                      JOIN product_stock ps ON p.product_id = ps.product_id 
//                                      SET p.product_name = ?, p.area = ?, p.price = ?, 
//                                          p.cost_price = ?, p.category_id = ?, p.supplier_id = ?, 
//                                          p.description = ?, p.product_image = ? 
//                                      WHERE p.product_id = ?");
//     $update_stmt->bind_param("ssddiissi", $productName, $area, $price, $costPrice, $category_id, $supplier_id, $description, $target_file, $product_id);
//     if ($update_stmt->execute()) {
//         // Start the session to use session variables
//         session_start();
//         $_SESSION['updateMessage'] = 'Product updated successfully.';
    
//         // Redirect to edit_product.php or any other page
//         header("Location: ../Storage%20Dashboard/Edit_Product.php");
//         exit(); // Don't forget to call exit after header redirection
//     } else {
//         session_start();
//         $_SESSION['updateMessage'] = 'Error updating product: ' . $update_stmt->error;
//         header("Location: ../Storage%20Dashboard/Edit_Product.php");
//         exit();
//     }
//     $update_stmt->close();
// }

// $mysqli->close(); // Close the database connection
?>
<?php
session_start();
include "../db_connect.php";

// Ensure transaction_type enum includes 'Edit'
// ALTER TABLE transactions MODIFY transaction_type ENUM('Add','Subtract','Delete','Edit') DEFAULT NULL;

// Initialize arrays for categories and suppliers
$categories = [];
$suppliers  = [];

// Fetch categories
$category_result = $mysqli->query("SELECT category_id, category_name FROM category ORDER BY category_name");
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row;
}

// Fetch suppliers
$supplier_result = $mysqli->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name");
while ($row = $supplier_result->fetch_assoc()) {
    $suppliers[] = $row;
}

$product = null;

// Check if product_id provided
if (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
    $stmt = $mysqli->prepare("
        SELECT 
          p.product_name, p.area, p.price, p.cost_price,
          p.category_id, p.supplier_id, p.description,
          p.product_image, ps.storage_area_id
        FROM products p
        JOIN product_stock ps ON p.product_id = ps.product_id
        WHERE p.product_id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->bind_result(
        $productName, $area, $price, $costPrice,
        $category_id, $supplier_id, $description,
        $productImage, $storageAreaId
    );
    if ($stmt->fetch()) {
        $product = [
            'product_name'    => $productName,
            'area'            => $area,
            'price'           => $price,
            'cost_price'      => $costPrice,
            'category_id'     => $category_id,
            'supplier_id'     => $supplier_id,
            'description'     => $description,
            'product_image'   => $productImage,
            'storage_area_id' => $storageAreaId
        ];
    } else {
        echo "No product found with the given ID.";
        exit;
    }
    $stmt->close();
} else {
    echo "Product ID is missing.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Extract posted data
    $productName = $_POST['productName']    ?? '';
    $area        = $_POST['area']           ?? '';
    $price       = (float)$_POST['price']   ?? 0.0;
    $costPrice   = (float)$_POST['costPrice'] ?? 0.0;
    $category_id = (int)$_POST['category_id'] ?? null;
    $supplier_id = (int)$_POST['supplier_id'] ?? null;
    $description = $_POST['description']    ?? '';

    // Handle file upload
    if (!empty($_FILES['productImage']['name'])) {
        $uploadDir    = "../uploads/";
        $filename     = basename($_FILES['productImage']['name']);
        $targetFile   = $uploadDir . $filename;
        move_uploaded_file($_FILES['productImage']['tmp_name'], $targetFile);
    } else {
        $targetFile = $product['product_image'];
    }

    // Perform the update (products + product_stock)
    $update_stmt = $mysqli->prepare("
        UPDATE products p
        JOIN product_stock ps ON p.product_id = ps.product_id
        SET 
          p.product_name  = ?,
          p.area          = ?,
          p.price         = ?,
          p.cost_price    = ?,
          p.category_id   = ?,
          p.supplier_id   = ?,
          p.description   = ?,
          p.product_image = ?
        WHERE p.product_id = ?
    ");
    $update_stmt->bind_param(
        "ssddiissi",
        $productName,
        $area,
        $price,
        $costPrice,
        $category_id,
        $supplier_id,
        $description,
        $targetFile,
        $product_id
    );

    if ($update_stmt->execute()) {
        // === Logging the edit ===
        if (isset($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];

            // Fetch user name
            $stmtUser = $mysqli->prepare("SELECT name FROM users WHERE user_id = ?");
            $stmtUser->bind_param("i", $user_id);
            $stmtUser->execute();
            $stmtUser->bind_result($user_name);
            $stmtUser->fetch();
            $stmtUser->close();

            // Prepare description
            $desc = "Product '{$productName}' edited by {$user_name}";

            // Insert log
            $logStmt = $mysqli->prepare("
                INSERT INTO transactions
                  (user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description)
                VALUES
                  (?, ?, NULL, 'Edit', 0, NOW(), ?)
            ");
            $logStmt->bind_param("iis", $user_id, $product_id, $desc);
            $logStmt->execute();
            $logStmt->close();
        }

        $_SESSION['updateMessage'] = 'Product updated successfully.';
    } else {
        $_SESSION['updateMessage'] = 'Error updating product: ' . $update_stmt->error;
    }

    $update_stmt->close();

    // Redirect back
    header("Location: ../Storage Dashboard/Edit_Product.php?product_id={$product_id}");
    exit;
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <?php include "navbar.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Edit Product</h1>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="productName" required value="<?= htmlspecialchars($product['product_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="area" class="form-label">area</label>
                            <input type="text" class="form-control" id="area" name="area" required value="<?= htmlspecialchars($product['area']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="price" name="price" required value="<?= htmlspecialchars($product['price']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="costPrice" class="form-label">Cost Price</label
                            ><input type="number" class="form-control" id="costPrice" name="costPrice" required value="<?= htmlspecialchars($product['cost_price']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['category_id'] ?>"<?= $category['category_id'] == $product['category_id'] ? ' selected' : '' ?>><?= htmlspecialchars($category['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="supplier" class="form-label">Supplier</label>
                            <select class="form-select" id="supplier" name="supplier_id" required>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['supplier_id'] ?>"<?= $supplier['supplier_id'] == $product['supplier_id'] ? ' selected' : '' ?>><?= htmlspecialchars($supplier['supplier_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="productImage" name="productImage">
                        <?php if (!empty($product['product_image'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($product['product_image']) ?>" alt="Product Image" style="width: 100px; height: auto;">
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary" name="update">Update Product</button>
                </form>
            </div>
        </main>
       
    </div>
</body>
</html>
