<?php
// Database configuration
$host = 'localhost';
$dbname = 'rc_ceramic_mall_db_agrima';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}

// Fetch Categories from Database
$stmt = $pdo->prepare("SELECT category_id, category_name FROM category ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Products Based on Category ID
if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Product Modal Example</title>
<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
}
.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
}
.close {
    color: #aaaaaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}
.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}
</style>
</head>
<body>
    <select id="categorySelect">
        <?php foreach ($categories as $category): ?>
            <option value="<?= htmlspecialchars($category['category_id']) ?>"><?= htmlspecialchars($category['category_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button onclick="openModal()">Show Products</button>
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <ul id="productList">Products will be listed here...</ul>
        </div>
    </div>
<script>
function openModal() {  
    const category_id = document.getElementById("categorySelect").value;
    fetch(`?category_id=${category_id}`)
        .then(response => response.json())
        .then(data => {
            const productList = document.getElementById("productList");
            productList.innerHTML = data.map(product => `<li>${product.product_name}</li>`).join('');
        })
        .catch(error => console.error('Error:', error));
    document.getElementById("productModal").style.display = "block";
}

function closeModal() {
    document.getElementById("productModal").style.display = "none";
}
</script>
</body>
</html>