<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}

// Each nav link now has an 'index' key for ordering
$sidebarLinks = [
    [
        'heading' => 'Dashboard',
        'links' => [
            ['index' => 1, 'href' => 'admin_dashboard.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Admin Dashboard'],
            ['index' => 2, 'href' => '../Storage Dashboard/Product.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Storage Dashboard'],
        ]
    ],
    [
        'heading' => 'Members',
        'links' => [
            ['index' => 3, 'href' => 'add_user.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Add Member'],
            ['index' => 4, 'href' => 'edit_user.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Edit & View Member'],
        ]
    ],
    [
        'heading' => 'Orders',
        'links' => [
            ['index' => 5, 'href' => 'new_order.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Create Order'],
            ['index' => 6, 'href' => 'admin_orders.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'View Order'],
            ['index' => 7, 'href' => 'minus_order_stock.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Minus Order Stock'],
            ['index' => 8, 'href' => 'assign_delivery.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Assign Delivery'],
            ['index' => 9, 'href' => 'approved_orders.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Create Bill'],
            ['index' => 10, 'href' => 'customBill.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Custom Bill'],
        ]
    ],
    [
        'heading' => 'Log',
        'links' => [
            ['index' => 11, 'href' => 'member_log.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Members Log'],
            ['index' => 12, 'href' => '../Storage Dashboard/Transaction.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Storage Log'],
        ]
    ],
    [
        'heading' => 'Inventory',
        'links' => [
            ['index' => 13, 'href' => '../Storage Dashboard/Report.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'View Stock'],
            ['index' => 14, 'href' => '../Storage Dashboard/Low_Stock.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Low Stock'],
        ]
    ],
    [
        'heading' => 'Others',
        'links' => [
            ['index' => 15, 'href' => 'recycle_bin.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Recycle Bin'],
            ['index' => 16, 'href' => 'delete_orders.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Delete Orders'],
        ]
    ],
    [
        'heading' => 'Report',
        'links' => [
            ['index' => 17, 'href' => '../Storage Dashboard/Low_Stock_Report.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Low Stock Report'],
        ]
    ],
    [
        'heading' => 'Ledger',
        'links' => [
            ['index' => 18, 'href' => 'customer_ledger.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Customer Ledger'],
            ['index' => 19, 'href' => 'member_ledger.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Member Ledger'],
            ['index' => 20, 'href' => 'delivery_payment.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Delivery Payment'],
        ]
    ],
];
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
                            foreach ($sidebarLinks as $section) {
                                echo '<div class="sb-sidenav-menu-heading">' . htmlspecialchars($section['heading']) . '</div>';
                                // Sort links by index before displaying
                                usort($section['links'], function($a, $b) {
                                    return $a['index'] <=> $b['index'];
                                });
                                foreach ($section['links'] as $link) {
                                    echo '<a class="nav-link" href="' . htmlspecialchars($link['href']) . '">';
                                    echo '<div class="sb-nav-link-icon"><i class="' . htmlspecialchars($link['icon']) . '"></i></div>';
                                    echo htmlspecialchars($link['label']);
                                    echo ' <span class="text-muted small ms-2">[' . $link['index'] . ']</span>'; // Show index
                                    echo '</a>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <!-- <?= $_SESSION['role_name'] ?> -->
                    </div>
                </nav>
            </div>