<?php 

$current_page = basename($_SERVER['PHP_SELF']);

?>

<aside class="sidebar">
    <div class="logo-section">
        <div
            style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Logo_of_the_Ministry_of_Transportation_of_the_Republic_of_Indonesia.svg/960px-Logo_of_the_Ministry_of_Transportation_of_the_Republic_of_Indonesia.svg.png'); background-size: contain; background-repeat: no-repeat; width: 35px; height: 35px; border-radius: 8px;">
        </div>
        <span>SISTEM PARKIR</span>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="../index.php" class="nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>">
                <img src="../assets/icons/dashboard.svg" class="nav-icon">
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../lokasi.php" class="nav-link <?= ($current_page == 'lokasi.php') ? 'active' : ''; ?>">
                <img src="../assets/icons/location.svg" class="nav-icon">
                <span>Lokasi</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../parkir.php" class="nav-link <?= ($current_page == 'parkir.php') ? 'active' : ''; ?>">
                <img src="../assets/icons/data.svg" class="nav-icon">
                <span>Data Parkir</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../tukang-parkir.php"
                class="nav-link <?= ($current_page == 'tukang-parkir.php') ? 'active' : ''; ?>">
                <img src="../assets/icons/juru-parkir.svg" class="nav-icon">
                <span>Juru Parkir</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../retribusi.php" class="nav-link <?= ($current_page == 'retribusi.php') ? 'active' : ''; ?>">
                <img src="../assets/icons/retribusi.svg" class="nav-icon">
                <span>Retribusi</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../peta.php" class="nav-link <?= ($current_page == 'peta.php') ? 'active' : ''; ?>">
                <img src="../assets/icons/peta.svg" class="nav-icon">
                <span>Peta Wilayah</span>
            </a>
        </li>
    </ul>

    <div style="margin-top: auto;">
        <a href="../auth/logout.php" style="color: #fda4af; text-decoration: none; font-size: 0.9rem;">Keluar Sistem</a>
    </div>
</aside>