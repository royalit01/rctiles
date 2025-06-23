<?php
include 'db_connect.php'; // Ensure this points to your database connection script

$query = "SELECT p.product_id, p.product_name, p.sku, p.price, s.quantity 
          FROM products p 
          JOIN product_stock s ON p.product_id = s.product_id 
          ORDER BY p.product_name ASC";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Management</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Product Inventory</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Quantity in Stock</th>
                <th>Adjust Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['sku']) ?></td>
                <td>$<?= number_format($row['price'], 2) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>
                    <button class='btn btn-sm btn-secondary change-quantity' data-product-id='<?= $row['product_id'] ?>' data-type='minus'>-</button>
                    <input type='text' value='<?= $row['quantity'] ?>' size='3' class='quantity-input' data-product-id='<?= $row['product_id'] ?>'>
                    <button class='btn btn-sm btn-secondary change-quantity' data-product-id='<?= $row['product_id'] ?>' data-type='plus'>+</button>
                </td>
                <td>
                    <button class='btn btn-primary' onclick='submitQuantity(<?= $row['product_id'] ?>)'>Update</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.change-quantity').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const type = this.dataset.type;
            const input = document.querySelector(`.quantity-input[data-product-id='${productId}']`);
            let currentQuantity = parseInt(input.value);

            if (type === 'plus') {
                currentQuantity++;
            } else if (type === 'minus' && currentQuantity > 0) {
                currentQuantity--;
            }

            input.value = currentQuantity;
        });
    });
});

function submitQuantity(productId) {
    const quantityInput = document.querySelector(`.quantity-input[data-product-id='${productId}']`);
    const quantity = quantityInput.value;
    // Implement AJAX to update quantity in database
    console.log('Submitting Product ID:', productId, 'With Quantity:', quantity);
}
</script>

</body>
</html>
