<?php
/**
 * Script untuk mengekstraksi lokasi parkir dari CSV ke XLS
 * Serta menyiapkan kolom Latitude dan Longitude
 */

$csvFile = 'Data-Parkir-Dishub.csv';
$outputFile = 'titik_koordinat_parkir.xls';

if (!file_exists($csvFile)) {
    die("Error: File $csvFile tidak ditemukan!");
}

// 1. Set Header agar browser mendownload file sebagai Excel
header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=\"$outputFile\"");
header("Pragma: no-cache");
header("Expires: 0");

// 2. Membuka file CSV
$file = fopen($csvFile, "r");

// 3. Mulai output tabel Excel
echo '<table border="1">';
echo '<thead>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <th>Lokasi Parkir</th>
            <th>Titik Parkir</th>
            <th>Latitude</th>
            <th>Longitude</th>
        </tr>
      </thead>';
echo '<tbody>';

// Lewati baris pertama (header CSV)
fgetcsv($file, 10000, ";");

while (($column = fgetcsv($file, 10000, ";")) !== FALSE) {
    // Lewati jika baris kosong
    if (empty(array_filter($column))) continue;

    // Ambil data berdasarkan indeks yang sesuai dengan struktur CSV kamu:
    // Kolom 10: LOKASI PARKIR
    // Kolom 12: TITIK PARKIR
    $lokasi = isset($column[10]) ? htmlspecialchars($column[10]) : '';
    $titik  = isset($column[12]) ? htmlspecialchars($column[12]) : '';

    // Cetak baris ke tabel Excel (Latitude & Longitude dibiarkan kosong)
    echo '<tr>';
    echo '<td>' . $lokasi . '</td>';
    echo '<td>' . $titik . '</td>';
    echo '<td></td>'; // Kolom Latitude Kosong
    echo '<td></td>'; // Kolom Longitude Kosong
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

fclose($file);
exit;