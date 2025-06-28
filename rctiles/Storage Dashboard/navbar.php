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
          <li><a class="dropdown-item" href="my_profile.php">My Profile</a></li>
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
                   <a class="nav-link" href="../admin_dashboard/admin_dashboard.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Admin Dashboard
                            </a>
                            <div class="sb-sidenav-menu-heading">Main Dashboard</div>
                           
                            <a class="nav-link" href="Product.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Product
                            </a>
                            <a class="nav-link" href="Transaction.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Transaction
                            </a>
                            <a class="nav-link" href="Add_Stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Add Stock
                            </a>
                            <a class="nav-link" href="Minus_Stock.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Minus Stock
                            </a>
                            <a class="nav-link" href="Add_Product.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Add Product
                            </a>


                            <div class="sb-sidenav-menu-heading">Edit Options</div>
                            <a class="nav-link collapsed" href="Edit_Product.php" >
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Product
                            </a>
                            <a class="nav-link collapsed" href="Edit_Category.php" >
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Category
                            </a>
                            <a class="nav-link collapsed" href="Edit_Supplier.php" >
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Supplier 
                            </a>
                            <a class="nav-link collapsed" href="Edit_Storage_Area.php" >
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Edit Storage Area 
                            </a>

                            
                            <div class="sb-sidenav-menu-heading">Advance Edit Options</div>
                            <!-- <a class="nav-link collapsed" href="Bulk_Stock_Update.php" >
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Bulk Stock Update
                            </a> -->
                            <a class="nav-link collapsed" href="Stock_Transfer.php" >
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Stock Transfer
                            </a>
                            <a class="nav-link collapsed" href="Stock_Update_Excel.php" >
                                <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                                Stock Update Excel
                            </a>

                            
                            <div class="sb-sidenav-menu-heading">Report</div>
                            <a class="nav-link collapsed" href="Report.php"  >
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Total Stock Report
                            </a>
                            <a class="nav-link collapsed" href="Add_Report.php"  >
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Add Stock Report
                            </a>
                            <a class="nav-link collapsed" href="Minus_Report.php"  >
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Minus Stock Report
                            </a>
                            <a class="nav-link collapsed" href="Low_Stock_Report.php"  >
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Low Stock Report
                            </a>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Admin
                    </div>
                </nav>
            </div> 

