<?php
include '../config/db.php';
include '../config/auth.php';
include '../config/retribusi.php';

checkLogin();
$user = current_user();
allowRole(['admin']);

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
?>

<!DOCTYPE html>
<html lang="id">

<?php include 'components/header.php'; ?>

<body class="bg-slate-50 flex">

    <?php include 'components/navbar.php'; ?>

    <div class="app-body">
        <?php include 'components/sidebar.php'; ?>

        <main class="main-content">

            <div class="container">
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.8rem; color: #1e293b; font-weight: 800">Dashboard Keuangan Retribusi</h1>
                    <p style="color: #64748b;">Periode Berjalan: <strong><?= date('F Y'); ?></strong></p>
                </div>

                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
                    <div class="card" style="border-left: 4px solid #10b981;">
                        <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">TOTAL REALISASI</p>
                        <h2 style="color: #1e293b; font-size: 1.5rem; margin-top: 0.5rem;">
                            Rp <?= number_format($summary['total_realisasi'], 0, ',', '.'); ?>
                        </h2>
                    </div>
                    <div class="card" style="border-left: 4px solid #3b82f6;">
                        <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">TOTAL TARGET</p>
                        <h2 style="color: #1e293b; font-size: 1.5rem; margin-top: 0.5rem;">
                            Rp <?= number_format($summary['total_target'], 0, ',', '.'); ?>
                        </h2>
                    </div>
                    <div class="card" style="border-left: 4px solid #f59e0b;">
                        <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">SELISIH (GAP)</p>
                        <h2 style="color: #ef4444; font-size: 1.5rem; margin-top: 0.5rem;">
                            Rp <?= number_format($summary['selisih'], 0, ',', '.'); ?>
                        </h2>
                    </div>
                    <div class="card" style="border-left: 4px solid <?= $ind['color']; ?>;">
                        <p style="color: #64748b; font-size: 0.875rem; font-weight: 600;">CAPAIAN (%)</p>
                        <h2 style="color: <?= $ind['color']; ?>; font-size: 1.5rem; margin-top: 0.5rem;">
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

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1rem; font-weight: bold; margin-bottom: 1rem;">Tren Setoran Bulanan
                            (<?= date('Y'); ?>)</h3>
                        <canvas id="barChart" height="250"></canvas>
                    </div>

                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="font-size: 1rem; font-weight: bold; margin-bottom: 1rem;">Target vs Realisasi</h3>
                        <canvas id="pieChart"></canvas>
                        <div style="margin-top: 1rem; text-align: center;">
                            <span style="font-size: 0.8rem; color: #64748b;">Status:
                                <strong><?= $ind['status']; ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labelsBulan = <?= json_encode($bulan_labels); ?>;
        const dataTren = <?= json_encode($data_tren); ?>;
        const totalTarget = <?= $summary['total_target'] ?? 0; ?>;
        const totalRealisasi = <?= $summary['total_realisasi'] ?? 0; ?>;

        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: labelsBulan,
                datasets: [{
                    label: 'Setoran (Rp)',
                    data: dataTren,
                    backgroundColor: '#3b82f6',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

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
    </script>
</body>

</html>