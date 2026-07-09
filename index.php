<?php
include 'config/db.php';
include 'config/auth.php';
include 'config/retribusi.php';

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

$q_map = "SELECT l.*, j.nama_lengkap AS jukir_utama, l.nama_lokasi AS nama
        FROM lokasi l
        LEFT JOIN jukir_utama j ON l.id = j.id_lokasi
        WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL";
$result = mysqli_query($conn, $q_map);
$lokasi_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $lokasi_data[] = $row;
}

$q_top_jukir = mysqli_query(
    $conn,
    "SELECT 
        ju.nama_lengkap AS jukir,
        l.nama_lokasi AS lokasi,
        COALESCE(SUM(t.jumlah_setoran), 0) AS total
    FROM jukir_utama ju
    LEFT JOIN lokasi l ON ju.id_lokasi = l.id
    LEFT JOIN transaksi_retribusi t ON t.id_jukir = ju.id 
        AND t.bulan = MONTH(CURRENT_DATE()) 
        AND t.tahun = YEAR(CURRENT_DATE())
    GROUP BY ju.id, ju.nama_lengkap, l.nama_lokasi
    ORDER BY total DESC
    LIMIT 5"
);

$top_jukir_data = [];
while ($j = mysqli_fetch_assoc($q_top_jukir)) {
    $top_jukir_data[] = $j;
}

$q_progress_wilayah = mysqli_query(
    $conn,
    "SELECT 
        kw.wilayah AS kecamatan,
        COALESCE(SUM(t.jumlah_setoran), 0) AS realisasi,
        (SELECT COALESCE(SUM(target_bulanan), 0) FROM jukir_utama ju2 WHERE ju2.id_korwil = kw.id) AS target
    FROM koordinator_wilayah kw
    LEFT JOIN jukir_utama ju ON ju.id_korwil = kw.id
    LEFT JOIN transaksi_retribusi t ON t.id_jukir = ju.id AND t.bulan = MONTH(CURRENT_DATE()) AND t.tahun = YEAR(CURRENT_DATE())
    GROUP BY kw.id, kw.wilayah
    ORDER BY realisasi DESC"
);
$progress_wilayah_data = [];
while ($row = mysqli_fetch_assoc($q_progress_wilayah)) {
    $progress_wilayah_data[] = $row;
}

// Fetch totals for the top right large numbers
$q_counts = mysqli_query($conn, "SELECT 
    (SELECT COUNT(*) FROM lokasi) as total_lokasi,
    (SELECT COUNT(*) FROM jukir_utama) as total_jukir,
    (SELECT COUNT(*) FROM koordinator_wilayah) as total_korwil
");
$counts = mysqli_fetch_assoc($q_counts);

// Fetch recent transactions for the dark task card
$q_recent = mysqli_query($conn, "SELECT t.*, j.nama_lengkap as nama_jukir 
                                 FROM transaksi_retribusi t 
                                 JOIN jukir_utama j ON t.id_jukir = j.id 
                                 ORDER BY t.tanggal_setoran DESC LIMIT 4");
$recent_tx = [];
while($r = mysqli_fetch_assoc($q_recent)) $recent_tx[] = $r;

// Compute profile photo path for the profile card
$user_id = $_SESSION['user_id'];
$q_foto = mysqli_query($conn, "SELECT foto FROM users WHERE id = '$user_id'");
$u_foto = mysqli_fetch_assoc($q_foto);
$_foto_file = $u_foto['foto'] ?? '';
$foto_path = (!empty($_foto_file) && file_exists('assets/img/users/' . $_foto_file))
    ? 'assets/img/users/' . $_foto_file
    : 'assets/img/default-avatar.png';

$q_live_feed = mysqli_query($conn, "SELECT 
                                        tr.tanggal_setoran, 
                                        tr.jumlah_setoran, 
                                        l.nama_lokasi,
                                        tr.metode_pembayaran
                                    FROM transaksi_retribusi tr
                                    INNER JOIN jukir_utama ju ON tr.id_jukir = ju.id
                                    INNER JOIN lokasi l ON ju.id_lokasi = l.id
                                    ORDER BY tr.tanggal_setoran DESC, tr.id DESC 
                                    LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<?php include 'components/header.php'; ?>
<body class="font-sans text-slate-800 antialiased min-h-screen relative overflow-x-hidden pt-24 selection:bg-brand-200" style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">

    <?php include 'components/navbar.php'; ?>

    <main class="container mx-auto" style="max-width:1400px;">
        <!-- Top Section: Greeting and 4 Metrics -->
        <div class="flex flex-col mb-8 mt-4 gap-6">
            <div class="flex flex-col lg:flex-row justify-between lg:items-end gap-4">
                <div>
                    <h1 class="text-3xl font-medium text-slate-500 tracking-tight leading-none mb-2">
                        Selamat datang, <span class="text-brand-950 font-black"><?= htmlspecialchars(ucwords(strtolower($user['nama']))) ?></span>
                    </h1>
                    <p class="text-sm text-slate-500 font-medium">Ringkasan Kinerja Retribusi Parkir Sidoarjo</p>
                </div>
                
                <!-- Right: Big Numbers -->
                <div class="flex flex-wrap items-center gap-4 sm:gap-6 bg-white rounded-2xl px-5 py-3 shadow-sm border border-slate-200">
                    <div class="flex items-center gap-2">
                        <i class="far fa-map text-[#888] text-[16px]"></i>
                        <span class="text-[1.25rem] font-bold leading-none tracking-tighter text-slate-800"><?= $counts['total_lokasi'] ?></span>
                        <span class="text-[10px] text-[#888] font-semibold uppercase mt-0.5">Titik</span>
                    </div>
                    <div class="w-px h-5 bg-[#e0e0e0] hidden sm:block"></div>
                    <div class="flex items-center gap-2">
                        <i class="far fa-user text-[#888] text-[16px]"></i>
                        <span class="text-[1.25rem] font-bold leading-none tracking-tighter text-slate-800"><?= $counts['total_jukir'] ?></span>
                        <span class="text-[10px] text-[#888] font-semibold uppercase mt-0.5">Petugas Parkir</span>
                    </div>
                    <div class="w-px h-5 bg-[#e0e0e0] hidden sm:block"></div>
                    <!-- <div class="flex items-center gap-2">
                        <i class="far fa-building text-[#888] text-[16px]"></i>
                        <span class="text-[1.25rem] font-bold leading-none tracking-tighter text-slate-800"><?= $counts['total_korwil'] ?></span>
                        <span class="text-[10px] text-[#888] font-semibold uppercase mt-0.5">Wilayah</span>
                    </div> -->
                    <div class="flex items-center gap-2">
                        <i class="far fa-building text-[#888] text-[16px]"></i>
                        <span class="text-[1.25rem] font-bold leading-none tracking-tighter text-slate-800">18</span>
                        <span class="text-[10px] text-[#888] font-semibold uppercase mt-0.5">Kecamatan</span>
                    </div>
                </div>
            </div>

            <!-- 4 Financial Cards (Premium Aesthetics) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-2">
                
                <!-- Target Bulanan -->
                <div class="group relative bg-white rounded-[20px] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(59,130,246,0.12)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">Target Bulan Ini</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-bullseye text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xl font-black text-slate-800 tracking-tight">Rp <?= number_format($summary['total_target'], 0, ',', '.') ?></p>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 w-0 bg-blue-500 group-hover:w-full transition-all duration-500"></div>
                </div>
                
                <!-- Realisasi -->
                <div class="group relative bg-white rounded-[20px] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(16,185,129,0.12)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">Realisasi Bulan Ini</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-wallet text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xl font-black text-emerald-600 tracking-tight">Rp <?= number_format($summary['total_realisasi'], 0, ',', '.') ?></p>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 w-0 bg-emerald-500 group-hover:w-full transition-all duration-500"></div>
                </div>
                
                <!-- Gap -->
                <div class="group relative bg-white rounded-[20px] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(244,63,94,0.12)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">Selisih (Gap)</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-arrow-down text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xl font-black text-rose-600 tracking-tight">Rp <?= number_format(max(0, $summary['total_target'] - $summary['total_realisasi']), 0, ',', '.') ?></p>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 w-0 bg-rose-500 group-hover:w-full transition-all duration-500"></div>
                </div>
                
                <!-- Persentase -->
                <div class="group relative bg-[#1e1b4b] rounded-[20px] p-6 shadow-[0_8px_30px_rgba(30,27,75,0.2)] hover:shadow-[0_15px_40px_rgba(30,27,75,0.3)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-[12px] font-bold text-white/60 uppercase tracking-widest mb-1">Pencapaian</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-white/10 text-white flex items-center justify-center backdrop-blur-sm group-hover:rotate-12 transition-transform duration-300">
                                <i class="fas fa-chart-pie text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-black text-white tracking-tight"><?= round($summary['persentase'], 1) ?><span class="text-base text-white/70 font-semibold ml-1">%</span></p>
                    </div>
                </div>

                <!-- Target Tahunan -->
                <div class="group relative bg-white rounded-[20px] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(59,130,246,0.12)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">Target Tahunan</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-bullseye text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xl font-black text-slate-800 tracking-tight">Rp <?= number_format($summary['target_tahunan'], 0, ',', '.') ?></p>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 w-0 bg-yellow-500 group-hover:w-full transition-all duration-500"></div>
                </div>

                <!-- Realisasi -->
                <div class="group relative bg-white rounded-[20px] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(16,185,129,0.12)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">Realisasi Tahunan</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-wallet text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xl font-black text-emerald-600 tracking-tight">Rp <?= number_format($summary['total_realisasi_tahunan'], 0, ',', '.') ?></p>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 w-0 bg-emerald-500 group-hover:w-full transition-all duration-500"></div>
                </div>

                <!-- Gap Tahunan -->
                <div class="group relative bg-white rounded-[20px] p-6 border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgba(244,63,94,0.12)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-[12px] font-bold text-slate-400 uppercase tracking-widest mb-1">Selisih (Gap)</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                                <i class="fas fa-arrow-down text-lg"></i>
                            </div>
                        </div>
                        <p class="text-xl font-black text-rose-600 tracking-tight">Rp <?= number_format(max(0, $summary['target_tahunan'] - $summary['total_realisasi_tahunan']), 0, ',', '.') ?></p>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 w-0 bg-rose-500 group-hover:w-full transition-all duration-500"></div>
                </div>
                
                <!-- Persentase -->
                <div class="group relative bg-[#1e1b4b] rounded-[20px] p-6 shadow-[0_8px_30px_rgba(30,27,75,0.2)] hover:shadow-[0_15px_40px_rgba(30,27,75,0.3)] transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10 group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-[12px] font-bold text-white/60 uppercase tracking-widest mb-1">Pencapaian</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-white/10 text-white flex items-center justify-center backdrop-blur-sm group-hover:rotate-12 transition-transform duration-300">
                                <i class="fas fa-chart-pie text-lg"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-black text-white tracking-tight"><?= round($summary['persentase_tahunan'], 1) ?><span class="text-base text-white/70 font-semibold ml-1">%</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Grid Layout (Tiers 2 & 3) -->
        <div class="grid grid-cols-12 gap-5 lg:gap-6">
            
            <!-- Tier 2, Column 1: Tren Retribusi (Span 8) -->
            <div class="col-span-12 md:col-span-7 lg:col-span-8">
                <div class="bg-white rounded-[16px] p-5 border border-[rgba(0,0,0,0.06)] shadow-floating flex flex-col" style="height: clamp(300px, 35vw, 360px);">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-[16px] lg:text-[18px] font-medium tracking-tight text-slate-800">Tren Setoran Bulanan</h3>
                        </div>
                        <i class="fas fa-chart-line text-[14px] text-slate-400"></i>
                    </div>
                    <div class="mb-2">
                        <span class="text-[20px] lg:text-[24px] font-bold text-slate-800 leading-none">Rp <?= number_format(max($data_tren), 0, ',', '.') ?></span>
                        <span class="text-[12px] text-slate-500 ml-2 hidden sm:inline">Setoran tertinggi tahun ini</span>
                    </div>
                    <div class="flex-1 relative mt-2 w-full h-full flex flex-col">
                        <div class="flex-1 relative min-h-0">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tier 2, Column 2: Top 5 Petugas Parkir (Span 4) -->
            <div class="col-span-12 md:col-span-5 lg:col-span-4">
                <div class="bg-white rounded-[16px] p-5 border border-[rgba(0,0,0,0.06)] shadow-floating flex flex-col" style="height: clamp(300px, 35vw, 360px);">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-[16px] lg:text-[18px] font-medium tracking-tight text-slate-800">Top 5 Petugas Parkir (Bulan Ini)</h3>
                        <i class="fas fa-medal text-[14px] text-brand-500"></i>
                    </div>
                    <div class="flex flex-col flex-1 overflow-y-auto pr-2" style="scrollbar-width: thin;">
                        <?php if(empty($top_jukir_data)): ?>
                            <div class="text-center text-slate-400 text-sm mt-10">Belum ada data setoran bulan ini.</div>
                        <?php else: ?>
                            <?php foreach($top_jukir_data as $idx => $tj): ?>
                                <div class="flex items-center gap-3 py-2.5 border-b border-slate-50 last:border-0">
                                    <div class="w-7 h-7 rounded-full bg-<?= $idx === 0 ? 'amber-100 text-amber-600' : ($idx === 1 ? 'slate-200 text-slate-500' : ($idx === 2 ? 'orange-100 text-orange-700' : 'brand-50 text-brand-600')) ?> flex items-center justify-center flex-shrink-0 font-bold text-xs">
                                        <?= $idx + 1 ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] font-semibold text-slate-800 truncate leading-tight"><?= htmlspecialchars(ucwords($tj['jukir'])) ?></p>
                                        <p class="text-[11px] text-slate-500 truncate"><?= htmlspecialchars($tj['lokasi']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[13px] font-bold text-slate-800">Rp <?= number_format($tj['total'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

            <!-- Tier 3, Column 1: Peta Sebaran Titik Parkir (Span 8) -->
            <div class="col-span-12 md:col-span-7 lg:col-span-8" style="margin-top: 2rem;">
                <div class="bg-white rounded-[16px] p-5 border border-[rgba(0,0,0,0.06)] shadow-floating flex flex-col" style="height: clamp(280px, 40vw, 380px);">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-[16px] lg:text-[18px] font-medium tracking-tight text-slate-800">Peta Distribusi Titik Parkir</h3>
                        </div>
                        <a href="peta.php" class="px-4 py-2 rounded-full bg-[#f0f0f0] text-slate-600 font-medium text-xs hover:bg-slate-200 transition flex items-center gap-2">
                            Buka Peta <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                    <div id="miniMap" class="flex-1 w-full rounded-[1rem] border border-slate-100 z-10"></div>
                </div>
            </div>

            <!-- Tier 3, Column 2: Progress Target Per Korwil (Span 4) -->
            <!-- <div class="col-span-12 md:col-span-5 lg:col-span-4">
                <div class="bg-[#1e1b4b] text-white rounded-[16px] p-5 shadow-floating flex flex-col" style="height: clamp(280px, 40vw, 380px);">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-[16px] font-medium">Progress Target Wilayah</h3>
                        <i class="fas fa-bullseye text-white/50"></i>
                    </div>
                    
                    <div class="flex flex-col flex-1 overflow-y-auto pr-2" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.2) transparent;">
                        <?php if(empty($progress_wilayah_data)): ?>
                            <div class="text-center text-white/50 text-sm mt-10">Tidak ada data target.</div>
                        <?php else: ?>
                            <?php foreach($progress_wilayah_data as $pw): 
                                $pct = $pw['target'] > 0 ? round(($pw['realisasi'] / $pw['target']) * 100) : 0;
                                $color = $pct >= 100 ? '#10b981' : ($pct >= 70 ? '#F5C518' : '#ef4444');
                            ?>
                                <div class="mb-4 last:mb-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[13px] font-medium text-white truncate"><?= htmlspecialchars(ucwords($pw['kecamatan'])) ?></span>
                                        <span class="text-[11px] font-bold text-white"><?= $pct ?>%</span>
                                    </div>
                                    <div class="text-[10px] text-white/50 text-right mb-1">
                                        Rp <?= number_format($pw['realisasi'], 0, ',', '.') ?> / Rp <?= number_format($pw['target'], 0, ',', '.') ?>
                                    </div>
                                    <div class="w-full h-[6px] bg-white/10 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full" style="width: <?= min(100, $pct) ?>%; background-color: <?= $color ?>;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div> -->

        <div class="card" style="margin-top: 2rem; padding: 24px; background: #ffffff; border-radius: 12px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <div>
                    <h3 class="text-[16px] lg:text-[18px] font-medium tracking-tight text-slate-800">Log Pengawasan Publik</h3>
                    <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.85rem;">Daftar realisasi setoran retribusi parkir terkini di wilayah Sidoarjo</p>
                </div>
            <div>
                <span style="display: inline-flex; align-items: center; gap: 6px; background-color: #f0f9ff; color: #0369a1; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                    <span style="width: 8px; height: 8px; background-color: #0ea5e9; border-radius: 50%; display: inline-block; animation: pulse 2s infinite;"></span>
                    Live Feed
                </span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                        <th style="padding: 12px 16px;">Waktu Setor</th>
                        <th style="padding: 12px 16px;">Titik Lokasi Parkir</th>
                        <th style="padding: 12px 16px; text-center">Metode</th>
                        <th style="padding: 12px 16px; text-align: right;">Nominal Setoran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($q_live_feed) > 0): ?>
                        <?php while ($feed = mysqli_fetch_assoc($q_live_feed)): 
                            $is_qris = (strtolower($feed['metode_pembayaran']) === 'qris');
                        ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                                <td style="padding: 14px 16px; color: #475569; font-size: 0.85rem;">
                                    <?= date('d M Y', strtotime($feed['tanggal_setoran'])); ?>
                                </td>
                            
                                <td style="padding: 14px 16px; color: #1e293b; font-weight: 500;">
                                    <?= $feed['nama_lokasi']; ?>
                                </td>

                                <td style="padding: 14px 16px; text-align: center;">
                                    <?php if ($is_qris): ?>
                                        <span style="background-color: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">QRIS</span>
                                    <?php else: ?>
                                        <span style="background-color: #fef3c7; color: #d97706; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">TUNAI</span>
                                    <?php endif; ?>
                                </td>
                            
                                <td style="padding: 14px 16px; text-align: right; color: #16a34a; font-weight: 700; font-size: 0.95rem;">
                                    Rp <?= number_format($feed['jumlah_setoran'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic;">Belum ada data transaksi setoran masuk bulan ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const labelsBulan = <?= json_encode($bulan_labels); ?>;
        const dataTren = <?= json_encode($data_tren); ?>;
        
        const maxVal = Math.max(...dataTren);
        const bgColors = dataTren.map(v => (v === maxVal && v > 0) ? '#F5C518' : '#2a2a2a');

        // Bar Chart (Tren Setoran)
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: labelsBulan,
                datasets: [{
                    label: 'Total Setoran',
                    data: dataTren,
                    backgroundColor: bgColors,
                    borderRadius: 8,
                    barPercentage: 0.3,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 12,
                        callbacks: {
                            label: function (context) {
                                return 'Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)', lineWidth: 1 },
                        border: { display: false },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'Jt';
                            },
                            font: { family: "'Inter', sans-serif", size: 10 },
                            color: '#64748b',
                            maxTicksLimit: 5
                        },
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { 
                            font: { family: "'Inter', sans-serif", size: 10 },
                            color: '#64748b',
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 12
                        },
                    }
                }
            }
        });

        // Leaflet Map
        document.addEventListener('DOMContentLoaded', function () {
            try {
                const map = L.map('miniMap', {
                    zoomControl: false,
                    scrollWheelZoom: false
                }).setView([-7.4478, 112.7183], 12);

                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
                }).addTo(map);

                const locations = <?= json_encode($lokasi_data) ?>;
                if (locations && locations.length > 0) {
                    locations.forEach(loc => {
                        if (loc.latitude && loc.longitude) {
                            L.circleMarker([loc.latitude, loc.longitude], {
                                radius: 7,
                                fillColor: "#4f46e5",
                                color: "#ffffff",
                                weight: 2,
                                opacity: 1,
                                fillOpacity: 1
                            }).addTo(map).bindPopup(`
                                <div style="font-family: 'Inter', sans-serif;">
                                    <strong style="display:block; margin-bottom:4px; color:#1e1b4b;">${loc.nama}</strong>
                                    <small style="color: #64748b;">Petugas Parkir: ${loc.jukir_utama || 'Tidak ada data'}</small>
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