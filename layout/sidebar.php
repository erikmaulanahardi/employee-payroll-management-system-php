<?php $username = $_SESSION['username']; ?>
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
        <div class="container-fluid px-4 pt-4"></div>