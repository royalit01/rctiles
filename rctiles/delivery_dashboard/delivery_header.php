
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .sb-topnav {
        background-color: #212529 !important;
        border-bottom: 1px solid #444;
        height: 56px;
        z-index: 1045;
    }
    .navbar-brand {
        color: #fff !important;
        font-weight: 500;
    }
    .navbar-nav .nav-link {
        color: #adb5bd !important;
    }
    .navbar-nav .nav-link:hover {
        color: #fff !important;
    }
    .form-control {
        background-color: #495057;
        border: 1px solid #6c757d;
        color: #fff;
    }
    .form-control:focus {
        background-color: #495057;
        border-color: #0d6efd;
        color: #fff;
    }
    .form-control::placeholder {
        color: #adb5bd;
    }
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    #layoutSidenav {
        display: flex;
        min-height: calc(100vh - 56px);
    }
    #layoutSidenav_nav {
        width: 225px;
        flex-shrink: 0;
        transition: left 0.3s;
        background: #212529;
    }
    .sb-sidenav {
        background-color: #212529;
        min-height: 100%;
    }
    .sb-sidenav-dark {
        background-color: #212529;
        color: #adb5bd;
    }
    .sb-sidenav-menu-heading {
        padding: 1.75rem 1rem 0.75rem;
        font-size: 0.75rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
    }
    .sb-sidenav .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: #adb5bd;
        text-decoration: none;
        transition: color 0.15s ease-in-out;
    }
    .sb-sidenav .nav-link:hover {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.075);
    }
    .sb-nav-link-icon {
        width: 1rem;
        font-size: 0.9rem;
        margin-right: 0.5rem;
    }
    .sb-sidenav-footer {
        padding: 0.75rem 1rem;
        margin-top: auto;
        font-size: 0.875rem;
        background-color: #343a40;
        color: #6c757d;
    }
    .sb-sidenav-footer .small {
        color: #6c757d;
    }
    @media (max-width: 991.98px) {
        #layoutSidenav {
            flex-direction: column;
        }
        #layoutSidenav_nav {
            position: fixed;
            top: 56px;
            left: -225px;
            height: 100vh;
            z-index: 1050;
            width: 225px;
            transition: left 0.3s;
        }
        #layoutSidenav_nav.active {
            left: 0;
        }
        #layoutSidenav_content {
            padding-left: 0 !important;
        }
    }
    @media (max-width: 575.98px) {
        .sb-topnav {
            height: auto;
            flex-direction: column;
        }
        .navbar-brand {
            font-size: 1.1rem;
        }
        #layoutSidenav_nav {
            width: 180px;
        }
    }
</style>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="index.html">Delivery Dashboard</a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" type="button">
        <i class="fas fa-bars"></i>
    </button>
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
                    <a class="nav-link" href="delivery_dashboard.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        live order List
                    </a>
                    <a class="nav-link" href="my_income.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Total Income
                    </a>
                    <a class="nav-link" href="my_ledger.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        My Ledger
                    </a>
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?>
            </div>
        </nav>
    </div>
    <!-- Your main content should be placed in #layoutSidenav_content in your page -->
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle for mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        var sidenav = document.getElementById('layoutSidenav_nav');

          });
</script>