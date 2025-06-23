<?php include "admin_header.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Order Submitted Successfully</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .success-container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin: auto;
            margin-top: 100px;
            text-align: center;
        }
        .success-container h2 {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <h2>🎉 Order Submitted Successfully!</h2>
            <p>Your order has been recorded and sent for approval.</p>
            <p><strong>Order ID:</strong> <?php echo isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : 'N/A'; ?></p>
            
            <a href="new_order.php" class="btn btn-primary">Create New Order</a>
        </div>
    </div>
</body>
</html>
