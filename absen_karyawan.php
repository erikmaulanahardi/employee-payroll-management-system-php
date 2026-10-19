<?php
require 'function.php';
require 'cek.php';
require 'kategori_jabatan.php';

if ($_SESSION['role'] !== 'user') {
    // Kalau yang masuk itu 'admin', tendang balik ke index.php dan kasih alert
    header('location:index.php?error=Halaman presensi mandiri ini hanya dikhususkan untuk akun dengan role User!');
    exit;
}
// Ambil username/NIP dari user yang sedang login saat ini
// (Asumsinya karyawan login menggunakan NIP sebagai username mereka)
$nip_user = $_SESSION['username']; 
$tanggal_hari_ini = date('Y-m-d');
$jam_sekarang = date('H:i:s');

// 1. Cek status kehadiran karyawan hari ini di database
$stmt_cek = $conn->prepare("SELECT id, jam_masuk, jam_keluar FROM presensi WHERE nip_pegawai = ? AND tanggal = ?");
$stmt_cek->bind_param("ss", $nip_user, $tanggal_hari_ini);
$stmt_cek->execute();
$res_cek = $stmt_cek->get_result();
$data_absen = $res_cek->fetch_assoc();
$stmt_cek->close();

// Tentukan status tombol berdasarkan data database
$status_absen = 'belum_masuk'; // Default
if ($data_absen) {
    if ($data_absen['jam_keluar'] == '00:00:00' || empty($data_absen['jam_keluar'])) {
        $status_absen = 'belum_pulang';
    } else {
        $status_absen = 'sudah_lengkap';
    }
}

// 2. PROSES KETIKA TOMBOL ABSEN DIKLIK
if (isset($_POST['aksi_absen'])) {
    $aksi = $_POST['aksi_absen'];

    // Ambil data parameter acuan dari pengaturan presensi
    $q_pengaturan = mysqli_query($conn, "SELECT jam_kerja_awal, toleransi_keterlambatan FROM pengaturan_presensi WHERE id = 1");
    $pengaturan = mysqli_fetch_assoc($q_pengaturan);
    $jam_kerja_awal = strtotime($pengaturan['jam_kerja_awal'] ?? '08:00:00');
    $toleransi = $pengaturan['toleransi_keterlambatan'] ?? 0;

    // A. EKSEKUSI ABSEN MASUK
    if ($aksi == 'masuk' && $status_absen == 'belum_masuk') {
        $jam_masuk_ts = strtotime($jam_sekarang);
        $selisih_menit = ($jam_masuk_ts - $jam_kerja_awal) / 60;
        
        if ($selisih_menit > $toleransi) {
            $menit_terlambat = $selisih_menit - $toleransi;
            $jam_terlambat = ceil($menit_terlambat / 60); // Pembulatan ke atas jam terlambat
        } else {
            $jam_terlambat = 0;
        }

        // Ambil Potongan per Jam
        $q_potongan = mysqli_query($conn, "SELECT potongan_per_jam FROM pengaturan_potongan LIMIT 1");
        $potongan_data = mysqli_fetch_assoc($q_potongan);
        $nominal_potongan = $potongan_data['potongan_per_jam'] ?? 5882;
        $total_potongan_tunjangan = $jam_terlambat * $nominal_potongan;

        // Insert log absen masuk menggunakan JAM SERVER ($jam_sekarang)
        $stmt_in = $conn->prepare("INSERT INTO presensi (nip_pegawai, tanggal, jam_masuk, jam_terlambat, potongan_tunjangan) VALUES (?, ?, ?, ?, ?)");
        $stmt_in->bind_param("sssid", $nip_user, $tanggal_hari_ini, $jam_sekarang, $jam_terlambat, $total_potongan_tunjangan);
        $stmt_in->execute();
        $stmt_in->close();

        echo "<script>alert('Berhasil Absen Masuk pada pukul $jam_sekarang!');window.location='absen_karyawan.php';</script>";
        exit;
    }

    // B. EKSEKUSI ABSEN PULANG
    if ($aksi == 'pulang' && $status_absen == 'belum_pulang') {
        $id_presensi = $data_absen['id'];
        
        // Ambil jabatan karyawan untuk menghitung uang lembur
        $j = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jabatan_pegawai FROM data_pegawai WHERE nip_pegawai='$nip_user'"));
        $jabatan = trim($j['jabatan_pegawai'] ?? '');
        
        $hari_angka = date('N', strtotime($tanggal_hari_ini)); 
        $hari_libur = ($hari_angka >= 6);
        $kategori = $kategori_jabatan[$jabatan] ?? $jabatan;

        // Ambil tarif lembur
        $q_tarif = mysqli_query($conn, "SELECT tarif_luar_jam_kerja, tarif_luar_hari_kerja FROM tarif_lembur WHERE LOWER(TRIM(kategori_jabatan)) = LOWER(TRIM('$kategori'))");
        $tarif_data = mysqli_fetch_assoc($q_tarif);
        $tarif_normal = $tarif_data['tarif_luar_jam_kerja'] ?? 0;
        $tarif_libur = $tarif_data['tarif_luar_hari_kerja'] ?? 0;

        $jam_normal_pulang = strtotime("17:00:00");
        $jam_keluar_ts = strtotime($jam_sekarang);

        if ($hari_libur) {
            $jam_masuk_asli = strtotime($data_absen['jam_masuk']);
            $jam_lembur = max(0, ($jam_keluar_ts - $jam_masuk_asli) / 3600);
            $tarif = $tarif_libur;
        } else {
            $jam_lembur = max(0, ($jam_keluar_ts - $jam_normal_pulang) / 3600);
            $tarif = $tarif_normal;
        }
        $uang_lembur = $jam_lembur * $tarif;

        // Update baris presensi hari ini menggunakan JAM SERVER ($jam_sekarang)
        $stmt_out = $conn->prepare("UPDATE presensi SET jam_keluar = ?, jam_lembur = ?, uang_lembur = ? WHERE id = ?");
        $stmt_out->bind_param("sddi", $jam_sekarang, $jam_lembur, $uang_lembur, $id_presensi);
        $stmt_out->execute();
        $stmt_out->close();

        // -----------------------------------------------------------------
        // SINKRONISASI TOTAL BULANAN KE TABEL DATA_GAJI
        // -----------------------------------------------------------------
        $bulan = date('m', strtotime($tanggal_hari_ini));
        $tahun = date('Y', strtotime($tanggal_hari_ini));

        $total_lbr = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(uang_lembur), 0) AS total FROM presensi WHERE nip_pegawai='$nip_user' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'"))['total'];
        $total_tlt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(jam_terlambat), 0) AS total FROM presensi WHERE nip_pegawai='$nip_user' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'"))['total'];

        // Sinkronisasi penuh termasuk jumlah_total
        $stmt_sync = $conn->prepare("UPDATE data_gaji SET 
            tunjangan_lembur = ?, jam_terlambat = ?, 
            jumlah_total = (gaji_pokok + tunjangan_keluarga + tunjangan_pangan + tunjangan_jabatan + tunjangan_keahliankhusus + tunjangan_operasional + tunjangan_kesehatan + tunjangan_pph + ?) WHERE nip_pegawai = ?");
        $stmt_sync->bind_param("ddds", $total_lbr, $total_tlt, $total_lbr, $nip_user);
        $stmt_sync->execute();
        $stmt_sync->close();

        echo "<script>alert('Berhasil Absen Pulang pada pukul $jam_sekarang! Data payroll disinkronkan.');window.location='absen_karyawan.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Presensi Mandiri Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-dark text-white text-center">
                        <h4 class="my-2"><i class="fas fa-clock me-2 text-warning"></i>E-Presensi PT BPR BKK Ungaran</h4>
                    </div>
                    <div class="card-body text-center py-5">
                        <h5 class="text-muted mb-1">Tanggal Hari Ini</h5>
                        <h3 class="fw-bold mb-4"><?= date('d F Y'); ?></h3>
                        
                        <div class="alert alert-secondary py-3 mb-4">
                            <span class="small text-uppercase d-block text-muted">ID Pengguna (NIP)</span>
                            <strong class="fs-5"><?= htmlspecialchars($nip_user); ?></strong>
                        </div>

                        <form method="POST">
                            <?php if ($status_absen == 'belum_masuk') { ?>
                                <input type="hidden" name="aksi_absen" value="masuk">
                                <button type="submit" class="btn btn-success btn-lg w-100 py-3 fs-4 shadow">
                                    <i class="fas fa-sign-in-alt me-2"></i> KLIK UNTUK ABSEN MASUK
                                </button>
                                <p class="text-muted mt-3 small">*Waktu akan dicatat otomatis sesuai jam server resmi.</p>
                            
                            <?php } else if ($status_absen == 'belum_pulang') { ?>
                                <div class="alert alert-success small mb-4">
                                    Anda telah absen masuk pada pukul: <strong><?= $data_absen['jam_masuk']; ?></strong>
                                </div>
                                <input type="hidden" name="aksi_absen" value="pulang">
                                <button type="submit" class="btn btn-danger btn-lg w-100 py-3 fs-4 shadow">
                                    <i class="fas fa-sign-out-alt me-2"></i> KLIK UNTUK ABSEN PULANG
                                </button>
                                <p class="text-muted mt-3 small">*Klik tombol saat jam kerja Anda telah selesai.</p>

                            <?php } else if ($status_absen == 'sudah_lengkap') { ?>
                                <div class="alert alert-dark py-4 border-0 shadow-sm">
                                    <i class="fas fa-check-circle text-success fs-1 mb-2"></i>
                                    <h5 class="fw-bold mb-1 text-dark">Presensi Hari Ini Selesai</h5>
                                    <p class="small text-muted mb-0">Terima kasih atas dedikasi Anda hari ini.</p>
                                </div>
                                <button class="btn btn-secondary btn-lg w-100 py-3 fs-4 disabled" disabled>
                                    <i class="fas fa-lock me-2"></i> PRESENSI SUDAH TERKUNCI
                                </button>
                            <?php } ?>
                        </form>
                    </div>
                    <div class="card-footer text-center bg-white">
                        <a href="index.php" class="btn btn-sm btn-outline-dark"><i class="fas fa-arrow-left me-1"></i> Kembali ke Menu Utama</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>