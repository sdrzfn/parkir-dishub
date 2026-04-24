<?php
include '../config/db.php';
include '../config/auth.php';
checkLogin();
$user = current_user();
allowRole(['kepala-dinas']);

$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jukir_utama");
$total_row = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_row / $limit);

// Query Jukir Utama
$sql = "SELECT 
            jukir_utama.*, 
            lokasi.nama_lokasi, 
            lokasi.kode_qris 
        FROM jukir_utama
        INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id
        ORDER BY jukir_utama.id DESC
        LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);

$list_lokasi = mysqli_query($conn, "SELECT id, nama_lokasi, kode_qris FROM lokasi ORDER BY nama_lokasi ASC");
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div>
                        <h1 style="font-size: 1.8rem; color: var(--sidebar-bg); margin: 0;">Daftar Juru Parkir</h1>
                        <p style="color: var(--text-muted); margin-top: 5px;">Manajemen data jukir utama dan pembantu
                        </p>
                    </div>
                    <button class="btn-primary" onclick="openAddModal()">+ Tambah Jukir</button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Jukir Utama (Klik untuk Pembantu)</th>
                                <th>NIK</th>
                                <th>No. Telp</th>
                                <th>Lokasi Parkir</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                $id_utama = $row['id'];

                                // Ambil Jukir Pembantu untuk jukir utama ini
                                $query_pembantu = mysqli_query($conn, "SELECT * FROM jukir_pembantu WHERE id_utama = '$id_utama'");
                                $jumlah_pembantu = mysqli_num_rows($query_pembantu);
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td>
                                        <details>
                                            <summary>
                                                <?= $row['nama_lengkap']; ?>
                                                <?php if ($jumlah_pembantu > 0): ?>
                                                    <span class="badge-pembantu"><?= $jumlah_pembantu; ?> Pembantu</span>
                                                <?php endif; ?>
                                            </summary>
                                            <ul class="pembantu-list">
                                                <?php if ($jumlah_pembantu > 0): ?>
                                                    <?php while ($p = mysqli_fetch_assoc($query_pembantu)): ?>
                                                        <li class="pembantu-item">
                                                            <strong><?= $p['nama_pembantu']; ?></strong> (NIK: <?= $p['nik']; ?>)
                                                        </li>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <li class="pembantu-item" style="font-style: italic;">Tidak ada jukir
                                                        pembantu
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </details>
                                    </td>
                                    <td><?= $row['nik']; ?></td>
                                    <td><?= $row['no_telp']; ?></td>
                                    <td>
                                        <div style="font-weight: 500;"><?= $row['nama_lokasi']; ?></div>
                                        <small style="color: var(--text-muted);"><?= $row['kode_qris']; ?></small>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <button class="btn-action btn-edit"
                                                onclick='openEditModal(<?= json_encode($row); ?>)'>Edit</button>
                                            <button class="btn-action btn-delete"
                                                onclick="confirmDelete(<?= $row['id']; ?>)">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination"></div>
            </div>
        </main>
    </div>

    <div id="modalJukir" class="modal">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 id="modalTitle">Tambah Juru Parkir</h3>
                <span style="cursor:pointer; font-size: 24px;" onclick="closeModal()">&times;</span>
            </div>

            <form action="store/proses_jukir.php?action=add" method="POST">
                <input type="hidden" name="id" id="form_id">

                <div class="form-group">
                    <label>NIK</label>
                    <input type="text" name="nik" id="form_nik" class="form-control" required maxlength="16">
                </div>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="form_nama" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Tempat, Tanggal Lahir</label>
                    <input type="text" name="ttl" id="form_ttl" class="form-control"
                        placeholder="Contoh: Sidoarjo, 12-05-1985">
                </div>

                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" id="form_alamat" class="form-control" rows="2"></textarea>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>No. Telp</label>
                        <input type="text" name="no_telp" id="form_telp" class="form-control">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Lokasi Penugasan</label>
                        <select name="id_lokasi" id="form_lokasi" class="form-control" required>
                            <option value="">-- Pilih Lokasi --</option>
                            <?php
                            mysqli_data_seek($list_lokasi, 0);
                            while ($lok = mysqli_fetch_assoc($list_lokasi)):
                                ?>
                                <option value="<?= $lok['id'] ?>"><?= $lok['kode_qris'] ?> - <?= $lok['nama_lokasi'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-primary" id="btnSubmit">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalJukir');
        const form = modal.querySelector('form');

        function openAddModal() {
            document.getElementById('modalTitle').innerText = "Tambah Juru Parkir Baru";
            form.action = "store/proses_jukir.php?action=add";
            form.reset();
            document.getElementById('form_id').value = "";

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = "Edit Data Juru Parkir";
            form.action = "store/proses_jukir.php?action=edit";

            document.getElementById('form_id').value = data.id;
            document.getElementById('form_nik').value = data.nik;
            document.getElementById('form_nama').value = data.nama_lengkap;
            document.getElementById('form_ttl').value = data.ttl;
            document.getElementById('form_alamat').value = data.alamat;
            document.getElementById('form_telp').value = data.no_telp;
            document.getElementById('form_lokasi').value = data.id_lokasi;

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>