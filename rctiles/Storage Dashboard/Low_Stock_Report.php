<?php
include "../db_connect.php";  // Ensure this is the correct path to your database connection script

$selected_storage_area = $_POST['storage_area'] ?? '';

// Build SQL query based on storage area selection
$query = "SELECT p.product_name, p.product_id, ps.quantity, ps.pieces_per_packet, ps.min_stock_level, sa.storage_area_name 
          FROM product_stock ps
          JOIN products p ON p.product_id = ps.product_id
          JOIN storage_areas sa ON sa.storage_area_id = ps.storage_area_id ";

if (!empty($selected_storage_area)) {
    $query .= " WHERE ps.storage_area_id = " . intval($selected_storage_area);
}

$query .= " AND (ps.quantity / ps.pieces_per_packet) < ps.min_stock_level
            ORDER BY p.product_name ASC";

$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
         
    </head>
    <body class="sb-nav-fixed">
    <?php  include 'navbar.php'; ?>


            <!-- ---------------------------- -->
            <div id="layoutSidenav_content">
                <main>
                <div class="container-fluid mt-3">
                        <h1 class="mt-4">Low Stock Report</h1>
                            <div class="card-body">
                                <form action="" method="POST" class="mb-3">
                                    <label for="storageAreaSelect" class="form-label">Storage Area:</label>
                                    <select id="storageAreaSelect" name="storage_area" class="form-select" onchange="this.form.submit();">
                                        <option value="">All Areas</option>
                                        <?php
                                        $areas = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas ORDER BY storage_area_name");
                                        while ($area = $areas->fetch_assoc()) {
                                            $selected = ($selected_storage_area == $area['storage_area_id']) ? 'selected' : '';
                                            echo "<option value='{$area['storage_area_id']}' $selected>{$area['storage_area_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </form>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    echo '<div class="table-responsive">
                                            <table class="table table-striped table-bordered mt-3" id="stockTable" >
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Product Name</th>
                                                        <th>Stock (Packets)</th>
                                                        <th>Min Stock Level (Packets)</th>
                                                        <th>Storage Area</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                                    while ($row = $result->fetch_assoc()) {
                                        $packets = floor($row['quantity'] / $row['pieces_per_packet']);
                                        echo "<tr>
                                                <td>{$row['product_name']}</td>
                                                <td>{$packets}</td>
                                                <td>{$row['min_stock_level']}</td>
                                                <td>{$row['storage_area_name']}</td>
                                              </tr>";
                                    }
                                    echo '</tbody>
                                        </table></div>';
                                } else {
                                    echo '<div class="alert alert-warning" role="alert">No products are low in stock for the selected area.</div>';
                                }
                                ?>
                                <div class="text-center">
                                    <button class="btn btn-primary " onclick="downloadPDF()">Download PDF</button>
                                </div>
                            </div>   
                    </div>
                </main> 

                <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            ·
                            <a href="#">Terms & Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
            </div>
         <!-- ---------------------------- -->   
        </div>
        <script>
            new simpleDatatables.DataTable("#dataTable");

            function downloadPDF() {
    const { jsPDF } = window.jspdf;  // Ensure jsPDF is loaded properly
    if (!jsPDF) {
        alert("jsPDF library is not loaded!");
        return;
    }

    const doc = new jsPDF();

    // Log to console or check that the document has been created
    console.log("jsPDF document created:", doc);
    doc.setFont("helvetica", "bold");
    doc.setTextColor(255, 0, 0);
    doc.text("RC CERAMIC MALL", 15, 18);
    doc.setTextColor(0, 0, 0);
    doc.text("Low Stock Report", 145, 18);

    

    // Ensure AutoTable function is available
    if (doc.autoTable) {
        // Check if table exists and is visible
        const tableElement = document.getElementById('stockTable');
        if (!tableElement) {
            alert("Table element not found!");
            return;
        }
        if (tableElement.rows.length === 0) {
            alert("No data available in the table to export!");
            return;
        }

        // Use the autoTable function to include the table data
        doc.autoTable({
            html: '#stockTable',
            startY: 30
        });
        doc.save("Total_Stock_Report.pdf");
    } else {
        alert("AutoTable plugin is not loaded correctly.");
    }
}
        </script>
         <!-- Include jsPDF -->
         <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
            <!-- Include jsPDF AutoTable plugin -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.19/jspdf.plugin.autotable.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/umd.simple-datatables.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
            <script src="../js/scripts.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
            <script src="../assets/demo/chart-area-demo.js"></script>
            <script src="../assets/demo/chart-bar-demo.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
            <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
