<?php
include '../config/db.php';
include '../config/auth.php';

checkLogin();
$user = current_user();
allowRole(['kepala-dinas']);
include '../api/fetch_profile.php';
?>

<!DOCTYPE html>
<html lang="id">
<?php include '../components/header.php'; ?>

<body class="font-sans text-slate-800 antialiased min-h-screen pt-24"
    style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">

    <?php include '../components/navbar.php'; ?>

    <main class="container mx-auto" style="max-width: 900px;">
        <?php include '../components/breadcrumb.php'; ?>

        <div class="page-header">
            <div>
                <h1 class="page-title">Pengaturan Profil</h1>
                <p class="page-subtitle">Kelola informasi pribadi dan keamanan akun Anda.</p>
            </div>
        </div>

        <div class="card" style="overflow: hidden;">
            <div style="height: 120px; background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%);"></div>
            <form action="../store/proses_profile.php" method="POST" enctype="multipart/form-data"
                style="padding: 0 30px 30px 30px; margin-top: -50px;">
                <input type="hidden" name="id" value="<?= $user['id']; ?>">

                <div style="display: flex; align-items: flex-end; gap: 20px; margin-bottom: 30px;">
                    <div style="position: relative;">
                        <img id="avatar-preview"
                            src="<?= $user['foto'] ? '../assets/img/users/' . $user['foto'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']) . '&background=1e1b4b&color=fff'; ?>"
                            style="width: 110px; height: 110px; border-radius: 20px; border: 5px solid white; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <label for="upload-foto"
                            style="position: absolute; bottom: 5px; right: -5px; background: #4f46e5; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                            📸
                        </label>
                        <input type="file" id="upload-foto" name="foto" style="display: none;"
                            accept=".jpg, .jpeg, .png, .img">
                    </div>
                    <div style="padding-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 1.2rem; color: #1e1b4b; font-weight: 700;">
                            <?= $user['nama']; ?>
                        </h3>
                        <p style="margin: 0; font-size: 0.85rem; color: #64748b;">
                            <?= $user['role'] ?? 'Administrator'; ?>
                        </p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= $user['nama']; ?>" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= $user['username']; ?>" required
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Password Baru <small style="color:#94a3b8; font-weight:400;">(Kosongkan jika tidak
                                ganti)</small></label>
                        <input type="password" name="password" placeholder="••••••••" class="form-control">
                    </div>
                </div>

                <div
                    style="margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 1.5rem; display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" onclick="history.back()" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </main>

    </div>
    <script>
        document.getElementById('upload-foto').addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (file) {
                const allowedExtensions = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedExtensions.includes(file.type)) {
                    alert('Format file tidak didukung! Pilih gambar dengan format JPG, JPEG, atau PNG.');
                    this.value = '';
                    return;
                }

                // Validasi ukuran file secara lokal (Maksimal 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar! Maksimal adalah 2MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // TOAST NOTIFIKASI
        (function () {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');

            if (status === 'success') {
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });

                toast.fire({ icon: 'success', title: 'Profil berhasil diperbarui!' });

                const url = new URL(window.location);
                url.searchParams.delete('status');
                window.history.replaceState({}, '', url);
            }
        })();
    </script>

</body>

</html>