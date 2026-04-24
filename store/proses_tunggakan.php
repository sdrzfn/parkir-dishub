<?php
include '../config/db.php';
include '../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_jukir = mysqli_real_escape_string($conn, $_POST['id_jukir']);
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis_surat']); // Contoh: SP1, SP2
    $metode = mysqli_real_escape_string($conn, $_POST['metode']);
    $admin = $_SESSION['nama'];
    $nominal = isset($_POST['nominal_tunggakan']) ? $_POST['nominal_tunggakan'] : 0;

    // --- LOGIKA PENANGGALAN INDONESIA ---
    $bulan_indo = [
        1 => "JANUARI",
        2 => "FEBRUARI",
        3 => "MARET",
        4 => "APRIL",
        5 => "MEI",
        6 => "JUNI",
        7 => "JULI",
        8 => "AGUSTUS",
        9 => "SEPTEMBER",
        10 => "OKTOBER",
        11 => "NOVEMBER",
        12 => "DESEMBER"
    ];

    $tgl_skrg = date('j');
    $bln_skrg_angka = (int) date('n');
    $thn_skrg = date('Y');

    // Periode tunggakan biasanya adalah bulan lalu
    $bln_lalu_angka = ($bln_skrg_angka == 1) ? 12 : $bln_skrg_angka - 1;
    $thn_lalu = ($bln_skrg_angka == 1) ? $thn_skrg - 1 : $thn_skrg;

    $nama_bulan_lalu = $bulan_indo[$bln_lalu_angka];
    $nama_bulan_skrg = $bulan_indo[$bln_skrg_angka];

    // --- AMBIL DATA JUKIR ---
    $q_jukir = mysqli_query($conn, "SELECT j.nama_lengkap, l.nama_lokasi FROM jukir_utama j 
                                    LEFT JOIN lokasi l ON j.id_lokasi = l.id WHERE j.id = $id_jukir");
    $data_jukir = mysqli_fetch_assoc($q_jukir);
    $nama_jukir_clean = str_replace(' ', '-', strtoupper($data_jukir['nama_lengkap']));

    // --- PENYESUAIAN NAMA FILE SESUAI DOKUMEN ---
    // $file_sp_name = "SURAT-PERINGATAN-" . strtoupper($jenis) . "-" . $nama_jukir_clean . "-" . $nama_bulan_skrg . "-" . $thn_skrg . ".pdf";
    $file_sp_name = "SURAT-PENAGIHAN-RETRIBUSI-PARKIR-BULAN-FEBRUARI 2026.pdf";
    // $file_tagihan_name = "SURAT-PENAGIHAN-RETRIBUSI-PARKIR-BULAN-" . $nama_bulan_lalu . "-" . $thn_lalu . ".pdf";
    $file_tagihan_name = "Surat-Peringatan-Jukir.docx";

    // --- SIMPAN LOG KE DATABASE ---
    $keterangan = "Otomatis: Tagihan periode $nama_bulan_lalu $thn_lalu | Dikirim $tgl_skrg $nama_bulan_skrg $thn_skrg";

    $query = "INSERT INTO log_aksi_jukir (id_jukir, jenis_surat, file_sp, file_tagihan, admin_input, keterangan) 
              VALUES ('$id_jukir', '$jenis', '$file_sp_name', '$file_tagihan_name', '$admin', '$keterangan')";

    if (mysqli_query($conn, $query)) {
        mysqli_query($conn, "UPDATE jukir_utama SET status_peringatan = '$jenis' WHERE id = '$id_jukir'");

        if ($metode == 'WhatsApp') {
            // Pesan WA menggunakan tanggal yang sesuai dokumen
            $pesan_teks = "*DISHUB KABUPATEN SIDOARJO*\n"
                . "Sidoarjo, $tgl_skrg " . ucfirst(strtolower($nama_bulan_skrg)) . " $thn_skrg\n\n"
                . "Yth. Pak/Bu *{$data_jukir['nama_lengkap']}*\n"
                . "Di Tempat\n\n"
                . "Berdasarkan evaluasi administrasi bulan *$nama_bulan_lalu $thn_lalu*, ditemukan kewajiban setoran sebesar *Rp " . number_format($nominal, 0, ',', '.') . "* yang belum terselesaikan.\n\n"
                . "Terlampir kami sampaikan secara otomatis:\n"
                . "1. *{$file_sp_name}*\n"
                . "2. *{$file_tagihan_name}*\n\n"
                . "Mohon segera melakukan pelunasan sebelum batas waktu yang ditentukan. Terima kasih.";

            $pesan_encoded = urlencode($pesan_teks);
            $no_wa = "6285784283713"; // Sesuaikan dengan logika nomor WA jukir Anda

            echo "<script>
                alert('Dokumen $nama_bulan_lalu berhasil digenerate dan dicatat.');
                window.open('https://wa.me/{$no_wa}?text={$pesan_encoded}', '_blank');
                window.location.href = '../retribusi-detail.php?id=$id_jukir&status=success';
            </script>";
        } else {
            header("Location: ../retribusi-detail.php?id=$id_jukir&status=success");
        }
    }
}