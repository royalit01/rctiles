<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Not logged in
    header("Location: ../login.php");
    exit;
}
include "../db_connect.php";
$userId = $_SESSION['user_id'];
$userQuery = $mysqli->query("SELECT sidebar_index FROM users WHERE user_id = $userId");
$userRow = $userQuery->fetch_assoc();
$allowedIndexes = json_decode($userRow['sidebar_index'], true) ?? [];

// Helper function to show nav link only if allowed
function showNav($index, $html, $allowedIndexes) {
    if (in_array((string)$index, $allowedIndexes)) {
        echo str_replace('{INDEX}', $index, $html);
    }
}

// Now you can check the role if you want:

?>

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="#">Storage Dashboard</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <!--<form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">-->
            <!--    <div class="input-group">-->
            <!--        <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />-->
            <!--        <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>-->
            <!--    </div>-->
            <!--</form>-->
            <!-- Navbar-->
            <div class="d-flex ms-auto">
    <ul class="navbar-nav me-1 me-lg-4">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fas fa-user fa-fw"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
          <li><a class="dropdown-item" href="../admin_dashboard/my_profile.php">My Profile</a></li>
          <!-- <li><a class="dropdown-item" href="#!">Activity Log</a></li> -->
          <li><hr class="dropdown-divider" /></li>
          <li><a class="dropdown-item" href="../login.php">Logout</a></li>
        </ul>
      </li>
    </ul>
  </div>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">

<div class="sb-sidenav-menu-heading">DASHBOARD</div>
<?php
// 22. Admin Dashboard
showNav(22, '
    <a class="nav-link" href="../admin_dashboard/admin_dashboard.php">
        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
        22. Admin Dashboard
    </a>
', $allowedIndexes);
// 23. Product
showNav(23, '
    <a class="nav-link" href="Product.php">
        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
        23. Product
    </a>
', $allowedIndexes);
// 24. Transaction
showNav(24, '
    <a class="nav-link" href="Transaction.php">
        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
        24. Transaction
    </a>
', $allowedIndexes);
// 25. Add Stock
showNav(25, '
    <a class="nav-link" href="Add_Stock.php">
        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
        25. Add Stock
    </a>
', $allowedIndexes);
// 26. Minus Stock
showNav(26, '
    <a class="nav-link" href="Minus_Stock.php">
        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
        26. Minus Stock
    </a>
', $allowedIndexes);
// 27. Add Product
showNav(27, '
    <a class="nav-link" href="Add_Product.php">
        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
        27. Add Product
    </a>
', $allowedIndexes);
// 28. Edit Product
showNav(28, '
    <a class="nav-link collapsed" href="Edit_Product.php">
        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
        28. Edit Product
    </a>
', $allowedIndexes);
// 29. Edit Category
showNav(29, '
    <a class="nav-link collapsed" href="Edit_Category.php">
        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
        29. Edit Category
    </a>
', $allowedIndexes);
// 30. Edit Supplier
showNav(30, '
    <a class="nav-link collapsed" href="Edit_Supplier.php">
        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
        30. Edit Supplier
    </a>
', $allowedIndexes);
// 31. Edit Storage Area
showNav(31, '
    <a class="nav-link collapsed" href="Edit_Storage_Area.php">
        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
        31. Edit Storage Area
    </a>
', $allowedIndexes);
// 32. Stock Transfer
showNav(32, '
    <a class="nav-link collapsed" href="Stock_Transfer.php">
        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
        32. Stock Transfer
    </a>
', $allowedIndexes);
// 33. Stock Update Excel
showNav(33, '
    <a class="nav-link collapsed" href="Stock_Update_Excel.php">
        <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
        33. Stock Update Excel
    </a>
', $allowedIndexes);
// 34. Total Stock Report
showNav(34, '
    <a class="nav-link collapsed" href="Report.php">
        <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
        34. Total Stock Report
    </a>
', $allowedIndexes);
// 35. Low Stock Report
showNav(35, '
    <a class="nav-link collapsed" href="Low_Stock_Report.php">
        <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
        35. Low Stock Report
    </a>
', $allowedIndexes);
?>

                          
<?php
if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">Edit Options</div>';
    showNav(28, '
        <a class="nav-link collapsed" href="Edit_Product.php">
            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
            28. Edit Product
        </a>
    ', $allowedIndexes);
    showNav(29, '
        <a class="nav-link collapsed" href="Edit_Category.php">
            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
            29. Edit Category
        </a>
    ', $allowedIndexes);
    showNav(30, '
        <a class="nav-link collapsed" href="Edit_Supplier.php">
            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
            30. Edit Supplier
        </a>
    ', $allowedIndexes);
    showNav(31, '
        <a class="nav-link collapsed" href="Edit_Storage_Area.php">
            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
            31. Edit Storage Area
        </a>
    ', $allowedIndexes);
}
?>

<?php
if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">Advance Edit Options</div>';
    showNav(32, '
        <a class="nav-link collapsed" href="Stock_Transfer.php">
            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
            32. Stock Transfer
        </a>
    ', $allowedIndexes);
    showNav(33, '
        <a class="nav-link collapsed" href="Stock_Update_Excel.php">
            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
            33. Stock Update Excel
        </a>
    ', $allowedIndexes);
}
?>
<?php
if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">Report</div>';
    showNav(34, '
        <a class="nav-link collapsed" href="Report.php">
            <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
            34. Total Stock Report
        </a>
    ', $allowedIndexes);
    showNav(35, '
        <a class="nav-link collapsed" href="Low_Stock_Report.php">
            <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
            35. Low Stock Report
        </a>
    ', $allowedIndexes);
}
?>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?= $_SESSION['role_name'] ?>
                    </div>
                </nav>
            </div>

