//approved_orders.php

<?php
include '../db_connect.php';

// Fetch approved orders with total amount and custom price
$sql = "SELECT po.order_id, c.name AS customer_name, c.phone_no, 
               o.total_amount, SUM(po.custom_price) AS custom_total
        FROM pending_orders po
        JOIN customers c ON po.customer_id = c.customer_id
        JOIN orders o ON po.order_id = o.order_id
        WHERE po.approved = 1
        GROUP BY po.order_id";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Approved Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-box {
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 50px;
        }
    </style>
</head>

<body class="sb-nav-fixed">
        <?php include "admin_header.php";  ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-box">
                        <h2 class="text-center mb-3">Approved Orders</h2>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Total Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone_no']); ?></td>
                                        
                                        <td>₹<?php echo number_format($row['total_amount'], 2); ?></td> <!-- Custom Price -->
                                        <td>
                                            <button class="btn btn-primary btn-sm" onclick="generateBill(<?php echo $row['order_id']; ?>)">Generate Bill</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

    
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Your Website 2023</div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script>
            function generateBill(orderId) {
                window.location.href = `generate_bill.php?order_id=${orderId}`;
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script> -->
        <!-- <script src="../assets/demo/chart-area-demo.js"></script> -->
        <!-- <script src="../assets/demo/chart-bar-demo.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
</body>
    

</html>


//admin_order.php
<?php 
include "admin_header.php";
include '../db_connect.php';

// Initialize filter variables
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all'; // Default: show all orders
$date_filter = isset($_GET['date']) ? $_GET['date'] : ''; // Date filter
$limit = 10; // Default number of entries to show

// Base SQL query
$sql = "SELECT DISTINCT o.order_id, c.name AS customer_name, c.phone_no, o.total_amount, 
                (SELECT SUM(custom_price) FROM pending_orders WHERE order_id = o.order_id) AS custom_total,
                po.approved, o.order_date
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN pending_orders po ON o.order_id = po.order_id
        WHERE 1=1";

// Add status filter
if ($status_filter !== 'all') {
    $sql .= " AND po.approved = " . ($status_filter === 'approved' ? 1 : ($status_filter === 'rejected' ? -1 : 0));
}

// Add date filter
if (!empty($date_filter)) {
    $sql .= " AND DATE(o.order_date) = '$date_filter'";
}

// Sort by order date (latest first) and limit results
$sql .= " ORDER BY o.order_date DESC LIMIT $limit";

// Execute the query
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-box {
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 50px;
        }
        .modal-body {
            max-height: 400px;
            overflow-y: auto;
        }
        .btn-approved, .btn-rejected {
            pointer-events: none;
            cursor: default;
        }
        .filter-section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="container-box">
            <h2 class="text-center mb-3">Orders</h2>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="status">Filter by Status:</label>
                            <select class="form-control" name="status" id="status">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Orders</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date">Filter by Date:</label>
                            <input type="date" class="form-control" name="date" id="date" value="<?php echo $date_filter; ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="?" class="btn btn-secondary ml-2">Clear Filters</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Original Price</th>
                        <th>Custom Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr id="orderRow-<?php echo $row['order_id']; ?>">
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone_no']); ?></td>
                            <td>₹<?php echo number_format($row['custom_total'], 2); ?></td>
                            <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td>
                                <?php if ($row['approved'] == 0): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php elseif ($row['approved'] == 1): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif ($row['approved'] == -1): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="viewProducts(<?php echo $row['order_id']; ?>)">View Products</button>
                                <?php if ($row['approved'] == 0): ?>
                                    <button class="btn btn-success btn-sm approve-btn" onclick="approveOrder(<?php echo $row['order_id']; ?>, this)">Approve</button>
                                    <button class="btn btn-danger btn-sm reject-btn" onclick="rejectOrder(<?php echo $row['order_id']; ?>, this)">Reject</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Viewing Products -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Original Price</th>
                                <th>Custom Price</th>
                            </tr>
                        </thead>
                        <tbody id="productDetails"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewProducts(orderId) {
            $.ajax({
                url: 'fetch_order_products.php',
                type: 'GET',
                data: { order_id: orderId },
                success: function(response) {
                    $('#productDetails').html(response);
                    $('#productModal').modal('show');
                }
            });
        }

        function approveOrder(orderId, button) {
            if (!confirm("Are you sure you want to approve this order?")) return;
            console.log("Approving order:", orderId); // Debugging
            $.ajax({
                url: 'update_order_status.php',
                type: 'POST',
                data: { order_id: orderId, action: 'approve' },
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error); // Debugging
                }
            });
        }

        function rejectOrder(orderId, button) {
            if (!confirm("Are you sure you want to reject this order?")) return;

            $.ajax({
                url: 'update_order_status.php',
                type: 'POST',
                data: { order_id: orderId, action: 'reject' },
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert("Error: " + response.message);
                    }
                }
            });
        }
    </script>
</body>
</html>


//submit_order.php
<?php
include '../db_connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging: Log received POST data
     echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    // Validate required fields
    if (empty($_POST['customer_name']) || empty($_POST['phone_no']) || empty($_POST['address']) || empty($_POST['city'])) {
        die("Error: Missing required fields.");
    }

    $customer_name = $_POST['customer_name'];
    $phone_no = $_POST['phone_no'];
    $address = $_POST['address'];
    $city = $_POST['city'];

    // Debugging: Log customer details
    echo "Customer Name: $customer_name<br>";
    echo "Phone: $phone_no<br>";
    echo "Address: $address<br>";
    echo "City: $city<br>";

    // Decode products JSON
    if (empty($_POST['products'])) {
        die("Error: No products selected.");
    }

    $products = json_decode($_POST['products'], true);
    if (!is_array($products)) {
        die("Error: Invalid products data.");
    }

    // Debugging: Log products
    echo "<pre>";
    print_r($products);
    echo "</pre>";

    // Get final price
    $final_price = isset($_POST['final_price']) ? floatval($_POST['final_price']) : 0;
    if ($final_price <= 0) {
        die("Error: Invalid final price.");
    }

    // Start transaction
    $mysqli->begin_transaction();
    try {
        // Step 1: Check if customer exists
        $stmt = $mysqli->prepare("SELECT customer_id FROM customers WHERE phone_no = ?");
        $stmt->bind_param("s", $phone_no);
        $stmt->execute();
        $stmt->bind_result($customer_id);
        $stmt->fetch();
        $stmt->close();

        // Step 2: Insert customer if not exists
        if (!$customer_id) {
            $stmt = $mysqli->prepare("INSERT INTO customers (name, phone_no, address, city) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $customer_name, $phone_no, $address, $city);
            $stmt->execute();
            $customer_id = $stmt->insert_id;
            $stmt->close();
            echo "New customer inserted. Customer ID: $customer_id<br>";
        } else {
            echo "Customer already exists. Customer ID: $customer_id<br>";
        }

        // Step 3: Insert order into `orders` table
        // ✅ Updated query to match the current structure of the `orders` table
        $stmt = $mysqli->prepare("INSERT INTO orders (customer_id, total_amount) VALUES (?, ?)");
        $stmt->bind_param("id", $customer_id, $final_price);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
        echo "Order inserted. Order ID: $order_id<br>";

        // Step 4: Insert each product into `pending_orders`
        foreach ($products as $product) {
            if (empty($product['id']) || empty($product['name']) || empty($product['quantity']) || empty($product['unitPrice']) || empty($product['totalPrice'])) {
                echo "Skipping invalid product:<br>";
                print_r($product);
                continue;
            }

            $multiplier = isset($product['multiplier']) ? intval($product['multiplier']) : 1;
            $adjusted_quantity = $product['quantity'] * $multiplier;
            $adjusted_total_price = $product['totalPrice'] * $multiplier;

            // ✅ Insert into `pending_orders` with default `approved` value of 0
            $stmt = $mysqli->prepare("INSERT INTO pending_orders (order_id, customer_id, product_id, product_name, quantity, original_price, custom_price, multiplier, approved) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("iiisidid", $order_id, $customer_id, $product['id'], $product['name'], $adjusted_quantity, $product['unitPrice'], $adjusted_total_price, $multiplier);
            $stmt->execute();
            $stmt->close();
            echo "Product inserted: {$product['name']}<br>";
        }

        // Commit transaction
        $mysqli->commit();
        echo "Transaction committed successfully.<br>";

        // Display success message
        echo "<div style='text-align:center; margin-top:50px;'>
              <h2>Order Submitted Successfully!</h2>
              <p><a href='new_order.php'>Create New Order</a></p>
              <p><a href='admin_orders.php'>View Orders</a></p>
              </div>";
    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();
        die("Error: " . $e->getMessage());
    }
} else {
    die("Error: Invalid request method.");
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
        <?php include "admin_header.php";  ?>
        <div id="layoutSidenav_content">
                <main>
                    <div class="d-flex justify-content-center align-items-center vh-100">
                        <div class="text-center">
                        <h2>🎉 Order Submitted Successfully!</h2>
                                <p>Your order has been recorded and sent for approval.</p>
                                <!-- <p><strong>Order ID:</strong>  -->
                                <!-- <?php echo isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : 'N/A'; ?></p> -->
                               
                        </div>
                    </div>
                </main>
        </div>
                
          
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script> -->
        <!-- <script src="../assets/demo/chart-area-demo.js"></script> -->
        <!-- <script src="../assets/demo/chart-bar-demo.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>

