<?php
require 'function.php';

if(isset($_SESSION['log'])){
    header('location:index.php');
    exit;
}

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

   
    $stmt = mysqli_prepare($conn, "SELECT * FROM account WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $cekdb = mysqli_stmt_get_result($stmt);
    
    $hitung = mysqli_num_rows($cekdb);

    if($hitung > 0){
    $data = mysqli_fetch_assoc($cekdb);


    if(password_verify($password, $data['password'])){
        session_regenerate_id(true);
        $_SESSION['log'] = 'True';
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $data['role'];

        $_SESSION['last_activity'] = time();

        header('location:index.php');
        exit;

    } else {
        // Password salah
        header('location:login.php?error=Username dan password tidak cocok!');
        exit;
    }

    } else {
        // Username tidak ada
        header('location:login.php?error=Username dan password tidak cocok!');
        exit;
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
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Login</h3></div>
                                    <div class="card-body">
                                        <form method="post">

                                        <?php if(isset($_GET['error'])): ?>
                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <?php echo htmlspecialchars($_GET['error']); ?>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            <?php endif; ?>

                                            <div class="form-floating mb-3">
                                                <input class="form-control" name="username" id="inputUsername" type="text" placeholder="username" required/>
                                                <label for="inputUsername">Username</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" name="password" id="inputPassword" type="password" placeholder="Password" required />
                                                <label for="inputPassword">Password</label>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">   
                                                <button class="btn btn-primary" name="login">Login</button>                                             
                                            </div>
                                            
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small"><a href="register.php">Belum punya akun? Daftarkan akun!</a></div>
                                        <a class="small text-muted" href="lupa_password.php">Lupa password akun Anda?</a>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
