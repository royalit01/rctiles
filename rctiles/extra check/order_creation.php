
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
    <style>
        .form-step { display: none; }
        .active-step { display: block; }
        .detail-entry { border: 1px solid #ccc; padding: 10px; border-radius: 5px; position: relative; margin-top: 10px; }
        .detail-entry:not(:first-of-type) { margin-top: 20px; }
        .detail-entry button { position: absolute; top: 10px; right: 10px; }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include "admin_header.php"; ?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container mt-5">
                <h1 class="mb-4">Create New Order</h1>
                <form id="orderForm" action="submit_order.php" method="post">
                    <!-- Step 1: Customer Information -->
                    <div class="form-step active-step" id="step1">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Name:</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_no" class="form-label">Phone Number:</label>
                            <input type="tel" class="form-control" id="phone_no" name="phone_no" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address:</label>
                            <input type="text" class="form-control" id="address" name="address" required>
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label">City:</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
                    </div>

                    <!-- Step 2: Order Details -->
                    <div class="form-step" id="step2">
                        <h2>Order Details</h2>
                        <div id="orderDetailsContainer">
                            <!-- Dynamic order detail entries will be inserted here -->
                        </div>
                        <button type="button" class="btn btn-success" onclick="addDetail()">Add New</button>
                        <br>
                        <button type="button" class="btn btn-secondary mt-2" onclick="prevStep()">Previous</button>
                        <button type="button" class="btn btn-primary mt-2" onclick="nextStep()">Next</button>
                    </div>

                    <!-- Step 3: Confirmation -->
                    <div class="form-step" id="step3">
                        <h2>Confirmation</h2>
                        <!-- Summary and confirmation fields -->
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                        <button type="submit" class="btn btn-success">Submit Order</button>
                    </div>
                </form>
            </div>
        </main>
        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; Your Website 2023</div>
                    <div>
                        <a href="#">Privacy Policy</a>
                        &middot;
                        <a href="#">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <script>
function nextStep() {
    let currentStep = document.querySelector('.form-step.active-step');
    let nextStep = currentStep.nextElementSibling;
    while (nextStep && !nextStep.classList.contains('form-step')) {
        nextStep = nextStep.nextElementSibling;
    }
    if (nextStep) {
        currentStep.classList.remove('active-step');
        currentStep.style.display = 'none';
        nextStep.classList.add('active-step');
        nextStep.style.display = 'block';
    }
}

function prevStep() {
    let currentStep = document.querySelector('.form-step.active-step');
    let prevStep = currentStep.previousElementSibling;
    while (prevStep && !prevStep.classList.contains('form-step')) {
        prevStep = prevStep.previousElementSibling;
    }
    if (prevStep) {
        currentStep.classList.remove('active-step');
        currentStep.style.display = 'none';
        prevStep.classList.add('active-step');
        prevStep.style.display = 'block';
    }
}

function addDetail() {
    const container = document.getElementById('orderDetailsContainer');
    const index = container.children.length + 1;
    const html = `
        <div class="detail-entry mb-3" id="detail-${index}">
            <div class="mb-3">
                <label for="title-${index}" class="form-label">Title:</label>
                <input type="text" class="form-control" id="title-${index}" name="titles[]" required>
            </div>
            <div class="mb-3 row">
                <label class="form-label col-sm-2">Wall Dimensions:</label>
                <div class="col">
                    <input type="number" class="form-control" placeholder="Length (m)" id="length-${index}" name="lengths[]" oninput="calculateAreas(${index})">
                </div>
                <div class="col">
                    <input type="number" class="form-control" placeholder="Width (m)" id="width-${index}" name="widths[]" oninput="calculateAreas(${index})">
                </div>
                <div class="col">
                    <input type="number" class="form-control" placeholder="Height (m)" id="height-${index}" name="heights[]" oninput="calculateAreas(${index})">
                </div>
                <div class="col-12">
                    <p id="wall_area-${index}">Wall Area: 0 m²</p>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="form-label col-sm-2">Floor Dimensions:</label>
                <div class="col">
                    <input type="number" class="form-control" placeholder="Length (m)" id="floor_length-${index}" name="floor_lengths[]" oninput="calculateAreas(${index})">
                </div>
                <div class="col">
                    <input type="number" class="form-control" placeholder="Width (m)" id="floor_width-${index}" name="floor_widths[]" oninput="calculateAreas(${index})">
                </div>
                <div class="col-12">
                    <p id="floor_area-${index}">Floor Area: 0 m²</p>
                </div>
            </div>
            <div class="col-12">
                <p id="total_area-${index}">Total Area: 0 m²</p>
            </div>
            <div class="mb-3">
                <label for="category-${index}" class="form-label">Category:</label>
                <select class="form-select" id="category-${index}" name="categories[]">
                    <?php
                    include '../db_connect.php'; // Include your database connection
                    $sql = "SELECT category_id, category_name FROM category";
                    $result = $mysqli->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row["category_id"] . '">' . $row["category_name"] . '</option>';
                        }
                    } else {
                        echo '<option>No categories available</option>';
                    }
                    $mysqli->close();
                    ?>
                </select>
            </div>
            <button type="button" class="btn btn-danger" onclick="removeDetail('detail-${index}')">Remove</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function removeDetail(id) {
    const detail = document.getElementById(id);
    detail && detail.parentNode.removeChild(detail);
}

function calculateAreas(index) {
    const length = parseFloat(document.getElementById(`length-${index}`).value) || 0;
    const width = parseFloat(document.getElementById(`width-${index}`).value) || 0;
    const height = parseFloat(document.getElementById(`height-${index}`).value) || 0;
    const wallArea = length * width*height;
    document.getElementById(`wall_area-${index}`).textContent = `Wall Area: ${wallArea.toFixed(2)} m²`;

    const floor_length = parseFloat(document.getElementById(`floor_length-${index}`).value) || 0;
    const floor_width = parseFloat(document.getElementById(`floor_width-${index}`).value) || 0;
    const floorArea = floor_length * floor_width;
    document.getElementById(`floor_area-${index}`).textContent = `Floor Area: ${floorArea.toFixed(2)} m²`;

    const totalArea = wallArea + floorArea;
    document.getElementById(`total_area-${index}`).textContent = `Total Area: ${totalArea.toFixed(2)} m²`;
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.form-step').forEach(step => {
        step.style.display = 'none'; // Initially hide all steps
    });
    document.querySelector('.form-step.active-step').style.display = 'block'; // Show only the active step
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="../assets/demo/chart-area-demo.js"></script>
    <script src="../assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
</body>
</html>
