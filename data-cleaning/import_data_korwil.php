<?php
include '../config/db.php';
set_time_limit(0);

$file_mapping = [
    "Data Petugas Parkir Utama & Pembantu beserta Target Bulanannya-Sidoarjo-1.csv" => 1,
    "Data Petugas Parkir Utama & Pembantu beserta Target Bulanannya-Sidoarjo-2.csv" => 2,
    "Data Petugas Parkir Utama & Pembantu beserta Target Bulanannya-Porong.csv" => 3,
    "Data Petugas Parkir Utama & Pembantu beserta Target Bulanannya-Waru.csv" => 4,
    "Data Petugas Parkir Utama & Pembantu beserta Target Bulanannya-Krian.csv" => 5,
];

echo "<h2>Proses Update ID Korwil Dimulai</h2><hr>";

foreach ($file_mapping as $filename => $id_korwil) {
    if (!file_exists($filename)) {
        echo "<p style='color:orange'>File tidak ditemukan: $filename (Lewati...)</p>";
        continue;
    }

    $file = fopen($filename, "r");

    // --- PERBAIKAN: Lewati 3 baris header agar data terbaca tepat di baris ke-4 ---
    fgetcsv($file, 10000, ";");
    fgetcsv($file, 10000, ";");
    fgetcsv($file, 10000, ";");

    $count_utama = 0;
    $count_pembantu = 0;

    // --- PERBAIKAN: Gunakan ";" sebagai delimiter ---
    while (($column = fgetcsv($file, 10000, ";")) !== FALSE) {

        $nik_utama = isset($column[4]) ? trim($column[4]) : '';
        $nik_pembantu = isset($column[9]) ? trim($column[9]) : '';

        // Bersihkan data dari kemungkinan SQL Injection
        $nik_utama = mysqli_real_escape_string($conn, $nik_utama);
        $nik_pembantu = mysqli_real_escape_string($conn, $nik_pembantu);

        // 1. Update Jukir Utama
        if (!empty($nik_utama) && $nik_utama != '-' && $nik_utama != 'NIK P.P Utama') {
            $sql_u = "UPDATE jukir_utama SET id_korwil = '$id_korwil' WHERE nik = '$nik_utama'";
            if (mysqli_query($conn, $sql_u) && mysqli_affected_rows($conn) > 0) {
                $count_utama++;
            }
        }

        // 2. Update Jukir Pembantu
        if (!empty($nik_pembantu) && $nik_pembantu != '-' && $nik_pembantu != 'NIK P.P Pembantu') {
            $sql_p = "UPDATE jukir_pembantu SET id_korwil = '$id_korwil' WHERE nik = '$nik_pembantu'";
            if (mysqli_query($conn, $sql_p) && mysqli_affected_rows($conn) > 0) {
                $count_pembantu++;
            }
        }
    }

    fclose($file);
    echo "<b>Selesai memproses:</b> $filename <br>";
    echo "Hasil: $count_utama Utama terupdate, $count_pembantu Pembantu terupdate.<br><br>";
}

echo "<hr><h3>Semua proses selesai!</h3>";
?>