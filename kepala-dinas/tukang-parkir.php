<?php
include '../config/db.php';
include '../config/auth.php';
checkLogin();
$user = current_user();
allowRole(['kepala-dinas']);

include '../api/fetch_jukir.php';
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
                <h1 class="page-title">Data Petugas Parkir</h1>
                <p class="page-subtitle">Manajemen titik parkir dan target retribusi</p>
            </div>
            <!-- <button class="btn-primary" onclick="openAddModal()">+ Tambah Petugas Parkir</button> -->
        </div>

        <div class="filter-panel">
            <form method="GET" action="" class="filter-search-row">
                <div class="filter-search-wrapper">
                    <i class="fas fa-search filter-search-icon"></i>
                    <input type="text" name="search" class="filter-search-input"
                        placeholder="Cari NIK atau Nama Petugas..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>

                <select name="kecamatan" class="filter-select" style="max-width: 200px;">
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

                <select name="tipe_jukir" class="filter-select" style="width: 150px;">
                    <option value="">Pilih Petugas</option>
                    <option value="utama" <?= $tipe_jukir == 'utama' ? 'selected' : '' ?>>Jukir Utama</option>
                    <option value="pembantu" <?= $tipe_jukir == 'pembantu' ? 'selected' : '' ?>>Jukir Pembantu</option>
                </select>

                <select name="titik_parkir" class="filter-select" style="max-width: 150px;">
                    <option value="">Semua Titik</option>
                    <option value="TJU" <?= $titik_parkir == 'TJU' ? 'selected' : '' ?>>TJU</option>
                    <option value="TKP" <?= $titik_parkir == 'TKP' ? 'selected' : '' ?>>TKP</option>
                </select>

                <button type="submit" class="filter-btn-search">
                    Terapkan Filter
                </button>
            </form>
        </div>

        <div
            class="table-container relative max-h-[65vh] overflow-y-auto overflow-x-auto w-full rounded-xl border border-slate-200 shadow-sm mt-4">
            <table class="custom-table w-full whitespace-nowrap">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Card</th>
                        <th class="sticky left-0 bg-slate-50 z-20 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">Nama Petugas
                            Utama</th>
                        <th class="hidden md:table-cell">NIK</th>
                        <th class="hidden md:table-cell">No. Telp</th>
                        <th>Lokasi Parkir</th>
                        <th class="hidden md:table-cell">File PKS</th>
                        <!-- <th style="text-align: center;">Aksi</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0):
                        $no = $offset + 1;
                        while ($row = mysqli_fetch_assoc($result)):
                            $id_utama = $row['id'];
                            $path_foto_utama = !empty($row['foto_id_card']) ? '../assets/img/jukir/utama/' . $row['foto_id_card'] : '../assets/img/no-image.jpg';
                            $has_pks_utama = !empty($row['file_pks']);
                            $path_pks_utama = $has_pks_utama ? '../assets/docs/pks/utama/' . $row['file_pks'] : '#';
                            $query_pembantu = mysqli_query($conn, "SELECT * FROM jukir_pembantu WHERE id_utama = $id_utama");
                            $jumlah_pembantu = mysqli_num_rows($query_pembantu);
                            ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 bg-white">
                                <td data-label="No" class="text-center font-medium"><?= $no++; ?></td>

                                <td data-label="ID Card" class="text-center">
                                    <img src="<?= $path_foto_utama ?>" class="img-thumbnail"
                                        style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; cursor: pointer;"
                                        onclick="window.open('<?= $path_foto_utama ?>', '_blank')">
                                </td>

                                <td data-label="Nama Petugas"
                                    class="py-3 px-6 sticky left-0 bg-white z-10 shadow-[2px_0_5px_rgba(0,0,0,0.05)]"
                                    style="font-weight: bold">
                                    <div class="font-bold text-gray-800 truncate max-w-[200px]"
                                        title="<?= htmlspecialchars($row['nama_lengkap']); ?>">
                                        <?= $row['nama_lengkap']; ?>
                                    </div>
                                    <?php if ($jumlah_pembantu > 0): ?>
                                        <button type="button" onclick="togglePembantuRow(<?= $id_utama ?>)"
                                            style="margin-top: 4px; padding: 2px 8px; font-size: 11px; background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; border-radius: 12px; font-weight: 600; cursor: pointer;">
                                            <i class="fa fa-users"></i> Lihat <?= $jumlah_pembantu; ?> Pembantu
                                        </button>
                                    <?php endif; ?>
                                </td>

                                <td data-label="NIK" class="py-3 px-6 hidden md:table-cell font-mono text-sm">
                                    <?= $row['nik']; ?>
                                </td>
                                <td data-label="No. Telp" class="py-3 px-6 hidden md:table-cell"><?= $row['no_telp']; ?></td>
                                <td data-label="Lokasi Parkir" class="py-3 px-6">
                                    <span class="font-medium"><?= $row['nama_lokasi']; ?></span>
                                </td>

                                <td data-label="File PKS" class="text-center hidden md:table-cell">
                                    <?php if ($has_pks_utama): ?>
                                        <a href="<?= $path_pks_utama ?>" target="_blank" class="btn-pks">
                                            <i class="fa fa-file-pdf"></i> Lihat PKS
                                        </a>
                                    <?php else: ?>
                                        <span style="color: gray; font-style: italic;">Belum ada</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Aksi" class="text-center">
                                    <div style="display: flex; gap: 5px; justify-content: flex-end;">
                                        <!-- <button class="btn-action btn-edit"
                                                    onclick='openEditModalUtama(<?= json_encode($row); ?>)' style="padding: 6px 12px; font-weight: bold;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn-action btn-delete"
                                                    onclick="hapusJukir('utama', <?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_lengkap']) ?>')" style="padding: 6px 12px; font-weight: bold;">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button> -->
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
                                        <td data-label="" class="text-center text-gray-400 text-xs"><i
                                                class="fa fa-level-up-alt fa-rotate-90"></i></td>

                                        <td data-label="ID Card" class="text-center">
                                            <img src="<?= $path_foto_pbt ?>" class="img-thumbnail"
                                                style="width: 35px; height: 35px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px dashed #cbd5e1;"
                                                onclick="window.open('<?= $path_foto_pbt ?>', '_blank')">
                                        </td>

                                        <td data-label="Nama Pembantu" class="py-2 px-6 text-gray-700">
                                            <div class="font-bold truncate max-w-[200px]"
                                                title="<?= htmlspecialchars($p['nama_pembantu']); ?>">
                                                <?= $p['nama_pembantu']; ?>
                                            </div>
                                        </td>

                                        <td data-label="NIK" class="py-2 px-6 font-mono text-xs text-gray-600"><?= $p['nik']; ?></td>
                                        <td data-label="Alamat" class="py-2 px-6 text-xs text-gray-500">
                                            <div class="truncate max-w-[150px]"
                                                title="<?= htmlspecialchars(!empty($p['alamat']) ? $p['alamat'] : '-') ?>">
                                                <?= !empty($p['alamat']) ? $p['alamat'] : '-'; ?>
                                            </div>
                                        </td>
                                        <td data-label="Lokasi" style="color: gray; font-style: italic;">Sama dengan Utama</td>

                                        <td data-label="File PKS" class="text-center">
                                            <?php if ($has_pks_pbt): ?>
                                                <a href="<?= $path_pks_pbt ?>" target="_blank" style="color: #059669; font-size: 12px;">
                                                    <i class="fa fa-file-pdf"></i> PKS Pbt
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs italic">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <td data-label="Aksi" class="text-center">
                                            <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                                <button type="button" class="btn-action btn-edit"
                                                    style="padding: 1px 6px; font-size: 11px;"
                                                    onclick='openEditModalPembantu(<?= json_encode($p); ?>)'>Edit</button>
                                                <button type="button" class="btn-action btn-delete"
                                                    style="padding: 1px 6px; font-size: 11px; border-radius: 4px;"
                                                    onclick="hapusJukir('pembantu', <?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_pembantu']) ?>')">Hapus</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile;
                            endif;
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-user-tag"></i></div>
                                    <p class="empty-state-title">Data Petugas Tidak Ditemukan</p>
                                    <p class="empty-state-desc">Belum ada data petugas parkir yang tersedia atau sesuai
                                        dengan pencarian Anda.</p>
                                </div>
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
                    'tipe_jukir' => $tipe_jukir,
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

    <div id="modalJukir" class="modal">
        <div class="modal-content" style="max-width: 600px; width: 100%;">
            <button type="button" onclick="closeModal()" class="btn-close-modal" aria-label="Tutup Modal"><i
                    class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3 id="modalTitle" style="margin: 0; font-weight: 700; color: var(--text-main);">Tambah Data Personel
                </h3>
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

                    <div class="modal-body">
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

                        <div class="form-group" style="flex: 1;">
                            <label>No. Rekening Bank Jatim</label>
                            <input type="number" name="no_rekening" id="form_rekening_utama" class="form-control"
                                placeholder="Contoh: 0123456789">
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
                                        placeholder="Masukkan nama lokasi parkir..." autocomplete="off" required>
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
                                                <?= $lok['nama_lokasi'] ?>
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
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-primary" id="btnSubmitUtama">Simpan Jukir Utama</button>
                    </div>
                </form>
            </div>

            <div id="slide-pembantu" class="form-slide" style="display: none;">
                <form action="../store/proses_jukir.php?action=add&type=pembantu" method="POST" id="form-jukir-pembantu"
                    enctype="multipart/form-data">
                    <input type="hidden" name="id_pembantu" id="form_id_pembantu">

                    <div class="modal-body">
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
                            <label>Foto ID Card</label>
                            <input type="file" name="foto_id_card" class="form-control" accept="image/*">
                            <small id="info_foto" style="display: none; color: #3498db; margin-top: 5px;"></small>
                        </div>

                        <!-- <div class="form-group">
                            <label>Upload File PKS</label>
                            <input type="file" name="file_pks" class="form-control" accept=".pdf, .doc, .docx, image/*">
                            <small id="info_file" style="display: none; color: #3498db; margin-top: 5px;"></small>
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label>No. Rekening Bank Jatim</label>
                            <input type="number" name="no_rekening_pembantu" id="form_rekening_pembantu"
                                class="form-control" placeholder="Contoh: 0123456789">
                        </div> -->

                        <div class="form-group">
                            <label>NIK Petugas Pembantu</label>
                            <input type="text" name="nik_pembantu" id="form_nik_pembantu" class="form-control" required
                                maxlength="16">
                        </div>

                        <div class="form-group">
                            <label>Nama Lengkap Pembantu</label>
                            <input type="text" name="nama_pembantu" id="form_nama_pembantu" class="form-control"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Alamat Petugas Pembantu</label>
                            <input type="text" name="alamat_pembantu" id="form_alamat_pembantu" class="form-control"
                                required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn-primary" id="btnSubmitPembantu">Simpan Petugas
                            Pembantu</button>
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
            // document.getElementById('info_file').style.display = 'none';

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
            document.getElementById('form_rekening_utama').value = data.no_rekening || "";
            if (document.getElementById('lokasi_search')) {
                document.getElementById('lokasi_search').value = data.nama_lokasi;
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
            // document.getElementById('form_rekening_pembantu').value = data.no_rekening || "";

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

        function hapusJukir(type, id, nama) {
            let role = type === 'utama' ? 'Jukir Utama' : 'Jukir Pembantu';
            let warningText = type === 'utama' ?
                `Apakah Anda yakin ingin menghapus <b>${nama}</b>?<br><br><span style="color:#ef4444;">Peringatan: Seluruh Petugas Pembantu yang terkait juga akan ikut terhapus!</span>` :
                `Apakah Anda yakin ingin menghapus <b>${nama}</b>?`;

            Swal.fire({
                title: `Hapus ${role}?`,
                html: warningText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#e2e8f0',
                confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    cancelButton: 'swal-cancel-dark',
                    popup: 'swal-popup-custom',
                },
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `../store/proses_jukir.php?action=delete&type=${type}&id=${id}`;
                }
            });
        }
    </script>
</body>

</html>