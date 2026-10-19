<?php
require 'function.php';
require 'cek.php';
require 'kategori_jabatan.php';

// Hak Akses Khusus Admin
if ($_SESSION['role'] !== 'admin') {
    header('location:index.php?error=Anda tidak memiliki hak akses ke halaman tersebut!');
    exit;
}

$username = $_SESSION['username'];

// =================================================================
// LOGIKA BACKEND: PROSES SIMPAN DATA PRESENSI & KALKULASI OTOMATIS
// =================================================================
if (isset($_POST['simpan_presensi'])) {
    $nip = trim($_POST['nip_pegawai']);
    $tanggal = $_POST['tanggal'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = $_POST['jam_keluar'];

    // 1. Anti-Cheat & Duplikasi (Prepared Statement)
    $stmt_cek = mysqli_prepare($conn, "SELECT id FROM presensi WHERE nip_pegawai = ? AND tanggal = ?");
    mysqli_stmt_bind_param($stmt_cek, "ss", $nip, $tanggal);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);
    $ada_duplikasi = mysqli_stmt_num_rows($stmt_cek) > 0;
    mysqli_stmt_close($stmt_cek);

    if ($ada_duplikasi) {
        echo "<script>alert('Error: Pegawai tersebut sudah tercatat melakukan presensi pada tanggal ini!');window.location='presensi.php';</script>";
        exit;
    }

    // 2. Hitung Durasi Jam Kerja Riil
    $jam_masuk_dt = new DateTime($jam_masuk);
    $jam_keluar_dt = new DateTime($jam_keluar);
    $interval = $jam_masuk_dt->diff($jam_keluar_dt);
    $jam_kerja = $interval->h + ($interval->i / 60);

    // 3. Hitung Akumulasi Menit Terlambat (Standar Masuk Jam 07:30 WIB)
    $jam_masuk_standar = new DateTime('07:30:00');
    $menit_terlambat = 0;
    if ($jam_masuk_dt > $jam_masuk_standar) {
        $diff_telat = $jam_masuk_standar->diff($jam_masuk_dt);
        $menit_terlambat = ($diff_telat->h * 60) + $diff_telat->i;
    }

    // 4. Hitung Jam Lembur Otomatis
    $hari_pekan = date('w', strtotime($tanggal));
    $is_weekend = ($hari_pekan == 6 || $hari_pekan == 0); // 6 = Sabtu, 0 = Minggu
    $jam_lembur = 0;

    if ($is_weekend) {
        $jam_lembur = $jam_kerja;
    } else {
        $jam_pulang_standar = new DateTime($tanggal . ' 17:00:00');
        if ($jam_keluar_dt > $jam_pulang_standar) {
            $diff_lembur = $jam_pulang_standar->diff($jam_keluar_dt);
            $jam_lembur = $diff_lembur->h + ($diff_lembur->i / 60);
        }
    }

    // 5. Ambil Nama Pegawai berdasarkan NIP
$stmt_nama = mysqli_prepare($conn, "SELECT nama_pegawai FROM data_gaji WHERE nip_pegawai = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_nama, "s", $nip);
    mysqli_stmt_execute($stmt_nama);
    $res_nama = mysqli_stmt_get_result($stmt_nama);
    $row_nama = mysqli_fetch_assoc($res_nama);
    $nama_pegawai = $row_nama['nama_pegawai'] ?? 'Karyawan';
    mysqli_stmt_close($stmt_nama);

    // 5.5 Hitung Potongan & Uang Lembur (Sinkron dengan Kebijakan Sistem)
    $q_pot = mysqli_query($conn, "SELECT potongan_per_jam FROM pengaturan_potongan LIMIT 1");
    $p_data = mysqli_fetch_assoc($q_pot);
    $rate = $p_data['potongan_per_jam'] ?? 5882;
    
    $jam_terlambat_desimal = $menit_terlambat / 60;
    $total_potongan = $jam_terlambat_desimal * $rate;

    // Ambil tarif lembur berdasarkan kategori jabatan untuk kalkulasi otomatis
    $j_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jabatan_pegawai FROM data_pegawai WHERE nip_pegawai = '$nip'"));
    $jab_peg = trim($j_data['jabatan_pegawai'] ?? '');
    
    // Cari kategori. Jika tidak ada di mapping, gunakan nama jabatannya langsung sebagai kategori pencarian
    $kat_target = $kategori_jabatan[$jab_peg] ?? $jab_peg;

    $tarif_q = mysqli_query($conn, "SELECT tarif_luar_jam_kerja, tarif_luar_hari_kerja FROM tarif_lembur WHERE LOWER(TRIM(kategori_jabatan)) = LOWER(TRIM('$kat_target'))");
    $t_data = mysqli_fetch_assoc($tarif_q);
    $tarif_normal = $t_data['tarif_luar_jam_kerja'] ?? 0;
    $tarif_libur = $t_data['tarif_luar_hari_kerja'] ?? 0;
    $uang_lembur = $jam_lembur * ($is_weekend ? $tarif_libur : $tarif_normal);

    // 6. Insert ke Tabel Presensi
    $stmt_ins = mysqli_prepare($conn, "INSERT INTO presensi (nip_pegawai, nama_pegawai, tanggal, jam_masuk, jam_keluar, jam_lembur, jam_terlambat, potongan_tunjangan, uang_lembur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_ins, "sssssdddd", $nip, $nama_pegawai, $tanggal, $jam_masuk, $jam_keluar, $jam_lembur, $jam_terlambat_desimal, $total_potongan, $uang_lembur);

    if (mysqli_stmt_execute($stmt_ins)) {
        // 7. Sinkronisasi data bulanan ke Master Payroll (data_gaji) agar laporan sinkron
        $bulan = date('m', strtotime($tanggal));
        $tahun = date('Y', strtotime($tanggal));
        
        $total_lbr = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(uang_lembur), 0) AS total FROM presensi WHERE nip_pegawai='$nip' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'"))['total'];
        $total_tlt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(jam_terlambat), 0) AS total FROM presensi WHERE nip_pegawai='$nip' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'"))['total'];

        // Update tunjangan lembur dan sinkronkan kolom jumlah total agar slip gaji konsisten
        mysqli_query($conn, "UPDATE data_gaji SET 
            tunjangan_lembur = '$total_lbr', 
            jam_terlambat = '$total_tlt',
            jumlah_total = (gaji_pokok + tunjangan_keluarga + tunjangan_pangan + tunjangan_jabatan + tunjangan_keahliankhusus + tunjangan_operasional + tunjangan_kesehatan + tunjangan_pph + $total_lbr - ($total_tlt * $rate)) 
            WHERE nip_pegawai = '$nip'");

        echo "<script>alert('Sukses: Data absensi harian berhasil diarsipkan ke sistem!');window.location='presensi.php';</script>";
    } else {
        echo "<script>alert('Error: Gagal menyimpan data presensi ke database!');window.location='presensi.php';</script>";
    }
    mysqli_stmt_close($stmt_ins);
}

// Ambil data pegawai untuk isi dropdown select
$q_pegawai = mysqli_query($conn, "SELECT nip_pegawai, nama_pegawai, jabatan_pegawai FROM data_pegawai ORDER BY nama_pegawai ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Presensi Karyawan - PT BPR BKK Ungaran</title>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .brand-title { font-size: 1.05rem; line-height: 1.2; color: #ffffff; display: inline-block; }
        .brand-title strong { font-size: 1.15rem; letter-spacing: 0.5px; color: #ffffff; text-shadow: 0px 0px 6px rgba(255, 255, 255, 0.45); }
        .brand-subtitle { font-size: 0.85rem; opacity: 0.85; letter-spacing: 0.3px; }
        .navbar-brand:hover .brand-title strong { text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.7); }
        
        /* KUSTOMISASI EYE CATCHING */
        .page-header-box {
            background: linear-gradient(135deg, #212529, #343a40);
            color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .custom-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .info-list li {
            padding-bottom: 10px;
            border-bottom: 1px dashed #dee2e6;
            margin-bottom: 10px;
        }
        .info-list li:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark shadow-sm">
        <a class="navbar-brand ps-3 d-flex align-items-center" href="index.php" style="height: 56px;">
            <span class="brand-title">
                <strong>Sistem Data Karyawan</strong>
                <br>
                <span class="brand-subtitle">PT BPR BKK Ungaran (Perseroda)</span>
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
                            <a class="nav-link active" href="presensi.php">
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
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0 text-white">Form Input Presensi Karyawan</h2>
                            <p class="text-white-50 mb-0 small">Dashboard Admin &bull; Presensi Karyawan</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-7">
                            <div class="card custom-card mb-4 shadow-sm border-0">
                                <div class="card-header bg-dark text-white fw-bold py-3">
                                    <i class="fas fa-edit me-2 text-warning"></i>Input Data Absensi Harian Karyawan
                                </div>
                                <div class="card-body bg-white text-dark p-4">
                                    <form method="post" class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small text-muted"><i class="fas fa-user me-1 text-primary"></i> Nama Pegawai / Karyawan</label>
                                            <select name="nip_pegawai" class="form-select border-secondary py-2" style="border-radius:8px;" required>
                                                <option value="" disabled selected>-- Silakan Pilih Karyawan --</option>
                                                <?php while($p = mysqli_fetch_assoc($q_pegawai)): ?>
                                                    <option value="<?= $p['nip_pegawai'] ?>">
                                                        <?= htmlspecialchars($p['nama_pegawai']) ?> (NIP: <?= $p['nip_pegawai'] ?> - <?= htmlspecialchars($p['jabatan_pegawai']) ?>)
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small text-muted"><i class="fas fa-calendar-alt me-1 text-primary"></i> Tanggal Presensi</label>
                                            <input type="date" name="tanggal" class="form-control py-2" style="border-radius:8px;" value="<?= date('Y-m-d') ?>" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-success"><i class="fas fa-clock me-1"></i> Jam Masuk Kantor (07:30 WIB)</label>
                                            <input type="time" name="jam_masuk" class="form-control border-success py-2" style="border-radius:8px;" value="07:30" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-danger"><i class="fas fa-history me-1"></i> Jam Keluar / Pulang Kantor</label>
                                            <input type="time" name="jam_keluar" class="form-control border-danger py-2" style="border-radius:8px;" value="17:00" required>
                                        </div>

                                        <div class="col-12 mt-4 text-end">
                                            <button type="submit" name="simpan_presensi" class="btn btn-primary fw-bold px-4 py-2 shadow-sm style-radius" style="border-radius:8px;">
                                                <i class="fas fa-save me-2 text-warning"></i>Rekam & Arsipkan Absensi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card custom-card mb-4 shadow-sm border-0">
                                <div class="card-header bg-primary text-white fw-bold py-3">
                                    <i class="fas fa-info-circle me-2 text-warning"></i>Informasi & Aturan Sistem Presensi
                                </div>
                                <div class="card-body bg-white text-dark p-4">
                                    <p class="mb-3 text-muted">Berikut adalah informasi dan aturan yang berlaku dalam sistem presensi:</p>
                                    <ul class="info-list ps-0 mb-0" style="list-style: none;">
                                        <li><i class="fas fa-check-circle text-success me-2"></i><strong>Jam Kerja Baku:</strong> Dihitung otomatis berdasarkan selisih jam masuk dan keluar kantor yang valid.</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i><strong>Jam Lembur:</strong> Dihitung jika keluar setelah jam 17:00 (hari kerja) atau seluruh jam (hari libur).</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i><strong>Uang Lembur:</strong> Berdasarkan tarif yang diatur sesuai kebijakan per kategori jabatan.</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i><strong>Keterlambatan:</strong> Dihitung dengan toleransi menit sesuai pengaturan sistem presensi.</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i><strong>Potongan Tunjangan:</strong> Terkalkulasi otomatis secara aman di halaman rekap penggajian bulanan tanpa merusak master data.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; PT BPR BKK Ungaran (PERSERODA) 2025</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>