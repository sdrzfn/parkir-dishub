<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/retribusi.php';

checkLogin();
$user = current_user();
allowRole(['super-admin']);

$summary = getGlobalFinanceSummary($conn);

$bulan_labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
$data_tren = array_fill(0, 12, 0);

$q_tren = mysqli_query($conn, "SELECT bulan, SUM(jumlah_setoran) as total 
                               FROM transaksi_retribusi 
                               WHERE tahun = YEAR(CURRENT_DATE()) 
                               GROUP BY bulan");
while ($t = mysqli_fetch_assoc($q_tren)) {
    $data_tren[(int) $t['bulan'] - 1] = (float) $t['total'];
}

$ind = getIndicator($summary['persentase']);
$q_map = "SELECT l.*, j.nama_lengkap AS jukir_utama 
        FROM lokasi l 
        LEFT JOIN jukir_utama j ON l.id = j.id_lokasi 
        WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL";
$result = mysqli_query($conn, $q_map);
$lokasi_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $lokasi_data[] = $row;
}
// $q_retribusi = mysqli_query($conn, "SELECT SUM(target_bulanan) as total FROM lokasi");
// $res_retribusi = mysqli_fetch_assoc($q_retribusi);
// $total_retribusi = $res_retribusi['total'] ?? 0;

// $q_lokasi = mysqli_query($conn, "SELECT COUNT(*) as total FROM lokasi");
// $total_lokasi = mysqli_fetch_assoc($q_lokasi)['total'];

// $q_korwil = mysqli_query($conn, "SELECT COUNT(*) as total FROM koordinator_wilayah");
// $total_korwil = mysqli_fetch_assoc($q_korwil)['total'];

// $q_utama = mysqli_query($conn, "SELECT COUNT(*) as total FROM jukir_utama");
// $total_utama = mysqli_fetch_assoc($q_utama)['total'];

// $q_pembantu = mysqli_query($conn, "SELECT COUNT(*) as total FROM jukir_pembantu");
// $total_pembantu = mysqli_fetch_assoc($q_pembantu)['total'];

// $q_wilayah = mysqli_query($conn, "SELECT COUNT(DISTINCT wilayah) as total FROM koordinator_wilayah");
// $total_wilayah = mysqli_fetch_assoc($q_wilayah)['total'];
?>

<!DOCTYPE html>
<html lang="id">

<?php include '../components/header.php'; ?>

<body class="bg-slate-50 flex">

    <?php include '../components/navbar.php'; ?>

    <div class="app-body">
        <?php include '../components/sidebar.php'; ?>

        <main class="main-content">

            <div class="container">
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.8rem; color: #1e293b; font-weight: 800">Dashboard Keuangan Retribusi</h1>
                    <p style="color: #64748b;">Periode Berjalan: <strong><?= date('F Y'); ?></strong></p>
                </div>

                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
                    <div class="card" style="border-left: 4px solid #10b981;">
                        <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">TOTAL REALISASI</p>
                        <h2 style="color: #1e293b; font-size: 1rem; margin-top: 0.5rem;">
                            Rp <?= number_format($summary['total_realisasi'], 0, ',', '.'); ?>
                        </h2>
                    </div>
                    <div class="card" style="border-left: 4px solid #3b82f6;">
                        <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">TOTAL TARGET</p>
                        <h2 style="color: #1e293b; font-size: 1rem; margin-top: 0.5rem;">
                            Rp <?= number_format($summary['total_target'], 0, ',', '.'); ?>
                        </h2>
                    </div>
                    <div class="card" style="border-left: 4px solid #f59e0b;">
                        <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">SELISIH (GAP)</p>
                        <h2 style="color: #ef4444; font-size: 1rem; margin-top: 0.5rem;">
                            Rp <?= number_format($summary['selisih'], 0, ',', '.'); ?>
                        </h2>
                    </div>
                    <div class="card" style="border-left: 4px solid <?= $ind['color']; ?>;">
                        <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">CAPAIAN (%)</p>
                        <h2 style="color: <?= $ind['color']; ?>; font-size: 1rem; margin-top: 0.5rem;">
                            <?= round($summary['persentase'], 1); ?>%
                        </h2>
                    </div>
                </div>

                <div class="card" style="margin-top: 1.5rem; padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: bold; font-size: 0.9rem;">Progress Capaian Target Daerah</span>
                        <span
                            style="font-weight: bold; color: <?= $ind['color']; ?>;"><?= round($summary['persentase'], 1); ?>%</span>
                    </div>
                    <div
                        style="width: 100%; bg-color: #e2e8f0; border-radius: 999px; height: 12px; background: #f1f5f9;">
                        <div
                            style="width: <?= min($summary['persentase'], 100); ?>%; background: <?= $ind['color']; ?>; height: 12px; border-radius: 999px; transition: width 0.5s;">
                        </div>
                    </div>
                </div>

                <div
                    style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1rem; font-weight: bold; margin-bottom: 1rem;">Tren Setoran Bulanan
                            (<?= date('Y'); ?>)</h3>
                        <canvas id="barChart" height="250"></canvas>
                    </div>
                    <div class="card" style="height: 350px; display: flex; flex-direction: column;">
                        <div style="margin-bottom: 20px;">
                            <h4 style="margin: 0; color: #1e293b;">Perbandingan Pendapatan Antar Kecamatan</h4>
                            <span style="font-size: 11px; color: #94a3b8;">Bulan:
                                <?= date('F Y') ?>
                            </span>
                        </div>
                        <div style="flex: 1; min-height: 0;">
                            <canvas id="chartKecamatan"></canvas>
                        </div>
                    </div>
                </div>
                <div class="map-section" style="margin-top: 2rem;">

                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h4 style="margin: 0; color: #1e293b; font-weight: 700; font-size: 1.2rem;">Sebaran Titik
                                Parkir</h4>
                            <p style="font-size: 13px; color: #64748b; margin-top: 4px;">Wilayah Operasional Kabupaten
                                Sidoarjo</p>
                        </div>
                        <a href="../super-admin/peta.php"
                            style="background: #3b82f6; color: white; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 600; transition: all 0.3s; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.15); display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-expand-arrows-alt"></i> Lihat Peta Detail
                        </a>
                    </div>

                    <div class="card"
                        style="padding: 0; overflow: hidden; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div id="miniMap" style="height: 400px; width: 100%; z-index: 1;"></div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const labelsBulan = <?= json_encode($bulan_labels); ?>;
        const dataTren = <?= json_encode($data_tren); ?>;
        const totalTarget = <?= $summary['total_target']; ?>;
        const totalRealisasi = <?= $summary['total_realisasi']; ?>;

        // new Chart(document.getElementById('barChart'), {
        //     type: 'bar',
        //     data: {
        //         labels: labelsBulan,
        //         datasets: [{
        //             label: 'Setoran (Rp)',
        //             data: dataTren,
        //             backgroundColor: '#3b82f6',
        //             borderRadius: 8
        //         }]
        //     },
        //     options: {
        //         responsive: true,
        //         maintainAspectRatio: false,
        //         plugins: { legend: { display: false } }
        //     }
        // });

        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Realisasi', 'Sisa Target'],
                datasets: [{
                    data: [totalRealisasi, Math.max(0, totalTarget - totalRealisasi)],
                    backgroundColor: ['<?= $ind['color']; ?>', '#e2e8f0'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            try {
                const map = L.map('miniMap', {
                    zoomControl: true,
                    scrollWheelZoom: false
                }).setView([-7.4478, 112.7183], 12); // Koordinat Sidoarjo

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                // Render Marker dari PHP
                const locations = <?= json_encode($lokasi_data) ?>;

                if (locations && locations.length > 0) {
                    locations.forEach(loc => {
                        if (loc.latitude && loc.longitude) {
                            const marker = L.circleMarker([loc.latitude, loc.longitude], {
                                radius: 6,
                                fillColor: "#3b82f6",
                                color: "#ffffff",
                                weight: 2,
                                opacity: 1,
                                fillOpacity: 0.9
                            }).addTo(map);

                            marker.bindPopup(`
                        <div style="font-family: sans-serif;">
                            <strong style="display:block; margin-bottom:4px;">${loc.nama}</strong>
                            <small style="color: #64748b;">Jukir: ${loc.jukir_utama || 'Tidak ada data'}</small>
                        </div>
                    `);
                        }
                    });
                }
            } catch (error) {
                console.error("Gagal memuat peta:", error);
            }
        });
    </script>
</body>

</html>