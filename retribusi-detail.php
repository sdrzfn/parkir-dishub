<?php
include 'config/db.php';
include 'config/auth.php';
include 'config/retribusi.php';

checkLogin();
$id_jukir = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$query_jukir = "SELECT j.*, l.nama_lokasi, l.kode_qris, l.titik_parkir, l.target_bulanan as target_lokasi
                FROM jukir_utama j
                LEFT JOIN lokasi l ON j.id_lokasi = l.id
                WHERE j.id = $id_jukir";
$res_jukir = mysqli_query($conn, $query_jukir);
$d = mysqli_fetch_assoc($res_jukir);

if (!$d) {
    die("Data Jukir tidak ditemukan.");
}

$bulan_ini = date('m');
$tahun_ini = date('Y');
$q_stats = mysqli_query($conn, "SELECT SUM(jumlah_setoran) as total FROM transaksi_retribusi 
                                WHERE id_jukir = $id_jukir AND bulan = '$bulan_ini' AND tahun = '$tahun_ini'");
$realisasi = mysqli_fetch_assoc($q_stats)['total'] ?? 0;
$target = $d['target_bulanan'] ?: $d['target_lokasi'];
$persen = ($target > 0) ? ($realisasi / $target) * 100 : 0;
$ind = getIndicator($persen);

$q_history = mysqli_query($conn, "SELECT * FROM transaksi_retribusi 
                                  WHERE id_jukir = $id_jukir 
                                  ORDER BY tanggal_setoran DESC, id DESC LIMIT 10");

$hari_ini = (int) date('d');
$rekomendasi = getRekomendasiAksi($persen, $hari_ini);

$filterSetoran = filterSetoran($conn);

$target = $d['target_bulanan'] ?: $d['target_lokasi'];
$realisasi = mysqli_fetch_assoc($q_stats)['total'] ?? 0;
$nilai_denda = hitungDenda($target, $realisasi);
$imbal_jasa = hitungImbalJasa($realisasi);
?>

<!DOCTYPE html>
<html lang="id">
<?php include 'components/header.php'; ?>

<body style="display: flex; margin: 0; padding: 0;">

    <?php include 'components/navbar.php'; ?>

    <div class="app-body w-full">

        <?php include 'components/sidebar.php'; ?>

        <main class="main-content">
            <div class="detail-container">
                <?php include 'components/breadcrumb.php'; ?>
                <div class="header-section">
                    <div class="header-title-box">
                        <a href="retribusi-parkir.php" class="btn-back"><i class="fas fa-arrow-left"></i></a>
                        <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">Detail Retribusi
                            Jukir</h2>
                    </div>
                    <button onclick="openModal()" class="btn-add-manual">
                        + Tambah Setoran Manual
                    </button>
                </div>

                <?php if ($rekomendasi): ?>
                    <div class="card"
                        style="margin-top: 20px; margin-bottom: 20px; border: 2px solid #fee2e2; background: #fffafb; padding: 20px; border-radius: 12px;">
                        <h4 style="margin: 0 0 10px 0; color: #991b1b;"><i class="fas fa-magic"></i> Jukir Perlu Diproses
                        </h4>
                        <p style="font-size: 13px; color: #7f1d1d; margin-bottom: 15px;">
                            Klik tombol di bawah untuk men-generate <b>Surat Peringatan</b> & <b>Surat Penagihan</b> secara
                            otomatis sesuai data bulan ini.
                        </p>

                        <form action="store/proses_tunggakan.php" method="POST">
                            <input type="hidden" name="id_jukir" value="<?= $id_jukir ?>">
                            <input type="hidden" name="jenis_surat" value="<?= $rekomendasi['kode'] ?>">
                            <input type="hidden" name="nominal_tunggakan" value="<?= $target - $realisasi ?>">

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: end;">
                                <div>
                                    <label class="form-label">Metode Pengiriman</label>
                                    <select name="metode" class="form-select" required>
                                        <option value="WhatsApp">Otomatis via WhatsApp</option>
                                        <option value="Sistem">Simpan ke Riwayat (Fisik)</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn-submit-modal"
                                    style="margin: 0; height: 42px; background: #ef4444; width: 100%;">
                                    <i class="fas fa-sync-alt"></i> Generate Surat
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="detail-grid">
                    <div class="card"
                        style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px 20px;">
                        <div class="profile-header" style="text-align: center; margin-bottom: 15px;">
                            <div class="avatar-circle"
                                style="margin: 0 auto 15px auto; width: 80px; height: 80px; font-size: 2rem;">
                                <?= substr($d['nama_lengkap'], 0, 1) ?>
                            </div>
                            <h3 style="margin: 0 0 5px 0; color: #1e293b; font-size: 1.25rem; font-weight: 700;">
                                <?= $d['nama_lengkap'] ?>
                            </h3>
                            <span class="badge-role" style="display: inline-block;">Petugas Parkir Utama</span>
                        </div>
                        <button onclick="openEditJukirModal()" class="btn-edit-profile">
                            <i class="fas fa-edit"></i> Edit Profil
                        </button>
                    </div>

                    <div class="card" style="position: relative;">
                        <div class="info-group">
                            <div class="info-item">
                                <span class="info-label">NIK</span>
                                <span class="info-value"><?= $d['nik'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Alamat</span>
                                <span class="info-value"><?= $d['alamat'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">No. Telepon</span>
                                <span class="info-value"><?= $d['no_telp'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 25px;">
                    <div class="card">
                        <h4 style="margin: 0 0 15px 0; color: #1e293b;"><i class="fas fa-map-marker-alt"
                                style="color: #ef4444; margin-right: 8px;"></i> Detail Penugasan</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div style="background: #f8fafc; padding: 15px; border-radius: 12px;">
                                <span class="info-label">Nama Lokasi</span>
                                <span class="info-value"><?= $d['nama_lokasi'] ?></span>
                            </div>
                            <div style="background: #f8fafc; padding: 15px; border-radius: 12px;">
                                <span class="info-label">Titik QRIS</span>
                                <span class="info-value" style="color: #2563eb;"><?= $d['kode_qris'] ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="stats-flex">
                            <div>
                                <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 5px;">Realisasi Setoran
                                    (Bulan ini)</p>
                                <h2 class="realisasi-amount">Rp <?= number_format($realisasi, 0, ',', '.') ?></h2>
                            </div>
                            <div style="text-align: right;">
                                <p class="target-label">Target: Rp <?= number_format($target, 0, ',', '.') ?></p>
                                <span class="percent-text"
                                    style="color: <?= $ind['color'] ?>"><?= round($persen, 1) ?>%</span>
                            </div>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar <?= $ind['bg'] ?>" style="width: <?= min($persen, 100) ?>%;">
                            </div>
                        </div>
                        <div
                            style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border-top: 1px dashed #e2e8f0; padding-top: 15px;">
                            <div
                                style="background: #fff5f5; padding: 12px; border-radius: 8px; border-left: 4px solid #ef4444;">
                                <span
                                    style="font-size: 11px; color: #991b1b; display: block; font-weight: 600; text-transform: uppercase;">Sanksi
                                    Denda (2%)</span>
                                <span style="font-size: 15px; color: #b91c1c; font-weight: 700;">
                                    Rp
                                    <?= number_format($nilai_denda, 0, ',', '.') ?>
                                </span>
                                <small style="display: block; color: #7f1d1d; font-size: 10px; margin-top: 2px;">
                                    <?= $nilai_denda > 0 ? '*Terhitung dari akumulasi tunggakan' : '*Target bulan ini terpenuhi' ?>
                                </small>
                            </div>
                            <div
                                style="background: #f0fdf4; padding: 12px; border-radius: 8px; border-left: 4px solid #10b981;">
                                <span
                                    style="font-size: 11px; color: #166534; display: block; font-weight: 600; text-transform: uppercase;">Hak
                                    Imbal Jasa Jukir (40%)</span>
                                <span style="font-size: 15px; color: #15803d; font-weight: 700;">
                                    Rp
                                    <?= number_format($imbal_jasa, 0, ',', '.') ?>
                                </span>
                                <small style="display: block; color: #14532d; font-size: 10px; margin-top: 2px;">
                                    *Upah bagi hasil dari realisasi setoran
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card"
                        style="padding: 0; overflow: hidden; background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div
                            style="padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <h4 style="margin: 0; color: #1e293b; font-size: 16px;">Riwayat Transaksi</h4>

                            <div style="display: flex; gap: 8px;">
                                <select id="filterBulan" onchange="loadRiwayat()"
                                    style="padding: 6px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 13px;">
                                    <?php
                                    $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                    foreach ($months as $i => $m) {
                                        $val = str_pad($i + 1, 2, "0", STR_PAD_LEFT);
                                        $sel = ($val == date('m')) ? 'selected' : '';
                                        echo "<option value='$val' $sel>$m</option>";
                                    }
                                    ?>
                                </select>
                                <select id="filterTahun" onchange="loadRiwayat()"
                                    style="padding: 6px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 13px;">
                                    <option value="2025">2025</option>
                                    <option value="2026" selected>2026</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-container">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8fafc; text-align: left;">
                                        <th
                                            style="padding: 12px 15px; color: #64748b; font-size: 12px; text-transform: uppercase;">
                                            Tanggal</th>
                                        <th
                                            style="padding: 12px 15px; color: #64748b; font-size: 12px; text-transform: uppercase;">
                                            Termin</th>
                                        <th
                                            style="padding: 12px 15px; color: #64748b; font-size: 12px; text-transform: uppercase;">
                                            Metode</th>
                                        <th
                                            style="padding: 12px 15px; color: #64748b; font-size: 12px; text-transform: uppercase; text-align: right;">
                                            Nominal</th>
                                        <th
                                            style="padding: 12px 15px; color: #64748b; font-size: 12px; text-transform: uppercase; text-align: center;">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="isiRiwayat">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($rekomendasi): ?>
                        <div class="card"
                            style="margin-top: 20px; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; background: white;">
                            <h4 style="margin: 0 0 15px 0; color: #1e293b;"><i class="fas fa-history"></i> Riwayat Surat
                                Peringatan</h4>

                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                    <thead>
                                        <tr style="text-align: left; border-bottom: 2px solid #f1f5f9;">
                                            <th style="padding: 10px; color: #64748b;">Tanggal Aksi</th>
                                            <th style="padding: 10px; color: #64748b;">Jenis</th>
                                            <th style="padding: 10px; color: #64748b;">File Dokumen (PDF)</th>
                                            <th style="padding: 10px; color: #64748b;">Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Query mengambil log terbaru
                                        $q_log = mysqli_query($conn, "SELECT * FROM log_aksi_jukir WHERE id_jukir = $id_jukir ORDER BY tanggal_aksi DESC");

                                        if (mysqli_num_rows($q_log) > 0):
                                            while ($log = mysqli_fetch_assoc($q_log)):
                                                ?>
                                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                                    <td style="padding: 10px; font-weight: 500;">
                                                        <?= date('d M Y', strtotime($log['tanggal_aksi'])) ?>
                                                        <br><small style="color: #94a3b8;">
                                                            <?= date('H:i', strtotime($log['tanggal_aksi'])) ?>
                                                            WIB
                                                        </small>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <span class="badge"
                                                            style="background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                                                            <?= strtoupper($log['jenis_surat']) ?>
                                                        </span>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <div style="display: flex; flex-direction: column; gap: 5px;">
                                                            <?php if ($log['file_sp']): ?>
                                                                <a href="uploads/surat/<?= $log['file_sp'] ?>" target="_blank"
                                                                    style="color: #3b82f6; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                                                    <i class="fas fa-file-pdf"></i> <span style="font-size: 11px;">
                                                                        <?= $log['file_sp'] ?>
                                                                    </span>
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if ($log['file_tagihan']): ?>
                                                                <a href="uploads/surat/<?= $log['file_tagihan'] ?>" target="_blank"
                                                                    style="color: #10b981; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                                                                    <i class="fas fa-file-invoice-dollar"></i> <span
                                                                        style="font-size: 11px;">
                                                                        <?= $log['file_tagihan'] ?>
                                                                    </span>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <span style="color: #1e293b; font-weight: 500;">
                                                            <?= $log['admin_input'] ?>
                                                        </span>
                                                        <br><small style="color: #94a3b8; font-size: 10px;">
                                                            <?= $log['keterangan'] ?>
                                                        </small>
                                                    </td>
                                                </tr>
                                                <?php
                                            endwhile;
                                        else:
                                            ?>
                                            <tr>
                                                <td colspan="4" style="padding: 30px; text-align: center; color: #94a3b8;">
                                                    <i class="fas fa-folder-open"
                                                        style="display: block; font-size: 24px; margin-bottom: 10px;"></i>
                                                    Belum ada riwayat dokumen untuk petugas parkir ini.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    </div>
    </main>
    </div>

    <div id="modalEditJukir" class="modal-backdrop" style="display: none;">
        <div class="modal-content-edit">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Edit Data Petugas Parkir</h3>
                <button onclick="closeEditJukirModal()" class="btn-close-modal"><i class="fas fa-times"></i></button>
            </div>
            <form action="store/proses_jukir.php?action=edit" method="POST" class="modal-body">
                <input type="hidden" name="id" value="<?= $d['id'] ?>">

                <div style="margin-bottom: 15px;">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-input" value="<?= $d['nama_lengkap'] ?>"
                        required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-input" value="<?= $d['nik'] ?>" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label class="form-label">Nomor Telepon</label>
                    <input type="text" name="no_telp" class="form-input" value="<?= $d['no_telp'] ?>" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-input"
                        style="height: 80px; resize: none;"><?= $d['alamat'] ?></textarea>
                </div>

                <div class="modal-footer-actions">
                    <button type="submit" class="btn-submit-modal">Simpan Perubahan</button>
                    <button type="button" onclick="closeEditJukirModal()" class="btn-secondary-modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalSetoran" class="modal-backdrop" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Input Setoran Manual</h3>
                <button onclick="closeModal()" class="btn-close-modal"><i class="fas fa-times"></i></button>
            </div>
            <form action="store/proses_retribusi.php" method="POST" class="modal-body">
                <input type="hidden" name="id_jukir" value="<?= $id_jukir ?>">

                <div style="margin-bottom: 15px;">
                    <label class="form-label">Nominal Setoran (Rp)</label>
                    <input type="number" name="jumlah" class="form-input" required placeholder="Contoh: 50000">
                </div>

                <div class="form-row">
                    <div>
                        <label class="form-label">Termin</label>
                        <select name="termin" class="form-select">
                            <option value="1">Termin 1</option>
                            <option value="2">Termin 2</option>
                            <option value="3">Termin 3</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="form-input" required>
                    </div>
                </div>

                <div class="modal-footer-actions">
                    <button type="submit" class="btn-submit-modal">Simpan Setoran</button>
                    <button type="button" onclick="closeModal()" class="btn-secondary-modal">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Edit Data Setoran</h4>
                <button type="button" class="btn-close-x" onclick="closeEditModal()">&times;</button>
            </div>

            <form action="store/proses_retribusi.php" method="POST" class="modal-form">
                <input type="hidden" name="id_jukir" id="edit_id_jukir_hidden" value="<?= $id_jukir ?>">
                <input type="hidden" name="id_setoran" id="edit_id">

                <div class="form-group">
                    <label>Tanggal Setoran</label>
                    <input type="date" name="tanggal" id="edit_tanggal" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Termin</label>
                        <input type="number" name="termin" id="edit_termin" placeholder="Contoh: 1" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal (Rp)</label>
                        <input type="number" name="jumlah" id="edit_nominal" placeholder="0" required>
                    </div>
                </div>

                <div class="modal-footer-edit">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // modal tambah setoran
        function openModal() {
            document.getElementById('modalSetoran').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('modalSetoran').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Intercept submit form tambah setoran
        document.getElementById('modalSetoran')
            .querySelector('form')
            .addEventListener('submit', function (e) {
                e.preventDefault();

                const form = this;
                const nominal = document.querySelector('[name="jumlah"]').value;
                const termin = document.querySelector('[name="termin"]').value;
                const tanggal = document.querySelector('[name="tanggal"]').value;
                const tgl_fmt = new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                const rp = 'Rp ' + Number(nominal).toLocaleString('id-ID');

                Swal.fire({
                    title: 'Konfirmasi Input Setoran',
                    html: `
                <div style="text-align:left; font-size:14px; color:#334155; line-height:2;">
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <span style="color:#94a3b8; font-weight:600;">Nominal</span>
                        <span style="font-weight:700; color:#10b981; font-size:16px;">${rp}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <span style="color:#94a3b8; font-weight:600;">Termin</span>
                        <span style="font-weight:600; color:#1e293b;">Termin ${termin}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0;">
                        <span style="color:#94a3b8; font-weight:600;">Tanggal</span>
                        <span style="font-weight:600; color:#1e293b;">${tgl_fmt}</span>
                    </div>
                </div>
                <p style="margin-top:16px; font-size:13px; color:#64748b;">
                    Pastikan data sudah benar sebelum disimpan.
                </p>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
                    cancelButtonText: 'Periksa Lagi',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#e2e8f0',
                    customClass: {
                        cancelButton: 'swal-cancel-dark',
                        popup: 'swal-popup-custom',
                    },
                    reverseButtons: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

        // MODAL EDIT SETORAN
        function openEditModal(btn) {
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            const idJukirFallback = btn.getAttribute('data-id-jukir') || "<?= $id_jukir ?>";
            document.getElementById('edit_id_jukir_hidden').value = idJukirFallback;
            document.getElementById('edit_tanggal').value = btn.getAttribute('data-tanggal');
            document.getElementById('edit_termin').value = btn.getAttribute('data-termin');
            document.getElementById('edit_nominal').value = btn.getAttribute('data-nominal');
            document.getElementById('modalEdit').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('modalEdit').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.getElementById('modalEdit')
            .querySelector('form')
            .addEventListener('submit', function (e) {
                e.preventDefault();

                const form = this;
                const nominal = document.getElementById('edit_nominal').value;
                const termin = document.getElementById('edit_termin').value;
                const tanggal = document.getElementById('edit_tanggal').value;
                const tgl_fmt = new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                const rp = 'Rp ' + Number(nominal).toLocaleString('id-ID');

                Swal.fire({
                    title: 'Konfirmasi Perubahan Setoran',
                    html: `
                <div style="text-align:left; font-size:14px; color:#334155; line-height:2;">
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <span style="color:#94a3b8; font-weight:600;">Nominal Baru</span>
                        <span style="font-weight:700; color:#10b981; font-size:16px;">${rp}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <span style="color:#94a3b8; font-weight:600;">Termin</span>
                        <span style="font-weight:600; color:#1e293b;">Termin ${termin}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0;">
                        <span style="color:#94a3b8; font-weight:600;">Tanggal</span>
                        <span style="font-weight:600; color:#1e293b;">${tgl_fmt}</span>
                    </div>
                </div>
                <p style="margin-top:16px; font-size:13px; color:#64748b;">
                    Data setoran yang lama akan ditimpa. Lanjutkan?
                </p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Perbarui',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#e2e8f0',
                    customClass: {
                        cancelButton: 'swal-cancel-dark',
                        popup: 'swal-popup-custom',
                    },
                    reverseButtons: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

        // MODAL EDIT JUKIR
        function openEditJukirModal() {
            document.getElementById('modalEditJukir').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeEditJukirModal() {
            document.getElementById('modalEditJukir').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        window.addEventListener('click', function (e) {
            if (e.target === document.getElementById('modalSetoran')) closeModal();
            if (e.target === document.getElementById('modalEdit')) closeEditModal();
            if (e.target === document.getElementById('modalEditJukir')) closeEditJukirModal();
        });

        // LOAD RIWAYAT SETORAN
        function loadRiwayat() {
            const bulan = document.getElementById('filterBulan').value;
            const tahun = document.getElementById('filterTahun').value;
            const area = document.getElementById('isiRiwayat');
            const idJukir = "<?= $_GET['id'] ?? '' ?>";

            area.style.opacity = '0.5';
            fetch(`config/retribusi.php?bulan=${bulan}&tahun=${tahun}&id_jukir=${idJukir}&ajax_riwayat=1`)
                .then(res => {
                    if (!res.ok) throw new Error('Network error');
                    return res.text();
                })
                .then(data => {
                    area.innerHTML = data;
                    area.style.opacity = '1';
                })
                .catch(() => {
                    area.innerHTML = "<tr><td colspan='5' style='text-align:center;padding:20px;color:red;'>Gagal mengambil data.</td></tr>";
                    area.style.opacity = '1';
                });
        }
        // TOAST NOTIFIKASI STATUS (setelah redirect)
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

            if (status === 'tambah') {
                toast.fire({ icon: 'success', title: 'Setoran berhasil ditambahkan!' });
            } else if (status === 'edit') {
                toast.fire({ icon: 'success', title: 'Data setoran berhasil diperbarui!' });
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
</body>

</html>