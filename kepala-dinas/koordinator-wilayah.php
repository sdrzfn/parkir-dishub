<?php
include '../config/db.php';
include '../config/auth.php';
checkLogin();
$user = current_user();
allowRole(['kepala-dinas']);

$sql = "SELECT * FROM koordinator_wilayah ORDER BY wilayah ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<?php include '../components/header.php'; ?>

<body style="display: flex; margin: 0; padding: 0;">

    <?php include '../components/navbar.php'; ?>

    <div class="app-body" style="flex: 1;">
        <?php include '../components/sidebar.php'; ?>

        <main class="main-content">
            <div class="container">
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.8rem; color: var(--sidebar-bg); margin: 0;">Manajemen Koordinator Wilayah
                    </h1>
                    <p style="color: var(--text-muted); margin-top: 5px;">Daftar penanggung jawab wilayah Sidoarjo</p>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Wilayah</th>
                                <th>Nama Koordinator</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= $row['wilayah']; ?></strong></td>
                                    <td><?= $row['nama_korwil']; ?></td>
                                    <td style="text-align: center;">
                                        <a href="detail-korwil.php?id=<?= $row['id']; ?>" class="btn-primary"
                                            style="text-decoration: none; padding: 5px 15px; font-size: 0.8rem;">
                                            Lihat Detail & Jukir
                                        </a>
                                    </td>
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