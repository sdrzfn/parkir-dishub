<?php
// ── Filters ───────────────────────────────────────────────
$search      = isset($_GET['search'])     ? trim($_GET['search'])     : '';
$filter_kec  = isset($_GET['kecamatan'])  ? trim($_GET['kecamatan'])  : '';
$filter_status = isset($_GET['status'])   ? trim($_GET['status'])     : '';

$limit  = 25;
$page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$bulan_ini = date('m');
$tahun_ini = date('Y');

// ── WHERE builder ─────────────────────────────────────────
$where = "WHERE 1=1";

if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (ju.nama_lengkap LIKE '%$s%'
                  OR l.nama_lokasi   LIKE '%$s%'
                  OR l.kode_qris     LIKE '%$s%')";
}

if ($filter_kec !== '') {
    $k = mysqli_real_escape_string($conn, $filter_kec);
    $where .= " AND kw.wilayah = '$k'";
}

// ── Count query ───────────────────────────────────────────
$count_sql = "SELECT COUNT(DISTINCT ju.id) AS total
              FROM jukir_utama ju
              LEFT JOIN lokasi l        ON ju.id_lokasi = l.id
              LEFT JOIN koordinator_wilayah kw ON ju.id_korwil = kw.id
              $where";
$total_result = mysqli_query($conn, $count_sql);
$total_row    = mysqli_fetch_assoc($total_result)['total'];
$total_pages  = max(1, ceil($total_row / $limit));

// ── Main query ────────────────────────────────────────────
$sql = "SELECT
            ju.id,
            ju.nama_lengkap  AS nama_utama,
            ju.target_bulanan AS target,
            l.nama_lokasi    AS lokasi,
            l.kode_qris,
            kw.wilayah       AS kecamatan,
            (SELECT GROUP_CONCAT(nama_pembantu SEPARATOR ', ')
             FROM jukir_pembantu WHERE id_utama = ju.id) AS nama_pembantu,
            IFNULL(SUM(tr.jumlah_setoran), 0) AS realisasi
        FROM jukir_utama ju
        LEFT JOIN lokasi l        ON ju.id_lokasi = l.id
        LEFT JOIN koordinator_wilayah kw ON ju.id_korwil = kw.id
        LEFT JOIN transaksi_retribusi tr ON ju.id = tr.id_jukir
            AND MONTH(tr.tanggal_setoran) = MONTH(CURRENT_DATE())
            AND YEAR(tr.tanggal_setoran)  = YEAR(CURRENT_DATE())
        $where
        GROUP BY ju.id, l.nama_lokasi, l.kode_qris, kw.wilayah
        ORDER BY ju.nama_lengkap ASC
        LIMIT $offset, $limit";

$result     = mysqli_query($conn, $sql);
$list_lokasi = mysqli_query($conn, "SELECT id, nama_lokasi, kode_qris FROM lokasi ORDER BY nama_lokasi ASC");

// ── Wilayah list for filter ───────────────────────────────
$list_wilayah = mysqli_query($conn, "SELECT DISTINCT wilayah FROM koordinator_wilayah ORDER BY wilayah ASC");