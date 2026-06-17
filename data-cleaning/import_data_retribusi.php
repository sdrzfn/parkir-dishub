<?php
include '../config/db.php';
set_time_limit(0);

// Nama file CSV Global terbaru
$filename = "Data-Parkir-Utama-Pembantu-beserta-Target-Bulanannya-GLOBAL.csv";

if (!file_exists($filename)) {
    die("File $filename tidak ditemukan.");
}

// Fungsi otomatis deteksi delimiter (, atau ;)
function getDelimiter($file) {
    $handle = fopen($file, 'r');
    $line = fgets($handle);
    fclose($handle);
    return (substr_count($line, ';') > substr_count($line, ',')) ? ';' : ',';
}

$delimiter = getDelimiter($filename);
$file = fopen($filename, "r");

// Ambil baris ke-1 (Header Utama)
$header1 = fgetcsv($file, 10000, $delimiter);
// Ambil baris ke-2 (Sub-Header Termin)
$header2 = fgetcsv($file, 10000, $delimiter);

// --- PENCARIAN INDEKS KOLOM OTOMATIS ---
// Mencegah Undefined Key / Error jika format kolom dari Dishub bergeser
$idx_nama = 3; // Default Index Nama P.P Utama
$idx_nik = 4;  // Default Index NIK P.P Utama
$idx_target = 42; // Fallback jika tidak ditemukan

// Cari posisi kolom '/bulan' secara dinamis di baris kedua
if ($header2) {
    foreach ($header2 as $index => $col_name) {
        if (trim(strtolower($col_name)) === '/bulan') {
            $idx_target = $index;
            break;
        }
    }
}

echo "<h3>Sinkronisasi Target Bulanan via NIK</h3>";
echo "<p>Metode: Update Target Saja (Sistem menemukan kolom target di index ke-{$idx_target})</p><hr>";

$updated = 0;
$not_found = 0;
$row_idx = 2; // Mulai dari baris data ke-3

while (($column = fgetcsv($file, 10000, $delimiter)) !== FALSE) {
    $row_idx++;

    // Pastikan kolom NIK ada isinya, jika kosong (berarti itu Jukir Pembantu) langsung dilewati
    if (!isset($column[$idx_nik]) || empty(trim($column[$idx_nik]))) {
        continue;
    }

    $nama_csv     = mysqli_real_escape_string($conn, trim($column[$idx_nama] ?? 'Tanpa Nama'));
    $nik          = mysqli_real_escape_string($conn, trim($column[$idx_nik]));
    
    // Ambil data target sesuai index dinamis yang ditemukan
    $target_raw   = isset($column[$idx_target]) ? $column[$idx_target] : '0';
    // Hapus format Rupiah, koma, dan spasi agar jadi angka murni
    $target_clean = (int) preg_replace('/[^0-9]/', '', $target_raw);

    if ($target_clean > 0) {
        // Cari jukir HANYA berdasarkan NIK
        $check = mysqli_query($conn, "SELECT id FROM jukir_utama WHERE nik = '$nik'");

        if (mysqli_num_rows($check) > 0) {
            $existing = mysqli_fetch_assoc($check);
            $id_jukir = $existing['id'];

            // PROSES UPDATE: HANYA target_bulanan, membiarkan SPMT apa adanya
            $sql_update = "UPDATE jukir_utama SET target_bulanan = '$target_clean' WHERE id = '$id_jukir'";
            
            if (mysqli_query($conn, $sql_update)) {
                echo "<span style='color:green'>[UPDATE]</span> $nama_csv (NIK: $nik) -> Target: Rp " . number_format($target_clean, 0, ',', '.') . "<br>";
                $updated++;
            }
        } else {
            echo "<span style='color:red'>[NOT FOUND]</span> Baris $row_idx: NIK $nik ($nama_csv) tidak terdaftar di database.<br>";
            $not_found++;
        }
    }
}

fclose($file);
echo "<hr><b>Proses Selesai!</b><br>";
echo "Total Target Jukir Diperbarui: <strong>$updated</strong> <br>";
echo "Total Jukir Tidak Ditemukan: <strong>$not_found</strong>";
?>