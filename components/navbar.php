<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user = current_user();
$role = $_SESSION['role'] ?? '';

// Determine path prefixes based on role
if ($role === 'super-admin' || $role === 'kepala-dinas' || $role === 'bendahara') {
    $logout_path   = "../auth/logout.php";
    $profile_path  = "profile.php";
    $base_url      = "";
    $img_folder    = "../assets/img/users/";
    $img_base      = "../";
} else {
    $logout_path   = "auth/logout.php";
    $profile_path  = "profile.php";
    $base_url      = "";
    $img_folder    = "assets/img/users/";
    $img_base      = "";
}

// 1. Config-driven navigation object
$nav_config = [
    [
        'label' => 'Dashboard',
        'href'  => 'index.php',
        'roles' => ['admin', 'kepala-dinas', 'super-admin', 'bendahara']
    ],
    [
        'label' => 'Retribusi',
        'href'  => 'retribusi-parkir.php',
        'roles' => ['admin', 'kepala-dinas', 'super-admin', 'bendahara']
    ],
    [
        'label' => 'Lokasi',
        'href'  => 'lokasi.php',
        'roles' => ['admin', 'kepala-dinas', 'super-admin']
    ],
    [
        'label' => 'Korwil',
        'href'  => 'koordinator-wilayah.php',
        'roles' => ['admin', 'kepala-dinas', 'super-admin']
    ],
    [
        'label' => 'Petugas Parkir',
        'href'  => 'tukang-parkir.php',
        'roles' => ['admin', 'kepala-dinas', 'super-admin']
    ],
    [
        'label' => 'Peta',
        'href'  => 'peta.php',
        'roles' => ['admin', 'kepala-dinas', 'super-admin', 'bendahara']
    ],
    [
        'label' => 'Admin',
        'href'  => 'kelola-pengguna.php',
        'roles' => ['super-admin']
    ],
];

$user_id = $_SESSION['user_id'];
$query_user = mysqli_query($conn, "SELECT nama, foto FROM users WHERE id = '$user_id'");
$user_navbar = mysqli_fetch_assoc($query_user);
$nama_file_foto = $user_navbar['foto'] ?? '';
$foto_path = (!empty($nama_file_foto) && file_exists($img_folder . $nama_file_foto))
    ? $img_folder . $nama_file_foto
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['nama'] ?? 'U') . '&background=1e1b4b&color=fff&size=80';

// Visual active state styling
/**
 * @param string $href
 * @param string $current_page
 * @return string
 */
function getNavLinkClass($href, $current_page) {
    if ($current_page === $href) {
        return "px-5 py-2 rounded-full text-sm font-semibold bg-brand-950 text-white shadow-md";
    }
    return "px-5 py-2 rounded-full text-sm font-medium text-slate-600 hover:text-brand-950 hover:bg-slate-100/60 transition-colors";
}
?>

<!-- 2. Invisible intersection observer target at the very top of the page -->
<div id="nav-observer-target" class="absolute top-0 w-full h-1 pointer-events-none"></div>

<!-- Fixed background gradient (shared) -->
<div class="fixed inset-0 -z-10 pointer-events-none" style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">
    <div class="absolute inset-0 bg-gradient-to-br from-transparent via-brand-50/20 to-brand-100/30"></div>
</div>

<!-- 
  DESKTOP NAVBAR WRAPPER (Fixed top)
  Tracks "hero" vs "scrolled". Initial state: hero.
-->
<div id="navbar-wrapper" class="fixed top-0 left-0 w-full z-50 flex justify-center py-5 transition-[padding] duration-500" data-nav-state="hero">
    
    <!-- Morphing Inner Container -->
    <!-- Default state (Hero) is w-full px-8. JS/GSAP Flip will transition it to fit-content floating pill -->
    <nav id="nav-morph-container" class="flex items-center justify-between w-full px-8 md:px-[60px]" data-flip-id="nav-container">
        
        <!-- Logo Pill -->
        <a href="<?= $base_url ?>index.php" class="glass-target flex items-center gap-3 bg-white/70 backdrop-blur-xl px-4 py-2 rounded-full border border-white/60 shadow-soft no-underline flex-shrink-0" data-flip-id="nav-logo">
            <img src="<?= $img_base ?>assets/img/Logo-Dishub.png" alt="Dishub Logo" class="w-8 h-8 object-contain drop-shadow-sm">
            <div class="flex flex-col justify-center">
                <span class="font-bold text-[15px] leading-none text-brand-950 tracking-tight">SI-PARKIR</span>
                <span class="font-normal text-[10px] leading-none text-slate-500 mt-0.5 tracking-wide uppercase">Dishub Sidoarjo</span>
            </div>
        </a>

        <!-- Center Links -->
        <div class="glass-target flex items-center gap-1 bg-white/70 backdrop-blur-xl p-1.5 rounded-full border border-white/60 shadow-soft overflow-hidden" data-flip-id="nav-links">
            <?php foreach ($nav_config as $nav_item): ?>
                <?php if (in_array($role, $nav_item['roles'])): ?>
                    <a href="<?= $base_url . $nav_item['href'] ?>" class="<?= getNavLinkClass($nav_item['href'], $current_page) ?>">
                        <?= htmlspecialchars($nav_item['label']) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Right Controls -->
        <div class="flex items-center gap-2 flex-shrink-0" data-flip-id="nav-controls">
            <!-- Settings -->
            <a href="<?= $profile_path ?>" class="glass-target w-10 h-10 flex items-center justify-center bg-white/70 backdrop-blur-xl rounded-full border border-white/60 shadow-soft text-slate-500 hover:text-brand-950 transition-colors" title="Profil" data-flip-id="nav-cog">
                <i class="fas fa-cog text-[15px]"></i>
            </a>

            <!-- Profile Dropdown -->
            <div class="relative group cursor-pointer" data-flip-id="nav-avatar">
                <div class="glass-target w-10 h-10 rounded-full bg-white/70 backdrop-blur-xl p-0.5 border border-white/60 shadow-soft overflow-hidden">
                    <img src="<?= $foto_path ?>" alt="Avatar" class="w-full h-full rounded-full object-cover">
                </div>
                <!-- Dropdown Content -->
                <div class="absolute right-0 mt-2 w-52 bg-white/95 backdrop-blur-md rounded-2xl shadow-floating py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 border border-white/60 z-50 transform origin-top-right scale-95 group-hover:scale-100">
                    <div class="px-4 py-2.5 border-b border-slate-100">
                        <p class="text-sm font-semibold text-brand-950 truncate"><?= htmlspecialchars($user['nama'] ?? '') ?></p>
                        <p class="text-xs text-slate-500 capitalize"><?= $role ?></p>
                    </div>
                    <a href="<?= $profile_path ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 transition">
                        <i class="fas fa-user-circle text-brand-400 w-4"></i> Profil Saya
                    </a>
                    <a href="<?= $logout_path ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition">
                        <i class="fas fa-sign-out-alt w-4"></i> Keluar
                    </a>
                </div>
            </div>
        </div>
        
    </nav>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MOBILE TOP HEADER
     Visible only on screens < 768px.
══════════════════════════════════════════════════════════════ -->
<header id="mobile-top-header" class="fixed top-0 left-0 right-0 z-[990] backdrop-blur-xl border-b border-white/40 shadow-sm px-4 flex items-center justify-between md:hidden" style="background: rgba(255,255,255,0.88); height: 60px;">
    <a href="<?= $base_url ?>index.php" class="flex items-center gap-3 no-underline" style="text-decoration:none;">
        <img src="<?= $img_base ?>assets/img/Logo-Dishub.png" alt="Dishub Logo" class="w-9 h-9 object-contain drop-shadow-sm">
        <div class="flex flex-col justify-center">
            <span class="font-bold leading-none text-brand-950" style="font-size:15px; letter-spacing:-0.02em;">SI Parkir</span>
            <span class="font-normal leading-none text-slate-500 mt-0.5 tracking-wide uppercase" style="font-size:9px;">Dishub Sidoarjo</span>
        </div>
    </a>
    <button type="button" onclick="openSidebar()" aria-label="Buka Menu" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100/80 text-slate-600 hover:bg-slate-200 transition-colors" style="border:none; cursor:pointer;">
        <i class="fas fa-bars" style="font-size:17px;"></i>
    </button>
</header>

<!-- ═══════════════════════════════════════════════════════════
     SIDEBAR (OFF-CANVAS) FOR MOBILE
══════════════════════════════════════════════════════════════ -->
<div id="mobile-sidebar-overlay" class="fixed inset-0 bg-black/40 z-[1000] backdrop-blur-sm transition-opacity opacity-0 pointer-events-none" onclick="closeSidebar()"></div>
<?php
// Map technical role names to human-readable sidebar labels
$role_labels = [
    'admin'         => 'Admin Dishub',
    'super-admin'   => 'Super Admin',
    'kepala-dinas'  => 'Kepala Dinas',
    'bendahara'     => 'Bendahara',
];
$role_display = $role_labels[$role] ?? ucfirst($role);
?>
<aside id="mobile-sidebar" class="fixed top-0 right-0 h-full w-[280px] bg-white z-[1001] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Sidebar Header: User Profile -->
    <div class="p-5 flex items-center justify-between bg-gradient-to-br from-brand-950 to-brand-700">
        <div class="flex items-center gap-3">
            <div class="relative flex-shrink-0">
                <img src="<?= $foto_path ?>" alt="Avatar" class="w-11 h-11 rounded-full object-cover border-2 border-white/30 shadow-md">
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-400 border-2 border-white rounded-full"></span>
            </div>
            <div class="flex flex-col min-w-0">
                <span class="font-bold text-sm text-white truncate max-w-[145px]"><?= htmlspecialchars(ucwords(strtolower($user['nama'] ?? ''))) ?></span>
                <span class="font-medium text-[10px] text-white/70 mt-0.5"><?= htmlspecialchars($role_display) ?></span>
                <span class="font-normal text-[9px] text-white/50 tracking-wide uppercase">Dishub Sidoarjo</span>
            </div>
        </div>
        <button type="button" onclick="closeSidebar()" class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-full bg-white/10 text-white/80 hover:bg-white/20 hover:text-white transition-colors">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto py-4 px-3 flex flex-col gap-1">
        <?php if (in_array($role, ['admin','kepala-dinas','super-admin'])): ?>
        <a href="<?= $base_url ?>lokasi.php" class="flex items-center gap-3 p-3 rounded-xl <?= $current_page === 'lokasi.php' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' ?> transition">
            <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-current"><i class="fas fa-map-pin"></i></div>
            <span class="font-semibold text-sm">Lokasi</span>
        </a>
        <a href="<?= $base_url ?>koordinator-wilayah.php" class="flex items-center gap-3 p-3 rounded-xl <?= $current_page === 'koordinator-wilayah.php' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' ?> transition">
            <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-current"><i class="fas fa-sitemap"></i></div>
            <span class="font-semibold text-sm">Korwil</span>
        </a>
        <?php endif; ?>
        <?php if ($role === 'super-admin'): ?>
        <a href="<?= $base_url ?>kelola-pengguna.php" class="flex items-center gap-3 p-3 rounded-xl <?= $current_page === 'kelola-pengguna.php' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' ?> transition">
            <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-current"><i class="fas fa-user-shield"></i></div>
            <span class="font-semibold text-sm">Admin</span>
        </a>
        <?php endif; ?>
        <a href="<?= $profile_path ?>" class="flex items-center gap-3 p-3 rounded-xl <?= $current_page === 'profile.php' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' ?> transition">
            <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center text-current"><i class="fas fa-user-circle"></i></div>
            <span class="font-semibold text-sm">Profil</span>
        </a>
    </div>
    <div class="p-4 border-t border-slate-100">
        <a href="<?= $logout_path ?>" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 font-semibold text-sm transition">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</aside>

<!-- ═══════════════════════════════════════════════════════════
     MOBILE BOTTOM NAVIGATION BAR (PILL STYLE)
══════════════════════════════════════════════════════════════ -->
<nav class="bottom-nav md:hidden" role="navigation" aria-label="Navigasi Bawah">
    <div class="bottom-nav-inner">
        <div class="bottom-nav-pill-bg"></div>
        <div class="bottom-nav-grid">
            <!-- Dashboard -->
            <a href="<?= $base_url ?>index.php" class="bottom-nav-item <?= $current_page === 'index.php' ? 'active' : '' ?>">
                <div class="bottom-nav-icon-wrapper">
                    <div class="bottom-nav-icon"><i class="fas fa-home"></i></div>
                </div>
                <span class="bottom-nav-label">Beranda</span>
            </a>

            <!-- Retribusi -->
            <?php if (in_array($role, ['admin','kepala-dinas','super-admin','bendahara'])): ?>
            <a href="<?= $base_url ?>retribusi-parkir.php" class="bottom-nav-item <?= $current_page === 'retribusi-parkir.php' ? 'active' : '' ?>">
                <div class="bottom-nav-icon-wrapper">
                    <div class="bottom-nav-icon"><i class="fas fa-wallet"></i></div>
                </div>
                <span class="bottom-nav-label">Retribusi</span>
            </a>
            <?php endif; ?>

            <!-- Jukir -->
            <?php if (in_array($role, ['admin','kepala-dinas','super-admin'])): ?>
            <a href="<?= $base_url ?>tukang-parkir.php" class="bottom-nav-item <?= $current_page === 'tukang-parkir.php' ? 'active' : '' ?>">
                <div class="bottom-nav-icon-wrapper">
                    <div class="bottom-nav-icon"><i class="fas fa-users"></i></div>
                </div>
                <span class="bottom-nav-label">Petugas Parkir</span>
            </a>
            <?php endif; ?>

            <!-- Peta -->
            <?php if (in_array($role, ['admin','kepala-dinas','super-admin','bendahara'])): ?>
            <a href="<?= $base_url ?>peta.php" class="bottom-nav-item <?= $current_page === 'peta.php' ? 'active' : '' ?>">
                <div class="bottom-nav-icon-wrapper">
                    <div class="bottom-nav-icon"><i class="fas fa-map-marker-alt"></i></div>
                </div>
                <span class="bottom-nav-label">Peta</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Ensure GSAP Flip is available
    if (typeof gsap === 'undefined' || typeof Flip === 'undefined') {
        console.warn('GSAP or Flip plugin is missing. Navbar morphing will fall back to static CSS.');
        return;
    }
    
    gsap.registerPlugin(Flip);

    const navWrapper = document.getElementById('navbar-wrapper');
    const morphContainer = document.getElementById('nav-morph-container');
    const glassTargets = Array.from(document.querySelectorAll('.glass-target'));
    const flipElements = Array.from(document.querySelectorAll('[data-flip-id]'));

    let isScrolled = false;
    let ticking = false;

    // The core Tailwind classes that give the "glass pill" look
    const glassClasses = ["bg-white/70", "backdrop-blur-xl", "border", "border-white/60", "shadow-soft"];

    function applyStateClasses(scrolled) {
        if (scrolled) {
            // Scrolled State: A single floating pill holding everything
            navWrapper.dataset.navState = "scrolled";
            navWrapper.classList.replace("py-5", "py-3");
            
            // Container morphs into a padded, background-filled pill
            morphContainer.classList.remove("w-full", "px-8", "md:px-[60px]", "justify-between");
            morphContainer.classList.add(...glassClasses, "rounded-full", "px-3", "py-2", "w-auto", "gap-8");

            // Remove backgrounds from internal elements (since the parent now acts as the pill)
            glassTargets.forEach(el => el.classList.remove(...glassClasses));
        } else {
            // Hero State: Wide, separate pills
            navWrapper.dataset.navState = "hero";
            navWrapper.classList.replace("py-3", "py-5");

            // Container morphs back to invisible wide wrapper
            morphContainer.classList.add("w-full", "px-8", "md:px-[60px]", "justify-between");
            morphContainer.classList.remove(...glassClasses, "rounded-full", "px-3", "py-2", "w-auto", "gap-8");

            // Restore individual pill backgrounds
            glassTargets.forEach(el => el.classList.add(...glassClasses));
        }
    }

    function toggleNavState(shouldBeScrolled) {
        if (shouldBeScrolled === isScrolled) return;

        // Kill any in-flight Flip animation first to prevent visual glitch
        Flip.killFlipsOf(flipElements);
        isScrolled = shouldBeScrolled;

        // 1. Capture state of all moving elements (FLIP)
        const state = Flip.getState(flipElements, { 
            props: "borderRadius,backgroundColor,boxShadow,padding" 
        });

        // 2. Change DOM Layout
        applyStateClasses(isScrolled);

        // 3. Invert and Play
        Flip.from(state, {
            duration: 0.5,
            ease: "power3.inOut",
            absolute: false,
            scale: false, // Force Flip to animate actual width/height instead of using CSS transforms, preventing layout flash
            nested: true,
            onComplete: () => {
                Flip.killFlipsOf(flipElements);
            }
        });
    }

    // Scroll listener with requestAnimationFrame for performance
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                const scrollPos = window.scrollY || document.documentElement.scrollTop;
                toggleNavState(scrollPos > 30);
                ticking = false;
            });
            ticking = true;
        }
    });

    // Check initial scroll position on load
    const initialScroll = window.scrollY || document.documentElement.scrollTop;
    if (initialScroll > 30) {
        // Apply instantly without animation
        isScrolled = true;
        applyStateClasses(true);
    }
});

// ── Sidebar Logic ────────────────────────────────────────
function openSidebar() {
    const sidebar = document.getElementById('mobile-sidebar');
    const overlay = document.getElementById('mobile-sidebar-overlay');
    if (!sidebar) return;
    overlay.classList.remove('opacity-0', 'pointer-events-none');
    overlay.classList.add('opacity-100');
    sidebar.classList.remove('translate-x-full');
    sidebar.classList.add('translate-x-0');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    const sidebar = document.getElementById('mobile-sidebar');
    const overlay = document.getElementById('mobile-sidebar-overlay');
    if (!sidebar) return;
    sidebar.classList.remove('translate-x-0');
    sidebar.classList.add('translate-x-full');
    overlay.classList.remove('opacity-100');
    overlay.classList.add('opacity-0', 'pointer-events-none');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', () => {
    // Close sidebar on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSidebar();
    });
});
</script>