<?php

require 'function.php';
require 'cek.php';

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$query = "
SELECT 
    d.no,
    d.nama_pegawai,
    d.kantor,
    d.jabatan_pegawai,
    d.gaji_pokok,
    d.tunjangan_keluarga,
    d.tunjangan_pangan,
    d.tunjangan_jabatan,
    d.tunjangan_keahliankhusus,
    d.tunjangan_operasional,
    d.tunjangan_kesehatan,
    d.tunjangan_pph,
    d.tunjangan_lembur,

    (
        d.gaji_pokok +
        d.tunjangan_keluarga +
        d.tunjangan_pangan +
        d.tunjangan_jabatan +
        d.tunjangan_keahliankhusus +
        d.tunjangan_operasional +
        d.tunjangan_kesehatan +
        d.tunjangan_pph +
        d.tunjangan_lembur
    ) AS total_gaji

FROM data_gaji d
ORDER BY d.no ASC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Rekap Gaji Lembur</title>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h3>Rekap Gaji & Lembur Pegawai</h3>

        <!-- Filter Bulan & Tahun -->
        <form method="GET" class="row mb-4">
            <div class="col-md-2">
                <select name="bulan" class="form-select">
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
                        $sel = ($key == $bulan) ? 'selected' : '';
                        echo "<option value='$key' $sel>$bl</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary">Tampilkan</button>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Kantor</th>
                    <th>Jabatan</th>
                    <th>Gaji Pokok</th>
                    <th>Total Tunjangan</th>
                    <th>Uang Lembur</th>
                    <th>Total Gaji</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($d = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $d['no'] ?></td>
                        <td><?= $d['nama_pegawai'] ?></td>
                        <td><?= $d['kantor'] ?></td>
                        <td><?= $d['jabatan_pegawai'] ?></td>
                        <td>Rp <?= number_format($d['gaji_pokok'], 0, ',', '.') ?></td>
                        <td>
                            Rp <?= number_format(
                                $d['tunjangan_keluarga'] +
                                $d['tunjangan_pangan'] +
                                $d['tunjangan_jabatan'] +
                                $d['tunjangan_keahliankhusus'] +
                                $d['tunjangan_operasional'] +
                                $d['tunjangan_kesehatan'] +
                                $d['tunjangan_pph'],
                                0,
                                ',',
                                '.'
                            ) ?>
                        </td>
                        <td>Rp <?= number_format($d['tunjangan_lembur'], 0, ',', '.') ?></td>
                        <td class="fw-bold">Rp <?= number_format($d['total_gaji'], 0, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>