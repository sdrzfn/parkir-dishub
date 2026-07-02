<?php
include '../config/db.php';
include '../config/auth.php';
checkLogin();
$user = current_user();
allowRole(['kepala-dinas']);

include '../api/fetch_peta.php';
?>
<!DOCTYPE html>
<html lang="id">

<?php include '../components/header.php'; ?>

<body class="font-sans text-slate-800 antialiased min-h-screen pt-24"
    style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">

    <?php include '../components/navbar.php'; ?>

    <main class="container mx-auto" style="max-width:1400px;">
        <?php include '../components/breadcrumb.php'; ?>
        <div style="margin-bottom: 1.5rem;">
            <h1 class="page-title">Peta Lokasi Parkir</h1>
            <p class="page-subtitle">Klik pada marker untuk melihat detail retribusi cepat.</p>
        </div>

        <div id="map-wrapper" class="card"
            style="padding: 0; overflow: hidden; border-radius: 1.5rem; border: 1px solid rgba(255,255,255,0.6); display: flex; flex-direction: column;">
            <div id="map" style="width: 100%; flex: 1; height: 60vh; min-height: 400px; z-index: 1;"></div>
        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        var map = L.map('map').setView([-7.4478, 112.7183], 13);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        var dataLokasi = <?php echo json_encode($lokasi_data); ?>;

        dataLokasi.forEach(function (item) {
            var marker = L.circleMarker([item.latitude, item.longitude], {
                radius: 9,
                fillColor: '#4f46e5',
                color: '#ffffff',
                weight: 2.5,
                opacity: 1,
                fillOpacity: 1
            }).addTo(map);

            var popupHTML = `
            <div style="width: 240px; font-family: 'Inter', sans-serif;">
                <h4 style="margin:0 0 8px 0; color:#1e1b4b; font-weight:700; font-size:14px;">${item.nama_lokasi}</h4>
                <table style="width:100%; font-size:12px; border-collapse: collapse; color: #475569;">
                    <tr><td style="padding:3px 0;"><b style="color:#0f172a;">QRIS</b></td><td>: ${item.kode_qris}</td></tr>
                    <tr><td style="padding:3px 0;"><b style="color:#0f172a;">Petugas Parkir</b></td><td>: ${item.jukir_utama || '-'}</td></tr>
                    <tr><td style="padding:3px 0;"><b style="color:#0f172a;">Target</b></td><td>: Rp ${Number(item.target_bulanan).toLocaleString('id-ID')}</td></tr>
                </table>
                <hr style="margin:10px 0; border:0; border-top:1px solid #e2e8f0;">
                <a href="lokasi.php" style="display:block; text-align:center; background:#4f46e5; color:white; text-decoration:none; font-weight:600; font-size:12px; padding:6px; border-radius:6px;">Detail Lokasi →</a>
            </div>
        `;
            marker.bindPopup(popupHTML);
        });

        // Ensure the map properly redraws and fills the available container space
        setTimeout(() => {
            map.invalidateSize();
        }, 100);

        window.addEventListener('resize', () => {
            map.invalidateSize();
        });
    </script>
</body>

</html>