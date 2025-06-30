<?php
session_start();
include "../db_connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Delivery Dashboard</title>
    <!-- Bootstrap & FontAwesome included in header -->
       <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

</head>
<body class="sb-nav-fixed">
<?php include "delivery_header.php"; ?>
            <div id="layoutSidenav_content">

<main class="main-content ">
                        <div class="card border-0 shadow rounded-3 p-4 bg-white mx-auto " style="max-width: 800px;">

 
          <div class="dashboard-header mb-4">
            <h2 class="dashboard-title">Delivery Dashboard</h2>
            <p class="dashboard-subtitle">Manage your delivery operations efficiently</p>
          </div>
          <div class="dashboard-actions">
            <div class="row g-3">
              <div class="col-md-6">
                <button class="btn btn-pending status-btn w-100" id="pendingBtn" data-status="pending">
                  <i class="fas fa-clock"></i>
                  <span>PENDING</span>
                  <div class="btn-count">15</div>
                </button>
              </div>
              <div class="col-md-6">
                <button class="btn btn-delivered status-btn w-100" id="deliveredBtn" data-status="delivered">
                  <i class="fas fa-check-circle"></i>
                  <span>DELIVERED</span>
                  <div class="btn-count">28</div>
                </button>
              </div>
            </div>
          </div>

  </div>
</main>
</div>

<!-- Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="statusModalLabel">
          <i class="modal-icon me-2"></i>
          <span id="modalTitle">Status Details</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="modalContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="actionBtn">Take Action</button>
      </div>
    </div>
  </div>
</div>

<footer class="footer text-center">
  &copy; Delivery System <?php echo date('Y'); ?>
  <div class="float-end">
    <a href="#">Privacy Policy</a> · <a href="#">Terms</a>
  </div>
</footer>

<!-- Styles for dashboard page -->
<style>
  body {
   
    background-color: #f8f9fa;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }
  .main-content {
    /* padding: 1.5rem; */
    /* min-height: calc(100vh - 56px); */
  }
  .content-wrapper {
    /* height: 100%; */
  }
  .content-area {
    /* min-height: calc(100vh - 150px); */
    background-color: #ffffff !important;
    border: 1px solid #e3e6f0;
    /* margin-bottom: 60px; */
  }
  .dashboard-header {
    text-align: center;
    border-bottom: 2px solid #f1f3f4;
    padding-bottom: 1.5rem;
  }
  .dashboard-title {
    color: #2c3e50;
    font-weight: 600;
    font-size: 2.2rem;
    margin-bottom: 0.5rem;
  }
  .dashboard-subtitle {
    font-size: 1rem;
    color: #6c757d;
  }
  .dashboard-actions {
    margin-top: 2rem;
  }
  .status-btn {
    position: relative;
    padding: 1.5rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
  }
  .status-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
  }
  .btn-count {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    border-radius: 50%;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
  }
  .btn-pending {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border: 1px solid #0a58ca;
  }
  .btn-pending:hover {
    background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
  }
  .btn-delivered {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    border: 1px solid #219653;
  }
  .btn-delivered:hover {
    background: linear-gradient(135deg, #27ae60 0%, #219653 100%);
  }
  .modal-content {
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  }
  .modal-header {
    background-color: #212529;
    color: white;
  }
  .modal-icon {
    font-size: 1.2rem;
  }
  .badge-pending {
    background-color: #0d6efd;
    color: white;
  }
  .badge-delivered {
    background-color: #2ecc71;
    color: white;
  }
  .footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #212529;
    padding: 10px 20px;
    border-top: 1px solid #444;
    font-size: 0.875rem;
    color: #adb5bd;
  }
  .footer a {
    color: #adb5bd;
    text-decoration: none;
  }
  .footer a:hover {
    color: #fff;
  }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('pendingBtn').addEventListener('click', () => showStatusModal('pending'));
  document.getElementById('deliveredBtn').addEventListener('click', () => showStatusModal('delivered'));

  function showStatusModal(status) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    const modalIcon = document.querySelector('.modal-icon');
    const actionBtn = document.getElementById('actionBtn');

    if (status === 'pending') {
      modalTitle.textContent = 'Pending Deliveries';
      modalIcon.className = 'fas fa-clock modal-icon text-primary';
      modalContent.innerHTML = `<div class="list-group">
        <div class="list-group-item"><strong>Order #12345</strong> - John Doe <span class="badge badge-pending float-end">Pending</span></div>
        <div class="list-group-item"><strong>Order #12346</strong> - Jane Smith <span class="badge badge-pending float-end">Pending</span></div>
      </div>`;
      actionBtn.textContent = 'Process Orders';
      actionBtn.className = 'btn btn-primary';
    } else {
      modalTitle.textContent = 'Delivered Orders';
      modalIcon.className = 'fas fa-check-circle modal-icon text-success';
      modalContent.innerHTML = `<div class="list-group">
        <div class="list-group-item"><strong>Order #12340</strong> - Alice <span class="badge badge-delivered float-end">Delivered</span></div>
        <div class="list-group-item"><strong>Order #12341</strong> - Bob <span class="badge badge-delivered float-end">Delivered</span></div>
      </div>`;
      actionBtn.textContent = 'View Reports';
      actionBtn.className = 'btn btn-success';
    }

    modal.show();
  }

  document.getElementById('actionBtn').addEventListener('click', () => {
    const modalTitle = document.getElementById('modalTitle').textContent;
    alert(modalTitle.includes('Pending') ? 'Redirecting to processing page...' : 'Opening reports...');
  });
</script>
</body>
</html>