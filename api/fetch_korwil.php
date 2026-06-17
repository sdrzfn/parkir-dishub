<?php
$id_korwil = $_GET['id'];

// Endpoint all korwil
$korwil_query = mysqli_query($conn, "SELECT * FROM koordinator_wilayah WHERE id = '$id_korwil'");
$korwil = mysqli_fetch_assoc($korwil_query);

// Endpoint hitung jukir utama di korwil
$jumlah_q1 = mysqli_query($conn, "SELECT COUNT(*) AS total_jukir_utama FROM jukir_utama WHERE id_korwil = '$id_korwil'");
$jumlah_jukir_utama = mysqli_fetch_assoc($jumlah_q1);

// Endpoint hitung jukir pembantu di korwil
$jumlah_q2 = mysqli_query($conn, "SELECT COUNT(*) AS total_jukir_pembantu FROM jukir_pembantu WHERE id_korwil = '$id_korwil'");
$jumlah_jukir_pembantu = mysqli_fetch_assoc($jumlah_q2);

// Endpoint fetch jukir utama sesuai korwil
$sql_utama = "SELECT jukir_utama.*, lokasi.nama_lokasi 
              FROM jukir_utama 
              LEFT JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id 
              WHERE jukir_utama.id_korwil = '$id_korwil'";
$res_utama = mysqli_query($conn, $sql_utama);

// Endpoint fetch jukir pembantu seusai korwil
$sql_pembantu = "SELECT jukir_pembantu.*, jukir_utama.nama_lengkap as nama_induk 
                 FROM jukir_pembantu 
                 LEFT JOIN jukir_utama ON jukir_pembantu.id_utama = jukir_utama.id 
                 WHERE jukir_pembantu.id_korwil = '$id_korwil'";
$res_pembantu = mysqli_query($conn, $sql_pembantu);