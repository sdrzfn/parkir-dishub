<?php
include '../config/db.php';

set_time_limit(0);

$xlsx_path = __DIR__ . '/Titik-Koordinat-terbaru.xlsx';

if (!file_exists($xlsx_path)) {
    die("❌ File tidak ditemukan: $xlsx_path");
}

// ============================================================
// BACA XLSX (ZipArchive + SimpleXML — built-in PHP, tanpa library)
// ============================================================

function readXlsxRows(string $path, int $startRow = 3): array
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        die("❌ Gagal membuka file xlsx.");
    }

    // Shared strings — semua teks di xlsx disimpan di file ini
    $sharedStrings = [];
    $ssXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssXml) {
        $ss = simplexml_load_string($ssXml);
        foreach ($ss->si as $si) {
            $text = '';
            foreach ($si->r as $r) {
                $text .= (string) $r->t;
            }
            if (empty($text)) {
                $text = (string) $si->t;
            }
            $sharedStrings[] = $text;
        }
    }

    // Sheet pertama
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if (!$sheetXml) {
        die("❌ Gagal membaca sheet1.xml");
    }

    $sheet = simplexml_load_string($sheetXml);
    $rows  = [];

    foreach ($sheet->sheetData->row as $row) {
        $rowIndex = (int) $row['r'];
        if ($rowIndex < $startRow) continue;

        $cells = [];
        foreach ($row->c as $cell) {
            preg_match('/([A-Z]+)/', (string) $cell['r'], $m);
            $colIndex = columnLetterToIndex($m[1]);

            $type = (string) $cell['t'];
            $v    = (string) $cell->v;

            if ($type === 's') {
                // Shared string → ambil teks
                $val = $sharedStrings[(int) $v] ?? '';
            } elseif (is_numeric($v) && $type !== 'str') {
                // Numerik — pertahankan sebagai float penuh
                // Gunakan bcmath-style string dulu agar tidak kehilangan presisi
                $val = $v + 0; // PHP float (64-bit)
            } else {
                $val = $v;
            }

            $cells[$colIndex] = $val;
        }

        // Pastikan selalu ada 6 kolom (A-F)
        $row_arr = [];
        for ($i = 0; $i <= 5; $i++) {
            $row_arr[] = $cells[$i] ?? null;
        }
        $rows[] = $row_arr;
    }

    return $rows;
}

function columnLetterToIndex(string $col): int
{
    $col   = strtoupper($col);
    $index = 0;
    for ($i = 0; $i < strlen($col); $i++) {
        $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    return $index - 1; // 0-based: A=0, B=1, ...
}

/**
 * Konversi koordinat berskala besar ke desimal geografis.
 *
 * Excel/xlsx menyimpan koordinat seperti -7457356506994820
 * yang sebenarnya adalah -7.457356507 (dikali 10^15 karena presisi floating
 * point hilang saat disimpan tanpa format khusus).
 *
 * Fungsi ini mencari pembagi 10^n yang menghasilkan nilai dalam range
 * koordinat Jawa Timur. Tidak ada pembulatan — nilai dikembalikan apa adanya.
 *
 * @param  mixed  $val   Nilai mentah dari xlsx
 * @param  string $type  'lat' atau 'lng'
 * @return float|null    Koordinat desimal, atau null jika tidak bisa dikonversi
 */
function smartConvertCoord($val, string $type): ?float
{
    if ($val === null || $val === '' || $val === false) return null;

    $val = (float) $val;
    if ($val == 0.0) return null;

    // Range koordinat Jawa Timur (Sidoarjo sekitarnya)
    [$min, $max] = $type === 'lat' ? [-9.0, -6.0] : [110.0, 115.0];

    for ($exp = 0; $exp <= 17; $exp++) {
        $result = $val / (10 ** $exp);
        if ($result >= $min && $result <= $max) {
            return $result; // presisi penuh, tanpa round
        }
    }

    return null;
}

// ============================================================
// PROSES DATA
// ============================================================

$rows = readXlsxRows($xlsx_path, startRow: 3);

echo "<pre style='background:#0f172a; color:#94a3b8; padding:20px; font-family:monospace; font-size:13px; line-height:1.6;'>";
echo "<span style='color:#f8fafc; font-weight:bold;'>=== IMPORT KOORDINAT LOKASI ===</span>\n";
echo "File    : $xlsx_path\n";
echo "Baris   : " . count($rows) . " data\n\n";

$updated   = 0;
$not_found = 0;
$no_coord  = 0;
$errors    = [];

foreach ($rows as $col) {
    // Kolom: [0]=No, [1]=Nama Lokasi, [2]=Titik Parkir, [3]=Latitude, [4]=Longitude, [5]=Alamat
    $nama_lokasi = isset($col[1]) ? trim((string) $col[1]) : '';

    // Skip baris kosong atau header yang terlewat
    if (empty($nama_lokasi) || strtolower($nama_lokasi) === 'lokasi parkir') continue;

    $lat = smartConvertCoord($col[3] ?? null, 'lat');
    $lng = smartConvertCoord($col[4] ?? null, 'lng');

    // Lokasi tanpa koordinat — tetap laporkan, set ke NULL di DB
    if ($lat === null || $lng === null) {
        // Update ke NULL supaya tidak ada sisa data lama yang salah
        $nama_esc = mysqli_real_escape_string($conn, $nama_lokasi);
        $res = mysqli_query($conn, "SELECT id FROM lokasi WHERE nama_lokasi = '$nama_esc' LIMIT 1");
        $found = mysqli_fetch_assoc($res);

        if (!$found) {
            // Coba fuzzy match
            $res = mysqli_query($conn, "SELECT id FROM lokasi 
                                        WHERE nama_lokasi LIKE '%$nama_esc%' 
                                           OR '$nama_esc' LIKE CONCAT('%', nama_lokasi, '%') 
                                        LIMIT 1");
            $found = mysqli_fetch_assoc($res);
        }

        if ($found) {
            mysqli_query($conn, "UPDATE lokasi SET latitude = NULL, longitude = NULL WHERE id = {$found['id']}");
            echo "<span style='color:#f59e0b;'>⚠ NULL  </span> $nama_lokasi (koordinat tidak tersedia di file)\n";
        } else {
            echo "<span style='color:#64748b;'>– SKIP  </span> $nama_lokasi (tidak ada koordinat & tidak ada di DB)\n";
        }
        $no_coord++;
        continue;
    }

    // Cari lokasi di DB — exact match
    $nama_esc = mysqli_real_escape_string($conn, $nama_lokasi);
    $res      = mysqli_query($conn, "SELECT id, nama_lokasi FROM lokasi WHERE nama_lokasi = '$nama_esc' LIMIT 1");
    $lokasi   = mysqli_fetch_assoc($res);

    if (!$lokasi) {
        // Fuzzy match: DB mengandung nama xlsx, atau nama xlsx mengandung DB
        $res    = mysqli_query($conn, "SELECT id, nama_lokasi FROM lokasi 
                                       WHERE nama_lokasi LIKE '%$nama_esc%' 
                                          OR '$nama_esc' LIKE CONCAT('%', nama_lokasi, '%') 
                                       LIMIT 1");
        $lokasi = mysqli_fetch_assoc($res);
    }

    if (!$lokasi) {
        echo "<span style='color:#ef4444;'>✗ MISSING</span> $nama_lokasi\n";
        $errors[] = $nama_lokasi;
        $not_found++;
        continue;
    }

    // OVERWRITE — tanpa kondisi, langsung update
    $sql = "UPDATE lokasi SET latitude = $lat, longitude = $lng WHERE id = {$lokasi['id']}";

    if (mysqli_query($conn, $sql)) {
        echo "<span style='color:#10b981;'>✓ UPDATED</span> {$lokasi['nama_lokasi']}\n";
        echo "          <span style='color:#475569;'>lat=$lat | lng=$lng</span>\n";
        $updated++;
    } else {
        echo "<span style='color:#ef4444;'>✗ ERROR  </span> [{$lokasi['id']}] " . mysqli_error($conn) . "\n";
    }
}

echo "\n<span style='color:#f8fafc; font-weight:bold;'>=== RINGKASAN ===</span>\n";
echo "<span style='color:#10b981;'>✓ Berhasil diupdate    : $updated lokasi</span>\n";
echo "<span style='color:#f59e0b;'>⚠ Tanpa koordinat     : $no_coord lokasi (di-set NULL)</span>\n";
echo "<span style='color:#ef4444;'>✗ Tidak ada di DB     : $not_found lokasi</span>\n";

if (!empty($errors)) {
    echo "\n<span style='color:#f8fafc;'>Daftar nama tidak cocok dengan database:</span>\n";
    foreach ($errors as $e) {
        echo "  <span style='color:#64748b;'>- $e</span>\n";
    }
    echo "\n<span style='color:#94a3b8;'>→ Pastikan nama_lokasi di DB sama persis dengan file xlsx.</span>\n";
    echo "<span style='color:#94a3b8;'>  Gunakan UPDATE manual atau sesuaikan nama di salah satunya.</span>\n";
}

echo "\n<span style='color:#10b981;'>Selesai.</span>\n";
echo "</pre>";