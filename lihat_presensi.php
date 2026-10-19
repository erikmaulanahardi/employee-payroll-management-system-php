<?php
require 'function.php';
require 'cek.php';

$username = $_SESSION['username'];

// Proteksi hak akses: Hanya Admin atau user tervalidasi yang bisa melihat
if (!isset($_SESSION['role'])) {
    header('location:index.php?error=Silakan login terlebih dahulu!');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Data Presensi Karyawan - Sistem Manajemen Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .brand-title { font-size: 1.05rem; line-height: 1.2; color: #ffffff; display: inline-block; }
        .brand-title strong { font-size: 1.15rem; letter-spacing: 0.5px; color: #ffffff; text-shadow: 0px 0px 6px rgba(255, 255, 255, 0.45); }
        .brand-subtitle { font-size: 0.85rem; opacity: 0.85; letter-spacing: 0.3px; }
        .navbar-brand:hover .brand-title strong { text-shadow: 0px 0px 10px rgba(255,255,255,0.7); }
        .navbar-brand:hover .brand-subtitle { opacity: 1; }
        
        /* PLANG HEADER BOX GRADIENT EYE-CATCHING BY GEMINI */
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
                            <div class="sb-nav-link-icon"><i class="fas fa-clipboard-check"></i></div> Input Presensi
                        </a>
                        <a class="nav-link active" href="lihat_presensi.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div> Data Presensi
                        </a>
                        <a class="nav-link" href="absen_karyawan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div> Absen Mandiri Karyawan
                        </a>
                        <a class="nav-link" href="menu_pengaturan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div> Pengaturan Sistem
                        </a>
                        <?php } ?>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <?= htmlspecialchars($username); ?>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 pt-4">
                    
                    <div class="page-header-box d-flex align-items-center mb-4">
                        <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-warning me-3">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0 text-white">Riwayat Presensi Karyawan</h2>
                            <p class="text-white-50 mb-0 small">PT BPR BKK Ungaran (Perseroda) &bull; Arsip Kehadiran & Lembur Pegawai</p>
                        </div>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Data Presensi</li>
                    </ol>

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-dark text-white d-flex align-items-center">
                            <i class="fas fa-table me-2"></i>
                            <strong class="me-auto">Log Kehadiran & Lembur Pegawai</strong>
                            <?php if($_SESSION['role'] == 'admin') { ?>
                                <a href="presensi.php" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Tambah Presensi</a>
                            <?php } ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatablesSimple" class="table table-bordered table-hover table-striped align-middle text-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIP Pegawai</th>
                                            <th>Nama Karyawan</th>
                                            <th>Tanggal</th>
                                            <th>Jam Masuk</th>
                                            <th>Jam Keluar</th>
                                            <th>Jam Lembur</th>
                                            <th>Uang Lembur</th>
                                            <th>Jam Terlambat</th>
                                            <th>Potongan Tunjangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Ambil data langsung dari tabel presensi (Nama sudah tersimpan secara permanen di sana)
                                        $query_log = "SELECT * FROM presensi ORDER BY tanggal DESC, id DESC";
                                        $run_log = mysqli_query($conn, $query_log);
                                        $no = 1;

                                        while ($row = mysqli_fetch_array($run_log)) {
                                            $nip_pegawai = $row['nip_pegawai'];
                                            $nama_pegawai = $row['nama_pegawai'] ?: 'Pegawai';
                                            $tanggal = date('d-m-Y', strtotime($row['tanggal']));
                                            $jam_masuk = $row['jam_masuk'];
                                            $jam_keluar = $row['jam_keluar'];
                                            $jam_lembur = $row['jam_lembur'];
                                            $uang_lembur = number_format($row['uang_lembur'], 0, ',', '.');
                                            $jam_terlambat = $row['jam_terlambat'];
                                            $potongan_tunjangan = number_format($row['potongan_tunjangan'], 0, ',', '.');
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><strong><?= htmlspecialchars($nip_pegawai); ?></strong></td>
                                                <td><?= htmlspecialchars($nama_pegawai); ?></td>
                                                <td><?= $tanggal; ?></td>
                                                <td><span class="badge bg-success"><?= $jam_masuk; ?></span></td>
                                                <td><span class="badge bg-danger"><?= $jam_keluar; ?></span></td>
                                                <td><?= $jam_lembur; ?> Jam</td>
                                                <td>Rp <?= $uang_lembur; ?></td>
                                                <td>
                                                    <?php 
                                                    // Paksa data dari database menjadi desimal murni
                                                    $angka_telat = floatval($jam_terlambat); 
                                                    
                                                    // Tentukan class warna berdasarkan nilai keterlambatan
                                                    $class_warna = ($angka_telat > 0) ? 'text-danger fw-bold' : 'text-muted';
                                                    
                                                    // Tentukan teks yang akan ditampilkan
                                                    $teks_tampil = ($angka_telat > 0) ? $angka_telat . ' Jam' : 'Tepat Waktu';
                                                    ?>
                                                    
                                                    <span class="<?= $class_warna; ?>"><?= $teks_tampil; ?></span>
                                                </td>
                                                <td>
                                                    <?php if($row['potongan_tunjangan'] > 0) { ?>
                                                        <span class="text-danger">-Rp <?= $potongan_tunjangan; ?></span>
                                                    <?php } else { ?>
                                                        <span class="text-success">Rp 0</span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
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
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>
</html>