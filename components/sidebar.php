<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';

if ($role === 'super-admin' || $role === 'kepala-dinas' || $role === 'bendahara') {
    $base_url = "";
    $asset_path = "../assets/";
} else {
    $base_url = "";
    $asset_path = "assets/";
}
?>

<div class="sidebar-overlay" id="sidebarOverlay">
    <aside class="sidebar" id="sidebar"
        style="background: #1e293b; color: white; display: flex; flex-direction: column; border-right: 1px solid #334155;">
        <div class="sidebar-header" style="display: flex; align-items: center; justify-content: space-between;">
            <button id="toggleSidebar" class="hamburger-sidebar"
                style="background: none; border: none; cursor: pointer; color: white; padding-left: 4px">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        <!-- <div class="logo-section">
        <div
            style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Logo_of_the_Ministry_of_Transportation_of_the_Republic_of_Indonesia.svg/960px-Logo_of_the_Ministry_of_Transportation_of_the_Republic_of_Indonesia.svg.png'); background-size: contain; background-repeat: no-repeat; width: 35px; height: 35px; border-radius: 8px;">
        </div>
        <span>SISTEM PARKIR</span>
    </div> -->

        <ul class="nav-menu">
            <?php if ($role === 'admin' || $role === 'kepala-dinas'): ?>
                <li class="nav-item">
                    <a href="<?= $base_url; ?>index.php"
                        class="nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>" title="Dashboard">
                        <img src="<?= $asset_path; ?>icons/dashboard.svg" class="nav-icon">
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>retribusi-parkir.php"
                        class="nav-link <?= ($current_page == 'retribusi-parkir.php') ? 'active' : ''; ?>"
                        title="Retribusi">
                        <img src="<?= $asset_path; ?>icons/retribusi.svg" class="nav-icon">
                        <span class="nav-text">Retribusi</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>lokasi.php"
                        class="nav-link <?= ($current_page == 'lokasi.php') ? 'active' : ''; ?>" title="Lokasi">
                        <img src="<?= $asset_path; ?>icons/location.svg" class="nav-icon">
                        <span class="nav-text">Lokasi</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>koordinator-wilayah.php"
                        class="nav-link <?= ($current_page == 'koordinator-wilayah.php') ? 'active' : ''; ?>"
                        title="Korwil">
                        <img src="<?= $asset_path; ?>icons/data.svg" class="nav-icon">
                        <span class="nav-text">Koordinator Wilayah</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>tukang-parkir.php"
                        class="nav-link <?= ($current_page == 'tukang-parkir.php') ? 'active' : ''; ?>" title="Petugas Parkir">
                        <img src="<?= $asset_path; ?>icons/juru-parkir.svg" class="nav-icon">
                        <span class="nav-text">Petugas Parkir</span>
                    </a>
                </li>                

                <li class="nav-item">
                    <a href="<?= $base_url; ?>peta.php"
                        class="nav-link <?= ($current_page == 'peta.php') ? 'active' : ''; ?>" title="Peta Wilayah">
                        <img src="<?= $asset_path; ?>icons/peta.svg" class="nav-icon">
                        <span class="nav-text">Peta Wilayah</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'super-admin'): ?>
                <li class="nav-item">
                    <a href="<?= $base_url; ?>index.php"
                        class="nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>" title="Dashboard">
                        <img src="<?= $asset_path; ?>icons/dashboard.svg" class="nav-icon">
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>retribusi-parkir.php"
                        class="nav-link <?= ($current_page == 'retribusi-parkir.php') ? 'active' : ''; ?>"
                        title="Retribusi">
                        <img src="<?= $asset_path; ?>icons/retribusi.svg" class="nav-icon">
                        <span class="nav-text">Retribusi</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>lokasi.php"
                        class="nav-link <?= ($current_page == 'lokasi.php') ? 'active' : ''; ?>" title="Lokasi">
                        <img src="<?= $asset_path; ?>icons/location.svg" class="nav-icon">
                        <span class="nav-text">Lokasi</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>koordinator-wilayah.php"
                        class="nav-link <?= ($current_page == 'koordinator-wilayah.php') ? 'active' : ''; ?>"
                        title="Korwil">
                        <img src="<?= $asset_path; ?>icons/data.svg" class="nav-icon">
                        <span class="nav-text">Koordinator Wilayah</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>tukang-parkir.php"
                        class="nav-link <?= ($current_page == 'tukang-parkir.php') ? 'active' : ''; ?>" title="Petugas Parkir">
                        <img src="<?= $asset_path; ?>icons/juru-parkir.svg" class="nav-icon">
                        <span class="nav-text">Petugas Parkir</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>peta.php"
                        class="nav-link <?= ($current_page == 'peta.php') ? 'active' : ''; ?>" title="Peta Wilayah">
                        <img src="<?= $asset_path; ?>icons/peta.svg" class="nav-icon">
                        <span class="nav-text">Peta Wilayah</span>
                    </a>
                </li>

                <li class="nav-item" style="margin-top: 10px; border-top: 1px solid #334155; padding-top: 10px;">
                    <a href="<?= $base_url; ?>kelola-pengguna.php"
                        class="nav-link <?= ($current_page == 'kelola-pengguna.php') ? 'active' : ''; ?>"
                        title="Kelola Admin">
                        <img src="<?= $asset_path; ?>icons/settings.svg" class="nav-icon">
                        <span class="nav-text">Kelola Admin</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'bendahara'): ?>
                <li class="nav-item">
                    <a href="<?= $base_url; ?>index.php"
                        class="nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>" title="Dashboard">
                        <img src="<?= $asset_path; ?>icons/dashboard.svg" class="nav-icon">
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= $base_url; ?>retribusi-parkir.php"
                        class="nav-link <?= ($current_page == 'retribusi-parkir.php') ? 'active' : ''; ?>"
                        title="Retribusi">
                        <img src="<?= $asset_path; ?>icons/retribusi.svg" class="nav-icon">
                        <span class="nav-text">Retribusi</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="<?= $base_url; ?>peta.php"
                        class="nav-link <?= ($current_page == 'peta.php') ? 'active' : ''; ?>"
                        title="Retribusi">
                        <img src="<?= $asset_path; ?>icons/peta.svg" class="nav-icon">
                        <span class="nav-text">Peta</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <!-- <div style="margin-top: auto;">
            <a href="auth/logout.php" style="color: #fda4af; text-decoration: none; font-size: 0.9rem;">Keluar
                Sistem</a>
        </div> -->
    </aside>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleMenu() {
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        }

        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleMenu();
        });

        if (overlay) {
            overlay.addEventListener('click', toggleMenu);
        }
    });
</script>