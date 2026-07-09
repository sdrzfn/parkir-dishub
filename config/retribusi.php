<?php
require_once __DIR__ . '/db.php';

/**
 * Ringkasan keuangan
 * @param mysqli $conn
 */
function getGlobalFinanceSummary($conn)
{
    $bulan = date('m');
    $tahun = date('Y');

    $sql = "SELECT 
                SUM(j.target_bulanan)                        AS total_target,
                SUM(j.target_bulanan) * 12                   AS target_tahunan,
                COALESCE(SUM(t.jumlah_setoran), 0)           AS total_realisasi
            FROM jukir_utama j
            LEFT JOIN transaksi_retribusi t ON j.id = t.id_jukir 
                AND t.bulan = '$bulan' 
                AND t.tahun = '$tahun'";

    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);

    // Realisasi tahunan — query terpisah karena harus SUM semua bulan di tahun ini
    $q_tahunan = mysqli_query($conn, "SELECT COALESCE(SUM(jumlah_setoran), 0) AS total_tahunan
                                      FROM transaksi_retribusi
                                      WHERE tahun = '$tahun'");
    $data['total_realisasi_tahunan'] = mysqli_fetch_assoc($q_tahunan)['total_tahunan'] ?? 0;

    // Kalkulasi bulanan
    $data['selisih'] = $data['total_target'] - $data['total_realisasi'];
    $data['persentase'] = ($data['total_target'] > 0)
        ? ($data['total_realisasi'] / $data['total_target']) * 100
        : 0;

    // Kalkulasi tahunan
    $data['selisih_tahunan'] = $data['target_tahunan'] - $data['total_realisasi_tahunan'];
    $data['persentase_tahunan'] = ($data['target_tahunan'] > 0)
        ? ($data['total_realisasi_tahunan'] / $data['target_tahunan']) * 100
        : 0;

    return $data;
}

/**
 * Total retribusi
 * @param mysqli $conn
 */
function getJukirPerformance($conn)
{
    $bulan = date('m');
    $tahun = date('Y');

    $sql = "SELECT 
                j.id, j.nama_lengkap, j.target_bulanan,
                l.nama_lokasi, l.latitude, l.longitude,
                COALESCE(SUM(t.jumlah_setoran), 0) as realisasi
            FROM jukir_utama j
            JOIN lokasi l ON j.id_lokasi = l.id
            LEFT JOIN transaksi_retribusi t ON j.id = t.id_jukir 
                AND t.bulan = '$bulan' 
                AND t.tahun = '$tahun'
            GROUP BY j.id";

    return mysqli_query($conn, $sql);
}

/**
 * Helper warna berdasarkan persentase
 * @param float $persen
 */
function getIndicator($persen)
{
    if ($persen < 50)
        return ['color' => '#ef4444', 'bg' => 'bg-red-500', 'status' => 'Kritis'];
    if ($persen < 80)
        return ['color' => '#f59e0b', 'bg' => 'bg-amber-500', 'status' => 'Kurang'];
    return ['color' => '#10b981', 'bg' => 'bg-green-500', 'status' => 'Tercapai'];
}

/**
 * Menghitung tunggakan
 * @param float $realisasi
 * @param float $target
 * @return float
 */
function hitungTunggakan($realisasi, $target)
{
    return $target - $realisasi;
}

/**
 * Rekomendasi Aksi
 * @param float|int $persen
 * @param int $hari_ini
 * @return array|null
 */
function getRekomendasiAksi($persen, $hari_ini)
{
    if ($persen < 50 && $hari_ini > 20) {
        return ['kode' => 'sp1', 'label' => 'Unduh SP', 'color' => 'danger'];
    } elseif ($persen < 80) {
        return ['kode' => 'tagihan', 'label' => 'Unduh Surat Tagihan', 'color' => 'warning'];
    }
    return null; // Tidak perlu aksi
}

filterSetoran($conn);

/**
 * Filter setoran
 * @param mysqli $conn
 */
function filterSetoran($conn)
{
    $filter_bulan = isset($_GET['bulan']) ? mysqli_real_escape_string($conn, $_GET['bulan']) : date('m');
    $filter_tahun = isset($_GET['tahun']) ? mysqli_real_escape_string($conn, $_GET['tahun']) : date('Y');
    $search_id = isset($_GET['search_id']) ? mysqli_real_escape_string($conn, $_GET['search_id']) : '';
    $id_jukir = isset($_GET['id_jukir']) ? mysqli_real_escape_string($conn, $_GET['id_jukir']) : '';

    $query_base = "SELECT * FROM transaksi_retribusi WHERE 1=1";

    if ($id_jukir) {
        $query_base .= " AND id_jukir = '$id_jukir'";
    }

    if ($search_id) {
        $query_base .= " AND id LIKE '%$search_id%'";
    } else {
        $query_base .= " AND MONTH(tanggal_setoran) = '$filter_bulan' AND YEAR(tanggal_setoran) = '$filter_tahun'";
    }

    $query_base .= " ORDER BY tanggal_setoran DESC";

    if (isset($_GET['ajax_riwayat'])) {
        $q_history = mysqli_query($conn, $query_base);
        if (mysqli_num_rows($q_history) > 0) {
            while ($h = mysqli_fetch_assoc($q_history)) {
                $tgl = date('d M Y', strtotime($h['tanggal_setoran']));
                $nominal = number_format($h['jumlah_setoran'], 0, ',', '.');
                $metode = strtoupper($h['metode_pembayaran'] ?? 'TUNAI');
                $idKarcis = strtoupper($h['id_karcis']);
                $noSeriAwal = ($h['no_seri_awal']);
                $noSeriAkhir = ($h['no_seri_akhir']);
                $kodeQris = strtoupper($h['kode_qris'] ?? '-');
                echo "
            <tr>
                <td data-label='Tanggal' style='padding:14px 20px; font-weight:500; color:#334155; font-size:14px;'>$tgl</td>
                <td data-label='Nomor Seri Karcis' style='padding:14px 20px;'>
                    <span style='background:#f1f5f9; padding:4px 12px; border-radius:999px; font-size:12px; color:#475569; font-weight:600;'>
                        {$idKarcis} - {$noSeriAwal} - {$noSeriAkhir}
                    </span>
                </td>
                <td data-label='Kode QRIS' style='padding:14px 20px;'>
                    <span style='background:#e0e7ff; padding:4px 12px; border-radius:999px; font-size:12px; color:#4f46e5; font-weight:600;'>
                        $kodeQris
                    </span>
                </td>
                <td data-label='Metode' style='padding:14px 20px;'>
                    <span style='background:#e0e7ff; padding:4px 12px; border-radius:999px; font-size:12px; color:#4f46e5; font-weight:600;'>
                        $metode
                    </span>
                </td>
                <td data-label='Nominal' style='padding:14px 20px; text-align:right; font-weight:700; color:#1e1b4b; font-size:14px;'>
                    Rp $nominal
                </td>
                <td data-label='Aksi' style='padding:14px 20px; text-align:center;'>
                    <div style='display:flex; gap:6px; justify-content:flex-end;'>
                        <button type='button' class='btn-action btn-edit'
                            data-id='{$h['id']}'
                            data-id_jukir='{$h['id_jukir']}'
                            data-tanggal='{$h['tanggal_setoran']}'
                            data-termin='{$h['termin']}'
                            data-metode='{$h['metode_pembayaran']}'
                            data-nominal='{$h['jumlah_setoran']}'
                            data-jenis-kendaraan='{$h['jenis_kendaraan']}'
                            data-no-seri-awal='{$h['no_seri_awal']}'
                            data-no-seri-akhir='{$h['no_seri_akhir']}'
                            data-id-karcis='{$h['id_karcis']}'
                            data-jumlah-karcis='{$h['jumlah_karcis']}'
                            data-bundel-karcis='{$h['bundel_karcis']}'
                            data-kode-qris='{$h['kode_qris']}'
                            onclick='openEditModal(this)'>
                            <i class='fas fa-pencil-alt' style='font-size:11px;'></i> Edit
                        </button>
                        <button type='button' class='btn-action btn-delete'
                            data-id='{$h['id']}'
                            data-nominal='Rp $nominal'
                            data-tgl='$tgl'
                            onclick='hapusSetoran(this)'>
                            <i class='fas fa-trash' style='font-size:11px;'></i>
                        </button>
                    </div>
                </td>
            </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>
                <div class='empty-state'>
                    <div class='empty-state-icon'><i class='fas fa-receipt'></i></div>
                    <p class='empty-state-title'>Belum ada transaksi</p>
                    <p class='empty-state-desc'>Tidak ada riwayat setoran pada periode yang dipilih.</p>
                </div>
            </td></tr>";
        }
        exit;
    }
}

/**
 * Fungsi menghitung denda jika realisasi kurang dari target
 * @param float|int $target
 * @param float|int $realisasi
 * @return float|int
 */
function hitungDenda($target, $realisasi)
{
    if ($realisasi >= $target) {
        return 0;
    }
    $tunggakan = $target - $realisasi;
    return 0.02 * $tunggakan; // Denda 2% dari sisa tunggakan
}

/**
 * Fungsi menghitung bagi hasil / imbal jasa jukir 40%
 * @param float|int $realisasi
 * @return float|int
 */
function hitungImbalJasa($realisasi)
{
    return 0.40 * $realisasi; // 40% dari total pendapatan yang disetor
}