<?php
require 'vendor/autoload.php';
include 'function.php';

use Dompdf\Dompdf;

$dataseluruhkaryawan = mysqli_query($conn, "SELECT * FROM data_pegawai");

$html = '
<h2 style="text-align:center;">DATA SELURUH KARYAWAN</h2>

<table width="100%" border="1" cellspacing="0" cellpadding="6" style="font-size:12px; border-collapse: collapse;">
    <thead>
        <tr style="background:#eee; font-weight:bold;">
            <th>NIP</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Golongan</th>
            <th>Grade</th>
            <th>Tahun Bekerja</th>
            <th>Status</th>
            <th>Gaji Pokok</th>
            <th>Tgl Bergabung</th>
            <th>Tgl Selesai Kerja</th>
            <th>Kantor Cabang</th>
            <th>Email</th>
            <th>Alamat</th>
            <th>Tempat Lahir</th>
            <th>Tgl Lahir</th>
            <th>Jenis Kelamin</th>
            <th>Status Kawin</th>
            <th>No HP</th>
            <th>Agama</th>
            <th>Pendidikan</th>
            <th>Gol Darah</th>
            <th>NPWP</th>
            <th>No KTP</th>
            <th>No KK</th>
        </tr>
    </thead>
    <tbody>
';

while ($data = mysqli_fetch_array($dataseluruhkaryawan)) {

    $gajipokok = number_format($data['gajipokok_pegawai'], 0, ',', '.');

    $html .= '
    <tr>
        <td>'.$data['nip_pegawai'].'</td>
        <td>'.$data['nama_pegawai'].'</td>
        <td>'.$data['jabatan_pegawai'].'</td>
        <td>'.$data['golongan_pegawai'].'</td>
        <td>'.$data['grade_pegawai'].'</td>
        <td>'.$data['tahun_bekerja'].'</td>
        <td>'.$data['status_kepegawaian'].'</td>
        <td>'.$gajipokok.'</td>
        <td>'.$data['tanggal_bergabung'].'</td>
        <td>'.$data['tanggal_selesaikerja'].'</td>
        <td>'.$data['kantor_cabang'].'</td>
        <td>'.$data['email_pegawai'].'</td>
        <td>'.$data['alamat_domisili'].'</td>
        <td>'.$data['tempat_lahir'].'</td>
        <td>'.$data['tanggal_lahir'].'</td>
        <td>'.$data['jenis_kelamin'].'</td>
        <td>'.$data['status_perkawinan'].'</td>
        <td>'.$data['nomor_hp'].'</td>
        <td>'.$data['agama'].'</td>
        <td>'.$data['pendidikan_terakhir'].'</td>
        <td>'.$data['golongan_darah'].'</td>
        <td>'.$data['npwp'].'</td>
        <td>'.$data['nomor_ktp'].'</td>
        <td>'.$data['nomor_kk'].'</td>
    </tr>';
}

$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

$dompdf->stream("data-seluruh-karyawan.pdf", array("Attachment" => true));

?>