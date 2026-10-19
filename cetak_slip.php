<?php
require 'function.php';
require 'cek.php';

// Proteksi Hak Akses: Pastikan user sudah login
if (!isset($_SESSION['role'])) {
    header('location:login.php');
    exit;
}

if (!isset($_GET['no'])) {
    header('location:rekap_penggajian.php');
    exit;
}

$no_slip = trim($_GET['no']);
$aksi = $_GET['aksi'] ?? 'cetak';

// 1. Ambil pengaturan nominal potongan keterlambatan dari database
$query_pengaturan = mysqli_query($conn, "SELECT potongan_per_jam FROM pengaturan_potongan LIMIT 1");
$pengaturan = mysqli_fetch_array($query_pengaturan);
$potongan_per_jam = $pengaturan['potongan_per_jam'] ?? 5882;

// =================================================================
// KEAMANAN SIBER: QUERY DATA GAJI UTAMA MENGGUNAKAN PREPARED STATEMENT
// =================================================================
$stmt_gaji = mysqli_prepare($conn, "SELECT * FROM data_gaji WHERE no = ?");
mysqli_stmt_bind_param($stmt_gaji, "i", $no_slip);
mysqli_stmt_execute($stmt_gaji);
$result_gaji = mysqli_stmt_get_result($stmt_gaji);
$data = mysqli_fetch_array($result_gaji);
mysqli_stmt_close($stmt_gaji);

if (!$data) {
    echo "<script>alert('Data slip gaji tidak ditemukan di sistem!');window.close();</script>";
    exit;
}

$nip = $data['nip_pegawai'];

// =================================================================
// DYNAMIC QUERY BULANAN: HITUNG TOTAL JAM LEMBUR DARI TABEL PRESENSI
// =================================================================
// Mengambil akumulasi jam lembur bulan berjalan agar sinkron dengan tunjangan_lembur
$bulan_sekarang = date('m');
$tahun_sekarang = date('Y');
$query_jam_lembur = mysqli_query($conn, "SELECT IFNULL(SUM(jam_lembur), 0) AS total_jam_lembur FROM presensi WHERE nip_pegawai = '$nip' AND MONTH(tanggal) = '$bulan_sekarang' AND YEAR(tanggal) = '$tahun_sekarang'");
$res_jam_lembur = mysqli_fetch_assoc($query_jam_lembur);
$total_akumulasi_jam_lembur = $res_jam_lembur['total_jam_lembur'] ?? 0;

// 2. PROSES LOGIKA PERHITUNGAN PAYROLL SINKRON
$jam_terlambat = $data['jam_terlambat'] ?? 0;
$total_potongan_keterlambatan = $jam_terlambat * $potongan_per_jam;

// Tunjangan operasional bersih (kotor dikurangi potongan keterlambatan)
$t_operasional_kotor = $data['tunjangan_operasional'];
$t_operasional_clean = max(0, $t_operasional_kotor - $total_potongan_keterlambatan);

// Hitung total seluruh penerimaan/kotor (Lembur masuk sebagai pendapatan)
$total_penerimaan_kotor = $data['gaji_pokok'] + $data['tunjangan_keluarga'] + $data['tunjangan_pangan'] + $data['tunjangan_jabatan'] + $data['tunjangan_keahliankhusus'] + $t_operasional_kotor + $data['tunjangan_kesehatan'] + $data['tunjangan_pph'] + $data['tunjangan_lembur'];

// Formula Akhir Take Home Pay (Total Penerimaan Kotor dikurangi Potongan Kedisiplinan)
$penerimaan_bersih_thp = max(0, $total_penerimaan_kotor - $total_potongan_keterlambatan);

// PROSES HEADER: Jika aksi adalah download berkas offline HTML
if ($aksi == 'download') {
    $filename = "Slip_Gaji_" . str_replace(' ', '_', $data['nama_pegawai']) . "_" . date('M_Y') . ".html";
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
    <title>Slip_Gaji_<?= str_replace(' ', '_', $data['nama_pegawai']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body { background-color: #fff; color: #000; font-family: 'Courier New', Courier, monospace; }
        .slip-container { border: 2px dashed #212529; padding: 35px; margin-top: 30px; border-radius: 8px; background-color: #fff; }
        .line { border-top: 2px dashed #212529; margin: 15px 0; }
        .sub-section-title { font-weight: bold; text-decoration: underline; font-size: 0.95rem; text-transform: uppercase; margin-bottom: 8px; color: #212529; }
        .table-slip td { font-size: 0.9rem; padding: 4px 0; }
        .text-danger { color: #dc3545 !important; }
        @media print {
            body { margin: 0; background-color: #fff; }
            .no-print { display: none !important; }
            .slip-container { border: 2px dashed #000; box-shadow: none; margin-top: 0; padding: 15px; }
        }
    </style>
</head>
<body <?= ($aksi == 'cetak') ? 'onload="window.print();"' : ''; ?>>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 slip-container">
            
            <div class="text-center">
                <h4 class="mb-1"><strong>PT BPR BKK UNGARAN (PERSERODA)</strong></h4>
                <p class="small mb-1">Kantor Penempatan Penugasan: **<?= htmlspecialchars($data['kantor']); ?>**</p>
                <h5 class="fw-bold text-uppercase tracking-wider">SLIP RINCIAN RESMI GAJI & TUNJANGAN PEGAWAI</h5>
            </div>
            
            <div class="line"></div>
            
            <div class="row small g-2">
                <div class="col-6">
                    <p class="mb-1"><strong>NIP Pegawai :</strong> <?= htmlspecialchars($data['nip_pegawai']); ?></p>
                    <p class="mb-1"><strong>Nama Lengkap:</strong> <?= htmlspecialchars($data['nama_pegawai']); ?></p>
                </div>
                <div class="col-6 text-end">
                    <p class="mb-1"><strong>Jabatan Struktural:</strong> <?= htmlspecialchars($data['jabatan_pegawai']); ?></p>
                    <p class="mb-1"><strong>Waktu Cetak Server :</strong> <?= date('d-m-Y H:i:s'); ?></p>
                </div>
            </div>
            
            <div class="line"></div>
            
            <div class="row mt-3">
                <div class="col-md-6 border-end" style="border-right: 1px dashed #212529 !important;">
                    <div class="sub-section-title"><i class="fas fa-arrow-down text-success me-1"></i> I. Komponen Penerimaan</div>
                    <table class="table table-borderless table-sm table-slip mb-0">
                        <tr><td>Gaji Pokok</td><td class="text-end">Rp <?= number_format($data['gaji_pokok'], 0, ',', '.'); ?></td></tr>
                        <tr><td>Tunjangan Keluarga</td><td class="text-end">Rp <?= number_format($data['tunjangan_keluarga'], 0, ',', '.'); ?></td></tr>
                        <tr><td>Tunjangan Pangan</td><td class="text-end">Rp <?= number_format($data['tunjangan_pangan'], 0, ',', '.'); ?></td></tr>
                        <tr><td>Tunjangan Jabatan</td><td class="text-end">Rp <?= number_format($data['tunjangan_jabatan'], 0, ',', '.'); ?></td></tr>
                        <tr><td>Tunjangan Keahlian</td><td class="text-end">Rp <?= number_format($data['tunjangan_keahliankhusus'], 0, ',', '.'); ?></td></tr>
                        <tr><td>Tunjangan Operasional (Kotor)</td><td class="text-end">Rp <?= number_format($t_operasional_kotor, 0, ',', '.'); ?></td></tr>
                        <tr><td>Tunjangan Kesehatan</td><td class="text-end">Rp <?= number_format($data['tunjangan_kesehatan'], 0, ',', '.'); ?></td></tr>
                        <tr><td>Tunjangan PPH</td><td class="text-end">Rp <?= number_format($data['tunjangan_pph'], 0, ',', '.'); ?></td></tr>
                        
                        <tr class="fw-bold text-primary">
                            <td>Tunjangan Lembur (<?= round($total_akumulasi_jam_lembur, 1); ?> Jam)</td>
                            <td class="text-end">Rp <?= number_format($data['tunjangan_lembur'], 0, ',', '.'); ?></td>
                        </tr>
                        <tr class="table-light fw-bold">
                            <td>Total Bruto Kotor</td>
                            <td class="text-end">Rp <?= number_format($total_penerimaan_kotor, 0, ',', '.'); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <div class="sub-section-title"><i class="fas fa-arrow-up text-danger me-1"></i> II. Komponen Potongan</div>
                    <table class="table table-borderless table-sm table-slip mb-0">
                        <?php if ($jam_terlambat > 0) : ?>
                            <tr class="text-danger">
                                <td>Potongan Keterlambatan Absensi<br><small class="text-muted">(Akumulasi: <?= $jam_terlambat; ?> Jam x Rp <?= number_format($potongan_per_jam, 0, ',', '.'); ?>)</small></td>
                                <td class="text-end align-bottom fw-bold">-Rp <?= number_format($total_potongan_keterlambatan, 0, ',', '.'); ?></td>
                            </tr>
                        <?php else : ?>
                            <tr class="text-muted">
                                <td>Potongan Keterlambatan Absensi<br><small class="text-muted">(0 Jam / Pegawai Disiplin)</small></td>
                                <td class="text-end align-bottom">Rp 0</td>
                            </tr>
                        <?php endif; ?>
                        
                        <tr class="table-light fw-bold text-danger">
                            <td>Total Potongan Gaji</td>
                            <td class="text-end">-Rp <?= number_format($total_potongan_keterlambatan, 0, ',', '.'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="line" style="margin-top: 25px;"></div>
            
            <table class="table table-bordered bg-dark text-white text-center shadow-sm">
                <thead>
                    <tr>
                        <th class="py-2"><h4 class="mb-0 fw-bold tracking-wide"><i class="fas fa-money-check-alt text-warning me-2"></i> TOTAL PENERIMAAN BERSIH (TAKE HOME PAY)</h4></th>
                        <th class="py-2 text-end px-4"><h3 class="mb-0 fw-bold text-warning">Rp <?= number_format($penerimaan_bersih_thp, 0, ',', '.'); ?></h3></th>
                    </tr>
                </thead>
            </table>
            
            <div class="line"></div>
            <p class="text-center small mb-4"><em>*Rincian slip ini divalidasi dan diterbitkan secara elektronik oleh Otoritas Server Keuangan PT BPR BKK Ungaran</em></p>
            
            <div class="text-center no-print mt-3">
                <button onclick="window.print();" class="btn btn-primary btn-sm px-3 me-2"><i class="fas fa-print me-1"></i> Cetak Ulang Dokumen</button>
                <button onclick="window.close();" class="btn btn-secondary btn-sm px-3"><i class="fas fa-times me-1"></i> Tutup Halaman</button>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>