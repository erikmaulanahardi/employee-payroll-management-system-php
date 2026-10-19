<?php
require 'function.php';
require 'cek.php';

$username = $_SESSION['username'];

// Ambil pengaturan potongan untuk kalkulasi pengeluaran gaji
$query_pengaturan = mysqli_query($conn, "SELECT potongan_per_jam FROM pengaturan_potongan LIMIT 1");
$pengaturan = mysqli_fetch_array($query_pengaturan);
$potongan_per_jam = $pengaturan['potongan_per_jam'] ?? 5882;

// =================================================================
// LOGIKA ANALYTICS RINGKAS (QUERY DATABASE)
// =================================================================

// 1. Hitung Total Karyawan
$query_karyawan = mysqli_query($conn, "SELECT COUNT(*) AS total_karyawan FROM data_pegawai");
$data_karyawan = mysqli_fetch_assoc($query_karyawan);
$total_karyawan = $data_karyawan['total_karyawan'] ?? 0;

// 2. Hitung Total Akun Pengguna Sistem
$query_account = mysqli_query($conn, "SELECT COUNT(*) AS total_account FROM account");
$data_account = mysqli_fetch_assoc($query_account);
$total_account = $data_account['total_account'] ?? 0;

// 3. Hitung Total Pengeluaran Gaji Bulan Ini
$total_pengeluaran_gaji = 0;
$query_gaji_massal = mysqli_query($conn, "SELECT * FROM data_gaji");
while ($row = mysqli_fetch_array($query_gaji_massal)) {
    $potongan = ($row['jam_terlambat'] ?? 0) * $potongan_per_jam;
    $t_operasional_bersih = max(0, $row['tunjangan_operasional'] - $potongan);
    
    $sub_jumlah = $row['gaji_pokok'] + $row['tunjangan_keluarga'] + $row['tunjangan_pangan'] + $row['tunjangan_jabatan'] + $row['tunjangan_keahliankhusus'] + $t_operasional_bersih;
    $total_bersih_karyawan = $sub_jumlah + $row['tunjangan_kesehatan'] + $row['tunjangan_pph'] + $row['tunjangan_lembur'];
    
    $total_pengeluaran_gaji += $total_bersih_karyawan;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    
    <style>
        .menu-card {
            height: 200px;
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: 0.25s;
        }

        .menu-card:hover {
            transform: scale(1.05);
            box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.25);
        }

        .menu-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
        }

        .welcome-box {
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .welcome-title {
            font-size: 2.3rem;
            font-weight: 700;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            margin-top: 10px;
            opacity: 0.9;
        }

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
            text-shadow: 0px 0px 10px rgba(255, 255, 255, 0.7);
        }

        .navbar-brand:hover .brand-subtitle {
            opacity: 1;
        }

        .analytics-card {
            border-radius: 15px;
            border: none;
            color: white;
            padding: 20px;
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
                            </a>
                            <a class="nav-link" href="menu_pengaturan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                                Pengaturan Sistem
                            </a>
                        <?php } ?>
                        
                        <a class="nav-link" href="absen_karyawan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            Absen Mandiri Karyawan
                        </a>
                        <a class="nav-link" href="ganti_password.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                            Ganti Password
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
                    
                    <div class="welcome-box">
                        <div class="welcome-title">Selamat Datang, <?= htmlspecialchars($username); ?>!</div>
                        <div class="welcome-subtitle">
                             Sistem Manajemen Data Karyawan PT BPR BKK Ungaran (Perseroda)<br>
                             Silakan pilih menu yang ingin Anda akses di bawah ini.
                        </div>
                    </div>

                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt me-3 fs-4 text-danger"></i>
                                <div>
                                    <strong>Sistem Keamanan:</strong> <?= htmlspecialchars($_GET['error']); ?>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                         <div class="col-xl-12">
                            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                                <div class="card-header bg-dark text-white d-flex align-items-center" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                    <i class="fas fa-chart-bar me-2 text-warning"></i>
                                    <strong class="me-auto">Grafik Alokasi Anggaran Gaji Per Kantor Cabang</strong>
                                    <span class="badge bg-primary">Live Data</span>
                                </div>
                                <div class="card-body">
                                    <canvas id="grafikPayrollCabang" width="100%" height="30"></canvas>
                                </div>
                            </div>
                         </div>
                    </div>       

                    <div class="row mb-4 g-3">
                        <div class="col-xl-4 col-md-6">
                            <div class="card analytics-card bg-success d-flex flex-row align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="opacity: 0.85; font-size: 0.85rem;">Total Karyawan Aktif</h6>
                                    <h2 class="fw-bold mb-0"><?= $total_karyawan; ?> <span style="font-size: 1.1rem; font-weight: normal;">Orang</span></h2>
                                </div>
                                <div class="fs-1 text-white-50"><i class="fas fa-users"></i></div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <div class="card analytics-card bg-primary d-flex flex-row align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="opacity: 0.85; font-size: 0.85rem;">Alokasi Anggaran Gaji</h6>
                                    <h3 class="fw-bold mb-0" style="font-size: 1.6rem;">Rp <?= number_format($total_pengeluaran_gaji, 0, ',', '.'); ?></h3>
                                </div>
                                <div class="fs-1 text-white-50"><i class="fas fa-wallet"></i></div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-12">
                            <div class="card analytics-card bg-purple d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(45deg, #6f42c1, #a06ee1);">
                                <div>
                                    <h6 class="text-uppercase mb-1" style="opacity: 0.85; font-size: 0.85rem;">Akun Terdaftar di Sistem</h6>
                                    <h2 class="fw-bold mb-0"><?= $total_account; ?> <span style="font-size: 1.1rem; font-weight: normal;">User</span></h2>
                                </div>
                                <div class="fs-1 text-white-50"><i class="fas fa-user-lock"></i></div>
                            </div>
                        </div>
                    </div>
 
                    <div class="row g-4 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <a href="data_karyawan.php" class="text-decoration-none">
                                <div class="card text-white bg-primary menu-card shadow-sm">
                                    <i class="bi bi-people-fill menu-icon"></i>
                                    <h4 class="fw-bold">Data Karyawan</h4>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <a href="tambah_karyawan.php" class="text-decoration-none">
                                <div class="card text-white bg-danger menu-card shadow-sm">
                                    <i class="bi bi-file-earmark-plus-fill menu-icon"></i>
                                    <h4 class="fw-bold">Tambah Karyawan</h4>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <a href="rekap_penggajian.php" class="text-decoration-none">
                                <div class="card text-dark bg-warning menu-card shadow-sm">
                                    <i class="bi bi-wallet-fill menu-icon"></i>
                                    <h4 class="fw-bold">Rekap Penggajian</h4>
                                </div>
                            </a>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <a href="presensi.php" class="text-decoration-none">
                                <div class="card text-white menu-card shadow-sm" style="background-color: #ff7f00;">
                                    <i class="bi bi-archive-fill menu-icon"></i>
                                    <h4 class="fw-bold">Presensi Karyawan</h4>
                                </div>
                            </a>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>

    <?php
    // Pemrosesan Array untuk menampung Label Cabang dan Nilai Nominal Uangnya
    $array_cabang = [];
    $array_total_gaji = [];

    // Ambil list kantor cabang unik dari tabel data_gaji
    $query_cabang_unik = mysqli_query($conn, "SELECT DISTINCT kantor FROM data_gaji WHERE kantor IS NOT NULL AND kantor != ''");
    
    while ($cabang_row = mysqli_fetch_array($query_cabang_unik)) {
        $nama_cabang = $cabang_row['kantor'];
        $total_gaji_per_cabang = 0;
        
        // Hitung akumulasi payroll khusus untuk kantor cabang yang sedang di-looping
        $query_hitung_gaji = mysqli_query($conn, "SELECT * FROM data_gaji WHERE kantor = '$nama_cabang'");
        while ($gaji_row = mysqli_fetch_array($query_hitung_gaji)) {
            $potongan = ($gaji_row['jam_terlambat'] ?? 0) * $potongan_per_jam;
            $t_operasional_bersih = max(0, $gaji_row['tunjangan_operasional'] - $potongan);
            
            $sub_jumlah = $gaji_row['gaji_pokok'] + $gaji_row['tunjangan_keluarga'] + $gaji_row['tunjangan_pangan'] + $gaji_row['tunjangan_jabatan'] + $gaji_row['tunjangan_keahliankhusus'] + $t_operasional_bersih;
            $total_bersih = $sub_jumlah + $gaji_row['tunjangan_kesehatan'] + $gaji_row['tunjangan_pph'] + $gaji_row['tunjangan_lembur'];
            
            $total_gaji_per_cabang += $total_bersih;
        }
        
        // Simpan data ke dalam penampung array PHP
        $array_cabang[] = $nama_cabang;
        $array_total_gaji[] = $total_gaji_per_cabang;
    }

    // Ubah array PHP menjadi format JSON agar bisa dibaca lancar oleh Javascript Chart.js
    $json_cabang = json_encode($array_cabang);
    $json_total_gaji = json_encode($array_total_gaji);
    ?>

    <script>
    // Set font bawaan Chart.js agar senada dengan Bootstrap SB Admin
    Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#292b2c';

    // Inisialisasi Rendering Bar Chart (Grafik Batang)
    var ctx = document.getElementById("grafikPayrollCabang");
    var myLineChart = new Chart(ctx, {
      type: 'bar', 
      data: {
        labels: <?php echo $json_cabang; ?>, 
        datasets: [{
          label: "Alokasi Anggaran Gaji (Rp)",
          backgroundColor: "rgba(13, 110, 253, 0.8)", 
          borderColor: "rgba(13, 110, 253, 1)",
          borderWidth: 1,
          data: <?php echo $json_total_gaji; ?>, 
        }],
      },
      options: {
        scales: {
          xAxes: [{
            gridLines: { display: false },
            ticks: { maxTicksLimit: 10 }
          }],
          yAxes: [{
            ticks: {
              min: 0,
              maxTicksLimit: 5,
              // FIX TYPO REGEX: diubah dari (\d3) menjadi (\d{3}) agar Javascript tidak macet!
              callback: function(value, index, values) {
                return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
              }
            },
            gridLines: { color: "rgba(0, 0, 0, .075)" }
          }],
        },
        legend: { display: false },
        tooltips: {
          callbacks: {
            label: function(tooltipItem, data) {
              return " Anggaran Gaji: Rp " + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
          }
        }
      }
    });
    </script>
</body>
</html>