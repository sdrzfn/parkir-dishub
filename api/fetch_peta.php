<?php
$sql = "SELECT l.*, j.nama_lengkap AS jukir_utama 
        FROM lokasi l 
        LEFT JOIN jukir_utama j ON l.id = j.id_lokasi 
        WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL";
$result = mysqli_query($conn, $sql);
$lokasi_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $lokasi_data[] = $row;
}