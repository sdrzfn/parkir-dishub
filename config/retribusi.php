<?php
require_once __DIR__ . '/db.php';

/**
 * Ringkasan keuangan
 */
function getGlobalFinanceSummary($conn)
{
    $bulan = date('m');
    $tahun = date('Y');

    $sql = "SELECT 
                SUM(j.target_bulanan) as total_target,
                COALESCE(SUM(t.jumlah_setoran), 0) as total_realisasi
            FROM jukir_utama j
            LEFT JOIN transaksi_retribusi t ON j.id = t.id_jukir 
                AND t.bulan = '$bulan' 
                AND t.tahun = '$tahun'";

    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);

    $data['selisih'] = $data['total_target'] - $data['total_realisasi'];
    $data['persentase'] = ($data['total_target'] > 0) ? ($data['total_realisasi'] / $data['total_target']) * 100 : 0;

    return $data;
}

/**
 * Total retribusi
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
 */
function getIndicator($persen)
{
    if ($persen < 50)
        return ['color' => '#ef4444', 'bg' => 'bg-red-500', 'status' => 'Kritis'];
    if ($persen < 80)
        return ['color' => '#f59e0b', 'bg' => 'bg-amber-500', 'status' => 'Kurang'];
    return ['color' => '#10b981', 'bg' => 'bg-green-500', 'status' => 'Tercapai'];
}

function hitungTunggakan($realisasi, $target)
{
    return $target - $realisasi;
}

function getRekomendasiAksi($persen, $hari_ini)
{
    if ($persen < 50 && $hari_ini > 20) {
        return ['kode' => 'sp1', 'label' => 'Terbitkan SP 1', 'color' => 'danger'];
    } elseif ($persen < 80) {
        return ['kode' => 'tagihan', 'label' => 'Kirim Tagihan', 'color' => 'warning'];
    }
    return null; // Tidak perlu aksi
}

filterSetoran($conn);

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
                $tgl = date('d/m/Y', strtotime($h['tanggal_setoran']));
                $nominal = number_format($h['jumlah_setoran'], 0, ',', '.');
                echo "
            <tr>
                <td style='padding: 15px; font-weight: 500; color: #334155; font-size: 14px;'>$tgl</td>
                <td style='padding: 15px;'>
                    <span style='background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-size: 12px; color: #475569; font-weight: 600;'>
                        Termin {$h['termin']}
                    </span>
                </td>
                <td style='padding: 15px; text-transform: uppercase; font-size: 12px; font-weight: bold; color: #64748b;'>
                    {$h['metode_pembayaran']}
                </td>
                <td style='padding: 15px; text-align: right; font-weight: 700; color: #1e293b; font-size: 14px;'>
                    Rp $nominal
                </td>
                <td style='padding: 15px; text-align: center;'>
                    <button type='button' class='btn-edit' 
                        style='background: #3b82f6; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;'
                        data-id='{$h['id']}' 
                        data-id_jukir='{$h['id_jukir']}'
                        data-tanggal='{$h['tanggal_setoran']}'
                        data-termin='{$h['termin']}'
                        data-metode='{$h['metode_pembayaran']}'
                        data-nominal='{$h['jumlah_setoran']}'
                        onclick='openEditModal(this)'>
                        Edit
                    </button>
                </td>
            </tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='padding: 40px; text-align: center; color: #94a3b8;'>Tidak ada transaksi pada periode ini.</td></tr>";
        }
        exit;
    }
}