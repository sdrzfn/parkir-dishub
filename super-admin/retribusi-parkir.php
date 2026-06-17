<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/retribusi.php';
include '../api/fetch_retribusi.php';

checkLogin();
$user = current_user();
allowRole(['super-admin']);

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
                        <h1 class="page-title">Daftar Retribusi Petugas Parkir</h1>
                        <p class="page-subtitle">Manajemen Retribusi Petugas Parkir</p>
                    </div>
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
                                <th class="py-3 px-6">Denda (2%)</th>
                                <th class="py-3 px-6">Imbal Jasa (40%)</th>
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
                                $target_dana = $row['target'];
                                $realisasi_dana = $row['realisasi'];

                                $tunggakan = ($target_dana > $realisasi_dana) ? ($target_dana - $realisasi_dana) : 0;
                                $denda = $tunggakan * 0.02;
                                $imbal_jasa = $realisasi_dana * 0.40;
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
                                    <td class="text-success">Rp <?= number_format($realisasi_dana, 0, ',', '.'); ?></td>
                                    <td class="text-primary">Rp <?= number_format($target_dana, 0, ',', '.'); ?></td>
                                    <td
                                        class="<?= ($persen >= 100) ? 'text-success' : (($persen >= 65) ? 'text-warning' : 'text-danger'); ?> font-bold">
                                        <?= $persen; ?>%
                                    </td>

                                    <td style="color: #b91c1c; font-weight: bold;">
                                        <?= $denda > 0 ? 'Rp ' . number_format($denda, 0, ',', '.') : '<span style="color: #94a3b8; font-weight: normal;">-</span>'; ?>
                                    </td>

                                    <td style="color: #16a34a; font-weight: bold;">
                                        Rp <?= number_format($imbal_jasa, 0, ',', '.'); ?>
                                    </td>

                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <button class="btn-action"
                                                onclick="window.location.href='retribusi-detail.php?id=<?= $row['id']; ?>'"
                                                style="background-color: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                                Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr id="pembantu-<?= $id_utama; ?>" class="row-pembantu" style="display: none;">
                                    <td></td>
                                    <td colspan="7">
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
                <h3 id="modalTitle">Tambah Petugas Parkir</h3>
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
            document.getElementById('modalTitle').innerText = "Tambah Petugas Parkir Baru";
            form.action = "../store/proses_jukir.php?action=add";
            form.reset();
            document.getElementById('form_id').value = "";

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = "Edit Data Petugas Parkir";
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