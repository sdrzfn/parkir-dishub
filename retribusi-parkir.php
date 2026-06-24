<?php
include 'config/db.php';
include 'config/auth.php';
include 'config/retribusi.php';
include 'api/fetch_retribusi.php';

checkLogin();
$user = current_user();
allowRole(['admin']);

/**
 * @param float|int $realisasi
 * @param float|int $target
 * @return float
 */
function hitungPersentase($realisasi, $target)
{
    if ($target <= 0) {
        return 0;
    }
    $persen = ($realisasi / $target) * 100;
    return round($persen, 2);
}

// MEKANISME PENGELOMPOKAN DATA (ANTI-LAG & TETAP MENJAGA PARAMETER DB)
$red_list = [];
$yellow_list = [];
$green_list = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $persen = hitungPersentase($row['realisasi'], $row['target']);
        if ($persen >= 100) {
            $green_list[] = $row;
        } elseif ($persen > 50) {
            $yellow_list[] = $row;
        } else {
            $red_list[] = $row;
        }
    }
}

// Konfigurasi Blok Tabel Berdasarkan Kategori Warna
$tables_config = [
    [
        'title' => '🔴 Kategori Kritis',
        'list' => $red_list,
        'row_style' => 'background-color: rgba(239, 68, 68, 0.04); border-left: 4px solid #ef4444;',
        'empty_msg' => 'Tidak ada juru parkir dengan performa kritis di halaman ini.'
    ],
    [
        'title' => '🟡 Kategori Waspada (>50% - 99%)',
        'list' => $yellow_list,
        'row_style' => 'background-color: rgba(245, 158, 11, 0.04); border-left: 4px solid #f59e0b;',
        'empty_msg' => 'Tidak ada juru parkir dengan performa menengah di halaman ini.'
    ],
    [
        'title' => '🟢 Kategori Lunas / Target Tercapai (100%+)',
        'list' => $green_list,
        'row_style' => 'background-color: rgba(34, 197, 94, 0.04); border-left: 4px solid #22c55e;',
        'empty_msg' => 'Belum ada juru parkir yang melunasi target di halaman ini.'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">

<?php include 'components/header.php'; ?>

<body class="font-sans text-slate-800 antialiased min-h-screen pt-24" style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">

    <?php include 'components/navbar.php'; ?>

    <main class="container mx-auto" style="max-width:1400px; padding: 27px 55px;">
        <?php include 'components/breadcrumb.php'; ?>

        <div class="page-header">
            <div>
                <h1 class="page-title">Daftar Retribusi Petugas Parkir</h1>
                <p class="page-subtitle">Manajemen Retribusi Petugas Parkir</p>
            </div>
        </div>

        <div class="filter-panel">
            <form method="GET" action="" class="filter-search-row">
                <div class="filter-search-wrapper">
                    <i class="fas fa-search filter-search-icon"></i>
                    <input type="text" name="search" class="filter-search-input" placeholder="Cari Jukir, Lokasi, atau QRIS..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                
                <select name="kecamatan" class="filter-select" style="max-width: 200px;">
                    <option value="">Semua Wilayah</option>
                    <?php 
                    if(isset($list_wilayah)) {
                        mysqli_data_seek($list_wilayah, 0);
                        while ($w = mysqli_fetch_assoc($list_wilayah)): 
                            $sel = (isset($filter_kec) && $filter_kec == $w['wilayah']) ? 'selected' : '';
                    ?>
                        <option value="<?= $w['wilayah'] ?>" <?= $sel ?>><?= $w['wilayah'] ?></option>
                    <?php endwhile; } ?>
                </select>

                <button type="submit" class="filter-btn-search">
                    Terapkan Filter
                </button>
            </form>
        </div>

        <?php foreach ($tables_config as $table): ?>
            <div class="table-section-wrapper" style="margin-top: 2rem; margin-bottom: 1rem;">
                <h2 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 8px;">
                    <?= $table['title'] ?> 
                    <span style="font-size: 0.8rem; background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 20px;">
                        <?= count($table['list']) ?> Data
                    </span>
                </h2>

                <div class="table-container relative max-h-[45vh] overflow-y-auto overflow-x-auto w-full" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                    <table class="custom-table w-full whitespace-nowrap">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th class="sticky left-0 bg-slate-50 z-20 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">Nama Jukir (Utama/Pembantu)</th>
                                <th>Lokasi</th>
                                <th class="hidden md:table-cell">Realisasi</th>
                                <th class="hidden md:table-cell">Target</th>
                                <th>Persentase</th>
                                <th>Denda (2%)</th>
                                <th>Imbal Jasa (40%)</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (count($table['list']) > 0):
                                $no = 1;
                                foreach ($table['list'] as $row):
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
                                <tr class="row-utama" onclick="togglePembantu(<?= $id_utama; ?>)" style="<?= $table['row_style'] ?>">
                                    <td data-label="No"><?= $no++; ?></td>
                                    <td data-label="Nama Jukir" class="col-nama sticky left-0 z-10 shadow-[2px_0_5px_rgba(0,0,0,0.05)]" style="background-color: inherit;">
                                        <div class="flex-nama items-center">
                                            <i class="fa fa-chevron-right icon-toggle" id="icon-<?= $id_utama; ?>"></i>
                                            <strong class="truncate max-w-[200px]" title="<?= htmlspecialchars($row['nama_utama']); ?>">
                                                <?= $row['nama_utama']; ?>
                                            </strong>
                                            <?php if ($jumlah_pembantu > 0): ?>
                                                <span class="badge-count whitespace-nowrap"><?= $jumlah_pembantu; ?> Pembantu</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Lokasi">
                                        <div class="truncate max-w-[150px]" title="<?= htmlspecialchars($row['lokasi']); ?>">
                                            <?= $row['lokasi']; ?>
                                        </div>
                                    </td>
                                    <td data-label="Realisasi" class="text-success font-medium hidden md:table-cell">Rp <?= number_format($realisasi_dana, 0, ',', '.'); ?></td>
                                    <td data-label="Target" class="text-primary hidden md:table-cell">Rp <?= number_format($target_dana, 0, ',', '.'); ?></td>
                                    <td data-label="Persentase" class="<?= ($persen >= 100) ? 'text-success' : (($persen >= 65) ? 'text-warning' : 'text-danger'); ?> font-bold">
                                        <?= $persen; ?>%
                                    </td>

                                    <td data-label="Denda (2%)" style="color: #b91c1c; font-weight: bold;">
                                        <?= $denda > 0 ? 'Rp ' . number_format($denda, 0, ',', '.') : '<span style="color: #94a3b8; font-weight: normal;">-</span>'; ?>
                                    </td>

                                    <td data-label="Imbal Jasa (40%)" style="color: #16a34a; font-weight: bold;">
                                        Rp <?= number_format($imbal_jasa, 0, ',', '.'); ?>
                                    </td>

                                    <td data-label="Aksi" style="text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <button class="btn-action btn-edit"
                                                onclick="event.stopPropagation(); window.location.href='retribusi-detail.php?id=<?= $row['id']; ?>'"
                                                style="padding: 6px 12px; font-weight: bold;">
                                                <i class="fas fa-eye" style="margin-right: 4px;"></i> Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr id="pembantu-<?= $id_utama; ?>" class="row-pembantu" style="display: none; background-color: #ffffff;">
                                    <td></td>
                                    <td colspan="8">
                                        <div class="pembantu-container" style="padding: 10px 15px;">
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
                                                                <td data-label="Nama Pembantu">
                                                                    <div class="truncate max-w-[150px]" title="<?= htmlspecialchars($p['nama_pembantu']); ?>">
                                                                        <?= $p['nama_pembantu']; ?>
                                                                    </div>
                                                                </td>
                                                                <td data-label="NIK"><?= $p['nik']; ?></td>
                                                                <td data-label="Alamat">
                                                                    <div class="truncate max-w-[200px]" title="<?= htmlspecialchars($p['alamat'] ?? '-'); ?>">
                                                                        <?= $p['alamat'] ?? '-'; ?>
                                                                    </div>
                                                                </td>
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
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 20px; color: #64748b; font-style: italic;">
                                        <?= $table['empty_msg'] ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (count($red_list) === 0 && count($yellow_list) === 0 && count($green_list) === 0): ?>
            <div class="empty-state" style="margin-top: 2rem;">
                <div class="empty-state-icon"><i class="fas fa-receipt"></i></div>
                <p class="empty-state-title">Data Retribusi Tidak Ditemukan</p>
                <p class="empty-state-desc">Belum ada data retribusi petugas parkir yang tersedia atau sesuai dengan pencarian Anda.</p>
            </div>
        <?php endif; ?>

        <div class="flex items-center justify-between mt-6 px-4">
            <p class="text-sm text-slate-500">
                Menampilkan halaman <span class="font-medium text-slate-900"><?= $page ?></span> dari <span class="font-medium text-slate-900"><?= max(1, $total_pages) ?></span>
            </p>
            <nav class="flex items-center gap-2" aria-label="Pagination">
                <a href="<?= $page > 1 ? '?page=' . ($page - 1) : '#' ?>" 
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $page > 1 ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed pointer-events-none' ?>"
                   aria-disabled="<?= $page <= 1 ? 'true' : 'false' ?>">
                    &larr; Sebelumnya
                </a>

                <div class="flex items-center gap-1 hidden sm:flex">
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>" 
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors <?= ($page == $i) ? 'bg-brand-950 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>

                <a href="<?= $page < $total_pages ? '?page=' . ($page + 1) : '#' ?>" 
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $page < $total_pages ? 'text-slate-600 hover:bg-slate-100' : 'text-slate-300 cursor-not-allowed pointer-events-none' ?>"
                   aria-disabled="<?= $page >= $total_pages ? 'true' : 'false' ?>">
                    Selanjutnya &rarr;
                </a>
            </nav>
        </div>
    </main>

    <div id="modalJukir" class="modal">
        <div class="modal-content">
            <button type="button" onclick="closeModal()" class="btn-close-modal" aria-label="Tutup Modal"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3 id="modalTitle" style="margin: 0; font-weight: 700; color: var(--text-main);">Tambah Petugas Parkir</h3>
            </div>

            <form action="store/proses_jukir.php?action=add" method="POST">
                <input type="hidden" name="id" id="form_id">
                <div class="modal-body">
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
                        <input type="text" name="ttl" id="form_ttl" class="form-control" placeholder="Contoh: Sidoarjo, 12-05-1985">
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
                                    <option value="<?= $lok['id'] ?>"><?= $lok['kode_qris'] ?> - <?= $lok['nama_lokasi'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
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
            form.action = "store/proses_jukir.php?action=add";
            form.reset();
            document.getElementById('form_id').value = "";
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = "Edit Data Petugas Parkir";
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

            if (status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
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
</body>
</html>