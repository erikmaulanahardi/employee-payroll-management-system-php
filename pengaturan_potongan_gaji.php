<?php

require 'function.php';
require 'cek.php';
require 'kategori_jabatan.php';

if ($_SESSION['role'] !== 'admin') {
    header('location:index.php?error=Anda tidak memiliki hak akses ke halaman tersebut!');
    exit;
}

$success = "";
$error = "";

if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $potongan_per_jam = intval($_POST['potongan_per_jam']);
    
    // UPDATE POTONGAN
    $result = mysqli_query($conn, "UPDATE pengaturan_potongan SET potongan_per_jam=$potongan_per_jam WHERE id=$id");
    
    if ($result) {
        $success = "Pengaturan potongan gaji berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui pengaturan potongan gaji!";
    }
}

// Ambil semua pengaturan potongan
$q = mysqli_query($conn, "SELECT * FROM pengaturan_potongan LIMIT 1");
$row = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Pengaturan Potongan Gaji - Sistem Manajemen Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<style>
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
        text-shadow: 0px 0px 10px rgba(255,255,255,0.7);
    }

    .navbar-brand:hover .brand-subtitle {
        opacity: 1;
    }
</style>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php">
            <strong>Sistem Manajemen Data Karyawan</strong><br>
            <span style="font-size: 0.85rem;">PT BPR BKK Ungaran (Perseroda)</span>
        </a>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                </ul>
            </li>
        </ul>
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
                        <?php if($_SESSION['role'] == 'admin'){ ?>
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
                        <a class="nav-link" href="menu_pengaturan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                            Pengaturan Sistem
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </nav>
        </div>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Pengaturan Potongan Gaji</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Menu Utama</a></li>
                        <li class="breadcrumb-item"><a href="menu_pengaturan.php">Pengaturan Sistem</a></li>
                        <li class="breadcrumb-item active">Pengaturan Potongan Gaji</li>
                    </ol>
                    
                    <?php if ($success != ""): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error != ""): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> <strong>Informasi:</strong><br>
                        Halaman ini digunakan untuk mengatur nilai potongan gaji karyawan berdasarkan jam keterlambatan. 
                        Perubahan akan langsung berlaku untuk semua perhitungan tunjangan operasional karyawan.
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-sliders-h"></i> Pengaturan Potongan Gaji
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="id" value="<?= $row['id'] ?? 1 ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label"><strong>Potongan per Jam Keterlambatan (Rp/jam)</strong></label>
                                    <input type="number" name="potongan_per_jam" class="form-control" value="<?= $row['potongan_per_jam'] ?? 5882 ?>" required>
                                    <small class="form-text text-muted">Nominal yang akan dikurangi dari tunjangan operasional untuk setiap 1 jam keterlambatan</small>
                                </div>
                                
                                <div class="alert alert-secondary">
                                    <strong>Catatan:</strong><br>
                                    Perubahan akan berlaku untuk semua perhitungan tunjangan operasional karyawan yang baru dibuat setelah ini.
                                </div>
                                
                                <button type="submit" name="update" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                                <a href="menu_pengaturan.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </form>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>