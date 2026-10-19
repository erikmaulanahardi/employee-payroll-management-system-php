<?php
require 'function.php';
require 'cek.php';

$username = $_SESSION['username'];

// Proses jika tombol Ganti Password diklik
if (isset($_POST['update_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_baru = $_POST['konfirmasi_baru'];

    // 1. Validasi panjang password baru
    if (strlen($password_baru) < 6) {
        $error = "Password baru minimal harus 6 karakter!";
    } 
    // 2. Validasi kecocokan password baru & konfirmasi
    else if ($password_baru !== $konfirmasi_baru) {
        $error = "Konfirmasi password baru tidak cocok!";
    } 
    else {
        // 3. Ambil password hash saat ini dari database berdasarkan username yang sedang login
        $stmt_cek = mysqli_prepare($conn, "SELECT password FROM account WHERE username = ?");
        mysqli_stmt_bind_param($stmt_cek, "s", $username);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);
        $data = mysqli_fetch_assoc($result_cek);
        mysqli_stmt_close($stmt_cek);

        if ($data) {
            // 4. Verifikasi apakah password lama yang diinput sudah benar
            if (password_verify($password_lama, $data['password'])) {
                
                // 5. Jika benar, hash password baru dengan Bcrypt
                $password_baru_hashed = password_hash($password_baru, PASSWORD_BCRYPT);
                
                // 6. Update password baru ke database secara aman (Prepared Statement)
                $stmt_update = mysqli_prepare($conn, "UPDATE account SET password = ? WHERE username = ?");
                mysqli_stmt_bind_param($stmt_update, "ss", $password_baru_hashed, $username);
                $sukses_update = mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);

                if ($sukses_update) {
                    $success = "Password Anda berhasil diperbarui! Mengalihkan...";
                    // Otomatis lempar balik ke index setelah 2 detik
                    header('refresh:2;url=index.php');
                } else {
                    $error = "Terjadi kesalahan sistem, gagal memperbarui password.";
                }

            } else {
                $error = "Password lama yang Anda masukkan salah!";
            }
        } else {
            $error = "Sesi pengguna tidak valid.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Project TTU - Ganti Password</title>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .brand-title { font-size: 1.05rem; line-height: 1.2; color: #ffffff; display: inline-block; }
        .brand-title strong { font-size: 1.15rem; letter-spacing: 0.5px; color: #ffffff; text-shadow: 0px 0px 6px rgba(255, 255, 255, 0.45); }
        .brand-subtitle { font-size: 0.85rem; opacity: 0.85; letter-spacing: 0.3px; }
        .navbar-brand:hover .brand-title strong { text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.7); }
        
        /* BOX JUDUL GRADIENT PREMIUM BY GEMINI */
        .page-header-box {
            background: linear-gradient(135deg, #212529, #343a40);
            color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark shadow-sm">
        <a class="navbar-brand ps-3 d-flex align-items-center" href="index.php" style="height: 56px;">
            <span class="brand-title">
                <strong>Sistem Data Karyawan</strong>
                <br>
                <span class="text-muted" style="font-size: 0.78rem; opacity: 0.85; letter-spacing: 0.2px;">PT BPR BKK Ungaran (Perseroda)</span>
            </span>
        </a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 text-white opacity-75" id="sidebarToggle" href="#!"><i class="fas fa-bars fa-lg"></i></button>
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0"></form>
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
                        <a class="dropdown-item py-2 small text-danger fw-bold" href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                            <i class="fas fa-sign-out-alt me-2" style="width: 15px;"></i> Log Out Sistem
                        </a>
                    </li>
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
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div> Menu Utama
                        </a>
                        <div class="sb-sidenav-menu-heading">Manajemen Karyawan</div>
                        <a class="nav-link" href="data_karyawan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div> Data Karyawan
                        </a>
                        <div class="sb-sidenav-menu-heading">Fitur Lainnya</div>
                        <a class="nav-link" href="rekap_penggajian.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div> Rekap Penggajian
                        </a>
                        <?php if($_SESSION['role'] == 'admin'){ ?>
                            <div class="sb-sidenav-menu-heading">Menu Admin</div>
                            <a class="nav-link" href="tambah_karyawan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div> Tambah Karyawan
                            </a>
                            <a class="nav-link" href="presensi.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-clipboard-check"></i></div> Presensi Karyawan
                            </a>
                            <a class="nav-link" href="lihat_presensi.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div> Data Presensi
                            </a>
                            <a class="nav-link" href="menu_pengaturan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div> Pengaturan Sistem
                            </a>
                        <?php } ?>
                        <a class="nav-link" href="absen_karyawan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div> Absen Mandiri Karyawan
                        </a>
                        <a class="nav-link active" href="ganti_password.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div> Ganti Password
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <strong><?= htmlspecialchars($username); ?></strong>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 pt-4">
                    
                    <div class="page-header-box d-flex align-items-center mb-4">
                        <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-warning me-3">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0 text-white">Keamanan Akun</h2>
                            <p class="text-white-50 mb-0 small">PT BPR BKK Ungaran (Perseroda) &bull; Pengaturan Pembaruan Kredensial Pengguna</p>
                        </div>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Menu Utama</a></li>
                        <li class="breadcrumb-item active">Ganti Password</li>
                    </ol>

                    <div class="row justify-content-start">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4 border-0">
                                <div class="card-header bg-dark text-white fw-bold">
                                    <i class="fas fa-key me-1 text-warning"></i> Form Pembaruan Password
                                </div>
                                <div class="card-body bg-white">
                                    
                                    <?php if(isset($error)): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            <?= htmlspecialchars($error); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(isset($success)): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <?= htmlspecialchars($success); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    <?php endif; ?>

                                    <form method="post">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-muted text-uppercase">Password Saat Ini (Lama)</label>
                                            <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password lama Anda" required />
                                        </div>
                                        <hr class="opacity-25">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-muted text-uppercase">Password Baru</label>
                                            <input type="password" name="password_baru" class="form-control border-primary" placeholder="Minimal 6 karakter" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small text-muted text-uppercase">Konfirmasi Password Baru</label>
                                            <input type="password" name="konfirmasi_baru" class="form-control border-primary" placeholder="Ulangi password baru" required />
                                        </div>
                                        
                                        <div class="mt-4 d-flex gap-2">
                                            <button type="submit" name="update_password" class="btn btn-primary shadow-sm">
                                                <i class="fas fa-save me-1"></i> Simpan Password Baru
                                            </button>
                                            <a href="index.php" class="btn btn-secondary shadow-sm">Batal</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; PT BPR BKK Ungaran (PERSERODA) 2026</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>