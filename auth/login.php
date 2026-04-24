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

<body class="bg-slate-100 flex items-center justify-center min-h-screen p-6">
    <div id="login-container" class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <div class="flex justify-center gap-2 mb-4">
                <img src="../assets/img/logo-kab-sidoarjo.png" class="h-10">
                <img src="../assets/img/logo-dishub.png" class="h-10">
            </div>
            <h2 class="text-2xl font-bold text-blue-900">SI-PARKIR</h2>
            <p class="text-gray-500 mt-1">Dishub Kabupaten Sidoarjo</p>
        </div>

        <form action="" method="POST" class="space-y-5" id="loginForm">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition"
                    placeholder="Masukkan username">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition"
                    placeholder="********">
            </div>
            <button type="submit" id="btnSubmit"
                class="w-full bg-blue-900 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all active:scale-95">
                <span id="btnText">Masuk ke Sistem</span>
                <div id="mini-spinner"
                    class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></div>
            </button>
        </form>
    </div>

    <div id="big-spinner-container" class="hidden fixed inset-0 bg-slate-100 flex-col items-center justify-center z-50">
        <div class="animate-spin h-20 w-20 border-8 border-blue-900 border-t-transparent rounded-full mb-4"></div>
        <p class="text-blue-900 font-bold text-xl animate-pulse">Menyiapkan Dashboard...</p>
    </div>

    <div id="error-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center shadow-2xl animate-bounce-in">
            <div class="text-red-500 text-5xl mb-4">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Login Gagal</h3>
            <p id="error-message" class="text-gray-600 mb-6"></p>
            <button onclick="closeError()"
                class="w-full bg-gray-800 text-white py-2 rounded-lg font-semibold hover:bg-black transition">Tutup</button>
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
    </script>
</body>

</html>