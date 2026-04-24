<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/retribusi.php';

checkLogin();
$user = current_user();
allowRole(['super-admin']);

$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM jukir_utama");
$total_row = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_row / $limit);

$bulan_ini = date('m');
$tahun_ini = date('Y');

$sql = "SELECT 
            ju.id,
            ju.nama_lengkap AS nama_utama,
            (SELECT GROUP_CONCAT(nama_pembantu SEPARATOR ', ') 
            FROM jukir_pembantu 
            WHERE id_utama = ju.id) AS nama_pembantu,
            l.nama_lokasi AS lokasi,
            ju.target_bulanan AS target,
            IFNULL(SUM(tr.jumlah_setoran), 0) AS realisasi
        FROM jukir_utama ju
        LEFT JOIN lokasi l ON ju.id_lokasi = l.id
        LEFT JOIN transaksi_retribusi tr ON ju.id = tr.id_jukir 
            AND MONTH(tr.tanggal_setoran) = MONTH(CURRENT_DATE())
            AND YEAR(tr.tanggal_setoran) = YEAR(CURRENT_DATE())
        GROUP BY ju.id, l.nama_lokasi
        LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);
$list_lokasi = mysqli_query($conn, "SELECT id, nama_lokasi, kode_qris FROM lokasi ORDER BY nama_lokasi ASC");

$q_percentage = "";

function hitungPersentase($realisasi, $target)
{
    if ($target <= 0) {
        return 0;
    }
    $persen = ($realisasi / $target) * 100;
    return round($persen, 2);
}
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
                        <h1 style="font-size: 1.8rem; color: var(--sidebar-bg); margin: 0;">Daftar Retribusi Juru Parkir
                        </h1>
                        <p style="color: var(--text-muted); margin-top: 5px;">Manajemen Retribusi Juru Parkir
                        </p>
                    </div>
                    <!-- <button class="btn-primary" onclick="openAddModal()">+ Tambah Jukir</button> -->
                </div>

                <div class="table-container">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 uppercase text-sm leading-normal">
                                <th class="py-3 px-6">No</th>
                                <th class="py-3 px-6">Nama Jukir (Utama/Pembantu)</th>
                                <th class="py-3 px-6">Lokasi</th>
                                <th class="py-3 px-6">Realisasi</th>
                                <th class="py-3 px-6">Target</th>
                                <th class="py-3 px-6">Persentase</th>
                                <th class="py-3 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                $id_utama = $row['id'];
                                $query_pembantu = mysqli_query($conn, "SELECT * FROM jukir_pembantu WHERE id_utama = '$id_utama'");
                                $pembantu_list = [];
                                while ($p = mysqli_fetch_assoc($query_pembantu)) {
                                    $pembantu_list[] = $p;
                                }
                                $jumlah_pembantu = count($pembantu_list);

                                $persen = hitungPersentase($row['realisasi'], $row['target']);
                                ?>
                                <tr class="row-utama" onclick="togglePembantu(<?= $id_utama; ?>)">
                                    <td><?= $no++; ?></td>
                                    <td class="col-nama">
                                        <div class="flex-nama">
                                            <i class="fa fa-chevron-right icon-toggle" id="icon-<?= $id_utama; ?>"></i>
                                            <strong><?= $row['nama_utama']; ?></strong>
                                            <?php if ($jumlah_pembantu > 0): ?>
                                                <span class="badge-count"><?= $jumlah_pembantu; ?> Pembantu</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= $row['lokasi']; ?></td>
                                    <td class="text-success">Rp <?= number_format($row['realisasi'], 0, ',', '.'); ?></td>
                                    <td class="text-primary">Rp <?= number_format($row['target'], 0, ',', '.'); ?></td>

                                    <?php $persen = hitungPersentase($row['realisasi'], $row['target']); ?>
                                    <td
                                        class="<?= ($persen >= 100) ? 'text-success' : (($persen >= 65) ? 'text-warning' : 'text-danger'); ?> font-bold">
                                        <?= $persen; ?>%
                                    </td>

                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <button class="btn-action"
                                                onclick="window.location.href='../super-admin/retribusi-detail.php?id=<?= $row['id']; ?>'"
                                                style="background-color: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                                Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr id="pembantu-<?= $id_utama; ?>" class="row-pembantu" style="display: none;">
                                    <td></td>
                                    <td colspan="5">
                                        <div class="pembantu-container">
                                            <?php if ($jumlah_pembantu > 0): ?>
                                                <table class="table-inner">
                                                    <thead>
                                                        <tr>
                                                            <th>Nama Jukir Pembantu</th>
                                                            <th>NIK</th>
                                                            <th>Alamat</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($pembantu_list as $p): ?>
                                                            <tr>
                                                                <td><?= $p['nama_pembantu']; ?></td>
                                                                <td><?= $p['nik']; ?></td>
                                                                <td><?= $p['alamat'] ?? '-'; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <p class="no-data">Tidak ada jukir pembantu terdaftar.</p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="<?= ($page == $i) ? 'active' : '' ?>">
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

            <form action="../store/proses_jukir.php?action=add" method="POST">
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
                    <button type="button" class="btn-secondary" onclick="btnBatal()">Batal</button>
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
            form.action = "../store/proses_jukir.php?action=add";
            form.reset();
            document.getElementById('form_id').value = "";

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = "Edit Data Juru Parkir";
            form.action = "../store/proses_jukir.php?action=edit";

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

        function btnBatal() {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                btnBatal();
            }
        }

        function togglePembantu(id) {
            const row = document.getElementById('pembantu-' + id);
            const icon = document.getElementById('icon-' + id);

            if (row.style.display === 'none') {
                row.style.display = 'table-row';
                icon.classList.add('icon-active');
            } else {
                row.style.display = 'none';
                icon.classList.remove('icon-active');
            }
        }
    </script>
</body>

</html>