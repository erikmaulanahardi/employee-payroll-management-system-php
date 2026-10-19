<?php
require 'function.php';
require 'cek.php';
require 'kategori_jabatan.php';

$username = $_SESSION['username'];

// Hak Akses Khusus Admin
if ($_SESSION['role'] !== 'admin') {
    header('location:index.php?error=Anda tidak memiliki hak akses ke halaman tersebut!');
    exit;
}

$success = "";
$error = "";

if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $tarif_luar_jam_kerja = intval($_POST['tarif_luar_jam_kerja']);
    $tarif_luar_hari_kerja = intval($_POST['tarif_luar_hari_kerja']);
    
    // 1. Ambil nama kategori jabatan yang diubah berdasarkan ID (Prepared Statement)
    $stmt_kat = mysqli_prepare($conn, "SELECT kategori_jabatan FROM tarif_lembur WHERE id = ?");
    mysqli_stmt_bind_param($stmt_kat, "i", $id);
    mysqli_stmt_execute($stmt_kat);
    $res_kat = mysqli_stmt_get_result($stmt_kat);
    $row_kategori = mysqli_fetch_assoc($res_kat);
    $kategori_target = $row_kategori['kategori_jabatan'] ?? '';
    mysqli_stmt_close($stmt_kat);
    
    // 2. Ekseksusi Update Tarif Baru ke Tabel Master
    $stmt_up = mysqli_prepare($conn, "UPDATE tarif_lembur SET tarif_luar_jam_kerja = ?, tarif_luar_hari_kerja = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt_up, "iii", $tarif_luar_jam_kerja, $tarif_luar_hari_kerja, $id);
    $result_update_tarif = mysqli_stmt_execute($stmt_up);
    mysqli_stmt_close($stmt_up);
    
    if ($result_update_tarif) {
        $bulan_berjalan = date('m');
        $tahun_berjalan = date('Y');
        
        // 3. Ambil semua data pegawai dari database untuk proses pemindaian massal
        $q_all_pegawai = mysqli_query($conn, "SELECT nip_pegawai, jabatan_pegawai FROM data_gaji");
        $count_updated = 0;
        
        while ($pegawai = mysqli_fetch_assoc($q_all_pegawai)) {
            $nip = $pegawai['nip_pegawai'];
            $jabatan = $pegawai['jabatan_pegawai'];
            
            // COCOKKAN JABATAN DENGAN ARRAY DI FILE KATEGORI_JABATAN.PHP
            $kat_jabatan_pegawai = $kategori_jabatan[$jabatan] ?? null;
            
            // Jika kategori jabatan pegawai ini sama dengan tarif kategori yang baru saja diubah admin
            if ($kat_jabatan_pegawai === $kategori_target) {
                
                // HITUNG ULANG AKUMULASI NOMINAL LEMBUR SECARA REAL-TIME DARI LOG PRESENSI BULANAN
                $query_hitung_lembur = "
                    SELECT 
                        SUM(CASE 
                            WHEN DATE_FORMAT(tanggal, '%w') >= 6 OR DATE_FORMAT(tanggal, '%w') = 0 THEN jam_lembur * ?
                            ELSE jam_lembur * ?
                        END) AS kalkulasi_lembur_baru
                    FROM presensi
                    WHERE nip_pegawai = ?
                    AND MONTH(tanggal) = ?
                    AND YEAR(tanggal) = ?
                ";
                
                $stmt_calc = mysqli_prepare($conn, $query_hitung_lembur);
                mysqli_stmt_bind_param($stmt_calc, "iisss", $tarif_luar_hari_kerja, $tarif_luar_jam_kerja, $nip, $bulan_berjalan, $tahun_berjalan);
                mysqli_stmt_execute($stmt_calc);
                $res_calc = mysqli_stmt_get_result($stmt_calc);
                $data_lembur = mysqli_fetch_assoc($res_calc);
                $total_lembur_baru = $data_lembur['kalkulasi_lembur_baru'] ?? 0;
                mysqli_stmt_close($stmt_calc);
                
                // UPDATE TUNJANGAN LEMBUR BARU KE TABEL DATA_GAJI AGAR REKAP & SLIP OTOMATIS BERUBAH
                $stmt_up_gaji = mysqli_prepare($conn, "UPDATE data_gaji SET tunjangan_lembur = ? WHERE nip_pegawai = ?");
                mysqli_stmt_bind_param($stmt_up_gaji, "ds", $total_lembur_baru, $nip);
                if (mysqli_stmt_execute($stmt_up_gaji)) {
                    $count_updated++;
                }
                mysqli_stmt_close($stmt_up_gaji);
            }
        }
        
        $success = "Sukses! Tarif lembur untuk kategori <strong>" . htmlspecialchars($kategori_target) . "</strong> berhasil diperbarui. Sistem berhasil mere kalkulasi otomatis nominal lembur dari " . $count_updated . " pegawai terkait bulan ini.";
    } else {
        $error = "Gagal memperbarui tarif lembur di sistem database!";
    }
}

// Ambil data tarif lembur terbaru untuk ditampilkan ke tabel
$q_tarif = mysqli_query($conn, "SELECT * FROM tarif_lembur ORDER BY kategori_jabatan ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Pengaturan Rumus Lembur - Sistem Manajemen Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .brand-title { font-size: 1.05rem; line-height: 1.2; color: #ffffff; display: inline-block; }
        .brand-title strong { font-size: 1.15rem; letter-spacing: 0.5px; color: #ffffff; text-shadow: 0px 0px 6px rgba(255, 255, 255, 0.45); }
        .brand-subtitle { font-size: 0.85rem; opacity: 0.85; letter-spacing: 0.3px; }
        .navbar-brand:hover .brand-title strong { text-shadow: 0px 0px 10px rgba(255,255,255,0.7); }
        .navbar-brand:hover .brand-subtitle { opacity: 1; }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3 d-flex align-items-center" href="index.php">
            <span class="brand-title">
                <strong>Sistem Manajemen Data Karyawan</strong>
                <br>
                <span class="brand-subtitle">PT BPR BKK Ungaran (Perseroda)</span>
            </span>
        </a>
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0"></form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="logout.php">Log Out Akun</a></li>
                </ul>
            </li>
        </ul>
        <button class="btn btn-link text-white me-3" id="sidebarToggle"><i class="fas fa-bars fa-lg"></i></button>
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
                        <a class="nav-link" href="ganti_password.php">
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
                    <h1>Pengaturan Tarif Lembur</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Menu Utama</a></li>
                        <li class="breadcrumb-item active">Pengaturan Tarif Lembur</li>
                    </ol>
                    
                    <?php if ($success != ""): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error != ""): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-1"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info shadow-sm" role="alert">
                        <i class="fas fa-info-circle"></i> <strong>Informasi:</strong><br>
                        Halaman ini digunakan untuk mengubah tarif lembur berdasarkan kategori jabatan dan jam / hari kerja.
                    </div>
                    
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-calculator me-1"></i> Daftar Tarif Lembur Per Kategori Jabatan
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%" class="text-center">No</th>
                                            <th width="40%">Kategori Jabatan</th>
                                            <th width="22%" class="text-center">Tarif Luar Jam Kerja (Hari Biasa)</th>
                                            <th width="22%" class="text-center">Tarif Luar Hari Kerja (Sabtu/Minggu)</th>
                                            <th width="11%" class="text-center">Aksi Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; while($row = mysqli_fetch_assoc($q_tarif)): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><strong><?= htmlspecialchars($row['kategori_jabatan']) ?></strong></td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark fw-bold px-3 py-2">
                                                    Rp <?= number_format($row['tarif_luar_jam_kerja'], 0, ',', '.') ?> / Jam
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                                                    Rp <?= number_format($row['tarif_luar_hari_kerja'], 0, ',', '.') ?> / Jam
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit me-1 text-warning"></i> Ubah Tarif
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content shadow-lg border-0">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title fw-bold"><i class="fas fa-sliders-h me-2"></i>Ubah Tarif Lembur</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body bg-light">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold small">Kategori Jabatan</label>
                                                                <input type="text" class="form-control bg-white fw-bold" value="<?= htmlspecialchars($row['kategori_jabatan']) ?>" disabled>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold small text-info">Tarif Luar Jam Kerja (Rp/Jam)</label>
                                                                <input type="number" name="tarif_luar_jam_kerja" class="form-control border-info" value="<?= $row['tarif_luar_jam_kerja'] ?>" required>
                                                                <div class="form-text text-muted small">*Digunakan untuk lembur hari biasa (Senin - Jumat) selepas jam operasional kantor.</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold small text-warning">Tarif Luar Hari Kerja (Rp/Jam)</label>
                                                                <input type="number" name="tarif_luar_hari_kerja" class="form-control border-warning" value="<?= $row['tarif_luar_hari_kerja'] ?>" required>
                                                                <div class="form-text text-muted small">*Digunakan untuk lembur di hari libur resmi pekanan kantor (Sabtu - Minggu).</div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="update" class="btn btn-primary btn-sm fw-bold"><i class="fas fa-save me-1 text-success"></i> Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-5 shadow-sm">
                        <div class="card-header bg-dark text-white fw-bold"><i class="fas fa-square-root-alt me-1"></i>Informasi</div>
                        <div class="card-body bg-white">
                            <h5 class="fw-bold text-decoration-underline mb-3">Formula Inti Mesin Penggajian:</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light mb-3">
                                        <h6 class="fw-bold text-info"><i class="fas fa-calendar-alt"></i> 1. Lembur Hari Kerja (Senin-Jumat)</h6>
                                        <hr class="my-2">
                                        <p class="mb-1 small"><strong>Durasi Jam Lembur:</strong> Jam Absen Keluar - Jam 17:00 WIB</p>
                                        <p class="mb-0 small"><strong>Rumus Pendapatan:</strong> Durasi Lembur &times; Tarif Luar Jam Kerja</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded bg-light mb-3">
                                        <h6 class="fw-bold text-warning"><i class="fas fa-calendar-times"></i> 2. Lembur Hari Libur (Sabtu-Minggu)</h6>
                                        <hr class="my-2">
                                        <p class="mb-1 small"><strong>Durasi Jam Lembur:</strong> Jam Absen Keluar - Jam Absen Masuk</p>
                                        <p class="mb-0 small"><strong>Rumus Pendapatan:</strong> Durasi Lembur &times; Tarif Luar Hari Kerja</p>
                                    </div>
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