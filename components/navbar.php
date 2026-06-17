<?php
$user = current_user();
$role = $_SESSION['role'] ?? '';

if ($role === 'super-admin' || $role === 'kepala-dinas' || $role === 'bendahara') {
    $logout_path = "../auth/logout.php";
} else {
    $logout_path = "auth/logout.php";
}

if ($role === 'super-admin' || $role === 'kepala-dinas' || $role === 'bendahara') {
    $img_folder = "../assets/img/users/";
    $default_avatar = "../assets/img/default-avatar.png";
} else {
    $img_folder = "assets/img/users/";
    $default_avatar = "assets/img/default-avatar.png";
}

$user_id = $_SESSION['user_id'];
$query_user = mysqli_query($conn, "SELECT nama, foto FROM users WHERE id = '$user_id'");
$user_navbar = mysqli_fetch_assoc($query_user);
?>

<header class="header">
    <div style="display: flex; align-items: center; gap: 15px;">
        <button id="toggleSidebar" class="hamburger-navbar"
            style="background: none; border: none; cursor: pointer; color: black; padding-left: 4px">
            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <!-- <img src="../assets/img/logo-kab-sidoarjo.png" alt="Logo Sidoarjo" style="height: 45px;"> -->
        <img src="../assets/img/Logo-Dishub.png" alt="Logo Dishub"
            style="height: 45px; margin-right: 5px; margin-left: 10px;">
        <div style="border-left: 3px solid #e2e8f0; height: 30px;"></div>
        <div class="navbar-label">
            <h2 class="navbar-label-text">
                SI-PARKIR <span class="navbar-label-sub">Dishub Sidoarjo</span>
            </h2>
        </div>
    </div>

    <div class="user-profile-wrapper" style="position: relative;">
        <div class="user-profile" onclick="toggleDropdown()"
            style="display: flex; align-items: center; gap: 12px; padding-left: 20px; cursor: pointer; user-select: none;">
            <div style="text-align: right; line-height: 1.2;">
                <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b;"><?= $user['nama'] ?></div>
                <div
                    style="font-size: 0.65rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    Petugas Dinas</div>
            </div>
            <div style="position: relative;">
                <?php
                $nama_file_foto = $user_navbar['foto'] ?? '';
                $foto_path = (!empty($nama_file_foto) && file_exists($img_folder . $nama_file_foto))
                    ? $img_folder . $nama_file_foto
                    : $default_avatar;
                ?>

                <img src="<?= $foto_path; ?>" alt="Profile Image"
                    style="width: 42px; height: 42px; border-radius: 12px; object-fit: cover; border: 2px solid #f1f5f9;">
                <div
                    style="position: absolute; bottom: -2px; right: -2px; width: 12px; height: 12px; background: #22c55e; border-radius: 50%; border: 2px solid white;">
                </div>
            </div>
            <i class="fas fa-chevron-down" style="font-size: 0.7rem; color: #64748b; margin-left: 5px;"></i>
        </div>

        <div id="profileDropdown" class="dropdown-menu">
            <a href="profile.php" class="dropdown-item">
                <span class="icon">👤</span> Profil Saya
            </a>
            <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 5px 0;">
            <a href="<?= $logout_path; ?>" class="dropdown-item" style="color: #ef4444;">
                <span class="icon">🚪</span> Keluar
            </a>
        </div>
    </div>
</header>
<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
    }

    window.onclick = function (event) {
        if (!event.target.closest('.user-profile-wrapper')) {
            const dropdowns = document.getElementsByClassName("dropdown-menu");
            for (let i = 0; i < dropdowns.length; i++) {
                let openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
</script>