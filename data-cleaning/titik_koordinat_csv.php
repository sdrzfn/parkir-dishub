<?php
$inputFile = 'Data-Parkir-Dishub.csv';
$outputFile = 'Titik-Koordinat-Parkir.csv';

if (($handle = fopen($inputFile, "r")) !== FALSE) {
    $outputHandle = fopen($outputFile, "w");

    $headerBaru = ['KODE QRIS', 'LOKASI PARKIR', 'TITIK PARKIR', 'latitude', 'longitude'];
    fputcsv($outputHandle, $headerBaru, ";");

    fgetcsv($handle, 10000, ";");

    $rowCount = 0;

    while (($data = fgetcsv($handle, 10000, ";")) !== FALSE) {
        if (empty(array_filter($data)) || empty($data[10])) {
            continue;
        }

        $qris = $data[14] ?? '';
        $lokasi = $data[10] ?? '';
        $titik = $data[12] ?? '';

        // Baris data baru dengan latitude dan longitude kosong
        $rowBaru = [$qris, $lokasi, $titik, '', ''];

        fputcsv($outputHandle, $rowBaru, ";");
        $rowCount++;
    }

    fclose($handle);
    fclose($outputHandle);

    echo "--- Proses Selesai ---<br>";
    echo "Berhasil mengekstrak <b>$rowCount</b> titik lokasi.<br>";
    echo "File tersimpan sebagai: <b>$outputFile</b>";
} else {
    echo "Error: File $inputFile tidak ditemukan.";
}
?>