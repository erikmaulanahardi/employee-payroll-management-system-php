<?php
require 'function.php';
require 'cek.php';
require 'kategori_jabatan.php';

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'user';
}

$username = $_SESSION['username'];

// Ambil pengaturan denda denda dari database
$query_pengaturan = mysqli_query($conn, "SELECT potongan_per_jam FROM pengaturan_potongan LIMIT 1");
$pengaturan = mysqli_fetch_array($query_pengaturan);
$potongan_per_jam = $pengaturan['potongan_per_jam'] ?? 5882;

// =================================================================
// LOGIKA BACKEND: PROSES SIMPAN EDIT DATA GAJI & SINKRONISASI TOTAL
// =================================================================
if (isset($_POST['updatedatagaji'])) {
    $no_id = $_POST['no'];
    $nama_pegawai = trim($_POST['nama_pegawai']);
    $kantor_cabang = trim($_POST['kantor']);
    $jabatan_pegawai = trim($_POST['jabatan_pegawai']);
    $gaji_pokok = floatval($_POST['gaji_pokok']);
    $tunjangan_keluarga = floatval($_POST['tunjangan_keluarga']);
    $tunjangan_pangan = floatval($_POST['tunjangan_pangan']);
    $tunjangan_jabatan = floatval($_POST['tunjangan_jabatan']);
    $tunjangan_keahliankhusus = floatval($_POST['tunjangan_keahliankhusus']);
    $tunjangan_operasional = floatval($_POST['tunjangan_operasional']);
    $tunjangan_kesehatan = floatval($_POST['tunjangan_kesehatan']);
    $tunjangan_pph = floatval($_POST['tunjangan_pph']);
    $tunjangan_lembur = floatval($_POST['tunjangan_lembur']);
    
    $stmt = mysqli_prepare($conn, "UPDATE data_gaji SET nama_pegawai=?, kantor=?, jabatan_pegawai=?, gaji_pokok=?, tunjangan_keluarga=?, tunjangan_pangan=?, tunjangan_jabatan=?, tunjangan_keahliankhusus=?, tunjangan_operasional=?, tunjangan_kesehatan=?, tunjangan_pph=?, tunjangan_lembur=? WHERE no=?");
    mysqli_stmt_bind_param($stmt, "sssdddddddddi", $nama_pegawai, $kantor_cabang, $jabatan_pegawai, $gaji_pokok, $tunjangan_keluarga, $tunjangan_pangan, $tunjangan_jabatan, $tunjangan_keahliankhusus, $tunjangan_operasional, $tunjangan_kesehatan, $tunjangan_pph, $tunjangan_lembur, $no_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Sukses: Rekapitulasi data penggajian berhasil diperbarui!'); window.location='rekap_penggajian.php';</script>";
    } else {
        echo "<script>alert('Error: Gagal memperbarui data penggajian ke database!'); window.location='rekap_penggajian.php';</script>";
    }
    mysqli_stmt_close($stmt);
}

// Ambil data untuk tabel (Jika role user, kunci datanya hanya miliknya sendiri)
if ($_SESSION['role'] == 'admin') {
    $raw_gaji = mysqli_query($conn, "SELECT * FROM data_gaji ORDER BY no ASC");
} else {
    // Role USER: Proteksi filter NIP dinamis dari data pegawai
    $stmt_user = mysqli_prepare($conn, "SELECT nip_pegawai FROM data_pegawai WHERE email_pegawai = ? OR nip_pegawai = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_user, "ss", $username, $username);
    mysqli_stmt_execute($stmt_user);
    $res_user = mysqli_stmt_get_result($stmt_user);
    $data_user = mysqli_fetch_assoc($res_user);
    $user_nip = $data_user['nip_pegawai'] ?? '';
    mysqli_stmt_close($stmt_user);

    $raw_gaji = mysqli_query($conn, "SELECT * FROM data_gaji WHERE nip_pegawai = '$user_nip'");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Rekap Penggajian - PT BPR BKK Ungaran</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .brand-title { font-size: 1.05rem; line-height: 1.2; color: #ffffff; display: inline-block; }
        .brand-title strong { font-size: 1.15rem; letter-spacing: 0.5px; color: #ffffff; text-shadow: 0px 0px 6px rgba(255, 255, 255, 0.45); }
        .brand-subtitle { font-size: 0.85rem; opacity: 0.85; letter-spacing: 0.3px; }
        .navbar-brand:hover .brand-title strong { text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.7); }
        
        /* BOX JUDUL EYE-CATCHING GRADIENT BY GEMINI */
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
                    <a class="nav-link active" href="rekap_penggajian.php">
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
                
                <div class="page-header-box d-flex align-items-center mb-4">
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3 text-warning me-3">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-0 text-white">Rekap Penggajian</h2>
                        <p class="text-white-50 mb-0 small">PT BPR BKK Ungaran (Perseroda) &bull; Ringkasan Slip Payroll Bulanan Karyawan</p>
                    </div>
                </div>

                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Rekap Penggajian</li>
                </ol>

                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-dark text-white fw-bold d-flex align-items-center">
                        <i class="fas fa-wallet me-2 text-warning"></i> 
                        <span>Data Ringkasan Payroll Bulanan Karyawan</span>
                    </div>
                    <div class="card-body bg-white text-dark">
                        <div class="table-responsive">
                            <table id="datatablesSimple" class="table table-bordered table-striped table-hover align-middle small">
                                <thead class="table-light text-center align-middle">
                                    <tr>
                                        <th>No</th>
                                        <th>NIP Pegawai</th>
                                        <th>Nama Lengkap</th>
                                        <th>Kantor / Cabang</th>
                                        <th>Jabatan</th>
                                        <th>Gaji Pokok</th>
                                        <th>Total Tunjangan</th>
                                        <th>Potongan Telat</th>
                                        <th>Gaji Bersih (THP)</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no_urut = 1;
                                    while ($row = mysqli_fetch_array($raw_gaji)) {
                                        $no_id = $row['no'];
                                        $nip = $row['nip_pegawai'];
                                        
                                        // Proses kalkulasi denda denda terlambat dinamis
                                        $potongan_telat = ($row['jam_terlambat'] ?? 0) * $potongan_per_jam;
                                        $t_operasional_bersih = max(0, $row['tunjangan_operasional'] - $potongan_telat);
                                        
                                        // Kalkulasi Total Tunjangan
                                        $total_tunjangan = $row['tunjangan_keluarga'] + $row['tunjangan_pangan'] + $row['tunjangan_jabatan'] + $row['tunjangan_keahliankhusus'] + $t_operasional_bersih + $row['tunjangan_kesehatan'] + $row['tunjangan_pph'] + $row['tunjangan_lembur'];
                                        
                                        // Take Home Pay (THP)
                                        $gaji_bersih_thp = $row['gaji_pokok'] + $total_tunjangan;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no_urut++; ?></td>
                                        <td class="font-monospace fw-bold"><?= htmlspecialchars($nip); ?></td>
                                        <td><?= htmlspecialchars($row['nama_pegawai']); ?></td>
                                        <td><?= htmlspecialchars($row['kantor']); ?></td>
                                        <td><?= htmlspecialchars($row['jabatan_pegawai']); ?></td>
                                        <td class="text-end">Rp <?= number_format($row['gaji_pokok'], 0, ',', '.'); ?></td>
                                        <td class="text-end text-success">Rp <?= number_format($total_tunjangan, 0, ',', '.'); ?></td>
                                        <td class="text-end text-danger">-Rp <?= number_format($potongan_telat, 0, ',', '.'); ?></td>
                                        <td class="text-end fw-bold text-primary">Rp <?= number_format($gaji_bersih_thp, 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="cetak_slip.php?no=<?= $no_id; ?>&aksi=cetak" target="_blank" class="btn btn-sm btn-outline-primary btn-xs py-1 px-2 fw-bold shadow-sm">
                                                    <i class="fas fa-print"></i> Cetak Slip
                                                </a>
                                                <?php if($_SESSION['role'] == 'admin') { ?>
                                                <button class="btn btn-sm btn-dark btn-xs py-1 px-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#editModalGaji<?= $no_id; ?>">
                                                    <i class="fas fa-edit text-warning"></i> Edit
                                                </button>
                                                <?php } ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editModalGaji<?= $no_id; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content shadow-lg border-0" style="border-radius:15px;">
                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title fw-bold"><i class="fas fa-wallet text-warning me-2"></i>Edit Komponen Finansial Karyawan</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body bg-light row g-3 px-4 py-3">
                                                        <input type="hidden" name="no" value="<?= $no_id; ?>">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">Nomor Induk Pegawai (NIP)</label>
                                                            <input type="text" class="form-control bg-white font-monospace fw-bold" value="<?= htmlspecialchars($nip); ?>" disabled>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">Nama Lengkap Karyawan</label>
                                                            <input type="text" name="nama_pegawai" class="form-control" value="<?= htmlspecialchars($row['nama_pegawai']); ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold">Kantor / Cabang</label>
                                                            <input type="text" name="kantor" class="form-control" value="<?= htmlspecialchars($row['kantor']); ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold">Jabatan Struktural</label>
                                                            <input type="text" name="jabatan_pegawai" class="form-control" value="<?= htmlspecialchars($row['jabatan_pegawai']); ?>" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small fw-bold">Gaji Pokok Utama (Rp)</label>
                                                            <input type="number" name="gaji_pokok" class="form-control fw-bold border-primary" value="<?= $row['gaji_pokok']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="col-12"><hr class="my-1 opacity-25"></div>
                                                        
                                                        <div class="col-md-4">
                                                            <label class="form-label small text-success fw-bold">Tunjangan Keluarga (Rp)</label>
                                                            <input type="number" name="tunjangan_keluarga" class="form-control border-success" value="<?= $row['tunjangan_keluarga']; ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small text-success fw-bold">Tunjangan Pangan (Rp)</label>
                                                            <input type="number" name="tunjangan_pangan" class="form-control border-success" value="<?= $row['tunjangan_pangan']; ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small text-success fw-bold">Tunjangan Jabatan (Rp)</label>
                                                            <input type="number" name="tunjangan_jabatan" class="form-control border-success" value="<?= $row['tunjangan_jabatan']; ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small text-success fw-bold">Tunjangan Keahlian Khusus (Rp)</label>
                                                            <input type="number" name="tunjangan_keahliankhusus" class="form-control border-success" value="<?= $row['tunjangan_keahliankhusus']; ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small text-success fw-bold">Tunjangan Operasional (Rp)</label>
                                                            <input type="number" name="tunjangan_operasional" class="form-control border-success" value="<?= $row['tunjangan_operasional']; ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small text-success fw-bold">Tunjangan Kesehatan (Rp)</label>
                                                            <input type="number" name="tunjangan_kesehatan" class="form-control border-success" value="<?= $row['tunjangan_kesehatan']; ?>">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label small text-success fw-bold">Tunjangan PPh Pasal 21 (Rp)</label>
                                                            <input type="number" name="tunjangan_pph" class="form-control border-success" value="<?= $row['tunjangan_pph']; ?>">
                                                        </div>
                                                        <div class="col-md-8">
                                                            <label class="form-label small text-info fw-bold">Akumulasi Tunjangan Lembur Bulan Ini (Rp)</label>
                                                            <input type="number" name="tunjangan_lembur" class="form-control border-info fw-bold bg-white text-dark" value="<?= $row['tunjangan_lembur']; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-secondary shadow-sm btn-sm" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="updatedatagaji" class="btn btn-primary shadow-sm btn-sm fw-bold"><i class="fas fa-save me-1 text-success"></i> Terapkan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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