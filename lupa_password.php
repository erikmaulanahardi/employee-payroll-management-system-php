<?php
require 'function.php'; // Pastikan koneksi $conn ada di sini

if (isset($_POST['minta_reset'])) {
    $username = trim($_POST['username']);

    // Cek apakah username terdaftar di sistem
    $stmt = mysqli_prepare($conn, "SELECT id_user FROM account WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user) {
        // 1. Generate token secure 64 karakter
        $token = bin2hex(random_bytes(32));
        
        // 2. Set waktu kedaluwarsa: Sekarang + 15 Menit
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 3. Simpan token dan waktu kedaluwarsa ke database secara aman
        $stmt_update = mysqli_prepare($conn, "UPDATE account SET reset_token = ?, token_expires_at = ? WHERE username = ?");
        mysqli_stmt_bind_param($stmt_update, "sss", $token, $expires_at, $username);
        $sukses = mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);

        if ($sukses) {
            // Pembuatan link reset unik
            $link_reset = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/proses_reset.php?token=" . $token;
            
            // NOTE UNTUK DOSEN: Di server asli, variabel $link_reset ini dikirim pakai library PHPMailer ke email user.
            // Karena kita jalankan di localhost (XAMPP), kita tampilkan simulasi link-nya di layar agar bisa di-demo-kan langsung.
            $success_msg = "Permintaan berhasil! (Simulasi Email Terkirim)<br>Silakan klik link berikut untuk mereset password Anda:<br><a href='$link_reset' class='fw-bold text-white'>$link_reset</a>";
        } else {
            $error_msg = "Gagal memproses permintaan reset password.";
        }
    } else {
        $error_msg = "Username tidak ditemukan di dalam sistem!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Project TTU - Lupa Password</title>
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
                                <div class="card-header bg-dark text-white text-center"><h3 class="font-weight-light my-3">Pemulihan Akun</h3></div>
                                <div class="card-body">
                                    <div class="small mb-3 text-muted">Masukkan username Anda untuk mendapatkan link pemulihan password baru.</div>
                                    
                                    <?php if(isset($error_msg)): ?>
                                        <div class="alert alert-danger"><?= htmlspecialchars($error_msg); ?></div>
                                    <?php endif; ?>

                                    <?php if(isset($success_msg)): ?>
                                        <div class="alert alert-success bg-success text-white"><?= $success_msg; ?></div>
                                    <?php endif; ?>

                                    <form method="post">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputUsername" type="text" name="username" placeholder="Username Anda" required />
                                            <label for="inputUsername">Username</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class="small" href="login.php">Kembali ke Halaman Login</a>
                                            <button type="submit" name="minta_reset" class="btn btn-dark">Kirim Link Pemulihan</button>
                                        </div>
                                    </form>
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