<?php
require 'function.php'; // Pastikan koneksi database aktif

$token_valid = false;
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    // Cek apakah token cocok dan belum expired (waktu sekarang < token_expires_at)
    $now = date('Y-m-d H:i:s');
    $stmt = mysqli_prepare($conn, "SELECT id_user, username FROM account WHERE reset_token = ? AND token_expires_at > ?");
    mysqli_stmt_bind_param($stmt, "ss", $token, $now);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user) {
        $token_valid = true;
    } else {
        $error_msg = "Token pemulihan tidak valid atau sudah kedaluwarsa (Maksimal 15 menit)!";
    }
} else {
    $error_msg = "Akses ditolak! Token tidak ditemukan.";
}

// Eksekusi pembaruan password baru jika form dikirim
if (isset($_POST['simpan_password_baru']) && $token_valid) {
    $password_baru = $_POST['password_baru'];
    $konfirmasi_baru = $_POST['konfirmasi_baru'];

    if (strlen($password_baru) < 6) {
        $error_msg = "Password baru minimal harus 6 karakter!";
    } else if ($password_baru !== $konfirmasi_baru) {
        $error_msg = "Konfirmasi password baru tidak cocok!";
    } else {
        // Enkripsi password baru dengan Bcrypt yang aman dari Brute Force
        $password_hashed = password_hash($password_baru, PASSWORD_BCRYPT);

        // Update password dan kosongkan kembali token-nya agar tidak bisa dipakai ulang (Replay Attack)
        $stmt_update = mysqli_prepare($conn, "UPDATE account SET password = ?, reset_token = NULL, token_expires_at = NULL WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_update, "si", $password_hashed, $user['id_user']);
        $berhasil = mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);

        if ($berhasil) {
            $success_msg = "Password akun " . htmlspecialchars($user['username']) . " berhasil diubah! Mengalihkan ke login...";
            header('refresh:3;url=login.php');
        } else {
            $error_msg = "Gagal menyimpan password baru ke database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Project TTU - Set Password Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header bg-dark text-white text-center"><h3 class="font-weight-light my-3">Atur Password Baru</h3></div>
                                <div class="card-body">
                                    
                                    <?php if(isset($error_msg)): ?>
                                        <div class="alert alert-danger"><?= htmlspecialchars($error_msg); ?></div>
                                    <?php endif; ?>

                                    <?php if(isset($success_msg)): ?>
                                        <div class="alert alert-success"><?= htmlspecialchars($success_msg); ?></div>
                                    <?php endif; ?>

                                    <?php if($token_valid && !isset($success_msg)): ?>
                                        <form method="post">
                                            <div class="mb-3">
                                                <label class="small mb-1 fw-bold">Password Baru</label>
                                                <input class="form-control" type="password" name="password_baru" placeholder="Minimal 6 karakter" required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="small mb-1 fw-bold">Ulangi Password Baru</label>
                                                <input class="form-control" type="password" name="konfirmasi_baru" placeholder="Konfirmasi password baru" required />
                                            </div>
                                            <div class="d-grid mt-4">
                                                <button type="submit" name="simpan_password_baru" class="btn btn-success">Perbarui Password Saya</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>