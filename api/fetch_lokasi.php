<?php

$bulan_ini = date('F Y');

// Pagination
$limit = 25;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filtering dan Searching
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kecamatan = isset($_GET['kecamatan']) ? trim($_GET['kecamatan']) : '';
$titik_parkir = isset($_GET['titik_parkir']) ? trim($_GET['titik_parkir']) : '';

$where_clauses = ["1=1"];

if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "(l.kode_qris LIKE '%$s%' OR l.nama_lokasi LIKE '%$s%')";
}

if ($kecamatan !== '') {
    $k = mysqli_real_escape_string($conn, $kecamatan);
    $where_clauses[] = "kw.wilayah = '$k'";
}

if ($titik_parkir !== '') {
    $tp = mysqli_real_escape_string($conn, $titik_parkir);
    $where_clauses[] = "l.titik_parkir = '$tp'";
}

$where = "WHERE " . implode(" AND ", $where_clauses);
$base_join = "FROM lokasi l
              LEFT JOIN jukir_utama ju        ON l.id = ju.id_lokasi
              LEFT JOIN koordinator_wilayah kw ON ju.id_korwil = kw.id";

$total_result = mysqli_query($conn, "SELECT COUNT(DISTINCT l.id) AS total $base_join $where");
if (!$total_result)
    die("Query error: " . mysqli_error($conn));
$total_row = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_row / $limit);

$sql = "SELECT 
            l.*,
            ju.nama_lengkap  AS nama_jukir,
            kw.wilayah       AS kecamatan
        $base_join
        $where
        ORDER BY l.id ASC
        LIMIT $offset, $limit";
$result = mysqli_query($conn, $sql);
if (!$result)
    die("Query error: " . mysqli_error($conn));

$list_wilayah = mysqli_query($conn, "SELECT DISTINCT wilayah FROM koordinator_wilayah WHERE wilayah IS NOT NULL ORDER BY wilayah ASC");