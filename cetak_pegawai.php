<?php
require 'function.php';
require 'cek.php';

// Proteksi: Hanya user terautentikasi yang bisa akses halaman cetak
if (!isset($_SESSION['role'])) {
    header('location:login.php');
    exit;
}

if (!isset($_GET['nip'])) {
    header('location:data_karyawan.php');
    exit;
}

$nip = trim($_GET['nip']);
$aksi = $_GET['aksi'] ?? 'cetak';

// =================================================================
// ambil data profil karyawan dari database menggunakan prepared statement
// =================================================================
$stmt = mysqli_prepare($conn, "SELECT * FROM data_pegawai WHERE nip_pegawai = ?");
mysqli_stmt_bind_param($stmt, "s", $nip);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_array($result);
mysqli_stmt_close($stmt);

if (!$data) {
    echo "<script>alert('Data profil karyawan tidak dapat ditemukan!');window.close();</script>";
    exit;
}

// Fungsi bantu konversi tanggal format DB ke format Indonesia (DD-MM-YYYY)
function tgl_indo($tanggal) {
    if (empty($tanggal) || $tanggal == '0000-00-00') return '-';
    return date('d-m-Y', strtotime($tanggal));
}

// Proses jika user memilih fitur Download berkas offline HTML
if ($aksi == 'download') {
    $filename = "Biodata_Lengkap_" . str_replace(' ', '_', $data['nama_pegawai']) . "_" . $data['nip_pegawai'] . ".html";
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Type: text/html; charset=UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Biodata_Lengkap_<?= str_replace(' ', '_', $data['nama_pegawai']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body { background-color: #fff; color: #000; font-family: 'Segoe UI', Arial, sans-serif; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .profile-card { border: 2px solid #212529; padding: 40px; margin-top: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header-title { border-bottom: 4px double #212529; padding-bottom: 12px; margin-bottom: 25px; }
        .section-title { background-color: #e9ecef !important; font-weight: bold; font-size: 0.95rem; letter-spacing: 0.5px; text-transform: uppercase; color: #212529; }
        .table-profile td { padding: 10px 12px; font-size: 0.95rem; vertical-align: middle; }
        .table-profile td strong { color: #495057; }
        @media print { 
            .no-print { display: none !important; } 
            body { margin: 0; padding: 0; background-color: #fff; }
            .profile-card { border: none; padding: 0; margin-top: 0; box-shadow: none; }
        }
    </style>
</head>
<body <?= ($aksi == 'cetak') ? 'onload="window.print();"' : ''; ?>>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 profile-card">
            
            <div class="text-center header-title">
                <h2 class="mb-1"><strong>PT BPR BKK UNGARAN (PERSERODA)</strong></h2>
                <h5 class="text-secondary tracking-wide mb-0">DOKUMEN BIODATA LENGKAP REKAPITULASI KEPEGAWAIAN</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-profile align-middle">
                    
                    <tr>
                        <td colspan="4" class="section-title"><i class="fas fa-briefcase me-2"></i> Informasi Utama & Kepegawaian</td>
                    </tr>
                    <tr>
                        <td width="23%"><strong>NIP Pegawai</strong></td>
                        <td width="27%" class="fw-bold text-primary"><?= htmlspecialchars($data['nip_pegawai']); ?></td>
                        <td width="23%"><strong>Status Kerja</strong></td>
                        <td width="27%"><span class="badge bg-success py-1 px-2"><?= htmlspecialchars($data['status_kepegawaian']); ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>Nama Lengkap</strong></td>
                        <td class="fw-bold"><?= htmlspecialchars($data['nama_pegawai']); ?></td>
                        <td><strong>Gaji Pokok (Dasar)</strong></td>
                        <td class="fw-bold text-success">Rp <?= number_format($data['gajipokok_pegawai'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Jabatan Pegawai</strong></td>
                        <td><?= htmlspecialchars($data['jabatan_pegawai']); ?></td>
                        <td><strong>Golongan</strong></td>
                        <td><?= htmlspecialchars($data['golongan_pegawai']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Kantor Penempatan</strong></td>
                        <td><?= htmlspecialchars($data['kantor_cabang']); ?></td>
                        <td><strong>Grade Pegawai</strong></td>
                        <td><?= htmlspecialchars($data['grade_pegawai']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Bergabung</strong></td>
                        <td><?= tgl_indo($data['tanggal_bergabung']); ?></td>
                        <td><strong>Masa Kerja Kontrak</strong></td>
                        <td>Sampai <?= tgl_indo($data['tanggal_selesaikerja']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Lama Tahun Bekerja</strong></td>
                        <td colspan="3"><?= htmlspecialchars($data['tahun_bekerja']); ?> Tahun</td>
                    </tr>

                    <tr>
                        <td colspan="4" class="section-title"><i class="fas fa-user me-2"></i> Profil & Data Pribadi Karyawan</td>
                    </tr>
                    <tr>
                        <td><strong>Tempat Lahir</strong></td>
                        <td><?= htmlspecialchars($data['tempat_lahir']); ?></td>
                        <td><strong>Tanggal Lahir</strong></td>
                        <td><?= tgl_indo($data['tanggal_lahir']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Jenis Kelamin</strong></td>
                        <td><?= htmlspecialchars($data['jenis_kelamin']); ?></td>
                        <td><strong>Agama</strong></td>
                        <td><?= htmlspecialchars($data['agama']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status Perkawinan</strong></td>
                        <td><?= htmlspecialchars($data['status_perkawinan']); ?></td>
                        <td><strong>Golongan Darah</strong></td>
                        <td class="text-uppercase"><?= htmlspecialchars($data['golongan_darah']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Pendidikan Terakhir</strong></td>
                        <td><?= htmlspecialchars($data['pendidikan_terakhir']); ?></td>
                        <td><strong>Nomor HP / WhatsApp</strong></td>
                        <td><?= htmlspecialchars($data['nomor_hp']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email Resmi</strong></td>
                        <td colspan="3"><?= htmlspecialchars($data['email_pegawai']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Alamat Domisili</strong></td>
                        <td colspan="3"><?= htmlspecialchars($data['alamat_domisili']); ?></td>
                    </tr>

                    <tr>
                        <td colspan="4" class="section-title"><i class="fas fa-file-invoice me-2"></i> Legalitas & Administrasi Negara</td>
                    </tr>
                    <tr>
                        <td><strong>Nomor KTP (NIK)</strong></td>
                        <td colspan="3" class="font-monospace letter-spacing-1"><?= htmlspecialchars($data['nomor_ktp']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nomor Kartu Keluarga (KK)</strong></td>
                        <td colspan="3" class="font-monospace letter-spacing-1"><?= htmlspecialchars($data['nomor_kk']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nomor NPWP Pajak</strong></td>
                        <td colspan="3" class="font-monospace letter-spacing-1"><?= htmlspecialchars($data['npwp']); ?></td>
                    </tr>
                </table>
            </div>

            <div class="text-center mt-4 no-print">
                <button onclick="window.print();" class="btn btn-primary px-3 me-2"><i class="fas fa-print me-1"></i> Cetak Ulang Berkas</button>
                <button onclick="window.close();" class="btn btn-secondary px-3"><i class="fas fa-times me-1"></i> Tutup Halaman</button>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>