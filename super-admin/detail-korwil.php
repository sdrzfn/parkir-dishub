<?php
include '../config/db.php';
include '../config/auth.php';
checkLogin();
$user = current_user();
allowRole(['super-admin']);

include '../api/fetch_korwil.php';
?>

<!DOCTYPE html>
<html lang="id">
<?php include '../components/header.php'; ?>

<body style="display: flex; margin: 0;">
    <?php include '../components/navbar.php'; ?>

    <div class="app-body" style="flex: 1;">
        <?php include '../components/sidebar.php'; ?>

        <main class="main-content">
            <div class="container">
                <?php include '../components/breadcrumb.php'; ?>
                <a href="koordinator-wilayah.php"
                    style="text-decoration: none; color: var(--primary); font-size: 0.9rem;">← Kembali ke Daftar</a>

                <div
                    style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid var(--primary);">
                    <h2 style="margin: 0;"><?= $korwil['wilayah']; ?></h2>
                    <p style="margin: 5px 0 0; color: #666;">Koordinator:
                        <strong><?= $korwil['nama_korwil']; ?></strong>
                    </p>
                </div>

                <h3 style="color: var(--sidebar-bg);">Daftar Jukir Utama & Lokasi</h3>
                <div class="table-container" style="margin-bottom: 30px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Jukir Utama</th>
                                <th>NIK</th>
                                <th>Lokasi Penugasan</th>
                                <th>No. Telp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = mysqli_fetch_assoc($res_utama)): ?>
                                <tr>
                                    <td><strong><?= $u['nama_lengkap']; ?></strong></td>
                                    <td><?= $u['nik']; ?></td>
                                    <td><span
                                            style="color: var(--primary); font-weight: 500;"><?= $u['nama_lokasi']; ?></span>
                                    </td>
                                    <td><?= $u['no_telp']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <h3 style="color: var(--sidebar-bg);">Daftar Jukir Pembantu</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Jukir Pembantu</th>
                                <th>Membantu Jukir</th>
                                <th>Alamat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = mysqli_fetch_assoc($res_pembantu)): ?>
                                <tr>
                                    <td><?= $p['nama_pembantu']; ?></td>
                                    <td><small>Utama:</small> <?= $p['nama_induk']; ?></td>
                                    <td><?= $p['alamat_pembantu']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>