<?php
include 'config/db.php';
include 'config/auth.php';
include 'config/retribusi.php';
include 'config/helper.php';

checkLogin();
$id_jukir = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$query_jukir = "SELECT j.*,
                    l.nama_lokasi, l.kode_qris,
                    l.titik_parkir,
                    l.target_bulanan AS target_lokasi
                FROM jukir_utama j
                LEFT JOIN lokasi l ON j.id_lokasi = l.id
                WHERE j.id = $id_jukir";
$res_jukir = mysqli_query($conn, $query_jukir);
$d = mysqli_fetch_assoc($res_jukir);

if (!$d) {
    redirectBack('retribusi-parkir.php', ['status' => 'error', 'msg' => 'Data Petugas tidak ditemukan.']);
    exit;
}

$bulan_ini = date('n');
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

$nilai_denda = hitungDenda($target, $realisasi);
$imbal_jasa = hitungImbalJasa($realisasi);
?>

<!DOCTYPE html>
<html lang="id">
<?php include 'components/header.php'; ?>

<body class="font-sans text-slate-800 antialiased min-h-screen pt-24"
    style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">

    <?php include 'components/navbar.php'; ?>

    <main class="container mx-auto" style="max-width:1400px;">
        <?php include 'components/breadcrumb.php'; ?>
        <div class="detail-container">
            <div class="header-section">
                <div class="header-title-box">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">Detail Retribusi
                        Petugas Parkir</h2>
                </div>
                <button onclick="openModal()" class="btn-add-manual">
                    + Tambah Setoran Manual
                </button>
            </div>

            <?php if ($rekomendasi): ?>
                <div class="alert-card <?= $rekomendasi['color'] === 'danger' ? 'danger' : 'warning' ?>">
                    <h4 class="alert-card-title"><i class="fas fa-magic"></i> Petugas Parkir Perlu Diproses</h4>
                    <p class="alert-card-desc">
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
                            <button type="submit" class="btn-submit-modal" style="margin: 0; width: 100%;">
                                <i class="fas fa-sync-alt"></i> Generate Surat
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="detail-grid">
                <div class="card"
                    style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
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
                                Imbal Jasa Petugas (40%)</span>
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

                <div class="card" style="padding: 0; overflow: hidden;">
                    <div
                        style="padding: 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <h4 style="margin: 0; color: #1e293b; font-size: 16px;">Riwayat Transaksi</h4>

                        <div style="display: flex; gap: 8px;">
                            <select id="filterBulan" onchange="loadRiwayat()" class="form-select"
                                style="padding: 6px 12px; min-width: 130px; height: 38px;">
                                <?php
                                $months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                foreach ($months as $i => $m) {
                                    $val = str_pad($i + 1, 2, "0", STR_PAD_LEFT);
                                    $sel = ($val == date('m')) ? 'selected' : '';
                                    echo "<option value='$val' $sel>$m</option>";
                                }
                                ?>
                            </select>
                            <select id="filterTahun" onchange="loadRiwayat()" class="form-select"
                                style="padding: 6px 12px; min-width: 100px; height: 38px;">
                                <option value="2025">2025</option>
                                <option value="2026" selected>2026</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="custom-table" style="width: 100%; border-collapse: collapse;">
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
                                                <td data-label="Tanggal" style="padding: 10px; font-weight: 500;">
                                                    <?= date('d M Y', strtotime($log['tanggal_aksi'])) ?>
                                                    <br><small style="color: #94a3b8;">
                                                        <?= date('H:i', strtotime($log['tanggal_aksi'])) ?>
                                                        WIB
                                                    </small>
                                                </td>
                                                <td data-label="Jenis" style="padding: 10px;">
                                                    <span class="badge"
                                                        style="background: #fee2e2; color: #ef4444; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                                                        <?= strtoupper($log['jenis_surat']) ?>
                                                    </span>
                                                </td>
                                                <td data-label="Dokumen" style="padding: 10px;">
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
                                                <td data-label="Admin" style="padding: 10px;">
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
                                <?php if ($rekomendasi !== null):
                                    $q_log = mysqli_query($conn, "SELECT * FROM log_aksi_jukir WHERE id_jukir = $id_jukir ORDER BY tanggal_aksi DESC");
                                        if (mysqli_num_rows($q_log) > 0):
                                            while ($log = mysqli_fetch_assoc($q_log)):
                                            ?>
                                                <?php if ($rekomendasi['kode'] === 'dua_tombol'): ?>
                                                    <a href="uploads/surat/<?= $log['file_sp'] ?>" target="_blank" class="btn-action btn-danger">File SP</a>
                                                    <a href="uploads/surat/<?= $log['file_tagihan'] ?>" target="_blank" class="btn-action btn-warning">File Tagihan</a>

                                                <?php elseif ($rekomendasi['kode'] === 'sp1'): ?>
                                                    <a href="uploads/surat/<?= $log['file_sp'] ?>" target="_blank" class="btn-action btn-delete ?>"> 
                                                        <?= $rekomendasi['label'] ?> 
                                                    </a>

                                                <?php elseif ($rekomendasi['kode'] === 'tagihan'): ?>
                                                    <a href="uploads/surat/<?= $log['file_tagihan'] ?>" target="_blank" class="btn-action btn-<?= $rekomendasi['color'] ?>"> 
                                                        <?= $rekomendasi['label'] ?> 
                                                    </a>
                                                <?php endif; 
                                            endwhile;
                                        endif;
                                    ?>
                                <?php endif; ?>
                                <!-- <a href="config/cetak-sp.php?id=<?= $id_jukir ?>&kode=<?= $rekomendasi['kode'] ?>" target="_blank"
                                    class="btn-action btn-delete">
                                    <?= $rekomendasi['label'] ?>
                                </a> -->
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
    </main>

    <div id="modalEdit" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Edit Data Setoran</h4>
                <button type="button" onclick="closeEditModal()" class="btn-close-x">&times;</button>
            </div>

            <form action="store/proses_retribusi.php" method="POST">
                <input type="hidden" name="id_jukir" id="edit_id_jukir_hidden" value="<?= $id_jukir ?>">
                <input type="hidden" name="id_setoran" id="edit_id">

                <div class="modal-form">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tanggal Setoran</label>
                            <input type="date" name="tanggal" id="edit_tanggal" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="number" name="jumlah" id="edit_nominal" class="form-input" placeholder="0"
                                required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Jenis Kendaraan</label>
                            <select name="jenis_kendaraan" id="edit_jenis_kendaraan" class="form-select">
                                <option value="">Pilih Jenis</option>
                                <option value="R2">R2 — Roda 2</option>
                                <option value="R4">R4 — Roda 4</option>
                                <option value="R6">R6 — Roda 6+</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Lokasi</label>
                            <input type="text" class="form-input"
                                value="<?= htmlspecialchars($d['titik_parkir'] ?? '-') ?>" readonly
                                style="background:#f8fafc; color:#64748b;">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">ID Karcis</label>
                            <input type="text" name="id_karcis" id="edit_id_karcis" class="form-input"
                                placeholder="KRC-001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jumlah Karcis</label>
                            <input type="number" name="jumlah_karcis" id="edit_jumlah_karcis" class="form-input"
                                placeholder="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">No. Seri Awal</label>
                            <input type="text" name="no_seri_awal" id="edit_no_seri_awal" class="form-input"
                                placeholder="000001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Seri Akhir</label>
                            <input type="text" name="no_seri_akhir" id="edit_no_seri_akhir" class="form-input"
                                placeholder="000050">
                        </div>
                    </div>
                    <div class="modal-footer-edit">
                        <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                        <button type="submit" class="btn-save">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="modalSetoran" class="modal-backdrop" style="display: none;">
        <div class="modal-content">
            <button type="button" onclick="closeModal()" class="btn-close-modal" aria-label="Tutup Modal"><i
                    class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Input Setoran Manual</h3>
            </div>
            <form id="formSetoran" action="store/proses_retribusi.php" method="POST" class="modal-form">
                <input type="hidden" name="id_jukir" value="<?= $id_jukir ?>">

                <!-- Baris 1: Tanggal + Nominal -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Setoran</label>
                        <input type="date" name="tanggal" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal (Rp)</label>
                        <input type="number" name="jumlah" placeholder="0" required>
                    </div>
                </div>

                <!-- Baris 2: Jenis Kendaraan + Jenis Lokasi -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" class="form-control" required>
                            <option value="">Pilih Jenis</option>
                            <option value="R2">R2 — Roda 2</option>
                            <option value="R4">R4 — Roda 4</option>
                            <option value="R6">R6 — Roda 6+</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Lokasi</label>
                        <select name="titik_parkir" id="filter-titik" class="form-control">
                            <option value="">Semua Titik</option>
                            <option value="TJU" <?= $d['titik_parkir'] === 'TJU' ? 'selected' : '' ?>>TJU</option>
                            <option value="TKP" <?= $d['titik_parkir'] === 'TKP' ? 'selected' : '' ?>>TKP</option>
                        </select>
                        <!-- <input type="text" value="<?= $d['titik_parkir'] ?>" readonly class="form-control"
                            style="background:#f8fafc; color:#64748b;"> -->
                    </div>
                </div>

                <!-- Baris 3: ID Karcis + Jumlah Karcis -->
                <div class="form-row">
                    <div class="form-group">
                        <label>ID Karcis</label>
                        <input type="text" name="id_karcis" placeholder="Contoh: KRC-001">
                    </div>
                    <div class="form-group">
                        <label>Jumlah Karcis</label>
                        <input type="number" name="jumlah_karcis" placeholder="0">
                    </div>
                </div>

                <!-- Baris 4: Nomor Seri -->
                <div class="form-row">
                    <div class="form-group">
                        <label>No. Seri Awal</label>
                        <input type="text" name="no_seri_awal" placeholder="Contoh: 000001">
                    </div>
                    <div class="form-group">
                        <label>No. Seri Akhir</label>
                        <input type="text" name="no_seri_akhir" placeholder="Contoh: 000050">
                    </div>
                </div>

                <div class="modal-footer-edit">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-save">Simpan Setoran</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal-overlay">
        <div class="modal-content">
            <button type="button" onclick="closeEditModal()" class="btn-close-modal" aria-label="Tutup Modal"><i
                    class="fas fa-times"></i></button>
            <div class="modal-header">
                <h4>Edit Data Setoran</h4>
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
            document.getElementById('edit_id_jukir_hidden').value = btn.getAttribute('data-id-jukir') || "<?= $id_jukir ?>";
            document.getElementById('edit_tanggal').value = btn.getAttribute('data-tanggal');
            document.getElementById('edit_nominal').value = btn.getAttribute('data-nominal');
            document.getElementById('edit_jenis_kendaraan').value = btn.getAttribute('data-jenis-kendaraan') || '';
            document.getElementById('edit_id_karcis').value = btn.getAttribute('data-id-karcis') || '';
            document.getElementById('edit_jumlah_karcis').value = btn.getAttribute('data-jumlah-karcis') || '';
            document.getElementById('edit_no_seri_awal').value = btn.getAttribute('data-no-seri-awal') || '';
            document.getElementById('edit_no_seri_akhir').value = btn.getAttribute('data-no-seri-akhir') || '';
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

            // Skeleton loader state
            let skeletonHtml = '';
            for (let i = 0; i < 3; i++) {
                skeletonHtml += `
                <tr>
                    <td style="padding:14px 20px;"><span class="skeleton-cell medium"></span></td>
                    <td style="padding:14px 20px;"><span class="skeleton-cell short"></span></td>
                    <td style="padding:14px 20px;"><span class="skeleton-cell medium"></span></td>
                    <td style="padding:14px 20px; text-align:right;"><span class="skeleton-cell full"></span></td>
                    <td style="padding:14px 20px; text-align:center;"><span class="skeleton-cell short"></span></td>
                </tr>`;
            }
            area.innerHTML = skeletonHtml;

            fetch(`config/retribusi.php?bulan=${bulan}&tahun=${tahun}&id_jukir=${idJukir}&ajax_riwayat=1`)
                .then(res => {
                    if (!res.ok) throw new Error('Network error');
                    return res.text();
                })
                .then(data => {
                    area.innerHTML = data;
                })
                .catch(() => {
                    area.innerHTML = "<tr><td colspan='5' style='text-align:center;padding:20px;color:red;'>Gagal mengambil data.</td></tr>";
                });
        }

        // HAPUS SETORAN
        function hapusSetoran(btn) {
            const id = btn.getAttribute('data-id');
            const tgl = btn.getAttribute('data-tgl');
            const nominal = btn.getAttribute('data-nominal');

            Swal.fire({
                title: 'Hapus Riwayat Setoran?',
                html: `
                <div style="text-align:left; font-size:14px; color:#334155; line-height:2; margin-top:10px;">
                    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9;">
                        <span style="color:#94a3b8; font-weight:600;">Tanggal</span>
                        <span style="font-weight:600; color:#1e293b;">${tgl}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0;">
                        <span style="color:#94a3b8; font-weight:600;">Nominal</span>
                        <span style="font-weight:700; color:#ef4444; font-size:16px;">${nominal}</span>
                    </div>
                </div>
                <p style="margin-top:16px; font-size:13px; color:#ef4444; font-weight:600;">
                    Tindakan ini tidak dapat dibatalkan!
                </p>`,
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
                    window.location.href = 'store/hapus_setoran.php?id=' + id;
                }
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
            } else if (status === 'hapus') {
                toast.fire({ icon: 'success', title: 'Riwayat setoran berhasil dihapus!' });
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