<?php
include '../config/auth.php';
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama'];

            $redirect_url = '../index.php';
            if ($user['role'] === 'super-admin') {
                $redirect_url = '../super-admin/index.php';
            } elseif ($user['role'] === 'kepala-dinas') {
                $redirect_url = '../kepala-dinas/index.php';
            } elseif ($user['role'] === 'bendahara') {
                $redirect_url = '../bendahara/index.php';
            }

            echo json_encode(['status' => 'success', 'redirect' => $redirect_url]);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Password salah!']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan!']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | SI-PARKIR Dishub Sidoarjo</title>
    <meta name="description" content="Sistem Informasi Parkir Dinas Perhubungan Kabupaten Sidoarjo">
    <link rel="icon" type="image/png" href="../assets/img/logo-kab-sidoarjo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe',
                            300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1',
                            600: '#4f46e5', 700: '#4338ca', 800: '#3730a3',
                            900: '#312e81', 950: '#1e1b4b',
                        }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }

        /* Left panel animated gradient */
        .hero-bg {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4f46e5 100%);
        }

        /* Floating orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.25;
            animation: float 8s ease-in-out infinite;
        }
        .orb-1 { width: 320px; height: 320px; background: #818cf8; top: -80px; left: -80px; animation-delay: 0s; }
        .orb-2 { width: 240px; height: 240px; background: #a78bfa; bottom: -60px; right: -60px; animation-delay: 3s; }
        .orb-3 { width: 180px; height: 180px; background: #60a5fa; top: 50%; left: 50%; transform: translate(-50%,-50%); animation-delay: 1.5s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        /* Card glass */
        .login-card {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.8);
        }

        /* Input focus ring */
        .field-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1px solid rgba(226,232,240,0.9);
            background: #f8fafc;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: #0f172a;
            transition: all 0.2s ease;
            outline: none;
        }
        .field-input:focus {
            background: #fff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
        }
        .field-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            letter-spacing: 0.02em;
        }

        /* Submit btn */
        .btn-login {
            width: 100%;
            background: #1e1b4b;
            color: white;
            border: none;
            border-radius: 14px;
            padding: 13px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 14px rgba(30,27,75,0.25);
        }
        .btn-login:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,0.3); }
        .btn-login:active { transform: scale(0.98); }
        .btn-login:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        /* Spinner */
        .spin { animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Error modal bounce */
        @keyframes bounceIn {
            0% { transform: scale(0.7); opacity: 0; }
            70% { transform: scale(1.03); }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-bounce-in { animation: bounceIn 0.3s ease-out; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.3); border-radius: 999px; }
    </style>
</head>
<body class="min-h-screen flex bg-slate-100 selection:bg-brand-200 selection:text-brand-900">

    <!-- ══ Left Panel — Hero ═══════════════════════════════════ -->
    <div class="hidden lg:flex lg:w-[52%] hero-bg relative overflow-hidden flex-col justify-between p-14">
        <!-- Orbs -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <!-- Logo + Brand -->
        <div class="relative z-10">
            <div class="flex items-center gap-4 mb-2">
                <img src="../assets/img/logo-kab-sidoarjo.png" class="h-12 object-contain drop-shadow-lg" alt="Logo Sidoarjo">
                <img src="../assets/img/logo-dishub.png" class="h-12 object-contain drop-shadow-lg" alt="Logo Dishub">
            </div>
        </div>

        <!-- Center headline -->
        <div class="relative z-10 flex-1 flex flex-col justify-center">
            <h1 class="text-5xl font-light text-white leading-tight tracking-tight mb-4">
                Sistem Informasi<br>
                <span class="font-semibold">Pengelolaan Area Retribusi dan Kinerja Juru Parkir</span>
            </h1>
            <p class="text-brand-200 text-lg font-light leading-relaxed max-w-lg">
                Platform manajemen retribusi dan pengawasan petugas parkir Kabupaten Sidoarjo secara terpadu.
            </p>

            <!-- Stats pills -->
            <div class="flex gap-4 mt-8">
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                    <div class="text-white text-2xl font-semibold">Logging</div>
                    <div class="text-brand-300 text-xs mt-1 font-medium uppercase tracking-wider">Sistem</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                    <div class="text-white text-2xl font-semibold">Real-Time</div>
                    <div class="text-brand-300 text-xs mt-1 font-medium uppercase tracking-wider">Monitoring</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-3 text-center">
                    <div class="text-white text-2xl font-semibold">RBAC</div>
                    <div class="text-brand-300 text-xs mt-1 font-medium uppercase tracking-wider">Multi-Role</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="relative z-10 text-brand-400 text-xs">
            © 2025 Dinas Perhubungan Kab. Sidoarjo — Versi 2.0
        </div>
    </div>

    <!-- ══ Right Panel — Login Form ════════════════════════════ -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-10 relative overflow-auto">

        <!-- Mobile logo -->
        <div class="absolute top-6 left-6 flex items-center gap-3 lg:hidden">
            <img src="../assets/img/logo-kab-sidoarjo.png" class="h-9 object-contain" alt="">
            <img src="../assets/img/logo-dishub.png" class="h-9 object-contain" alt="">
        </div>

        <!-- Card -->
        <div id="login-container" class="login-card rounded-3xl shadow-2xl w-full max-w-[420px] p-8 sm:p-10 transition-all duration-300">

            <!-- Heading -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-brand-950 tracking-tight mb-1">Selamat Datang 👋</h2>
                <p class="text-sm text-slate-500">Masuk ke SI-PARKIR Dishub Sidoarjo</p>
            </div>

            <!-- Form -->
            <form id="loginForm" action="" method="POST" class="space-y-5">
                <!-- Username -->
                <div>
                    <label for="username" class="field-label">Username</label>
                    <div style="position: relative;">
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; display:flex;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input type="text" id="username" name="username" required
                            class="field-input" style="padding-left: 40px;"
                            placeholder="Masukkan username" autocomplete="username">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="passwordInput" class="field-label">Password</label>
                    <div style="position: relative;">
                        <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94a3b8; display:flex;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="passwordInput" name="password" required
                            class="field-input" style="padding-left: 40px; padding-right: 44px;"
                            placeholder="••••••••" autocomplete="current-password">
                        <button type="button" id="togglePassword" onclick="togglePasswordVisibility()"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#94a3b8; padding:4px; display:flex; align-items:center; transition:color 0.2s;"
                            onmouseenter="this.style.color='#475569'" onmouseleave="this.style.color='#94a3b8'">
                            <svg id="iconHide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                            <svg id="iconShow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" id="btnSubmit" class="btn-login mt-2">
                    <span id="btnText">Masuk ke Sistem</span>
                    <svg id="mini-spinner" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none;" class="spin">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                </button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-8">
                © 2025 Dishub Kab. Sidoarjo — All rights reserved
            </p>
        </div>
    </div>

    <!-- ══ Loading Overlay ═════════════════════════════════════ -->
    <div id="big-spinner-container" class="hidden fixed inset-0 bg-brand-950 flex flex-col items-center justify-center z-50 gap-6">
        <div class="relative">
            <div class="w-20 h-20 border-4 border-brand-800 border-t-brand-300 rounded-full spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <img src="../assets/img/logo-dishub.png" class="h-8 object-contain opacity-80" alt="">
            </div>
        </div>
        <p class="text-brand-200 font-medium text-lg animate-pulse">Menyiapkan Dashboard...</p>
    </div>

    <!-- ══ Error Modal ═════════════════════════════════════════ -->
    <div id="error-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl p-7 max-w-sm w-full text-center shadow-2xl animate-bounce-in">
            <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="text-red-500" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Login Gagal</h3>
            <p id="error-message" class="text-sm text-slate-500 mb-6 leading-relaxed"></p>
            <button onclick="closeError()"
                class="w-full bg-brand-950 hover:bg-brand-700 text-white py-3 rounded-2xl font-semibold text-sm transition-all">
                Coba Lagi
            </button>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const miniSpinner = document.getElementById('mini-spinner');
        const btnSubmit = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnText');
        const errorModal = document.getElementById('error-modal');
        const errorMessage = document.getElementById('error-message');
        const loginContainer = document.getElementById('login-container');
        const bigSpinner = document.getElementById('big-spinner-container');

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            btnSubmit.disabled = true;
            btnText.innerText = 'Memproses...';
            miniSpinner.style.display = 'block';

            const formData = new FormData(this);
            formData.append('action', 'login');

            fetch('login.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        loginContainer.classList.add('hidden');
                        bigSpinner.classList.remove('hidden');
                        bigSpinner.classList.add('flex');
                        setTimeout(() => { window.location.href = data.redirect; }, 1200);
                    } else {
                        showError(data.message);
                    }
                })
                .catch(() => showError('Terjadi kesalahan pada server. Silakan coba lagi.'));
        });

        function showError(msg) {
            errorMessage.innerText = msg;
            errorModal.classList.remove('hidden');
            errorModal.classList.add('flex');
            btnSubmit.disabled = false;
            btnText.innerText = 'Masuk ke Sistem';
            miniSpinner.style.display = 'none';
        }

        function closeError() {
            errorModal.classList.add('hidden');
            errorModal.classList.remove('flex');
        }

        function togglePasswordVisibility() {
            const input = document.getElementById('passwordInput');
            const iconHide = document.getElementById('iconHide');
            const iconShow = document.getElementById('iconShow');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            iconHide.style.display = isHidden ? 'none' : 'block';
            iconShow.style.display = isHidden ? 'block' : 'none';
        }
    </script>
</body>
</html>