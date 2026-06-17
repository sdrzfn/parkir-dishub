<?php
include 'config/db.php';
include 'config/auth.php';
checkLogin();
$user = current_user();
allowRole(['admin']);

include 'api/fetch_lokasi.php';
?>

<!DOCTYPE html>
<html lang="id">

<?php include 'components/header.php'; ?>

<body>

    <?php include 'components/navbar.php'; ?>

    <div class="app-body" style="flex: 1;">
        <?php include 'components/sidebar.php'; ?>

        <main class="main-content">
            <div class="container" style="padding: 2rem;">
                <?php include 'components/breadcrumb.php'; ?>
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Data Lokasi Parkir</h1>
                        <p class="page-subtitle">Manajemen titik parkir dan target retribusi</p>
                    </div>
                    <button class="btn-primary" onclick="openTambahModal()">+ Tambah Lokasi</button>
                </div>

                <form method="GET" action="">
                    <div class="filter-panel">
                        <div class="filter-search-row">
                            <div class="filter-search-wrapper">
                                <span class="filter-search-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8" />
                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                    </svg>
                                </span>
                                <input type="text" name="search" class="filter-search-input"
                                    value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Cari kode QRIS atau nama lokasi..." autocomplete="off">
                            </div>
                            <button type="submit" class="filter-btn-search">Cari</button>
                        </div>
                        <hr class="filter-divider">
                        <div class="filter-controls-row">
                            <div class="filter-field">
                                <label for="filter-wilayah">Koordinator Wilayah</label>
                                <select name="kecamatan" class="filter-select">
                                    <option value="">Semua Wilayah</option>
                                    <option value="Sidoarjo 1" <?= $kecamatan == 'Sidoarjo 1' ? 'selected' : '' ?>>Sidoarjo
                                        1</option>
                                    <option value="Sidoarjo 2" <?= $kecamatan == 'Sidoarjo 2' ? 'selected' : '' ?>>Sidoarjo
                                        2</option>
                                    <option value="Waru" <?= $kecamatan == 'Waru' ? 'selected' : '' ?>>Waru</option>
                                    <option value="Porong" <?= $kecamatan == 'Porong' ? 'selected' : '' ?>>Porong</option>
                                    <option value="Krian" <?= $kecamatan == 'Krian' ? 'selected' : '' ?>>Krian</option>
                                </select>
                            </div>
                            <div class="filter-field">
                                <label for="filter-titik">Titik Parkir</label>
                                <select name="titik_parkir" id="filter-titik" class="filter-select">
                                    <option value="">Semua Titik</option>
                                    <option value="TJU" <?= $titik_parkir === 'TJU' ? 'selected' : '' ?>>TJU</option>
                                    <option value="TKP" <?= $titik_parkir === 'TKP' ? 'selected' : '' ?>>TKP</option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-footer">
                            <p class="filter-result-info">
                                Menampilkan <strong>
                                    <?= number_format($total_row) ?>
                                </strong> lokasi
                                <?php if ($search !== '' || $kecamatan !== '' || $titik_parkir !== ''): ?>
                                    <span class="filter-active-badge">Filter aktif</span>
                                <?php endif; ?>
                            </p>
                            <a href="lokasi.php" class="filter-btn-reset">↺ Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Kode QRIS</th>
                                <th>Nama Lokasi</th>
                                <th class="col-hide-mobile">Jukir Utama</th>
                                <th>Target</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <?php $foto = !empty($row['foto']) ? 'assets/img/lokasi/' . $row['foto'] : 'assets/img/no-image.jpg'; ?>
                                            <img src="<?= $foto ?>" class="img-thumbnail">
                                        </td>
                                        <td style="font-weight:600; font-size:0.82rem;"><?= $row['kode_qris'] ?></td>
                                        <td>
                                            <?= htmlspecialchars($row['nama_lokasi']) ?>
                                            <span class="label-ptk"><?= $row['titik_parkir'] ?></span>
                                        </td>
                                        <td class="col-hide-mobile">
                                            <?= $row['nama_jukir'] ?? '<span class="no-data">Belum diset</span>' ?>
                                        </td>
                                        <td class="text-nominal">
                                            Rp <?= number_format($row['target_bulanan'], 0, ',', '.') ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <button class="btn-action btn-edit"
                                                onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
                                            <a href="store/proses_lokasi.php?action=delete&id=<?= $row['id'] ?>"
                                                class="btn-action btn-delete"
                                                onclick="return konfirmasiHapus(event, this.href)">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="padding:40px; text-align:center; color:#94a3b8;">
                                        Data tidak ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <?php
                    $query_params = http_build_query([
                        'search' => $search,
                        'kecamatan' => $kecamatan,
                        'titik_parkir' => $titik_parkir,
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

    <div id="modalLokasi" class="modal">
        <div class="modal-content">
            <form id="formLokasi" action="store/proses_lokasi.php?action=add" method="POST"
                enctype="multipart/form-data">
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
                        <!-- <input type="text" name="titik_parkir" id="titik_parkir" class="form-control" required
                            placeholder="Contoh: TJU"> -->
                        <select name="titik_parkir" id="titik_parkir" class="form-control">
                            <option value="TJU">TJU</option>
                            <option value="TKP">TKP</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Nominal Retribusi (Rp)</label>
                            <!-- <input type="number" name="nominal_retribusi" id="nominal_retribusi" class="form-control"
                                required> -->
                            <select name="nominal_retribusi" id="nominal_retribusi" class="form-control">
                                <option value="2000">Rp2.000,00</option>
                                <option value="3000">Rp3.000,00</option>
                                <option value="4000">Rp4.000,00</option>
                                <option value="5000">Rp5.000,00</option>
                            </select>
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
            var center = [lat || -7.4478, lng || 112.7183];

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

            initMapPicker();
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

        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        (function () {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');
            const msg = params.get('msg');

            if (!status) return;
            const url = new URL(window.location);
            url.searchParams.delete('status');
            url.searchParams.delete('msg');
            window.history.replaceState({}, '', url);

            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            if (status === 'success') {
                toast.fire({ icon: 'success', title: 'Data lokasi berhasil disimpan!' });
            } else if (status === 'delete') {
                toast.fire({ icon: 'success', title: 'Data lokasi berhasil dihapus!' });
            } else if (status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal menyimpan',
                    text: msg || 'Terjadi kesalahan pada server.',
                    confirmButtonColor: '#2563eb',
                });
            }

            if (status) {
                const url = new URL(window.location);
                url.searchParams.delete('status');
                url.searchParams.delete('msg');
                window.history.replaceState({}, '', url);
            }
        })();
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</body>

</html>