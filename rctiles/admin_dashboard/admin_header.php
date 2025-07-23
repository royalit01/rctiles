<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}



// Now you can check the role if you want:
include "../db_connect.php";
$userId = $_SESSION['user_id'];
$userQuery = $mysqli->query("SELECT sidebar_index FROM users WHERE user_id = $userId");
$userRow = $userQuery->fetch_assoc();
$allowedIndexes = json_decode($userRow['sidebar_index'], true);

// Ensure $allowedIndexes is always an array
if (!is_array($allowedIndexes)) {
    $allowedIndexes = [$userRow['sidebar_index']];
}

// Helper function to show nav link only if allowed
function showNav($index, $html, $allowedIndexes) {
    if (in_array((string)$index, $allowedIndexes)) {
        echo str_replace('{INDEX}', $index, $html);
    }
}
?>

<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="admin_dashboard.php">Admin Dashboard</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <!-- <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form> -->
            <!-- Navbar-->
              <div class="d-flex ms-auto">
    <ul class="navbar-nav me-1 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="my_profile.php">My Profile</a></li>
                        <!-- <li><a class="dropdown-item" href="#!">Activity Log</a></li> -->
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
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
           
                        
    <?php

    if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '                            <div class="sb-sidenav-menu-heading">Dashboard</div>';
    // 1. Admin Dashboard
    showNav(1, '
        <a class="nav-link" href="admin_dashboard.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Admin Dashboard
        </a>
    ', $allowedIndexes);
  echo '<a class="nav-link" href="../Storage Dashboard/Product.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Storage Dashboard
        </a>';
        }
    

         if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">Members</div>';
  
    // 2. Add Member
    showNav(2, '
        <a class="nav-link" href="add_user.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Add Member
        </a>
    ', $allowedIndexes);

    // 3. Edit & View Member
    showNav(3, '
        <a class="nav-link" href="edit_user.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Edit & View Member
        </a>
    ', $allowedIndexes);
   }

     if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">Orders</div>';
  
    // 4. Create Estimate Order
    showNav(4, '
        <a class="nav-link" href="create_estimate.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Create Order
        </a>
    ', $allowedIndexes);

    // 5. View Order
    showNav(5, '
        <a class="nav-link" href="admin_orders.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            View Order
        </a>
    ', $allowedIndexes);
    
    // // 6. create Order
    // showNav(6, '
    //         <a class="nav-link" href="new_order.php">
    //         <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
    //        Create Order
    //     </a>
    // ', $allowedIndexes);

    // 7. View Estimate
    // showNav(7, '
    //     <a class="nav-link" href="estimate.php">
    //         <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
    //         View Estimate
    //     </a>
    // ', $allowedIndexes);

    // 8. Minus Order Stock
    showNav(8, '
        <a class="nav-link" href="minus_order_stock.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Minus Order Stock
        </a>
    ', $allowedIndexes);

    // 9. Assign Delivery
    showNav(9, '
        <a class="nav-link" href="assign_delivery.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Assign Delivery
        </a>
    ', $allowedIndexes);

    // 10. Create Bill
    showNav(10, '
        <a class="nav-link" href="approved_orders.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Create Bill
        </a>
    ', $allowedIndexes);

    // 11. Custom Bill
    showNav(11, '
        <a class="nav-link" href="customBill.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Custom Bill
        </a>
    ', $allowedIndexes);
  }

     if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">Log</div>';
  

    // 12. Members Log
    showNav(12, '
        <a class="nav-link" href="member_log.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Members Log
        </a>
    ', $allowedIndexes);

    // 13. Storage Log
    showNav(13, '
        <a class="nav-link" href="../Storage Dashboard/Transaction.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Storage Log
        </a>
    ', $allowedIndexes);
   }
     if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">Inventory</div>';
  

    // 14. View Stock
    showNav(14, '
        <a class="nav-link" href="../Storage Dashboard/Report.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            View Stock
        </a>
    ', $allowedIndexes);

    // 15. Low Stock
    showNav(15, '
        <a class="nav-link" href="../Storage Dashboard/Low_Stock.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Low Stock
        </a>
    ', $allowedIndexes);
       }

        if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">others</div>';
  
    // 16. Recycle Bin
    showNav(16, '
        <a class="nav-link" href="recycle_bin.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Recycle Bin
        </a>
    ', $allowedIndexes);

    // 17. Delete Orders
    showNav(17, '
        <a class="nav-link" href="delete_orders.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Delete Orders
        </a>
    ', $allowedIndexes);
  }
     if ($_SESSION['role_id'] == 1 || $_SESSION['role_id'] == 2) {
    echo '<div class="sb-sidenav-menu-heading">Report</div>';
  

    // 18. Low Stock Report
    showNav(18, '
        <a class="nav-link" href="../Storage Dashboard/Low_Stock_Report.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Low Stock Report
        </a>
    ', $allowedIndexes);

    // 19. Customer Ledger
    showNav(19, '
        <a class="nav-link" href="customer_ledger.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Customer Ledger
        </a>
    ', $allowedIndexes);

    // 20. Member Ledger
    showNav(20, '
        <a class="nav-link" href="member_ledger.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Member Ledger
        </a>
    ', $allowedIndexes);

    // 21. Delivery Payment
    showNav(21, '
        <a class="nav-link" href="delivery_payment.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Delivery Payment
        </a>
    ', $allowedIndexes);
  }
    ?>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?php
                        // Fetch role name from roles table
                        $roleName = '';
                        if (isset($_SESSION['role_id'])) {
                            $roleId = (int)$_SESSION['role_id'];
                            $roleRes = $mysqli->query("SELECT role_name FROM roles WHERE role_id = $roleId");
                            if ($roleRes && $roleRow = $roleRes->fetch_assoc()) {
                                $roleName = htmlspecialchars($roleRow['role_name']);
                            }
                        }
                        echo $roleName;
                        ?>
                    </div>
                </nav>
            </div>