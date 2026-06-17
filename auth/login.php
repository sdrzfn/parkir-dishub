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

            echo json_encode([
                'status' => 'success',
                'redirect' => $redirect_url
            ]);
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
    <title>Login | SI-PARKIR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-kab-sidoarjo.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .animate-bounce-in {
            animation: bounceIn 0.3s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }

            70% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body
    class="bg-slate-100 flex items-center justify-center min-h-screen p-4 sm:p-6 selection:bg-blue-500 selection:text-white">

    <div id="login-container"
        class="bg-white p-6 sm:p-8 rounded-2xl sm:rounded-3xl shadow-xl w-full max-w-md border border-gray-100 transition-all duration-300">

        <div class="text-center mb-6 sm:mb-8">
            <div class="flex justify-center gap-3 mb-4">
                <img src="../assets/img/logo-kab-sidoarjo.png" class="h-10 sm:h-12 object-contain" alt="Logo Sidoarjo">
                <img src="../assets/img/logo-dishub.png" class="h-10 sm:h-12 object-contain" alt="Logo Dishub">
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-blue-900 tracking-wide">SI-PARKIR</h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Dishub Kabupaten Sidoarjo</p>
        </div>

        <form action="" method="POST" class="space-y-4 sm:space-y-5" id="loginForm">
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">Username</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-2.5 sm:py-3 text-sm sm:text-base rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    placeholder="Masukkan username">
            </div>
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="passwordInput" required
                        class="w-full px-4 py-2.5 sm:py-3 text-sm sm:text-base rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        placeholder="********" style="padding-right: 44px;">
                    <button type="button" id="togglePassword" onclick="togglePasswordVisibility()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
               background: none; border: none; cursor: pointer; color: #94a3b8;
               padding: 4px; display: flex; align-items: center; justify-content: center;
               transition: color 0.2s ease;" onmouseenter="this.style.color='#475569'"
                        onmouseleave="this.style.color='#94a3b8'" aria-label="Tampilkan password">

                        <!-- Icon mata tertutup (default — password tersembunyi) -->
                        <svg id="iconHide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>

                        <!-- Icon mata terbuka (password terlihat) -->
                        <svg id="iconShow" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" style="display: none;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" id="btnSubmit"
                class="w-full bg-blue-900 hover:bg-blue-800 text-white py-2.5 sm:py-3 text-sm sm:text-base rounded-xl font-bold flex items-center justify-center gap-2 transition-all active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-900 mt-2">
                <span id="btnText">Masuk ke Sistem</span>
                <div id="mini-spinner"
                    class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></div>
            </button>
        </form>
    </div>

    <div id="big-spinner-container"
        class="hidden fixed inset-0 bg-slate-100 flex flex-col items-center justify-center z-50 p-4">
        <div
            class="animate-spin h-14 w-14 sm:h-20 sm:w-20 border-4 sm:border-8 border-blue-900 border-t-transparent rounded-full mb-4">
        </div>
        <p class="text-blue-900 font-bold text-base sm:text-xl text-center animate-pulse">Menyiapkan Dashboard...</p>
    </div>

    <div id="error-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl p-5 sm:p-6 max-w-sm w-full text-center shadow-2xl animate-bounce-in mx-auto">
            <div class="text-red-500 text-4xl sm:text-5xl mb-3 sm:mb-4">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2">Login Gagal</h3>
            <p id="error-message" class="text-xs sm:text-sm text-gray-600 mb-5 sm:mb-6 leading-relaxed"></p>
            <button onclick="closeError()"
                class="w-full bg-gray-800 hover:bg-black text-white py-2.5 rounded-xl font-semibold transition text-sm sm:text-base focus:outline-none">Tutup</button>
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

        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            btnSubmit.disabled = true;
            btnText.innerText = "Memproses...";
            miniSpinner.classList.remove('hidden');

            const formData = new FormData(this);
            formData.append('action', 'login');

            fetch('login.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Sembunyikan form dan tampilkan loading besar
                        loginContainer.classList.add('hidden');
                        bigSpinner.classList.remove('hidden');
                        bigSpinner.classList.add('flex');

                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Terjadi kesalahan pada server. Silakan coba lagi.');
                });
        });

        function showError(msg) {
            errorMessage.innerText = msg;
            errorModal.classList.remove('hidden');
            errorModal.classList.add('flex');

            btnSubmit.disabled = false;
            btnText.innerText = "Masuk ke Sistem";
            miniSpinner.classList.add('hidden');
        }

        function closeError() {
            errorModal.classList.add('hidden');
            errorModal.classList.remove('flex');
        }

        function togglePasswordVisibility() {
            const input = document.getElementById('passwordInput');
            const iconHide = document.getElementById('iconHide');
            const iconShow = document.getElementById('iconShow');
            const btn = document.getElementById('togglePassword');

            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            iconHide.style.display = isHidden ? 'none' : 'block';
            iconShow.style.display = isHidden ? 'block' : 'none';
            btn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
        }
    </script>
</body>

</html>