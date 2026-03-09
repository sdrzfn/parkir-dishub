<?php

include 'config/db.php';
include 'config/auth.php';
$bulan_ini = date('F Y');

$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM lokasi");
$total_row = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_row / $limit);

$sql = "SELECT 
            lokasi.*, 
            jukir_utama.nama_lengkap AS nama_jukir 
        FROM lokasi 
        LEFT JOIN jukir_utama ON lokasi.id = jukir_utama.id_lokasi
        ORDER BY lokasi.id DESC
        LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="id">

<?php include 'components/header.php'; ?>

<body>

    <?php include 'components/sidebar.php'; ?>

    <div class="main-content" style="flex: 1;">
        <?php include 'components/navbar.php'; ?>

        <div class="container" style="padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 style="font-size: 1.8rem; color: #2c3e50; margin: 0;">Data Lokasi Parkir</h1>
                    <p style="color: #7f8c8d; margin-top: 5px;">Manajemen titik parkir dan target retribusi</p>
                </div>
                <button class="btn-primary" onclick="openTambahModal()">+ Tambah Lokasi</button>
            </div>

            <div class="table-container"
                style="background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 15px; text-align: left;">Foto</th>
                            <th style="padding: 15px; text-align: left;">Kode QRIS</th>
                            <th style="padding: 15px; text-align: left;">Nama Lokasi</th>
                            <th style="padding: 15px; text-align: left;">Jukir Utama</th>
                            <th style="padding: 15px; text-align: left;">Target</th>
                            <th style="padding: 15px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr style="border-top: 1px solid #eee;">
                                    <td style="padding: 15px;">
                                        <?php
                                        $foto = !empty($row['foto']) ? 'assets/img/lokasi/' . $row['foto'] : 'assets/img/no-image.jpg';
                                        ?>
                                        <img src="<?= $foto ?>"
                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td style="padding: 15px; font-weight: bold;"><?= $row['kode_qris'] ?></td>
                                    <td style="padding: 15px;">
                                        <?= $row['nama_lokasi'] ?><br>
                                        <small style="color: #95a5a6;"><?= $row['titik_parkir'] ?></small>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?= $row['nama_jukir'] ?? '<span style="color:#bdc3c7">Belum diset</span>' ?>
                                    </td>
                                    <td style="padding: 15px; color: #27ae60; font-weight: bold;">
                                        Rp <?= number_format($row['target_bulanan'], 0, ',', '.') ?>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <button class="btn-action btn-edit"
                                            onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
                                        <a href="store/hapus_lokasi.php?id=<?= $row['id'] ?>" class="btn-action btn-delete"
                                            onclick="return confirm('Hapus lokasi ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="padding: 30px; text-align: center; color: #95a5a6;">Data tidak
                                    ditemukan.</td>
                            </tr>
                        <?php endif; ?>
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
    </div>

    <div id="modalLokasi" class="modal">
        <div class="modal-content">
            <form id="formLokasi" action="store/proses_lokasi.php?action=add" method="POST" enctype="multipart/form-data">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <h3 id="modalTitle" style="margin: 0;">Tambah Lokasi Parkir</h3>
                    <span onclick="closeModal()" style="cursor:pointer; font-size: 24px; color: #95a5a6;">&times;</span>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="id_field">
                    <input type="hidden" name="foto_lama" id="foto_lama_field">

                    <div class="form-group">
                        <label>Kode QRIS</label>
                        <input type="text" name="kode_qris" id="kode_qris" class="form-control" required
                            placeholder="Contoh: TJU-001">
                    </div>

                    <div class="form-group">
                        <label>Nama Lokasi</label>
                        <input type="text" name="nama_lokasi" id="nama_lokasi" class="form-control" required
                            placeholder="Nama Jalan atau Area">
                    </div>

                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Latitude</label>
                            <input type="text" name="latitude" id="form_lat" class="form-control" placeholder="-7.xxxx"
                                readonly>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Longitude</label>
                            <input type="text" name="longitude" id="form_lng" class="form-control"
                                placeholder="112.xxxx" readonly>
                        </div>
                    </div>

                    <div id="mapPicker"
                        style="height: 250px; width: 100%; margin-bottom: 15px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>

                    <div class="form-group">
                        <label>Titik Parkir (TJU / TKP)</label>
                        <input type="text" name="titik_parkir" id="titik_parkir" class="form-control" required
                            placeholder="Contoh: TJU">
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Nominal Retribusi (Rp)</label>
                            <input type="number" name="nominal_retribusi" id="nominal_retribusi" class="form-control"
                                required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Target Bulanan (Rp)</label>
                            <input type="number" name="target_bulanan" id="target_bulanan" class="form-control"
                                required>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Terbilang Target</label>
                        <input type="text" name="terbilang_target" id="terbilang_target" class="form-control"
                            placeholder="Contoh: Satu Juta Rupiah">
                    </div>

                    <div class="form-group">
                        <label>Foto Lokasi</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small id="info_foto" style="display: none; color: #3498db; margin-top: 5px;"></small>
                    </div>
                </div>

                <div
                    style="margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var pickerMap;
        var pickerMarker;

        function initMapPicker(lat, lng) {
            var center = [lat || -7.4478, lng || 112.7183]; // Default Sidoarjo

            if (!pickerMap) {
                pickerMap = L.map('mapPicker').setView(center, 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(pickerMap);
                pickerMarker = L.marker(center, { draggable: true }).addTo(pickerMap);

                pickerMarker.on('dragend', function (e) {
                    var pos = pickerMarker.getLatLng();
                    document.getElementById('form_lat').value = pos.lat.toFixed(8);
                    document.getElementById('form_lng').value = pos.lng.toFixed(8);
                });

                pickerMap.on('click', function (e) {
                    pickerMarker.setLatLng(e.latlng);
                    document.getElementById('form_lat').value = e.latlng.lat.toFixed(8);
                    document.getElementById('form_lng').value = e.latlng.lng.toFixed(8);
                });
            } else {
                pickerMap.setView(center, 15);
                pickerMarker.setLatLng(center);
                setTimeout(() => pickerMap.invalidateSize(), 200); // Fix rendering modal
            }
        }

        const modal = document.getElementById('modalLokasi');
        const form = document.getElementById('formLokasi');
        const title = document.getElementById('modalTitle');

        function openTambahModal() {
            form.reset();
            form.action = 'store/proses_lokasi.php?action=add';
            title.innerText = 'Tambah Lokasi Parkir';
            document.getElementById('id_field').value = '';
            document.getElementById('info_foto').style.display = 'none';
            modal.style.display = 'flex';
        }

        function openEditModal(data) {
            form.reset();
            form.action = 'store/proses_lokasi.php?action=edit';
            title.innerText = 'Edit Lokasi Parkir';

            document.getElementById('id_field').value = data.id;
            document.getElementById('kode_qris').value = data.kode_qris;
            document.getElementById('nama_lokasi').value = data.nama_lokasi;
            document.getElementById('titik_parkir').value = data.titik_parkir;
            document.getElementById('nominal_retribusi').value = data.nominal_retribusi;
            document.getElementById('target_bulanan').value = data.target_bulanan;
            document.getElementById('terbilang_target').value = data.terbilang_target;
            document.getElementById('foto_lama_field').value = data.foto;
            document.getElementById('form_lat').value = data.latitude || '';
            document.getElementById('form_lng').value = data.longitude || '';

            modal.style.display = 'flex';
            initMapPicker(data.latitude, data.longitude);

            if (data.foto) {
                const info = document.getElementById('info_foto');
                info.innerText = "Foto saat ini: " + data.foto;
                info.style.display = 'block';
            }

            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</body>

</html>