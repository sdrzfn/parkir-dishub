<?php
include 'config/db.php';
// include 'config/auth.php'; // Aktifkan jika sudah ada file auth

// --- LOGIKA PAGINATION ---
$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data Jukir
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jukir_utama");
$total_row = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_row / $limit);

// Ambil data Jukir bergabung dengan tabel Lokasi
$sql = "SELECT 
            jukir_utama.*, 
            lokasi.nama_lokasi, 
            lokasi.kode_qris 
        FROM jukir_utama
        INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id
        ORDER BY jukir_utama.id DESC
        LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);

// Ambil semua daftar lokasi untuk dropdown di Modal
$list_lokasi = mysqli_query($conn, "SELECT id, nama_lokasi, kode_qris FROM lokasi ORDER BY nama_lokasi ASC");
?>

<!DOCTYPE html>
<html lang="id">

<?php include 'components/header.php'; ?>

<body style="display: flex; margin: 0; padding: 0;">

    <?php include 'components/sidebar.php'; ?>

    <div class="main-content" style="flex: 1;">
        <?php include 'components/navbar.php'; ?>

        <div class="container" style="padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 style="font-size: 1.8rem; margin: 0;">Data Juru Parkir</h1>
                    <p style="color: #666;">Menampilkan <?php echo mysqli_num_rows($result); ?> dari
                        <?php echo $total_row; ?> personil.
                    </p>
                </div>
                <button class="btn-primary" onclick="openAddModal()">+ Jukir Baru</button>
            </div>

            <div class="table-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                            <th>Kode & Nama</th>
                            <th>NIK</th>
                            <th>TTL</th>
                            <th>Alamat</th>
                            <th>Lokasi Tugas</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td>
                                        <div style="font-weight: bold; color: #2563eb;"><?= $row['kode_qris'] ?></div>
                                        <div style="font-weight: 600;"><?= $row['nama_lengkap'] ?></div>
                                        <small style="color: #666;"><?= $row['no_telp'] ?></small>
                                    </td>
                                    <td><?= $row['nik'] ?></td>
                                    <td><?= $row['ttl'] ?></td>
                                    <td style="font-size: 0.85rem; max-width: 200px;"><?= $row['alamat'] ?></td>
                                    <td><span class="badge badge-blue"
                                            style="background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;"><?= $row['nama_lokasi'] ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            <button class="btn-action btn-edit"
                                                onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                                            <a href="store/hapus_jukir.php?id=<?= $row['id'] ?>" class="btn-action btn-delete"
                                                onclick="return confirm('Hapus data petugas ini?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 20px;">Data jukir tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= ($page == $i) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
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