<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$conn = mysqli_connect("localhost", "root", "", "project_ttu");

$timeout_duration = 600; 

// Cek apakah user sudah login dan status 'log' bernilai 'True'
if (isset($_SESSION['log']) && $_SESSION['log'] === 'True') {
    
    // Cek apakah penanda aktivitas terakhir sudah ada
    if (isset($_SESSION['last_activity'])) {
        
        // Hitung berapa lama user menganggur (Waktu Sekarang - Waktu Terakhir Aktif)
        $elapsed_time = time() - $_SESSION['last_activity'];
        
        // Jika waktu menganggur melebihi batas durasi timeout
        if ($elapsed_time > $timeout_duration) {
            
            // Hapus semua data session
            session_unset();
            session_destroy();
            
            // Tendang user ke halaman login dengan pesan error khusus
            header("Location: login.php?error=Mohon maaf, Sesi Anda telah habis karena tidak aktif selama lebih dari 10 menit. Silakan login kembali.");
            exit;
        }
    }
    
    // Jika belum timeout, perbarui penanda waktu dengan waktu saat ini
    $_SESSION['last_activity'] = time();
}

// if(isset($_POST['addakun'])){
//     $username = $_POST['username'];
//     $password = $_POST['password'];

//     $stmt = mysqli_prepare($conn, "INSERT INTO account (username, password, role) VALUES (?, ?, ?)");
//     mysqli_stmt_bind_param($stmt, "sss", $username, $password, $role);
//     $role = 'user';
//     $addakun = mysqli_stmt_execute($stmt);
//     mysqli_stmt_close($stmt);
//     if($addakun){
//         header('location:login.php');
//     }else{
//         header('location:register.php');
//     }

// }

if(isset($_POST['tambahkaryawan'])){
    $nip_pegawai = $_POST['nip_pegawai'];
    $nama_pegawai = $_POST['nama_pegawai'];
    $jabatan_pegawai = $_POST['jabatan_pegawai'];
    $golongan_pegawai = $_POST['golongan_pegawai'];
    $grade_pegawai = $_POST['grade_pegawai'];
    $tahun_bekerja = $_POST['tahun_bekerja'];
    $status_kepegawaian = $_POST['status_kepegawaian'];
    $gajipokok_pegawai = $_POST['gajipokok_pegawai'];
    $tanggal_selesaikerja = $_POST['tanggal_selesaikerja'];
    $kantor_cabang = $_POST['kantor_cabang'];
    $tanggal_bergabung = $_POST['tanggal_bergabung'];
    $email_pegawai = $_POST['email_pegawai'];
    $alamat_domisili = $_POST['alamat_domisili'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $status_perkawinan = $_POST['status_perkawinan'];
    $nomor_hp = $_POST['nomor_hp'];
    $agama = $_POST['agama'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $golongan_darah = $_POST['golongan_darah'];
    $npwp = $_POST['npwp'];
    $nomor_ktp = $_POST['nomor_ktp'];
    $nomor_kk = $_POST['nomor_kk'];

    $stmt = mysqli_prepare($conn, "INSERT INTO data_pegawai (
        nip_pegawai, nama_pegawai, jabatan_pegawai, golongan_pegawai, grade_pegawai,
        tahun_bekerja, status_kepegawaian, gajipokok_pegawai, tanggal_selesaikerja, kantor_cabang,
        tanggal_bergabung, email_pegawai, alamat_domisili, tempat_lahir, tanggal_lahir,
        jenis_kelamin, status_perkawinan, nomor_hp, agama, pendidikan_terakhir,
        golongan_darah, npwp, nomor_ktp, nomor_kk
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssss", 
        $nip_pegawai, $nama_pegawai, $jabatan_pegawai, $golongan_pegawai, $grade_pegawai,
        $tahun_bekerja, $status_kepegawaian, $gajipokok_pegawai, $tanggal_selesaikerja, $kantor_cabang,
        $tanggal_bergabung, $email_pegawai, $alamat_domisili, $tempat_lahir, $tanggal_lahir,
        $jenis_kelamin, $status_perkawinan, $nomor_hp, $agama, $pendidikan_terakhir,
        $golongan_darah, $npwp, $nomor_ktp, $nomor_kk);

    $addkaryawan = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if($addkaryawan){
        header('location:data_karyawan.php');
    } else {
        echo 'Gagal';
        header('location:tambah_karyawan.php');
    }
}

if(isset($_POST['editkaryawan'])){
    $nip_pegawai = $_POST['nip_pegawai'];
    $nama_pegawai = $_POST['nama_pegawai'];
    $jabatan_pegawai = $_POST['jabatan_pegawai'];
    $golongan_pegawai = $_POST['golongan_pegawai'];
    $grade_pegawai = $_POST['grade_pegawai'];
    $tahun_bekerja = $_POST['tahun_bekerja'];
    $status_kepegawaian = $_POST['status_kepegawaian'];
    $gajipokok_pegawai = $_POST['gajipokok_pegawai'];
    $tanggal_selesaikerja = $_POST['tanggal_selesaikerja'];
    $kantor_cabang = $_POST['kantor_cabang'];
    $tanggal_bergabung = $_POST['tanggal_bergabung'];
    $email_pegawai = $_POST['email_pegawai'];
    $alamat_domisili = $_POST['alamat_domisili'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $status_perkawinan = $_POST['status_perkawinan'];
    $nomor_hp = $_POST['nomor_hp'];
    $agama = $_POST['agama'];
    $pendidikan_terakhir = $_POST['pendidikan_terakhir'];
    $golongan_darah = $_POST['golongan_darah'];
    $npwp = $_POST['npwp'];
    $nomor_ktp = $_POST['nomor_ktp'];
    $nomor_kk = $_POST['nomor_kk'];

    $stmt = mysqli_prepare($conn, "UPDATE data_pegawai SET
        nip_pegawai = ?, nama_pegawai = ?, jabatan_pegawai = ?, golongan_pegawai = ?, grade_pegawai = ?,
        tahun_bekerja = ?, status_kepegawaian = ?, gajipokok_pegawai = ?, tanggal_selesaikerja = ?, kantor_cabang = ?,
        tanggal_bergabung = ?, email_pegawai = ?, alamat_domisili = ?, tempat_lahir = ?, tanggal_lahir = ?,
        jenis_kelamin = ?, status_perkawinan = ?, nomor_hp = ?, agama = ?, pendidikan_terakhir = ?,
        golongan_darah = ?, npwp = ?, nomor_ktp = ?, nomor_kk = ?
        WHERE nip_pegawai = ?");

    mysqli_stmt_bind_param($stmt, "sssssssssssssssssssssss",
        $nip_pegawai, $nama_pegawai, $jabatan_pegawai, $golongan_pegawai, $grade_pegawai,
        $tahun_bekerja, $status_kepegawaian, $gajipokok_pegawai, $tanggal_selesaikerja, $kantor_cabang,
        $tanggal_bergabung, $email_pegawai, $alamat_domisili, $tempat_lahir, $tanggal_lahir,
        $jenis_kelamin, $status_perkawinan, $nomor_hp, $agama, $pendidikan_terakhir,
        $golongan_darah, $npwp, $nomor_ktp, $nomor_kk, $nip_pegawai);

    $update = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if($update){
        header('location:data_karyawan.php');
    }else{
        header('location:data_karyawan.php');
    }

}

if(isset($_POST['hapuskaryawan'])){
    $nip_pegawai = $_POST['nip_pegawai'];

    $stmt = mysqli_prepare($conn, "DELETE FROM data_pegawai WHERE nip_pegawai = ?");
    mysqli_stmt_bind_param($stmt, "s", $nip_pegawai);
    $hapus = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if($hapus){
        header('location:tabel_karyawan.php');
    }else{
        header('location:tabel_karyawan.php');
    }
}

if (isset($_POST['tambahdatagaji'])) {

    $nama = $_POST['nama_pegawai'];
    $kantor = $_POST['kantor'];
    $jabatan = $_POST['jabatan_pegawai'];
    $gaji_pokok = $_POST['gaji_pokok'];
    $t_keluarga = $_POST['tunjangan_keluarga'];
    $t_pangan = $_POST['tunjangan_pangan'];
    $t_jabatan = $_POST['tunjangan_jabatan'];
    $t_keahlian = $_POST['tunjangan_keahliankhusus'];
    $t_operasional = $_POST['tunjangan_operasional'];
    $t_kesehatan = $_POST['tunjangan_kesehatan'];
    $t_pph = $_POST['tunjangan_pph'];
    $t_lembur = $_POST['tunjangan_lembur'];

    $jumlah = 
        $t_keluarga +
        $t_pangan +
        $t_jabatan +
        $t_keahlian +
        $t_operasional +
        $t_kesehatan +
        $t_pph +
        $t_lembur;

    $jumlah_total = $gaji_pokok + $jumlah;

    $stmt = mysqli_prepare($conn, 
        "INSERT INTO data_gaji 
        (nama_pegawai, kantor, jabatan_pegawai, gaji_pokok,
        tunjangan_keluarga, tunjangan_pangan, tunjangan_jabatan,
        tunjangan_keahliankhusus, tunjangan_operasional, jumlah,
        tunjangan_kesehatan, tunjangan_pph, tunjangan_lembur, jumlah_total)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    mysqli_stmt_bind_param($stmt, "sssddddddddddd",
        $nama, $kantor, $jabatan, $gaji_pokok,
        $t_keluarga, $t_pangan, $t_jabatan,
        $t_keahlian, $t_operasional, $jumlah,
        $t_kesehatan, $t_pph, $t_lembur, $jumlah_total);

    $insert = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($insert) {
        echo "Berhasil disimpan!";
    } else {
        echo "Gagal menambahkan data";
    }
}

if (isset($_POST['updatedatagaji'])) {

    $no = $_POST['no'];
    $nama = $_POST['nama_pegawai'];
    $kantor = $_POST['kantor'];
    $jabatan = $_POST['jabatan_pegawai'];
    $gaji_pokok = $_POST['gaji_pokok'];
    $t_keluarga = $_POST['tunjangan_keluarga'];
    $t_pangan = $_POST['tunjangan_pangan'];
    $t_jabatan = $_POST['tunjangan_jabatan'];
    $t_keahlian = $_POST['tunjangan_keahliankhusus'];
    $t_operasional = $_POST['tunjangan_operasional'];
    $t_kesehatan = $_POST['tunjangan_kesehatan'];
    $t_pph = $_POST['tunjangan_pph'];
    $t_lembur = $_POST['tunjangan_lembur'];

    $jumlah = 
        $t_keluarga +
        $t_pangan +
        $t_jabatan +
        $t_keahlian +
        $t_operasional +
        $t_kesehatan +
        $t_pph +
        $t_lembur;

    $jumlah_total = $gaji_pokok + $jumlah;

    $stmt = mysqli_prepare($conn,
        "UPDATE data_gaji SET
        nama_pegawai = ?, kantor = ?, jabatan_pegawai = ?, gaji_pokok = ?,
        tunjangan_keluarga = ?, tunjangan_pangan = ?, tunjangan_jabatan = ?,
        tunjangan_keahliankhusus = ?, tunjangan_operasional = ?, jumlah = ?,
        tunjangan_kesehatan = ?, tunjangan_pph = ?, tunjangan_lembur = ?, jumlah_total = ?
        WHERE no = ?");

    mysqli_stmt_bind_param($stmt, "sssddddddddddi",
        $nama, $kantor, $jabatan, $gaji_pokok,
        $t_keluarga, $t_pangan, $t_jabatan,
        $t_keahlian, $t_operasional, $jumlah,
        $t_kesehatan, $t_pph, $t_lembur, $jumlah_total, $no);

    $update = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($update) {
        echo "Berhasil diupdate!";
    } else {
        echo "Gagal mengupdate!";
    }
}
?>