<?php
require 'function.php';
require 'cek.php';

$username = $_SESSION['username'];

if ($_SESSION['role'] !== 'admin') {
    header('location:index.php?error=Anda tidak memiliki hak akses ke halaman tersebut!');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Menu Pengaturan - Sistem Manajemen Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .menu-card {
            height: 280px;
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .menu-card:hover {
            transform: scale(1.08);
            box-shadow: 0px 12px 25px rgba(0, 0, 0, 0.3);
        }

        .menu-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .menu-card:hover .menu-icon {
            transform: scale(1.1);
            transition: 0.3s ease;
        }

        .menu-title {
            font-size: 1.3rem;
            font-weight: 700;
            text-align: center;
            line-height: 1.4;
        }

        .welcome-box {
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            color: white;
            padding: 50px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 50px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.95;
            line-height: 1.6;
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

        .menu-section-heading {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0d6efd;
            color: #333;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .card-link:hover {
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .menu-card {
                height: 240px;
            }

            .menu-icon {
                font-size: 3.5rem;
            }

            .menu-title {
                font-size: 1rem;
            }

            .welcome-title {
                font-size: 1.8rem;
            }

            .welcome-subtitle {
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3 d-flex align-items-center" href="index.php">
            <span class="brand-title">
                <strong>Sistem Manajemen Data Karyawan</strong>
                <br>
                <span class="brand-subtitle">PT BPR BKK Ungaran (Perseroda)</span>
            </span>
        </a>

        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">

            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="logout.php">Log Out Akun</a></li>
                </ul>
            </li>
        </ul>
        <button class="btn btn-link text-white me-3" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Menu Lobby</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                            Menu Utama
                        </a>
                        <div class="sb-sidenav-menu-heading">Manajemen Karyawan</div>
                        <a class="nav-link" href="data_karyawan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Data Karyawan
                        </a>
                        <div class="sb-sidenav-menu-heading">Fitur Lainnya</div>
                        <a class="nav-link" href="rekap_penggajian.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
                            Rekap Penggajian
                        </a>
                        <?php if ($_SESSION['role'] == 'admin') { ?>
                            <div class="sb-sidenav-menu-heading">Menu Admin</div>
                            <a class="nav-link" href="tambah_karyawan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                                Tambah Karyawan
                            </a>
                            <a class="nav-link" href="presensi.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-clipboard-check"></i></div>
                                Presensi Karyawan
                            </a>
                            <a class="nav-link" href="lihat_presensi.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                                Data Presensi
                            </a>
                            <a class="nav-link" href="menu_pengaturan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                                Pengaturan Sistem
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <?php echo $username; ?>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <div class="container py-5">
                        <!-- Welcome Box -->
                        <div class="welcome-box">
                            <div class="welcome-title">Menu Pengaturan Sistem</div>
                            <div class="welcome-subtitle">
                                Selamat datang, <strong><?php echo $username; ?></strong>!<br>
                                Kelola pengaturan sistem manajemen pegawai di sini
                            </div>
                        </div>

                        <!-- Menu Cards Section -->
                        <div class="container">
                            <h3 class="menu-section-heading">
                                <i class="fas fa-sliders-h"></i> Pengaturan Sistem
                            </h3>

                            <div class="row g-4 justify-content-center">

                                <!-- CARD 1: Pengaturan Uang Lembur (BIRU) -->
                                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                    <a href="pengaturan.php" class="card-link">
                                        <div class="card text-white bg-primary menu-card shadow-sm">
                                            <i class="bi bi-clock-history menu-icon"></i>
                                            <div class="menu-title">Pengaturan Tarif Lembur</div>
                                        </div>
                                    </a>
                                </div>

                                <!-- CARD 2: Pengaturan Pemotongan Gaji (MERAH) -->
                                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                    <a href="pengaturan_potongan_gaji.php" class="card-link">
                                        <div class="card text-white bg-danger menu-card shadow-sm">
                                            <i class="bi bi-calculator-fill menu-icon"></i>
                                            <div class="menu-title">Pengaturan Potongan Gaji</div>
                                        </div>
                                    </a>
                                </div>

                                <!-- CARD 3: Pengaturan Sistem Presensi (HIJAU) -->
                                <!-- <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                    <a href="pengaturan_presensi.php" class="card-link">
                                        <div class="card text-white menu-card shadow-sm"
                                            style="background: linear-gradient(135deg, #28a745, #20c997);">
                                            <i class="bi bi-calendar2-check-fill menu-icon"></i>
                                            <div class="menu-title">Pengaturan Sistem Presensi</div>
                                        </div>
                                    </a>
                                </div>  -->


                            </div>

                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; PT BPR BKK Ungaran 2025</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
</body>

</html>