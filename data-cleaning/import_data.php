<?php
include 'config/db.php';

// Tingkatkan batas waktu eksekusi agar tidak timeout jika data banyak
set_time_limit(0);

$filename = "Data-Parkir-Dishub.csv"; // Nama file CSV kamu
$file = fopen($filename, "r");

// Lewati baris pertama (header)
fgetcsv($file, 10000, ";");

echo "Proses import dimulai...<br>";

$last_id_lokasi = null;
$last_id_utama = null;

while (($column = fgetcsv($file, 10000, ";")) !== FALSE) {
    // Lewati jika baris benar-benar kosong
    if (empty(array_filter($column)))
        continue;

    // --- MAPPING DATA BERDASARKAN STRUKTUR GLOBAL.CSV ---

    // Data Lokasi (Hanya diisi jika kolom Lokasi tidak kosong)
    if (!empty($column[10])) {
        $nama_lokasi = mysqli_real_escape_string($conn, $column[10]);
        $titik = mysqli_real_escape_string($conn, $column[12]);
        $qris = mysqli_real_escape_string($conn, $column[14]);
        $nominal = (float) str_replace(['.', ','], ['', '.'], $column[15]);
        $target = (float) str_replace(['.', ','], ['', '.'], $column[19]);
        $terbilang = mysqli_real_escape_string($conn, $column[20]);

        $sql_loc = "INSERT INTO lokasi (kode_qris, nama_lokasi, titik_parkir, nominal_retribusi, target_bulanan, terbilang_target) 
                    VALUES ('$qris', '$nama_lokasi', '$titik', '$nominal', '$target', '$terbilang')
                    ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)";
        mysqli_query($conn, $sql_loc);
        $last_id_lokasi = mysqli_insert_id($conn);
    }

    // Data Jukir Utama (Hanya diisi jika nama jukir utama ada)
    if (!empty($column[1])) {
        $no_spmt = mysqli_real_escape_string($conn, $column[0]);
        $nama_u = mysqli_real_escape_string($conn, $column[1]);
        $nik_u = mysqli_real_escape_string($conn, $column[2]);
        $ttl_u = mysqli_real_escape_string($conn, $column[3]);
        $almt_u = mysqli_real_escape_string($conn, $column[4]);
        $telp_u = mysqli_real_escape_string($conn, $column[5]);

        $sql_u = "INSERT INTO jukir_utama (no_spmt, nik, nama_lengkap, ttl, alamat, no_telp, id_lokasi) 
                  VALUES ('$no_spmt', '$nik_u', '$nama_u', '$ttl_u', '$almt_u', '$telp_u', '$last_id_lokasi')
                  ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)";
        mysqli_query($conn, $sql_u);
        $last_id_utama = mysqli_insert_id($conn);
    }

    // Data Jukir Pembantu (Jika kolom nama pembantu di index 7 tidak kosong)
    if (!empty($column[7])) {
        $nama_p = mysqli_real_escape_string($conn, $column[7]);
        $almt_p = mysqli_real_escape_string($conn, $column[8]);
        $nik_p = mysqli_real_escape_string($conn, $column[9]);

        $sql_p = "INSERT IGNORE INTO jukir_pembantu (id_utama, nik, nama_pembantu, alamat_pembantu) 
                  VALUES ('$last_id_utama', '$nik_p', '$nama_p', '$almt_p')";
        mysqli_query($conn, $sql_p);
    }

    // Data Setoran (Tanggal 1, 11, 21)
    if ($last_id_lokasi) {
        $tgl1 = (float) str_replace(['.', ','], ['', '.'], $column[16]);
        $tgl11 = (float) str_replace(['.', ','], ['', '.'], $column[17]);
        $tgl21 = (float) str_replace(['.', ','], ['', '.'], $column[18]);
        $bulan = date('Y-m-01');

        $sql_s = "INSERT INTO setoran_retribusi (id_lokasi, bulan_tahun, setoran_tgl_1, setoran_tgl_11, setoran_tgl_21) 
                  VALUES ('$last_id_lokasi', '$bulan', '$tgl1', '$tgl11', '$tgl21')";
        mysqli_query($conn, $sql_s);
    }

    $row_count++;
}

fclose($file);
echo "<p>Berhasil mengimpor $row_count baris data.</p>";
?>