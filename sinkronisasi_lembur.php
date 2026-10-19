<?php
require 'function.php';
require 'cek.php';

if ($_SESSION['role'] !== 'admin') {
    header('location:index.php?error=Anda tidak memiliki hak akses ke halaman tersebut!');
    exit;
}

if (isset($_POST['sinkronisasi'])) {
    $bulan = $_POST['bulan'];
    $tahun = $_POST['tahun'];

    $query_pegawai = "SELECT DISTINCT nip_pegawai FROM data_gaji";
    $result_pegawai = mysqli_query($conn, $query_pegawai);

    $count = 0;
    while ($row = mysqli_fetch_assoc($result_pegawai)) {
        $nip = $row['nip_pegawai'];

        $query_lembur = "
            SELECT IFNULL(SUM(uang_lembur), 0) AS total_lembur
            FROM presensi
            WHERE nip_pegawai = '$nip'
            AND MONTH(tanggal) = '$bulan'
            AND YEAR(tanggal) = '$tahun'
        ";

        $result_lembur = mysqli_query($conn, $query_lembur);
        $data = mysqli_fetch_assoc($result_lembur);
        $total_lembur = $data['total_lembur'];

        $query_update = "
            UPDATE data_gaji 
            SET tunjangan_lembur = '$total_lembur'
            WHERE nip_pegawai = '$nip'
        ";

        if (mysqli_query($conn, $query_update)) {
            $count++;
        }
    }

    echo "<div class='alert alert-success'>✓ Sinkronisasi berhasil! $count pegawai telah diperbarui.</div>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sinkronisasi Lembur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-5">
        <div class="card" style="max-width: 500px; margin: 0 auto;">
            <div class="card-header bg-warning">
                <h5>Sinkronisasi Data Lembur ke Database</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select" required>
                            <option value="">-- Pilih Bulan --</option>
                            <?php
                            $bulan_arr = [
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember'
                            ];
                            foreach ($bulan_arr as $key => $bl) {
                                echo "<option value='$key'>$bl</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="<?= date('Y'); ?>" required>
                    </div>
                    <button type="submit" name="sinkronisasi" class="btn btn-success w-100">
                        <i class="fas fa-sync"></i> Sinkronisasi Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>