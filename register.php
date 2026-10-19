<?php
require 'function.php';

if(isset($_SESSION['log'])){
    header('location:index.php');
    exit;
}

if(isset($_POST['addakun'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    if($password !== $confirm_password){
        $error = "Password tidak cocok dengan konfirmasi password!";
    } 

    else if(strlen($password) < 6){
        $error = "Password minimal 6 karakter!";
    }
    else {

        $stmt_cek = mysqli_prepare($conn, "SELECT * FROM account WHERE username = ?");
        mysqli_stmt_bind_param($stmt_cek, "s", $username);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);
        $hitung = mysqli_num_rows($result_cek);
        mysqli_stmt_close($stmt_cek);

        if($hitung > 0){
            $error = "Username sudah terdaftar!";
        } else {

            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $role = 'user';
            $stmt = mysqli_prepare($conn, "INSERT INTO account (username, password, role) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $password_hashed, $role);

            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if($result){
                $success = "Akun berhasil dibuat! Silakan login.";
  
                header('refresh:2;url=login.php');
            } else {
                $error = "Gagal membuat akun. Coba lagi!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Project TTU - Sistem Manajemen Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Register Akun</h3>
                                </div>
                                <div class="card-body">
                                    <?php if(isset($error)): ?>
                                        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            <?php echo htmlspecialchars($error); ?>
                                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(isset($success)): ?>
                                        <div class='alert alert-success alert-dismissible fade show' role='alert'>
                                            <i class="fas fa-check-circle me-2"></i>
                                            <?php echo htmlspecialchars($success); ?>
                                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                        </div>
                                    <?php endif; ?>

                                        <form method="post">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="username" type="text"
                                                placeholder="username" required />
                                            <label for="inputEmail">Username</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="password" type="password"
                                                placeholder="password" required />
                                            <label for="inputPassword">Password (minimal 6 karakter)</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" name="confirm_password" type="password"
                                                placeholder="confirm_password" required />
                                            <label for="inputPassword">Konfirmasi Password</label>
                                        </div>
                                        <div class="card-footer text-center py-3">
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-block"
                                                    name="addakun">Buat Akun</button>
                                            </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="login.php">Sudah punya akun? Silakan login!</a></div>
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
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>