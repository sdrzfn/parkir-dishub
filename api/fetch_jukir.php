<?php

// Fungsi search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kecamatan = isset($_GET['kecamatan']) ? trim($_GET['kecamatan']) : '';
$titik_parkir = isset($_GET['titik_parkir']) ? trim($_GET['titik_parkir']) : '';
$where = "WHERE 1=1";
$tipe_jukir = isset($_GET['tipe_jukir']) ? trim($_GET['tipe_jukir']) : '';

// 2. Filter dasar untuk relasi lokasi dan wilayah
$where_base = " WHERE 1=1";
if ($kecamatan !== '') {
    $k = mysqli_real_escape_string($conn, $kecamatan);
    $where_base .= " AND lokasi.kecamatan = '$k'";
}
if ($titik_parkir !== '') {
    $tp = mysqli_real_escape_string($conn, $titik_parkir);
    $where_base .= " AND lokasi.titik_parkir = '$tp'";
}

// 3. Racik klausa WHERE pencarian teks secara terpisah berdasarkan TIPE JUKIR
$where_search = "";
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);

    if ($tipe_jukir === 'pembantu') {
        // Jika sedang di tab pembantu, cari langsung secara linear karena tabelnya di-join
        $where_search = " AND (jukir_utama.nama_lengkap LIKE '%$s%' 
                      OR jukir_utama.nik LIKE '%$s%'
                      OR lokasi.nama_lokasi LIKE '%$s%'
                      OR lokasi.kode_qris LIKE '%$s%'
                      OR jukir_pembantu.nama_pembantu LIKE '%$s%'
                      OR jukir_pembantu.nik LIKE '%$s%')";
    } else {
        // JIKA DI TAB UTAMA: Cari di data utama, ATAU cari utama yang ID-nya dimiliki oleh pembantu yang dicari (SUBQUERY)
        $where_search = " AND (jukir_utama.nama_lengkap LIKE '%$s%' 
                      OR jukir_utama.nik LIKE '%$s%'
                      OR lokasi.nama_lokasi LIKE '%$s%'
                      OR lokasi.kode_qris LIKE '%$s%'
                      OR jukir_utama.id IN (
                          SELECT id_utama FROM jukir_pembantu 
                          WHERE nama_pembantu LIKE '%$s%' OR nik LIKE '%$s%'
                      ))";
    }
}

// Gabungkan filter dasar dan filter pencarian teks
$where_final = $where_base . $where_search;

// 4. Hitung TOTAL ROW untuk Pagination (Pastikan query COUNT ini menggunakan $where_final)
if ($tipe_jukir === 'pembantu') {
    $count_sql = "SELECT COUNT(*) as total FROM jukir_pembantu 
                  INNER JOIN jukir_utama ON jukir_pembantu.id_utama = jukir_utama.id
                  INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id $where_final";
} else {
    $count_sql = "SELECT COUNT(*) as total FROM jukir_utama 
                  INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id $where_final";
}
$total_result = mysqli_query($conn, $count_sql);
$total_row = mysqli_fetch_assoc($total_result)['total'];

// 5. Atur Pagination Limit & Offset
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = ceil($total_row / $limit);

// 6. Eksekusi Query Utama Data
if ($tipe_jukir === 'pembantu') {
    $sql = "SELECT 
                jukir_utama.id_lokasi,
                jukir_utama.nama_lengkap AS nama_utama,
                jukir_utama.no_telp AS telp_utama,
                lokasi.nama_lokasi,
                jukir_pembantu.*
            FROM jukir_pembantu
            INNER JOIN jukir_utama ON jukir_pembantu.id_utama = jukir_utama.id
            INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id
            $where_final
            ORDER BY jukir_pembantu.id DESC
            LIMIT $offset, $limit";
} else {
    $sql = "SELECT 
                jukir_utama.*, 
                lokasi.nama_lokasi, 
                lokasi.kode_qris,
                lokasi.titik_parkir
            FROM jukir_utama
            INNER JOIN lokasi ON jukir_utama.id_lokasi = lokasi.id
            $where_final
            ORDER BY jukir_utama.id DESC
            LIMIT $offset, $limit";
}

$result = mysqli_query($conn, $sql);
$list_lokasi = mysqli_query($conn, "SELECT id, nama_lokasi, kode_qris FROM lokasi ORDER BY nama_lokasi ASC");
$list_wilayah = mysqli_query($conn, "SELECT DISTINCT wilayah FROM koordinator_wilayah ORDER BY wilayah ASC");