<?php
include 'config/db.php';
include 'config/auth.php';
checkLogin();

$id_jukir = (int) ($_GET['id']   ?? 0);
$kode     = $_GET['kode'] ?? 'tagihan';

$q = mysqli_query($conn, "SELECT j.*, l.nama_lokasi, l.kode_qris, kw.wilayah
                           FROM jukir_utama j
                           LEFT JOIN lokasi l ON j.id_lokasi = l.id
                           LEFT JOIN koordinator_wilayah kw ON j.id_korwil = kw.id
                           WHERE j.id = $id_jukir");
$d = mysqli_fetch_assoc($q);
if (!$d) die('Data tidak ditemukan');

$judul_map = [
    'tagihan' => 'SURAT TAGIHAN',
    'sp1'     => 'SURAT PERINGATAN PERTAMA (SP-1)',
    'sp2'     => 'SURAT PERINGATAN KEDUA (SP-2)',
    'sp3'     => 'SURAT PERINGATAN KETIGA (SP-3)',
];
$judul = $judul_map[$kode] ?? 'SURAT TAGIHAN';
$tanggal_cetak = date('d F Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $judul ?> — <?= $d['nama_lengkap'] ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; margin: 2cm; color: #000; }
        .kop { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop h2 { margin: 0; font-size: 14pt; }
        .kop p  { margin: 2px 0; font-size: 10pt; }
        h3.judul { text-align: center; text-decoration: underline; margin: 20px 0; font-size: 13pt; }
        table.detail { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table.detail td { padding: 5px 10px; vertical-align: top; }
        table.detail td:first-child { width: 35%; }
        table.detail td:nth-child(2) { width: 5%; }
        .ttd { margin-top: 60px; float: right; text-align: center; width: 250px; }
        .ttd .garis { margin-top: 60px; border-top: 1px solid #000; }
        @media print {
            .no-print { display: none; }
            body { margin: 1.5cm; }
        }
    </style>
</head>
<body>

<div class="kop">
    <h2>PEMERINTAH KABUPATEN SIDOARJO</h2>
    <h2>DINAS PERHUBUNGAN</h2>
    <p>Jl. Jaksa Agung Suprapto No. 1, Sidoarjo | Telp. (031) 8921155</p>
</div>

<h3 class="judul"><?= $judul ?></h3>
<p>Nomor: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/<?= date('Y') ?></p>

<p>Kepada Yth.<br>
<strong><?= htmlspecialchars($d['nama_lengkap']) ?></strong><br>
di tempat</p>

<p>Dengan hormat,<br>
Bersama surat ini kami sampaikan bahwa berdasarkan data retribusi parkir, Saudara belum
memenuhi kewajiban setoran retribusi parkir untuk periode bulan <?= date('F Y') ?>.</p>

<table class="detail">
    <tr><td>Nama Juru Parkir</td><td>:</td><td><?= htmlspecialchars($d['nama_lengkap']) ?></td></tr>
    <tr><td>NIK</td><td>:</td><td><?= $d['nik'] ?></td></tr>
    <tr><td>Lokasi Parkir</td><td>:</td><td><?= htmlspecialchars($d['nama_lokasi']) ?></td></tr>
    <tr><td>Kode QRIS</td><td>:</td><td><?= $d['kode_qris'] ?></td></tr>
    <tr><td>Wilayah</td><td>:</td><td><?= htmlspecialchars($d['wilayah'] ?? '-') ?></td></tr>
    <tr><td>Target Bulanan</td><td>:</td><td>Rp <?= number_format($d['target_bulanan'], 0, ',', '.') ?></td></tr>
</table>

<p>Demikian surat ini kami sampaikan. Atas perhatian dan kerjasamanya diucapkan terima kasih.</p>

<div class="ttd">
    <p>Sidoarjo, <?= $tanggal_cetak ?></p>
    <p>Kepala Dinas Perhubungan<br>Kabupaten Sidoarjo</p>
    <div class="garis"></div>
    <p><strong>NIP. ___________________</strong></p>
</div>

<div style="clear:both; margin-top: 40px;" class="no-print">
    <button onclick="window.print()" style="padding:10px 24px; background:#2563eb; color:white; border:none; border-radius:8px; cursor:pointer; font-size:14px;">
        🖨️ Cetak / Simpan PDF
    </button>
</div>

</body>
</html>