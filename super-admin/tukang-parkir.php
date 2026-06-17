<?php
include '../config/db.php';
include '../config/auth.php';
checkLogin();
$user = current_user();
allowRole(['super-admin']);

include '../api/fetch_jukir.php';
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
                <?php include '../components/breadcrumb.php'; ?>
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Data Petugas Parkir</h1>
                        <p class="page-subtitle">Manajemen titik parkir dan target retribusi</p>
                    </div>
                    <button class="btn-primary" onclick="openAddModal()">+ Tambah Petugas Parkir</button>
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
                                <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Cari Kode Lokasi atau Nama Jalan..."
                                    style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid #cbd5e1; ...">
                            </div>
                            <button type="submit" class="filter-btn-search">Cari</button>
                        </div>
                        <hr class="filter-divider">
                        <div class="filter-controls-row">
                            <div class="filter-field">
                                <label for="filter-wilayah">Kecamatan</label>
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
                                <label for="filter-wilayah">Titik Parkir</label>
                                <select name="titik_parkir" id="filter-titik" class="filter-select">
                                    <option value="">Semua Titik</option>
                                    <option value="TJU" <?= $titik_parkir == 'TJU' ? 'selected' : '' ?>>TJU</option>
                                    <option value="TKP" <?= $titik_parkir == 'TKP' ? 'selected' : '' ?>>TKP</option>
                                </select>
                            </div>
                        </div>

                        <div class="filter-footer">
                            <p class="filter-result-info">
                                Menampilkan <strong>
                                    <?= number_format($total_row) ?>
                                </strong> petugas parkir
                                <?php if ($search !== '' || $kecamatan !== '' || $titik_parkir !== ''): ?>
                                    <span class="filter-active-badge">Filter aktif</span>
                                <?php endif; ?>
                            </p>
                            <a href="tukang-parkir.php" class="filter-btn-reset">↺ Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Card</th>
                                <th>Nama Petugas Utama (Klik untuk Pembantu)</th>
                                <th>NIK</th>
                                <th>No. Telp</th>
                                <th>Lokasi Parkir</th>
                                <th>File PKS</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                $id_utama = $row['id'];
                                $path_foto_utama = !empty($row['foto_id_card']) ? '../assets/img/jukir/utama/' . $row['foto_id_card'] : '../assets/img/no-image.jpg';
                                $has_pks_utama = !empty($row['file_pks']);
                                $path_pks_utama = $has_pks_utama ? '../assets/docs/pks/utama/' . $row['file_pks'] : '#';
                                $query_pembantu = mysqli_query($conn, "SELECT * FROM jukir_pembantu WHERE id_utama = $id_utama");
                                $jumlah_pembantu = mysqli_num_rows($query_pembantu);
                                ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 bg-white">
                                    <td class="text-center font-medium"><?= $no++; ?></td>

                                    <td class="text-center">
                                        <img src="<?= $path_foto_utama ?>" class="img-thumbnail"
                                            style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; cursor: pointer;"
                                            onclick="window.open('<?= $path_foto_utama ?>', '_blank')">
                                    </td>

                                    <td class="py-3 px-6" style="font-weight: bold">
                                        <div class="font-bold text-gray-800"><?= $row['nama_lengkap']; ?></div>
                                        <?php if ($jumlah_pembantu > 0): ?>
                                            <button type="button" onclick="togglePembantuRow(<?= $id_utama ?>)"
                                                style="margin-top: 4px; padding: 2px 8px; font-size: 11px; background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 12px; font-weight: 600; cursor: pointer;">
                                                <i class="fa fa-users"></i> Lihat <?= $jumlah_pembantu; ?> Pembantu
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-3 px-6 font-mono text-sm"><?= $row['nik']; ?></td>
                                    <td class="py-3 px-6"><?= $row['no_telp']; ?></td>
                                    <td class="py-3 px-6">
                                        <span class="font-medium"><?= $row['nama_lokasi']; ?></span>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($has_pks_utama): ?>
                                            <a href="<?= $path_pks_utama ?>" target="_blank" class="btn-pks">
                                                <i class="fa fa-file-pdf"></i> Lihat PKS
                                            </a>
                                        <?php else: ?>
                                            <span style="color: gray; font-style: italic;">Belum ada</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-center">
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            <button class="btn-action btn-edit"
                                                onclick='openEditModalUtama(<?= json_encode($row); ?>)'>Edit</button>
                                            <a href="../store/proses_jukir.php?action=delete&type=utama&id=<?= $row['id'] ?>"
                                                class="btn-action btn-delete"
                                                onclick="return confirm('Hapus jukir utama?')">Hapus</a>
                                        </div>
                                    </td>
                                </tr>

                                <?php
                                if ($jumlah_pembantu > 0):
                                    while ($p = mysqli_fetch_assoc($query_pembantu)):
                                        $path_foto_pbt = !empty($p['foto_id_card']) ? '../assets/img/jukir/pembantu/' . $p['foto_id_card'] : '../assets/img/no-image.jpg';
                                        $has_pks_pbt = !empty($p['file_pks']);
                                        $path_pks_pbt = $has_pks_pbt ? '../assets/docs/pks/pembantu/' . $p['file_pks'] : '#';
                                        ?>
                                        <tr class="pembantu-row-<?= $id_utama ?> bg-gray-50 border-b border-gray-100"
                                            style="display: none;">
                                            <td class="text-center text-gray-400 text-xs"><i
                                                    class="fa fa-level-up-alt fa-rotate-90"></i></td>

                                            <td class="text-center">
                                                <img src="<?= $path_foto_pbt ?>" class="img-thumbnail"
                                                    style="width: 35px; height: 35px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px dashed #cbd5e1;"
                                                    onclick="window.open('<?= $path_foto_pbt ?>', '_blank')">
                                            </td>

                                            <td class="py-2 px-6 text-gray-700">
                                                <strong><?= $p['nama_pembantu']; ?></strong>
                                            </td>

                                            <td class="py-2 px-6 font-mono text-xs text-gray-600"><?= $p['nik']; ?></td>
                                            <td class="py-2 px-6 text-xs text-gray-500">
                                                <?= !empty($p['alamat']) ? $p['alamat'] : '-'; ?>
                                            </td>
                                            <td style="color: gray; font-style: italic;">Sama dengan Utama</td>

                                            <td class="text-center">
                                                <?php if ($has_pks_pbt): ?>
                                                    <a href="<?= $path_pks_pbt ?>" target="_blank"
                                                        style="color: #059669; font-size: 12px;">
                                                        <i class="fa fa-file-pdf"></i> PKS Pbt
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-400 text-xs italic">-</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="text-center">
                                                <div style="display: flex; gap: 4px; justify-content: center;">
                                                    <button type="button" class="btn-action btn-edit"
                                                        style="padding: 1px 6px; font-size: 11px;"
                                                        onclick='openEditModalPembantu(<?= json_encode($p); ?>)'>Edit</button>
                                                    <a href="../store/proses_jukir.php?action=delete&type=pembantu&id=<?= $p['id'] ?>"
                                                        style="padding: 1px 6px; font-size: 11px; background-color: #fee2e2; color: #ef4444; text-decoration: none; border-radius: 4px;"
                                                        onclick="return confirm('Hapus jukir pembantu ini?')">Hapus</a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    endwhile;
                                endif;
                                ?>

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
        <div class="modal-content" style="max-width: 600px; width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 id="modalTitle" style="margin: 0; color: var(--sidebar-bg);">Tambah Data Personel</h3>
                <span style="cursor:pointer; font-size: 24px; font-weight: bold; color: #64748b;"
                    onclick="closeModal()">&times;</span>
            </div>

            <div id="step-indicator-container"
                style="display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                <button type="button" id="btn-step-1" onclick="switchStep(1)"
                    style="flex: 1; padding: 10px; border: none; background: none; font-weight: 600; cursor: pointer; border-bottom: 2px solid #2563eb; color: #2563eb;">
                    Petugas Utama
                </button>
                <button type="button" id="btn-step-2" onclick="switchStep(2)"
                    style="flex: 1; padding: 10px; border: none; background: none; font-weight: 600; cursor: pointer; color: #64748b;">
                    Petugas Pembantu
                </button>
            </div>

            <div id="slide-utama" class="form-slide">
                <form action="../store/proses_jukir.php?action=add&type=utama" method="POST" id="form-jukir-utama"
                    enctype="multipart/form-data">
                    <input type="hidden" name="id" id="form_id">
                    <input type="hidden" name="file_lama" id="file_lama_field">
                    <input type="hidden" name="foto_lama" id="foto_lama_field">

                    <div class="form-group">
                        <label>Foto ID Card</label>
                        <input type="file" name="foto_id_card" class="form-control" accept="image/*">
                        <small id="info_foto" style="display: none; color: #3498db; margin-top: 5px;"></small>
                    </div>

                    <div class="form-group">
                        <label>NIK Petugas Utama</label>
                        <input type="text" name="nik" id="form_nik" class="form-control" required maxlength="16">
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="form_nama" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Upload File PKS</label>
                        <input type="file" name="file_pks" class="form-control" accept=".pdf, .doc, .docx, image/*">
                        <small id="info_file" style="display: none; color: #3498db; margin-top: 5px;"></small>
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

                        <div class="form-group" style="flex: 1; position: relative;">
                            <label>Lokasi Penugasan</label>
                            <div class="custom-search-select" style="position: relative;">
                                <input type="text" id="lokasi_search" class="form-control"
                                    placeholder="Ketik kode QRIS / jalan..." autocomplete="off" required>
                                <input type="hidden" name="id_lokasi" id="form_lokasi">

                                <div id="lokasi_options_panel"
                                    style="display: none; position: absolute; width: 100%; background: white; border: 1px solid #cbd5e1; border-top: none; max-height: 180px; overflow-y: auto; z-index: 9999; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                                    <?php
                                    mysqli_data_seek($list_lokasi, 0);
                                    while ($lok = mysqli_fetch_assoc($list_lokasi)):
                                        ?>
                                        <div class="lokasi-option-item" data-value="<?= $lok['id'] ?>"
                                            data-search="<?= strtolower($lok['kode_qris'] . ' ' . $lok['nama_lokasi']) ?>"
                                            style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem;">
                                            <strong><?= $lok['kode_qris'] ?></strong> - <?= $lok['nama_lokasi'] ?>
                                        </div>
                                    <?php endwhile; ?>

                                    <div id="lokasi_no_results"
                                        style="padding: 15px; text-align: center; color: #64748b; display: none; font-size: 0.85rem;">
                                        Lokasi tidak ditemukan.<br>
                                        <a href="lokasi.php"
                                            style="color: #2563eb; font-weight: 600; text-decoration: underline; display: inline-block; margin-top: 5px;">
                                            ➔ Tambah Halaman Lokasi Terlebih Dahulu
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-primary" id="btnSubmitUtama">Simpan Jukir Utama</button>
                    </div>
                </form>
            </div>

            <div id="slide-pembantu" class="form-slide" style="display: none;">
                <form action="../store/proses_jukir.php?action=add&type=pembantu" method="POST" id="form-jukir-pembantu"
                    enctype="multipart/form-data">
                    <input type="hidden" name="id_pembantu" id="form_id_pembantu">

                    <div class="form-group">
                        <label>Pilih Petugas Utama</label>
                        <select name="id_utama" id="form_id_utama" class="form-control" required>
                            <option value="">-- Pilih Petugas Utama --</option>
                            <?php
                            $all_utama = mysqli_query($conn, "SELECT id, nama_lengkap, nik FROM jukir_utama ORDER BY nama_lengkap ASC");
                            while ($ut = mysqli_fetch_assoc($all_utama)):
                                ?>
                                <option value="<?= $ut['id'] ?>"><?= $ut['nama_lengkap'] ?> (NIK: <?= $ut['nik'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>NIK Petugas Pembantu</label>
                        <input type="text" name="nik_pembantu" id="form_nik_pembantu" class="form-control" required
                            maxlength="16">
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap Pembantu</label>
                        <input type="text" name="nama_pembantu" id="form_nama_pembantu" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Alamat Petugas Pembantu</label>
                        <input type="text" name="alamat_pembantu" id="form_alamat_pembantu" class="form-control"
                            required>
                    </div>

                    <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-primary" id="btnSubmitPembantu">Simpan Petugas Pembantu</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        const modal = document.getElementById('modalJukir');

        const lokasiSearch = document.getElementById('lokasi_search');
        const lokasiHidden = document.getElementById('form_lokasi');
        const lokasiPanel = document.getElementById('lokasi_options_panel');
        const optionItems = document.querySelectorAll('.lokasi-option-item');
        const noResultsDiv = document.getElementById('lokasi_no_results');

        lokasiSearch.addEventListener('focus', () => { lokasiPanel.style.display = 'block'; });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.custom-search-select')) {
                lokasiPanel.style.display = 'none';
            }
        });

        lokasiSearch.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            let matches = 0;

            optionItems.forEach(item => {
                const searchContent = item.getAttribute('data-search');
                if (searchContent.includes(query)) {
                    item.style.display = 'block';
                    matches++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (matches === 0) {
                noResultsDiv.style.display = 'block';
            } else {
                noResultsDiv.style.display = 'none';
            }
        });

        optionItems.forEach(item => {
            item.addEventListener('click', () => {
                lokasiSearch.value = item.textContent.replace(/\s+/g, ' ').trim();
                lokasiHidden.value = item.getAttribute('data-value');
                lokasiPanel.style.display = 'none';
            });
        });

        function switchStep(step) {
            const tabUtama = document.getElementById('btn-step-1');
            const tabPembantu = document.getElementById('btn-step-2');
            const slideUtama = document.getElementById('slide-utama');
            const slidePembantu = document.getElementById('slide-pembantu');

            if (step === 1) {
                tabUtama.style.borderBottom = '2px solid #2563eb';
                tabUtama.style.color = '#2563eb';
                tabPembantu.style.borderBottom = 'none';
                tabPembantu.style.color = '#64748b';
                slideUtama.style.display = 'block';
                slidePembantu.style.display = 'none';
            } else {
                tabPembantu.style.borderBottom = '2px solid #2563eb';
                tabPembantu.style.color = '#2563eb';
                tabUtama.style.borderBottom = 'none';
                tabUtama.style.color = '#64748b';
                slideUtama.style.display = 'none';
                slidePembantu.style.display = 'block';
            }
        }

        function openAddModal() {
            document.getElementById('modalTitle').innerText = "Tambah Petugas Parkir";
            document.getElementById('step-indicator-container').style.display = 'flex';

            document.getElementById('form-jukir-utama').reset();
            document.getElementById('form-jukir-pembantu').reset();
            document.getElementById('form_id').value = "";
            document.getElementById('form_id_pembantu').value = "";
            document.getElementById('info_foto').style.display = 'none';
            document.getElementById('info_file').style.display = 'none';

            document.getElementById('form-jukir-utama').action = "../store/proses_jukir.php?action=add&type=utama";
            document.getElementById('form-jukir-pembantu').action = "../store/proses_jukir.php?action=add&type=pembantu";

            switchStep(1);
            document.getElementById('modalJukir').style.display = 'flex';
        }

        function openEditModalUtama(data) {
            document.getElementById('modalTitle').innerText = "Edit Data Petugas Parkir Utama";
            document.getElementById('step-indicator-container').style.display = 'none';

            document.getElementById('form-jukir-utama').action = "../store/proses_jukir.php?action=edit&type=utama";
            switchStep(1);

            document.getElementById('form_id').value = data.id;
            document.getElementById('form_nik').value = data.nik;
            document.getElementById('form_nama').value = data.nama_lengkap;
            document.getElementById('form_ttl').value = data.ttl;
            document.getElementById('form_alamat').value = data.alamat;
            document.getElementById('form_telp').value = data.no_telp;
            document.getElementById('form_lokasi').value = data.id_lokasi;
            if (document.getElementById('lokasi_search')) {
                document.getElementById('lokasi_search').value = data.kode_qris + " - " + data.nama_lokasi;
            }

            document.getElementById('modalJukir').style.display = 'flex';
        }

        function openEditModalPembantu(data) {
            document.getElementById('modalTitle').innerText = "Edit Data Petugas Parkir Pembantu";
            document.getElementById('step-indicator-container').style.display = 'none';

            document.getElementById('form-jukir-pembantu').action = "../store/proses_jukir.php?action=edit&type=pembantu";
            switchStep(2);

            document.getElementById('form_id_pembantu').value = data.id;
            // document.getElementById('form_id_test_utama').value = data.id_utama;
            document.getElementById('form_id_utama').value = data.id_utama;
            document.getElementById('form_nik_pembantu').value = data.nik;
            document.getElementById('form_nama_pembantu').value = data.nama_pembantu;
            document.getElementById('form_alamat_pembantu').value = data.alamat_pembantu || "";

            document.getElementById('modalJukir').style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // SweetAlert
        (function () {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');
            const msg = params.get('msg');

            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            if (!status) return;

            if (status === 'success_add_utama') {
                toast.fire({ icon: 'success', title: 'Petugas Utama berhasil didaftarkan!' });
            } else if (status === 'success_edit_utama') {
                toast.fire({ icon: 'success', title: 'Data Petugas Utama berhasil diperbarui!' });
            } else if (status === 'success_delete_utama') {
                toast.fire({ icon: 'success', title: 'Data Petugas Utama & Pembantu dicabut!' });
            } else if (status === 'success_add_pembantu') {
                toast.fire({ icon: 'success', title: 'Petugas Pembantu berhasil ditambahkan!' });
            } else if (status === 'success_edit_pembantu') {
                toast.fire({ icon: 'success', title: 'Data Petugas Pembantu berhasil diubah!' });
            } else if (status === 'success_delete_pembantu') {
                toast.fire({ icon: 'success', title: 'Petugas Pembantu berhasil dihapus!' });
            } else if (status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Operasi Gagal',
                    text: msg || 'Gagal memproses data ke database.',
                    confirmButtonColor: '#2563eb',
                });
            }

            const url = new URL(window.location);
            url.searchParams.delete('status');
            url.searchParams.delete('msg');
            window.history.replaceState({}, '', url);
        })();

        function togglePembantuRow(idUtama) {
            const rows = document.querySelectorAll('.pembantu-row-' + idUtama);

            rows.forEach(row => {
                if (row.style.display === 'none') {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>