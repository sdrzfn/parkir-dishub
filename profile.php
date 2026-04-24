<?php
include 'config/db.php';
include 'config/auth.php';

checkLogin();
$user = current_user();
allowRole(['admin']);

$user_id = $_SESSION['user_id'] ?? 1; 

$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<?php include 'components/header.php'; ?>
<body class="bg-slate-50 flex">

    <?php include 'components/navbar.php'; ?>

    <div class="app-body">
        <?php include 'components/sidebar.php'; ?>

        <main class="main-content">
            <div class="container" style="max-width: 900px; margin: 0 auto;">
                
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: #1d3967;">Pengaturan Profil</h1>
                    <p style="color: #64748b; font-size: 0.9rem;">Kelola informasi pribadi dan keamanan akun Anda.</p>
                </div>

                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                    <div style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #bbf7d0;">
                        ✅ Profil berhasil diperbarui!
                    </div>
                <?php endif; ?>

                <div class="card" style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0; background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                    <div style="height: 120px; background: linear-gradient(to right, #003366, #005599);"></div>
                    
                    <form action="store/proses_profile.php" method="POST" enctype="multipart/form-data" style="padding: 0 30px 30px 30px; margin-top: -50px;">
                        <input type="hidden" name="id" value="<?= $user['id']; ?>">

                        <div style="display: flex; align-items: flex-end; gap: 20px; margin-bottom: 30px;">
                            <div style="position: relative;">
                                <img src="<?= $user['foto'] ? 'assets/img/users/'.$user['foto'] : 'https://ui-avatars.com/api/?name='.$user['nama'].'&background=003366&color=fff'; ?>" 
                                     style="width: 110px; height: 110px; border-radius: 20px; border: 5px solid white; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <label for="upload-foto" style="position: absolute; bottom: 5px; right: -5px; background: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                    📸
                                </label>
                                <input type="file" id="upload-foto" name="foto" style="display: none;">
                            </div>
                            <div style="padding-bottom: 10px;">
                                <h3 style="margin: 0; font-size: 1.2rem; color: #eaeaf2;"><?= $user['nama']; ?></h3>
                                <p style="margin: 0; font-size: 0.85rem; color: #64748b;"><?= $user['role'] ?? 'Administrator'; ?></p>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?= $user['nama']; ?>" required
                                    style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem;">
                            </div>
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Username</label>
                                <input type="text" name="username" value="<?= $user['username']; ?>" required
                                    style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem;">
                            </div>
                            <!-- <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Email</label>
                                <input type="email" name="email" value="<?= $user['email']; ?>"
                                    style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem;">
                            </div> -->
                            <div class="form-group">
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Password Baru (Kosongkan jika tidak ganti)</label>
                                <input type="password" name="password" placeholder="********"
                                    style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 0.9rem;">
                            </div>
                        </div>

                        <div style="margin-top: 40px; border-top: 1px solid #f1f5f9; padding-top: 25px; display: flex; justify-content: flex-end; gap: 15px;">
                            <button type="button" onclick="history.back()" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #64748b; cursor: pointer; font-weight: 600;">Batal</button>
                            <button type="submit" style="padding: 10px 25px; border-radius: 8px; border: none; background: #003366; color: white; cursor: pointer; font-weight: 600;">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

</body>
</html>