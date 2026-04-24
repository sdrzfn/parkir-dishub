<?php
include '../config/db.php';
include '../config/auth.php';
checkLogin();
$user = current_user();
allowRole(['super-admin']);

$sql = "SELECT l.*, j.nama_lengkap AS jukir_utama 
        FROM lokasi l 
        LEFT JOIN jukir_utama j ON l.id = j.id_lokasi 
        WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL";
$result = mysqli_query($conn, $sql);
$lokasi_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $lokasi_data[] = $row;
}

?>
<!DOCTYPE html>
<html lang="id">

<?php include '../components/header.php'; ?>

<body style="display: flex; margin: 0; background: #f8fafc;">

    <?php include '../components/navbar.php'; ?>

    <div class="app-body">

        <?php include '../components/sidebar.php'; ?>

        <main class="main-content">
            <div class="container">
                <div style="margin-bottom: 1.5rem;">
                    <h1 style="font-size: 1.5rem; color: var(--sidebar-bg);">Peta Lokasi Parkir</h1>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Klik pada marker untuk melihat detail
                        retribusi
                        cepat.</p>
                </div>

                <div id="map-wrapper">
                    <div id="map"></div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        var map = L.map('map').setView([-7.4478, 112.7183], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var dataLokasi = <?php echo json_encode($lokasi_data); ?>;

        dataLokasi.forEach(function (item) {
            var marker = L.marker([item.latitude, item.longitude]).addTo(map);

            var popupHTML = `
            <div style="width: 220px; font-family: sans-serif;">
                <h4 style="margin:0 0 8px 0; color:#1e40af;">${item.nama_lokasi}</h4>
                <table style="width:100%; font-size:12px; border-collapse: collapse;">
                    <tr><td><b>QRIS</b></td><td>: ${item.kode_qris}</td></tr>
                    <tr><td><b>Jukir</b></td><td>: ${item.jukir_utama || '-'}</td></tr>
                    <tr><td><b>Retribusi</b></td><td>: Rp ${Number(item.target_bulanan).toLocaleString('id-ID')}</td></tr>
                </table>
                <hr style="margin:10px 0; border:0; border-top:1px solid #eee;">
                <a href="lokasi.php" style="display:block; text-align:center; color:#2563eb; text-decoration:none; font-weight:bold;">Detail Lokasi →</a>
            </div>
        `;
            marker.bindPopup(popupHTML);
        });
    </script>
</body>

</html>