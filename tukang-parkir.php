<?php
include 'config/db.php';
include 'config/auth.php';
checkLogin();
$user = current_user();
allowRole(['admin']);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kecamatan = isset($_GET['kecamatan']) ? trim($_GET['kecamatan']) : '';
$titik_parkir = isset($_GET['titik_parkir']) ? trim($_GET['titik_parkir']) : '';
$status_aktif = isset($_GET['status_aktif']);
$status_nonaktif = isset($_GET['status_nonaktif']);
$where = "WHERE 1=1";

if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (jukir_utama.nama_lengkap LIKE '%$s%' 
                  OR jukir_utama.nik LIKE '%$s%'
                  OR lokasi.nama_lokasi LIKE '%$s%'
                  OR lokasi.kode_qris LIKE '%$s%')";
}

if ($kecamatan !== '') {
    $k = mysqli_real_escape_string($conn, $kecamatan);
    $where .= " AND koordinator_wilayah.wilayah = '$k'";
}

if ($titik_parkir !== '') {
    $tp = mysqli_real_escape_string($conn, $titik_parkir);
    $where .= " AND lokasi.titik_parkir = '$tp'";
}
if ($status_aktif && !$status_nonaktif) {
    $where .= " AND jukir_utama.status_peringatan = 'aktif'";
} elseif (!$status_aktif && $status_nonaktif) {
    $where .= " AND jukir_utama.status_peringatan != 'aktif'";
}

$count_sql = "SELECT COUNT(*) AS total 
              FROM jukir_utama
              INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id
              LEFT JOIN koordinator_wilayah ON jukir_utama.id_korwil = koordinator_wilayah.id
              $where";
$total_result = mysqli_query($conn, $count_sql);
$total_row = mysqli_fetch_assoc($total_result)['total'];
$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_row / $limit);

$sql = "SELECT 
            jukir_utama.*, 
            lokasi.nama_lokasi, 
            lokasi.kode_qris,
            lokasi.titik_parkir,
            koordinator_wilayah.wilayah AS kecamatan
        FROM jukir_utama
        INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id
        LEFT JOIN koordinator_wilayah ON jukir_utama.id_korwil = koordinator_wilayah.id
        $where
        ORDER BY jukir_utama.id DESC
        LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);
$list_lokasi = mysqli_query($conn, "SELECT id, nama_lokasi, kode_qris FROM lokasi ORDER BY nama_lokasi ASC");
$list_wilayah = mysqli_query($conn, "SELECT DISTINCT wilayah FROM koordinator_wilayah ORDER BY wilayah ASC");
?>

<!DOCTYPE html>
<html lang="id">

<?php include 'components/header.php'; ?>

<body style="display: flex; margin: 0; padding: 0;">

    <?php include 'components/navbar.php'; ?>

    <div class="app-body" style="flex: 1;">

        <?php include 'components/sidebar.php'; ?>

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

                <form method="GET" action="">
                    <div class="filter-container">
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <div style="flex: 1; position: relative;">
                                <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Cari Kode Lokasi atau Nama Jalan..."
                                    style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid #cbd5e1; ...">
                            </div>
                            <button type="submit"
                                style="padding: 0 25px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                Cari Data
                            </button>
                        </div>
                        <div
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <label style="...">Kecamatan</label>
                                <select name="kecamatan" style="...">
                                    <option value="">Semua Kecamatan</option>
                                    <option value="sidoarjo" <?= $kecamatan == 'sidoarjo' ? 'selected' : '' ?>>Sidoarjo
                                        Kota</option>
                                    <option value="waru" <?= $kecamatan == 'waru' ? 'selected' : '' ?>>Waru</option>
                                    <option value="taman" <?= $kecamatan == 'taman' ? 'selected' : '' ?>>Taman</option>
                                    <option value="krian" <?= $kecamatan == 'krian' ? 'selected' : '' ?>>Krian</option>
                                    <option value="gedangan" <?= $kecamatan == 'gedangan' ? 'selected' : '' ?>>Gedangan
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label for="filter-titik">Titik Parkir</label>
                                <select name="titik_parkir" id="filter-titik" class="filter-select">
                                    <option value="">Semua Titik</option>
                                    <option value="TJU" <?= $titik_parkir == 'TJU' ? 'selected' : '' ?>>TJU</option>
                                    <option value="TKP" <?= $titik_parkir == 'TKP' ? 'selected' : '' ?>>TKP</option>
                                </select>
                            </div>
                            <div>
                                <label style="...">Status</label>
                                <div style="display: flex; gap: 10px; align-items: center; height: 40px;">
                                    <label style="...">
                                        <input type="checkbox" name="status_aktif" <?= $status_aktif ? 'checked' : '' ?>>
                                        Aktif
                                    </label>
                                    <label style="...">
                                        <input type="checkbox" name="status_nonaktif" <?= $status_nonaktif ? 'checked' : '' ?>> Non-Aktif
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div style="margin-top: 15px; text-align: right;">
                            <a href="tukang-parkir.php"
                                style="background: none; border: none; color: #ef4444; font-size: 0.85rem; font-weight: 500; cursor: pointer; text-decoration: underline;">
                                Reset Filter
                            </a>
                        </div>
                    </div>
                </form>

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
                <div class="pagination">
                    <?php
                    $query_params = http_build_query([
                        'search' => $search,
                        'kecamatan' => $kecamatan,
                        'titik_parkir' => $titik_parkir,
                        'status_aktif' => $status_aktif ? '1' : '',
                        'status_nonaktif' => $status_nonaktif ? '1' : '',
                    ]);
                    for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&<?= $query_params ?>" class="<?= ($page == $i) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
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