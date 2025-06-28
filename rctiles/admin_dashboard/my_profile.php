<?php
include "../db_connect.php";
 include "admin_header.php"; 

$user_id = $_SESSION['user_id'];// Fetch user details (assuming user ID is from session or URL)
// For demonstration, using hardcoded user_id
 

$query = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .profile-card {
            max-width: 850px;
            min-height: 350px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
        }
        .profile-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .profile-header {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 10px;
        }
        .profile-header:after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #3498db, #2ecc71);
        }
        .profile-img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .profile-img:hover {
            transform: scale(1.05);
        }
        .detail-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: 600;
            color: #3498db;
            margin-bottom: 0.5rem;
        }
        .detail-value {
            font-size: 1.1rem;
            color: #2c3e50;
        }
        @media (max-width: 768px) {
            .profile-img {
                width: 150px;
                height: 150px;
                margin-bottom: 1.5rem;
            }
            .profile-header {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-3">
                <div class="card my-4 profile-card mx-auto">
                    <div class="card-body p-4">
                        <h1 class="profile-header text-center">My Profile</h1>
                        <div class="row g-3 ms-md-5">
                            <div class="col-md-4 text-center">
                                <img src="../uploads/<?= htmlspecialchars($user['user_image']); ?>" alt="User Image" class="profile-img rounded-circle shadow">
                            </div>
                            <div class="col-md-8">
                                <div class="detail-item">
                                    <div class="detail-label">Name:</div>
                                    <div class="detail-value"><?= htmlspecialchars($user['name']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Email:</div>
                                    <div class="detail-value"><?= htmlspecialchars($user['email']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Phone Number:</div>
                                    <div class="detail-value"><?= htmlspecialchars($user['phone_no']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Aadhar Number:</div>
                                    <div class="detail-value"><?= htmlspecialchars($user['aadhar_id_no']); ?></div>
                                </div>
                                <div class="detail-item">
                                    <div class="detail-label">Role:</div>
                                    <div class="detail-value">
                                        <?php 
                                            $roleQuery = $mysqli->prepare("SELECT role_name FROM roles WHERE role_id = ?");
                                            $roleQuery->bind_param("i", $user['role_id']);
                                            $roleQuery->execute();
                                            $roleResult = $roleQuery->get_result()->fetch_assoc();
                                            echo htmlspecialchars($roleResult['role_name']);
                                        ?>
                                    </div>
                                </div>
                                <?php if ($user['role_id'] == 3): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Storage Area:</div>
                                        <div class="detail-value">
                                            <?php 
                                                $storageQuery = $mysqli->prepare("SELECT storage_area_name FROM storage_areas WHERE storage_area_id = ?");
                                                $storageQuery->bind_param("i", $user['storage_area_id']);
                                                $storageQuery->execute();
                                                $storageResult = $storageQuery->get_result()->fetch_assoc();
                                                echo htmlspecialchars($storageResult['storage_area_name']);
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/scripts.js"></script>
</body>
</html>
