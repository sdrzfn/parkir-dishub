<?php
$user = current_user();
$role = $_SESSION['role'] ?? '';

if ($role === 'super-admin' || $role === 'kepala-dinas') {
    $logout_path = "../auth/logout.php";
} else {
    $logout_path = "auth/logout.php";
}
?>

<header class="header">
    <div style="display: flex; align-items: center; gap: 15px;">
        <img src="../assets/img/logo-kab-sidoarjo.png" alt="Logo Sidoarjo" style="height: 45px;">
        <div style="border-left: 2px solid #e2e8f0; height: 30px;"></div>
        <img src="../assets/img/Logo-Dishub.png" alt="Logo Dishub" style="height: 45px;">
        <div style="margin-left: 10px;">
            <h2
                style="margin: 0; font-size: 1.2rem; color: var(--primary-color); letter-spacing: 1px; font-weight: 800;">
                SI-PARKIR <span style="font-weight: 400; color: #64748b;">Dishub Sidoarjo</span>
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
                <img src="https://ui-avatars.com/api/?name=Admin&background=003366&color=fff"
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