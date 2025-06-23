<?php
include "../db_connect.php";  // This path should correctly point to your database connection script

// Check for selected storage area and category
$selected_storage_area = $_POST['storage_area'] ?? null;
$selected_category = $_POST['category'] ?? null;

// Build the query dynamically based on selections
$query = "SELECT p.product_name, ps.quantity, ps.pieces_per_packet, sa.storage_area_name, c.category_name 
          FROM products p
          JOIN product_stock ps ON p.product_id = ps.product_id
          JOIN storage_areas sa ON ps.storage_area_id = sa.storage_area_id
          JOIN category c ON p.category_id = c.category_id ";

$where_clauses = [];
if ($selected_storage_area) {
    $where_clauses[] = "ps.storage_area_id = '" . $mysqli->real_escape_string($selected_storage_area) . "'";
}
if ($selected_category) {
    $where_clauses[] = "p.category_id = '" . $mysqli->real_escape_string($selected_category) . "'";
}

if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY p.product_name ASC"; // Default sorting by storage area name

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
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

        </head>
        <body class="sb-nav-fixed">
        <?php  include 'navbar.php'; ?>

                <!-- ---------------------------- -->
                <div id="layoutSidenav_content">
                    <main>
                    <div class="container-fluid mt-3">
        <h2 class="mt-4">Stock Report</h2>
        <form method="POST" id="filterForm">
            <!-- Storage Area Dropdown -->
            <div class="mb-3">
                <label for="storageAreaSelect" class="form-label">Storage Area:</label>
                <select id="storageAreaSelect" name="storage_area" class="form-select" onchange="this.form.submit();">
                    <option value="">All Storage Areas</option>
                    <?php
                    $areas = $mysqli->query("SELECT storage_area_id, storage_area_name FROM storage_areas ORDER BY storage_area_name");
                    while ($area = $areas->fetch_assoc()) {
                        $selected = ($selected_storage_area == $area['storage_area_id']) ? 'selected' : '';
                        echo "<option value='{$area['storage_area_id']}' $selected>{$area['storage_area_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <!-- Category Dropdown -->
            <div class="mb-3">
                <label for="categorySelect" class="form-label">Category:</label>
                <select id="categorySelect" name="category" class="form-select" onchange="this.form.submit();">
                    <option value="">All Categories</option>
                    <?php
                    if ($selected_storage_area) {
                        $categories = $mysqli->query("SELECT DISTINCT c.category_id, c.category_name FROM category c JOIN products p ON c.category_id = p.category_id JOIN product_stock ps ON p.product_id = ps.product_id WHERE ps.storage_area_id = '" . $mysqli->real_escape_string($selected_storage_area) . "' ORDER BY c.category_name");
                        while ($category = $categories->fetch_assoc()) {
                            $selected = ($selected_category == $category['category_id']) ? 'selected' : '';
                            echo "<option value='{$category['category_id']}' $selected>{$category['category_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </form>
        <!-- Table for Stock Report -->
        <table class="table table-striped table-bordered mt-3" id="stockTable">
            <thead class="table-dark">
                <tr>
                    <th>Product Name</th>
                    <th>Stock</th>
                    <th>Storage Area</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $packets = intdiv($row['quantity'], $row['pieces_per_packet']);
                        $pieces = $row['quantity'] % $row['pieces_per_packet'];
                        echo "<tr>
                                <td>{$row['product_name']}</td>
                                <td>{$packets} packets / {$pieces} pieces</td>
                                <td>{$row['storage_area_name']}</td>
                                <td>{$row['category_name']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="text-center">
    <button class="btn btn-primary " onclick="downloadPDF()">Download PDF</button>
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
        function updateCategories(storageAreaId) {
            const categorySelect = document.getElementById('categorySelect');
            categorySelect.innerHTML = '<option>Loading...</option>';

            // Fetch categories based on storage area
            fetch('get_categories.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'storage_area_id=' + storageAreaId
            })
            .then(response => response.json())
            .then(data => {
                categorySelect.innerHTML = '<option value="">All Categories</option>';
                data.forEach(cat => {
                    categorySelect.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
                });
            })
            .catch(error => console.error('Error:', error));
        }

        // Function to download the table as a PDF
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
    doc.text("Total Stock Report", 145, 18);

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
