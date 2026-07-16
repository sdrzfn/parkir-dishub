<?php
include '../config/db.php';
include '../config/auth.php';
checkLogin();
$user = current_user();
allowRole(['super-admin']);

include '../api/fetch_lokasi.php';
?>

<!DOCTYPE html>
<html lang="id">

<?php include '../components/header.php'; ?>

<body class="font-sans text-slate-800 antialiased min-h-screen pt-24"
    style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">

    <?php include '../components/navbar.php'; ?>

    <main class="container mx-auto" style="max-width:1400px;">
        <?php include '../components/breadcrumb.php'; ?>
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
                            value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama lokasi..."
                            autocomplete="off">
                    </div>
                    <button type="submit" class="filter-btn-search">Cari</button>
                </div>
                <hr class="filter-divider">
                <div class="filter-controls-row">
                    <div class="filter-field">
                        <label for="filter-wilayah">Kecamatan</label>
                        <select name="kecamatan" class="filter-select">
                            <option value="">Semua Kecamatan</option>
                            <?php
                            $list_kecamatan = [
                                "Balongbendo",
                                "Buduran",
                                "Candi",
                                "Gedangan",
                                "Jabon",
                                "Krembung",
                                "Krian",
                                "Porong",
                                "Prambon",
                                "Sedati",
                                "Sidoarjo",
                                "Sukodono",
                                "Taman",
                                "Tanggulangin",
                                "Tarik",
                                "Tulangan",
                                "Waru",
                                "Wonoayu"
                            ];
                            foreach ($list_kecamatan as $kec):
                                $selected = ($kecamatan === $kec) ? 'selected' : '';
                                echo "<option value='$kec' $selected>$kec</option>";
                            endforeach;
                            ?>
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

        <div
            class="table-container relative max-h-[65vh] overflow-y-auto overflow-x-auto w-full rounded-xl border border-slate-200 shadow-sm mt-4">
            <table class="custom-table w-full whitespace-nowrap">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <!-- <th class="hidden md:table-cell">Kode QRIS</th> -->
                        <th class="sticky left-0 bg-slate-50 z-20 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">Nama Lokasi</th>
                        <th class="col-hide-mobile">Petugas Parkir Utama</th>
                        <th class="hidden md:table-cell">Jenis Kendaraan</th>
                        <th class="hidden md:table-cell">Target Bulanan</th>
                        <th class="hidden md:table-cell">Target Harian</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td data-label="Foto">
                                    <?php $foto = !empty($row['foto']) ? '../assets/img/lokasi/' . $row['foto'] : '../assets/img/no-image.jpg'; ?>
                                    <img src="<?= $foto ?>" class="img-thumbnail">
                                </td>
                                <!-- <td data-label="Kode QRIS" class="hidden md:table-cell"
                                    style="font-weight:600; font-size:0.82rem;"><?= $row['kode_qris'] ?></td> -->
                                <td data-label="Nama Lokasi"
                                    class="sticky left-0 bg-white z-10 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
                                    <div class="truncate max-w-[200px]" title="<?= htmlspecialchars($row['nama_lokasi']) ?>">
                                        <?= htmlspecialchars($row['nama_lokasi']) ?>
                                    </div>
                                    <span class="label-ptk"><?= $row['titik_parkir'] ?></span>
                                </td>
                                <td data-label="Nama Jukir" class="col-hide-mobile">
                                    <div class="truncate max-w-[150px]"
                                        title="<?= htmlspecialchars($row['nama_jukir'] ?? 'Belum diset') ?>">
                                        <?= $row['nama_jukir'] ?? '<span class="no-data">Belum diset</span>' ?>
                                    </div>
                                </td>
                                <td data-label="Jenis Kendaraan" class="hidden md:table-cell">
                                    <?php
                                    $kats = json_decode($row['jenis_kendaraan'] ?? '[]', true);
                                    if (!empty($kats)) {
                                        foreach ($kats as $k) {
                                            $badgeColor = $k === 'R2' ? '#dbeafe|#1e40af' : ($k === 'R4' ? '#fef3c7|#92400e' : '#fce7f3|#9d174d');
                                            list($bg, $text) = explode('|', $badgeColor);
                                            echo "<span style='background:$bg; color:$text; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; margin-right:4px;'>$k</span>";
                                        }
                                    } else {
                                        echo '<span style="color:#94a3b8; font-style:italic;">Semua</span>';
                                    }
                                    ?>
                                </td>
                                <td data-label="Target Bulanan" class="text-nominal hidden md:table-cell">
                                    Rp <?= number_format($row['target_bulanan'], 0, ',', '.') ?>
                                </td>
                                <td data-label="Target Harian" class="text-nominal hidden md:table-cell">
                                    Rp <?= number_format($row['target_harian'], 0, ',', '.') ?>
                                </td>
                                <td data-label="Aksi" style="text-align:center;">
                                    <button class="btn-action btn-edit"
                                        onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
                                    <a href="../store/proses_lokasi.php?action=delete&id=<?= $row['id'] ?>"
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
        <div class="flex items-center justify-between mt-6 px-4">
            <p class="text-sm text-slate-500">
                Menampilkan halaman <span class="font-medium text-slate-900"><?= $page ?></span> dari <span
                    class="font-medium text-slate-900"><?= max(1, $total_pages) ?></span>
            </p>
            <nav class="flex items-center gap-2" aria-label="Pagination">
                <?php
                $query_params = http_build_query([
                    'search' => $search,
                    'kecamatan' => $kecamatan,
                    'titik_parkir' => $titik_parkir,
                ]);
                ?>

                <!-- Previous Button -->
                <a href="<?= $page > 1 ? '?page=' . ($page - 1) . '&' . $query_params : '#' ?>"
                    class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $page > 1 ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed pointer-events-none' ?>"
                    aria-disabled="<?= $page <= 1 ? 'true' : 'false' ?>">
                    &larr; Sebelumnya
                </a>

                <!-- Page Numbers -->
                <div class="flex items-center gap-1 hidden sm:flex">
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>&<?= $query_params ?>"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors <?= ($page == $i) ? 'bg-brand-950 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <!-- Next Button -->
                <a href="<?= $page < $total_pages ? '?page=' . ($page + 1) . '&' . $query_params : '#' ?>"
                    class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $page < $total_pages ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed pointer-events-none' ?>"
                    aria-disabled="<?= $page >= $total_pages ? 'true' : 'false' ?>">
                    Selanjutnya &rarr;
                </a>
            </nav>
        </div>
    </main>

    <div id="modalLokasi" class="modal">
        <div class="modal-content">
            <button type="button" onclick="closeModal()" class="btn-close-modal" aria-label="Tutup Modal"><i
                    class="fas fa-times"></i></button>
            <form id="formLokasi" action="../store/proses_lokasi.php?action=add" method="POST"
                enctype="multipart/form-data">
                <div class="modal-header">
                    <h3 id="modalTitle" style="margin: 0; font-weight: 700; color: var(--text-main);">Tambah Lokasi
                        Parkir</h3>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="id_field">
                    <input type="hidden" name="foto_lama" id="foto_lama_field">

                    <!-- <div class="form-group">
                        <label>Kode QRIS</label>
                        <input type="text" name="kode_qris" id="kode_qris" class="form-control" required
                            placeholder="Contoh: TJU-001">
                    </div> -->

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

                    <div class="form-group">
                        <label>Jenis Kendaraan yang Diampu</label>
                        <div style="display: flex; gap: 10px; margin-top: 8px; flex-wrap: wrap; align-items: center;">
                            <label class="jenis-kendaraan-option"
                                style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: #fff; transition: all 0.2s; user-select: none;"
                                onmouseenter="this.style.borderColor='#2563eb'; this.style.background='#f8fafc';"
                                onmouseleave="if(!this.querySelector('input').checked){this.style.borderColor='#e2e8f0'; this.style.background='#fff';}">
                                <input type="checkbox" name="jenis_kendaraan[]" value="R2" class="jenis-kendaraan-check"
                                    style="accent-color: #2563eb; width: 16px; height: 16px; cursor: pointer;"
                                    onchange="updateKendaraanStyle(this)">
                                <span style="font-size: 13px; font-weight: 500; color: #334155;">R2 (Roda 2)</span>
                                <span
                                    style="font-size: 11px; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">🛵</span>
                            </label>

                            <label class="jenis-kendaraan-option"
                                style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: #fff; transition: all 0.2s; user-select: none;"
                                onmouseenter="this.style.borderColor='#2563eb'; this.style.background='#f8fafc';"
                                onmouseleave="if(!this.querySelector('input').checked){this.style.borderColor='#e2e8f0'; this.style.background='#fff';}">
                                <input type="checkbox" name="jenis_kendaraan[]" value="R4" class="jenis-kendaraan-check"
                                    style="accent-color: #2563eb; width: 16px; height: 16px; cursor: pointer;"
                                    onchange="updateKendaraanStyle(this)">
                                <span style="font-size: 13px; font-weight: 500; color: #334155;">R4 (Roda 4)</span>
                                <span
                                    style="font-size: 11px; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">🚗</span>
                            </label>

                            <label class="jenis-kendaraan-option"
                                style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: #fff; transition: all 0.2s; user-select: none;"
                                onmouseenter="this.style.borderColor='#2563eb'; this.style.background='#f8fafc';"
                                onmouseleave="if(!this.querySelector('input').checked){this.style.borderColor='#e2e8f0'; this.style.background='#fff';}">
                                <input type="checkbox" name="jenis_kendaraan[]" value="R6" class="jenis-kendaraan-check"
                                    style="accent-color: #2563eb; width: 16px; height: 16px; cursor: pointer;"
                                    onchange="updateKendaraanStyle(this)">
                                <span style="font-size: 13px; font-weight: 500; color: #334155;">R6 (Roda 6+)</span>
                                <span
                                    style="font-size: 11px; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">🚛</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Kecamatan Wilayah</label>
                        <select name="kecamatan" id="form_kecamatan" class="form-control" required>
                            <option value="">-- Pilih Kecamatan --</option>
                            <?php
                            foreach ($list_kecamatan as $kec) {
                                echo "<option value='$kec'>$kec</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <!-- <div class="form-group" style="flex: 1;">
                            <label>Nominal Retribusi (Rp)</label>
                            <input type="number" name="nominal_retribusi" id="nominal_retribusi" class="form-control"
                                required>
                            <select name="nominal_retribusi" id="nominal_retribusi" class="form-control">
                                <option value="2000">Rp2.000,00</option>
                                <option value="3000">Rp3.000,00</option>
                                <option value="4000">Rp4.000,00</option>
                                <option value="5000">Rp5.000,00</option>
                            </select>
                        </div> -->
                        <div class="form-group" style="flex: 1;">
                            <label>Target Bulanan (Rp)</label>
                            <input type="number" name="target_bulanan" id="target_bulanan" class="form-control"
                                required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Target Harian (Rp)</label>
                            <input type="number" name="target_harian" id="target_harian" class="form-control" required>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Terbilang Target (Bulanan)</label>
                        <input type="text" name="terbilang_target" id="terbilang_target" class="form-control"
                            placeholder="Contoh: Satu Juta Rupiah">
                    </div>

                    <div class="form-group">
                        <label>Foto Lokasi</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <small id="info_foto" style="display: none; color: #3498db; margin-top: 5px;"></small>
                    </div>
                </div>

                <div class="modal-footer">
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
            form.action = '../store/proses_lokasi.php?action=add';
            title.innerText = 'Tambah Lokasi Parkir';
            document.getElementById('id_field').value = '';
            document.getElementById('info_foto').style.display = 'none';
            modal.style.display = 'flex';

            initMapPicker();
        }

        function openEditModal(data) {
            form.reset();
            form.action = '../store/proses_lokasi.php?action=edit';
            title.innerText = 'Edit Lokasi Parkir';

            document.getElementById('id_field').value = data.id;
            // document.getElementById('kode_qris').value = data.kode_qris;
            document.getElementById('nama_lokasi').value = data.nama_lokasi;
            document.getElementById('titik_parkir').value = data.titik_parkir;
            document.getElementById('nominal_retribusi').value = data.nominal_retribusi;
            document.getElementById('target_bulanan').value = data.target_bulanan;
            document.getElementById('target_harian').value = data.target_harian;
            document.getElementById('terbilang_target').value = data.terbilang_target;
            document.getElementById('foto_lama_field').value = data.foto;
            document.getElementById('form_lat').value = data.latitude || '';
            document.getElementById('form_lng').value = data.longitude || '';
            document.getElementById('form_kecamatan').value = data.kecamatan;

            const jenisKendaraan = data.jenis_kendaraan ? JSON.parse(data.jenis_kendaraan) : [];
            document.querySelectorAll('input[name="jenis_kendaraan[]"]').forEach(cb => {
                cb.checked = jenisKendaraan.includes(cb.value);
            });
            document.querySelectorAll('.jenis-kendaraan-check').forEach(cb => {
                updateKendaraanStyle(cb);
            });

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

        function updateKendaraanStyle(checkbox) {
            const label = checkbox.closest('label');
            if (checkbox.checked) {
                label.style.borderColor = '#2563eb';
                label.style.background = '#eff6ff';
                label.querySelectorAll('span')[1].style.background = '#dbeafe';
                label.querySelectorAll('span')[1].style.color = '#1e40af';
            } else {
                label.style.borderColor = '#e2e8f0';
                label.style.background = '#fff';
                label.querySelectorAll('span')[1].style.background = '#f1f5f9';
                label.querySelectorAll('span')[1].style.color = '#64748b';
            }
        }
    </script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</body>

</html>