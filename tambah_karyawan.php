<?php
require 'function.php';
require 'cek.php';

// Hak Akses: Cuma admin yang boleh menambah data karyawan
if ($_SESSION['role'] !== 'admin') {
    header('location:index.php?error=Anda tidak memiliki hak akses ke halaman tersebut!');
    exit;
}

$username = $_SESSION['username'];

// PROSES SUBMIT FORM TAMBAH KARYAWAN
if (isset($_POST['tambahkaryawan'])) {
    // 1. Ambil Data Input Form HTML
    $nip = trim($_POST['nip_pegawai']);
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

    // 2. VALIDASI KEAMANAN: Cek apakah NIP sudah terdaftar di data_pegawai (Anti-Duplikasi)
    $stmt_cek = mysqli_prepare($conn, "SELECT nip_pegawai FROM data_pegawai WHERE nip_pegawai = ?");
    mysqli_stmt_bind_param($stmt_cek, "s", $nip);
    mysqli_stmt_execute($stmt_cek);
    mysqli_stmt_store_result($stmt_cek);
    $nip_eksis = mysqli_stmt_num_rows($stmt_cek) > 0;
    mysqli_stmt_close($stmt_cek);

    if ($nip_eksis) {
        echo "<script>alert('Gagal! Karyawan dengan NIP tersebut sudah terdaftar di database.');window.location='tambah_karyawan.php';</script>";
        exit;
    }

    // 3. TRANSACTIONAL QUERY: Masukkan data secara berurutan ke 3 Tabel utama (data_pegawai, data_gaji, account)
    // Query 1: Simpan biodata lengkap murni ke tabel data_pegawai
    $q_pegawai = "INSERT INTO data_pegawai (nip_pegawai, nama_pegawai, jabatan_pegawai, golongan_pegawai, grade_pegawai, tahun_bekerja, status_kepegawaian, gajipokok_pegawai, tanggal_selesaikerja, kantor_cabang, tanggal_bergabung, email_pegawai, alamat_domisili, tempat_lahir, tanggal_lahir, jenis_kelamin, status_perkawinan, nomor_hp, agama, pendidikan_terakhir, golongan_darah, npwp, nomor_ktp, nomor_kk) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_pegawai = mysqli_prepare($conn, $q_pegawai);
    mysqli_stmt_bind_param($stmt_pegawai, "ssssssssssssssssssssssss", $nip, $nama, $jabatan, $golongan, $grade, $tahun_bekerja, $status_kepegawaian, $gajipokok_pegawai, $tanggal_selesaikerja, $kantor_cabang, $tanggal_bergabung, $email_pegawai, $alamat_domisili, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $status_perkawinan, $nomor_hp, $agama, $pendidikan_terakhir, $golongan_darah, $npwp, $nomor_ktp, $nomor_kk);
    $sukses_pegawai = mysqli_stmt_execute($stmt_pegawai);
    mysqli_stmt_close($stmt_pegawai);

    if ($sukses_pegawai) {
        // Query 2: Sinkronisasikan komponen awal ke tabel data_gaji (Master Payroll)
        $q_gaji = "INSERT INTO data_gaji (nip_pegawai, nama_pegawai, jabatan_pegawai, kantor, gaji_pokok, tunjangan_keluarga, tunjangan_pangan, tunjangan_jabatan, tunjangan_keahliankhusus, tunjangan_operasional, tunjangan_kesehatan, tunjangan_pph, tunjangan_lembur, jam_terlambat) VALUES (?, ?, ?, ?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
        
        $stmt_gaji = mysqli_prepare($conn, $q_gaji);
        mysqli_stmt_bind_param($stmt_gaji, "ssssd", $nip, $nama, $jabatan, $kantor_cabang, $gajipokok_pegawai);
        mysqli_stmt_execute($stmt_gaji);
        mysqli_stmt_close($stmt_gaji);

        // Query 3: BUAT AKUN LOGIN OTOMATIS (Username = NIP, Password = NIP Hashed Bcrypt)
        $password_default = password_hash($nip, PASSWORD_BCRYPT);
        $role_default = 'user';

        $q_akun = "INSERT INTO account (username, password, role) VALUES (?, ?, ?)";
        $stmt_akun = mysqli_prepare($conn, $q_akun);
        mysqli_stmt_bind_param($stmt_akun, "sss", $nip, $password_default, $role_default);
        $sukses_akun = mysqli_stmt_execute($stmt_akun);
        mysqli_stmt_close($stmt_akun);

        if ($sukses_akun) {
            echo "<script>alert('Sukses! Data karyawan berhasil disimpan ke data_pegawai & akun login otomatis aktif.');window.location='data_karyawan.php';</script>";
        } else {
            echo "<script>alert('Data karyawan tersimpan, namun pembuatan akun login otomatis gagal.');window.location='tambah_karyawan.php';</script>";
        }
    } else {
        echo "<script>alert('Gagal menyimpan data karyawan baru ke data_pegawai!');window.location='tambah_karyawan.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Project TTU - Tambah Data Karyawan</title>
        <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container mb-5">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between">
                                        <h3 class="font-weight-light my-2"><i class="fas fa-user-plus me-2 text-warning"></i>Tambah Data Karyawan Baru</h3>
                                        <a href="index.php" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
                                    </div>
                                    <div class="card-body bg-light">
                                        <form method="post">
                                            <style>
                                                .form-floating > label {
                                                    left: 0.75rem !important;
                                                }
                                            </style>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="nip_pegawai" type="text" placeholder="NIP" required/>
                                                    <label>NIP Pegawai (Username & Password Default)</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="nama_pegawai" type="text" placeholder="Nama" required/>
                                                    <label>Nama Lengkap Pegawai</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="jabatan_pegawai" type="text" placeholder="Jabatan" required />
                                                    <label>Jabatan Pegawai</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="golongan_pegawai" type="text" placeholder="Golongan" required/>
                                                    <label>Golongan Pegawai</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="grade_pegawai" type="text" placeholder="Grade" required/>
                                                    <label>Grade Pegawai</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="tahun_bekerja" type="text" placeholder="Tahun Bekerja" required/>
                                                    <label>Lama Tahun Bekerja</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="status_kepegawaian" type="text" placeholder="Status" required/>
                                                    <label>Status Kepegawaian</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="gajipokok_pegawai" type="number" placeholder="Gaji Pokok" required/>
                                                    <label>Gaji Pokok Pegawai (Nominal Rupiah)</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="tanggal_bergabung" type="date" required/>
                                                    <label>Tanggal Mulai Bergabung</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="tanggal_selesaikerja" type="date" required/>
                                                    <label>Masa Berakhir Kontrak Bekerja</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="kantor_cabang" type="text" placeholder="Kantor Cabang" required/>
                                                    <label>Penempatan Kantor Cabang</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="email_pegawai" type="email" placeholder="Email" required/>
                                                    <label>Email Pegawai</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="tempat_lahir" type="text" placeholder="Tempat Lahir" required/>
                                                    <label>Tempat Lahir</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="tanggal_lahir" type="date" required/>
                                                    <label>Tanggal Lahir</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="jenis_kelamin" type="text" placeholder="Jenis Kelamin" required/>
                                                    <label>Jenis Kelamin</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="status_perkawinan" type="text" placeholder="Status Kawin" required/>
                                                    <label>Status Perkawinan</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="nomor_hp" type="text" placeholder="No HP" required/>
                                                    <label>Nomor HP Aktif</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="agama" type="text" placeholder="Agama" required/>
                                                    <label>Agama</label>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="pendidikan_terakhir" type="text" placeholder="Pendidikan" required/>
                                                    <label>Pendidikan Terakhir</label>
                                                </div>
                                                <div class="col-md-6 mb-3 form-floating">
                                                    <input class="form-control" name="golongan_darah" type="text" placeholder="Gol Darah" required/>
                                                    <label>Golongan Darah</label>
                                                </div>
                                                
                                                <div class="col-md-12 mb-3 form-floating">
                                                    <input class="form-control" name="alamat_domisili" type="text" placeholder="Alamat" required/>
                                                    <label>Alamat Lengkap Domisili</label>
                                                </div>
                                                
                                                <div class="col-md-4 mb-3 form-floating">
                                                    <input class="form-control" name="npwp" type="text" placeholder="NPWP" required/>
                                                    <label>Nomor NPWP</label>
                                                </div>
                                                <div class="col-md-4 mb-3 form-floating">
                                                    <input class="form-control" name="nomor_ktp" type="text" placeholder="KTP" required/>
                                                    <label>Nomor KTP</label>
                                                </div>
                                                <div class="col-md-4 mb-3 form-floating">
                                                    <input class="form-control" name="nomor_kk" type="text" placeholder="KK" required/>
                                                    <label>Nomor Kartu Keluarga (KK)</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4 mb-0">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-dark btn-lg fw-bold shadow" name="tambahkaryawan">
                                                        <i class="fas fa-save me-2 text-success"></i> Simpan Data & Aktifkan E-Presensi
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
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