<?php
require 'function.php';
require 'cek.php';

$username = $_SESSION['username'];

// =================================================================
// LOGIKA BACKEND: PROSES EDIT DATA KARYAWAN
// =================================================================
if (isset($_POST['update_karyawan'])) {
    $nip = $_POST['nip_pegawai'];
    $nama = trim($_POST['nama_pegawai']);
    $jabatan = trim($_POST['jabatan_pegawai']);
    $golongan = trim($_POST['golongan_pegawai']);
    $grade = trim($_POST['grade_pegawai']);
    $tahun_bekerja = trim($_POST['tahun_bekerja']);
    $status_kepegawaian = trim($_POST['status_kepegawaian']);
    $gajipokok_pegawai = floatval($_POST['gajipokok_pegawai']);
    $tanggal_selesaikerja = $_POST['tanggal_selesaikerja'];
    $kantor_cabang = trim($_POST['kantor_cabang']);
    $tanggal_bergabung = $_POST['tanggal_bergabung'];
    $email_pegawai = trim($_POST['email_pegawai']);
    $alamat_domisili = trim($_POST['alamat_domisili']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $status_perkawinan = trim($_POST['status_perkawinan']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $agama = trim($_POST['agama']);
    $pendidikan_terakhir = trim($_POST['pendidikan_terakhir']);
    $golongan_darah = trim($_POST['golongan_darah']);
    $npwp = trim($_POST['npwp']);
    $nomor_ktp = trim($_POST['nomor_ktp']);
    $nomor_kk = trim($_POST['nomor_kk']);

    // Update data di data_pegawai secara aman (Prepared Statement)
    $q_up_pegawai = "UPDATE data_pegawai SET nama_pegawai=?, jabatan_pegawai=?, golongan_pegawai=?, grade_pegawai=?, tahun_bekerja=?, status_kepegawaian=?, gajipokok_pegawai=?, tanggal_selesaikerja=?, kantor_cabang=?, tanggal_bergabung=?, email_pegawai=?, alamat_domisili=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, status_perkawinan=?, nomor_hp=?, agama=?, pendidikan_terakhir=?, golongan_darah=?, npwp=?, nomor_ktp=?, nomor_kk=? WHERE nip_pegawai=?";
    $stmt1 = mysqli_prepare($conn, $q_up_pegawai);
    mysqli_stmt_bind_param($stmt1, "ssssssisssssssssssssssss", $nama, $jabatan, $golongan, $grade, $tahun_bekerja, $status_kepegawaian, $gajipokok_pegawai, $tanggal_selesaikerja, $kantor_cabang, $tanggal_bergabung, $email_pegawai, $alamat_domisili, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $status_perkawinan, $nomor_hp, $agama, $pendidikan_terakhir, $golongan_darah, $npwp, $nomor_ktp, $nomor_kk, $nip);
    $res1 = mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    if ($res1) {
        // Sinkronkan data krusial ke data_gaji (Master Payroll)
        $q_up_gaji = "UPDATE data_gaji SET nama_pegawai=?, jabatan_pegawai=?, kantor=?, gaji_pokok=? WHERE nip_pegawai=?";
        $stmt2 = mysqli_prepare($conn, $q_up_gaji);
        mysqli_stmt_bind_param($stmt2, "sssis", $nama, $jabatan, $kantor_cabang, $gajipokok_pegawai, $nip);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        echo "<script>alert('Data karyawan berhasil diperbarui!');window.location='data_karyawan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}

// =================================================================
// LOGIKA BACKEND: PROSES HAPUS KARYAWAN & AKUN
// =================================================================
if (isset($_POST['hapus_karyawan'])) {
    $nip = $_POST['nip_pegawai'];

    // 1. Hapus dari data_pegawai
    $stmt_del1 = mysqli_prepare($conn, "DELETE FROM data_pegawai WHERE nip_pegawai = ?");
    mysqli_stmt_bind_param($stmt_del1, "s", $nip);
    mysqli_stmt_execute($stmt_del1);
    mysqli_stmt_close($stmt_del1);

    // 2. Hapus dari data_gaji
    $stmt_del2 = mysqli_prepare($conn, "DELETE FROM data_gaji WHERE nip_pegawai = ?");
    mysqli_stmt_bind_param($stmt_del2, "s", $nip);
    mysqli_stmt_execute($stmt_del2);
    mysqli_stmt_close($stmt_del2);

    // 3. Hapus dari account login
    $stmt_del3 = mysqli_prepare($conn, "DELETE FROM account WHERE username = ?");
    mysqli_stmt_bind_param($stmt_del3, "s", $nip);
    mysqli_stmt_execute($stmt_del3);
    mysqli_stmt_close($stmt_del3);

    echo "<script>alert('Karyawan dan akun pengguna berhasil dihapus dari sistem!');window.location='data_karyawan.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Project TTU - Sistem Manajemen Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .brand-title { font-size: 1.05rem; line-height: 1.2; color: #ffffff; display: inline-block; }
        .brand-title strong { font-size: 1.15rem; letter-spacing: 0.5px; color: #ffffff; text-shadow: 0px 0px 6px rgba(255, 255, 255, 0.45); }
        .brand-subtitle { font-size: 0.85rem; opacity: 0.85; letter-spacing: 0.3px; }
        .navbar-brand:hover .brand-title strong { text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.7); }
        .navbar-brand:hover .brand-subtitle { opacity: 1; }
        .detail-label { font-weight: bold; color: #495057; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.3px; }
        .detail-value { color: #212529; border-bottom: 1px dashed #dee2e6; padding-bottom: 4px; font-size: 0.95rem; }
        
        /* BOX JUDUL EYE-CATCHING GRADIENT */
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
                        <a class="nav-link active" href="data_karyawan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div> Data Karyawan
                        </a>
                        <div class="sb-sidenav-menu-heading">Fitur Lainnya</div>
                        <a class="nav-link" href="rekap_penggajian.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div> Rekap Penggajian
                        </a>
                        <?php if ($_SESSION['role'] == 'admin') : ?>
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
                        <?php endif; ?>
                        <a class="nav-link" href="absen_karyawan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-clock"></i></div> Absen Mandiri Karyawan
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
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="fw-bold mb-0 text-white">Tabel Master Data Karyawan</h2>
                            <p class="text-white-50 mb-0 small">PT BPR BKK Ungaran (Perseroda) &bull; Dashboard Manajemen Biodata Pegawai</p>
                        </div>
                    </div>

                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Menu Utama</a></li>
                        <li class="breadcrumb-item active">Data Karyawan</li>
                    </ol>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> <strong>Informasi!:</strong><br>
                        Berikut ini merupakan tampilan dari database karyawan. Klik tombol <span class="badge bg-info text-dark">Detail</span> untuk melihat keseluruhan atribut data lengkap masing-masing karyawan secara detail.
                    </div>

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white"><i class="fas fa-users me-1"></i> Database Manajemen Pegawai</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatablesSimple" class="table table-bordered table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>NIP Pegawai</th>
                                            <th>Nama Lengkap</th>
                                            <th>Jabatan</th>
                                            <th>Kantor Cabang</th>
                                            <th>Nomor HP</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi / Manajemen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $dataseluruhkaryawan = mysqli_query($conn, "SELECT * FROM data_pegawai ORDER BY nama_pegawai ASC");
                                        while ($data = mysqli_fetch_array($dataseluruhkaryawan)) {
                                            $nip_pegawai = $data['nip_pegawai'];
                                            $nama_pegawai = $data['nama_pegawai'];
                                            $gajipokok_angka = $data['gajipokok_pegawai'];
                                        ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($nip_pegawai); ?></strong></td>
                                                <td><?= htmlspecialchars($nama_pegawai); ?></td>
                                                <td><?= htmlspecialchars($data['jabatan_pegawai']); ?></td>
                                                <td><?= htmlspecialchars($data['kantor_cabang']); ?></td>
                                                <td><?= htmlspecialchars($data['nomor_hp']); ?></td>
                                                <td><span class="badge bg-success"><?= htmlspecialchars($data['status_kepegawaian']); ?></span></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-info fw-bold text-dark me-1" data-bs-toggle="modal" data-bs-target="#detail<?= $nip_pegawai; ?>"><i class="fas fa-eye"></i> Detail</button>
                                                    
                                                    <?php if ($_SESSION['role'] == 'admin') : ?>
                                                        <button type="button" class="btn btn-sm btn-warning fw-bold text-dark me-1" data-bs-toggle="modal" data-bs-target="#edit<?= $nip_pegawai; ?>"><i class="fas fa-edit"></i> Edit</button>
                                                        <button type="button" class="btn btn-sm btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#hapus<?= $nip_pegawai; ?>"><i class="fas fa-trash"></i> Hapus</button>
                                                    <?php else : ?>
                                                        <span class="text-muted small">No Access</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                            <div class="modal fade" id="detail<?= $nip_pegawai; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content shadow-lg border-0">
                                                        <div class="modal-header bg-info text-dark">
                                                            <h5 class="modal-title fw-bold"><i class="fas fa-id-card me-2"></i>Profil Lengkap Biodata Karyawan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body bg-white" style="max-height: 75vh; overflow-y: auto;">
                                                            <div class="row g-3 p-2">
                                                                <div class="col-md-4"><div class="detail-label">NIP Karyawan</div><div class="detail-value fw-bold text-primary"><?= $nip_pegawai; ?></div></div>
                                                                <div class="col-md-8"><div class="detail-label">Nama Lengkap</div><div class="detail-value fw-bold"><?= htmlspecialchars($nama_pegawai); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Jabatan</div><div class="detail-value"><?= htmlspecialchars($data['jabatan_pegawai']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Golongan</div><div class="detail-value"><?= htmlspecialchars($data['golongan_pegawai']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Grade</div><div class="detail-value"><?= htmlspecialchars($data['grade_pegawai']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Lama Bekerja</div><div class="detail-value"><?= htmlspecialchars($data['tahun_bekerja']); ?> Tahun</div></div>
                                                                <div class="col-md-4"><div class="detail-label">Status Pegawai</div><div class="detail-value"><?= htmlspecialchars($data['status_kepegawaian']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Gaji Pokok</div><div class="detail-value fw-bold text-success">Rp <?= number_format($gajipokok_angka, 0, ',', '.'); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Kantor Penempatan</div><div class="detail-value"><?= htmlspecialchars($data['kantor_cabang']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Tanggal Bergabung</div><div class="detail-value"><?= !empty($data['tanggal_bergabung']) ? date('d-m-Y', strtotime($data['tanggal_bergabung'])) : '-'; ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Masa Akhir Kontrak</div><div class="detail-value"><?= !empty($data['tanggal_selesaikerja']) ? date('d-m-Y', strtotime($data['tanggal_selesaikerja'])) : '-'; ?></div></div>
                                                                <div class="col-md-6"><div class="detail-label">Email Instansi</div><div class="detail-value"><?= htmlspecialchars($data['email_pegawai']); ?></div></div>
                                                                <div class="col-md-6"><div class="detail-label">Nomor HP Aktif</div><div class="detail-value"><?= htmlspecialchars($data['nomor_hp']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Tempat Lahir</div><div class="detail-value"><?= htmlspecialchars($data['tempat_lahir']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Tanggal Lahir</div><div class="detail-value"><?= !empty($data['tanggal_lahir']) ? date('d-m-Y', strtotime($data['tanggal_lahir'])) : '-'; ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Jenis Kelamin</div><div class="detail-value"><?= htmlspecialchars($data['jenis_kelamin']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Status Perkawinan</div><div class="detail-value"><?= htmlspecialchars($data['status_perkawinan']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Agama</div><div class="detail-value"><?= htmlspecialchars($data['agama']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Pendidikan Terakhir</div><div class="detail-value"><?= htmlspecialchars($data['pendidikan_terakhir']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Golongan Darah</div><div class="detail-value text-uppercase"><?= htmlspecialchars($data['golongan_darah']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Nomor NPWP</div><div class="detail-value"><?= htmlspecialchars($data['npwp']); ?></div></div>
                                                                <div class="col-md-4"><div class="detail-label">Nomor KTP</div><div class="detail-value"><?= htmlspecialchars($data['nomor_ktp']); ?></div></div>
                                                                <div class="col-md-6"><div class="detail-label">Nomor Kartu Keluarga (KK)</div><div class="detail-value"><?= htmlspecialchars($data['nomor_kk']); ?></div></div>
                                                                <div class="col-md-12"><div class="detail-label">Alamat Domisili Lengkap</div><div class="detail-value"><?= htmlspecialchars($data['alamat_domisili']); ?></div></div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Tutup Detail</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="edit<?= $nip_pegawai; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content shadow-lg border-0">
                                                        <div class="modal-header bg-warning text-dark fw-bold">
                                                            <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Informasi Karyawan: <?= htmlspecialchars($nama_pegawai); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="nip_pegawai" value="<?= $nip_pegawai; ?>">
                                                            <div class="modal-body bg-light" style="max-height: 70vh; overflow-y: auto;">
                                                                <div class="row g-3">
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Nama Lengkap</label><input type="text" name="nama_pegawai" class="form-control" value="<?= htmlspecialchars($nama_pegawai); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Jabatan</label><input type="text" name="jabatan_pegawai" class="form-control" value="<?= htmlspecialchars($data['jabatan_pegawai']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Golongan</label><input type="text" name="golongan_pegawai" class="form-control" value="<?= htmlspecialchars($data['golongan_pegawai']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Grade</label><input type="text" name="grade_pegawai" class="form-control" value="<?= htmlspecialchars($data['grade_pegawai']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Lama Tahun Bekerja</label><input type="text" name="tahun_bekerja" class="form-control" value="<?= htmlspecialchars($data['tahun_bekerja']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Status Kepegawaian</label><input type="text" name="status_kepegawaian" class="form-control" value="<?= htmlspecialchars($data['status_kepegawaian']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Gaji Pokok (Nominal Angka)</label><input type="number" name="gajipokok_pegawai" class="form-control" value="<?= $gajipokok_angka; ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Kantor Cabang</label><input type="text" name="kantor_cabang" class="form-control" value="<?= htmlspecialchars($data['kantor_cabang']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Tanggal Bergabung</label><input type="date" name="tanggal_bergabung" class="form-control" value="<?= $data['tanggal_bergabung']; ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Masa Berakhir Kerja</label><input type="date" name="tanggal_selesaikerja" class="form-control" value="<?= $data['tanggal_selesaikerja']; ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Email Pegawai</label><input type="email" name="email_pegawai" class="form-control" value="<?= htmlspecialchars($data['email_pegawai']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Nomor HP</label><input type="text" name="nomor_hp" class="form-control" value="<?= htmlspecialchars($data['nomor_hp']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Tempat Lahir</label><input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($data['tempat_lahir']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="<?= $data['tanggal_lahir']; ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Jenis Kelamin</label><input type="text" name="jenis_kelamin" class="form-control" value="<?= htmlspecialchars($data['jenis_kelamin']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Status Perkawinan</label><input type="text" name="status_perkawinan" class="form-control" value="<?= htmlspecialchars($data['status_perkawinan']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Agama</label><input type="text" name="agama" class="form-control" value="<?= htmlspecialchars($data['agama']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Pendidikan Terakhir</label><input type="text" name="pendidikan_terakhir" class="form-control" value="<?= htmlspecialchars($data['pendidikan_terakhir']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Golongan Darah</label><input type="text" name="golongan_darah" class="form-control" value="<?= htmlspecialchars($data['golongan_darah']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">NPWP</label><input type="text" name="npwp" class="form-control" value="<?= htmlspecialchars($data['npwp']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Nomor KTP</label><input type="text" name="nomor_ktp" class="form-control" value="<?= htmlspecialchars($data['nomor_ktp']); ?>" required></div>
                                                                    <div class="col-md-6"><label class="form-label fw-bold small">Nomor Kartu Keluarga (KK)</label><input type="text" name="nomor_kk" class="form-control" value="<?= htmlspecialchars($data['nomor_kk']); ?>" required></div>
                                                                    <div class="col-md-12"><label class="form-label fw-bold small">Alamat Domisili</label><input type="text" name="alamat_domisili" class="form-control" value="<?= htmlspecialchars($data['alamat_domisili']); ?>" required></div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" name="update_karyawan" class="btn btn-warning fw-bold text-dark"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal fade" id="hapus<?= $nip_pegawai; ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content shadow-lg border-0">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Data</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="nip_pegawai" value="<?= $nip_pegawai; ?>">
                                                            <div class="modal-body text-center py-4">
                                                                <i class="fas fa-user-times text-danger fs-1 mb-3"></i>
                                                                <h5>Apakah Anda yakin ingin menghapus data karyawan ini?</h5>
                                                                <p class="text-muted mb-0 small">Nama: <strong><?= htmlspecialchars($nama_pegawai); ?></strong> (NIP: <?= $nip_pegawai; ?>)</p>
                                                                <p class="text-danger small mt-2 fw-bold">*Peringatan: Tindakan ini juga akan otomatis menghapus akun login E-Presensi ybs secara permanen!</p>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" name="hapus_karyawan" class="btn btn-danger fw-bold"><i class="fas fa-trash-alt me-1"></i> Ya, Hapus Permanen</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCariPegawai">
                                    <i class="fas fa-file-pdf me-2"></i>Cetak / Download Data Pegawai Individu
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </main>

            <div class="modal fade" id="modalCariPegawai" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-user-check me-2"></i> Pilih Data Pegawai</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="cetak_pegawai.php" method="GET" target="_blank">
                            <div class="modal-body">
                                <div class="alert alert-info py-2 small">
                                    <i class="fas fa-info-circle"></i> Silakan pilih nama pegawai untuk mengunduh berkas laporan biodata lengkap.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Pegawai / NIP:</label>
                                    <select class="form-select" name="nip" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        <?php
                                        $dropdown_pegawai = mysqli_query($conn, "SELECT nip_pegawai, nama_pegawai FROM data_pegawai ORDER BY nama_pegawai ASC");
                                        while($row = mysqli_fetch_array($dropdown_pegawai)){
                                            echo "<option value='".htmlspecialchars($row['nip_pegawai'])."'>".htmlspecialchars($row['nama_pegawai'])." (NIP: ".$row['nip_pegawai'].")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="aksi" value="cetak" class="btn btn-primary"><i class="fas fa-print me-1"></i> Buka & Cetak</button>
                                <button type="submit" name="aksi" value="download" class="btn btn-success"><i class="fas fa-download me-1"></i> Download PDF</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

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