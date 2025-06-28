<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.html">Admin Dashboard</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Settings</a></li>
                        <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="#!">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Dashboard</div>
                            <a class="nav-link" href="admin_dashboard.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Admin Dashboard
                            </a>
                            <a class="nav-link" href="../Storage Dashboard/Product.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Storage Dashboard
                            </a>

                            <div class="sb-sidenav-menu-heading">Members</div>
                            <a class="nav-link" href="add_user.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Add Member
                            </a>
                            <a class="nav-link" href="edit_user.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Edit & View Member
                            </a>

                            <div class="sb-sidenav-menu-heading">Orders</div>
                            <a class="nav-link" href="new_order.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Create Order
                            </a>
                            <a class="nav-link" href="admin_orders.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                View Order
                            </a>
                            <a class="nav-link" href="minus_order_stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Minus Order Stock
                            </a>
                            <a class="nav-link" href="assign_delivery.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Assign Delivery
                            </a>
                            <a class="nav-link" href="approved_orders.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Create Bill
                            </a>
                            <a class="nav-link" href="customBill.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Custom Bill
                            </a>

                            <div class="sb-sidenav-menu-heading">Log</div>
                            <a class="nav-link" href="member_log.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Members Log
                            </a>
                            <a class="nav-link" href="../Storage Dashboard/Transaction.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Storage Log
                            </a>

                            <div class="sb-sidenav-menu-heading">Inventory</div>
                            <a class="nav-link" href="../Storage Dashboard/Report.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                View Stock
                            </a>
                            <a class="nav-link" href="../Storage Dashboard/Low_Stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Low Stock 
                            </a>

                            <div class="sb-sidenav-menu-heading">others</div>
                            <a class="nav-link" href="recycle_bin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Recycle Bin
                            </a>
                            <a class="nav-link" href="delete_orders.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Delete Orders
                            </a>
                           
                            <div class="sb-sidenav-menu-heading">Report</div>
                            <!--<a class="nav-link" href="add_storagearea.php">-->
                            <!--    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>-->
                            <!--    Order report-->
                            <!--</a>-->
                            <a class="nav-link" href="../Storage Dashboard/Low_Stock_Report.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Low Stock Report
                            </a>
                            
                            <div class="sb-sidenav-menu-heading">Ledger</div>
                            <a class="nav-link" href="customer_ledger.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Customer Ledger
                            </a>
                            <a class="nav-link" href="member_ledger.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Member Ledger
                            </a>
                            <a class="nav-link" href="delivery_payment.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Delivery Payment
                            </a>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Admin
                    </div>
                </nav>
            </div>