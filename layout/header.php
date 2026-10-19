<?php
// Proteksi session ditaruh di paling atas layout master
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Sistem Manajemen Karyawan - PT BPR BKK Ungaran</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    
    <style>
        /* CSS Kustom bawaan index.php agar halaman tetap konsisten premium */
        .menu-card {
            height: 200px;
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: 0.25s;
        }
        .menu-card:hover {
            transform: scale(1.05);
            box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.25);
        }
        .menu-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
        }
        .welcome-box {
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .welcome-title {
            font-size: 2.3rem;
            font-weight: 700;
        }
        .welcome-subtitle {
            font-size: 1.1rem;
            margin-top: 10px;
            opacity: 0.9;
        }
        .brand-title {
            font-size: 1.05rem;
            line-height: 1.2;
            color: #ffffff;
            display: inline-block;
        }
        .brand-title strong {
            font-size: 1.15rem;
            letter-spacing: 0.5px;
            color: #ffffff;
            text-shadow: 0px 0px 6px rgba(255, 255, 255, 0.45);
        }
        .brand-subtitle {
            font-size: 0.85rem;
            opacity: 0.85;
            letter-spacing: 0.3px;
        }
        .navbar-brand:hover .brand-title strong {
            text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.7);
        }
        .navbar-brand:hover .brand-subtitle {
            opacity: 1;
        }
        .analytics-card {
            border-radius: 15px;
            border: none;
            color: white;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark shadow-sm">
    <a class="navbar-brand ps-3 d-flex align-items-center" href="index.php" style="height: 56px;">
        <span class="brand-title" style="line-height: 1.2;">
            <strong style="color: #ffffff;">Sistem Data Karyawan</strong>
            <br>
            <span class="text-muted" style="font-size: 0.78rem; opacity: 0.85; letter-spacing: 0.2px;">
                PT BPR BKK Ungaran (Perseroda)
            </span>
        </span>
    </a>
    
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 text-white opacity-75" id="sidebarToggle" href="#!">
        <i class="fas fa-bars fa-lg"></i>
    </button>
    
    <div class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0"></div>
    
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center bg-secondary bg-opacity-25 px-3 py-1 rounded-pill text-white" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-lg me-2 text-warning"></i>
                <span class="d-none d-sm-inline-block small fw-bold text-uppercase tracking-wider">
                    <?= htmlspecialchars($_SESSION['role']); ?>
                </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdown" style="border-radius: 8px;">
                <li class="px-3 py-2 bg-light rounded-top">
                    <div class="text-muted small">Logged in as:</div>
                    <div class="fw-bold text-dark text-truncate" style="max-width: 160px;">
                        <i class="fas fa-id-badge me-1 text-primary"></i><?= htmlspecialchars($username); ?>
                    </div>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item py-2 small" href="ganti_password.php">
                        <i class="fas fa-key text-muted me-2" style="width: 15px;"></i> Ganti Password
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 small text-danger fw-bold" href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                        <i class="fas fa-sign-out-alt me-2" style="width: 15px;"></i> Log Out Sistem
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<div id="layoutSidenav"></div>