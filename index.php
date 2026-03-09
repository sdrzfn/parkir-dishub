<?php

include 'config/auth.php';
include 'config/db.php';
// checkLogin();

?>
<!DOCTYPE html>
<html lang="en">

<?php include 'components/header.php'; ?>

<body class="bg-slate-50 flex">

    <?php include 'components/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'components/navbar.php'; ?>

        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.8rem; color: var(--sidebar-bg); font-weight: bold">Dashboard Parkir Kabupaten Sidoarjo</h1>
                <button class="btn-primary">+ Transaksi Baru</button>
            </div>

            <div class="stats-grid">
                <div class="card">
                    <p style="color: var(--text-muted); font-size: 0.8rem;">Total Retribusi</p>
                    <h2 style="margin-top: 5px;">Rp 12,4jt</h2>
                </div>
                <div class="card">
                    <p style="color: var(--text-muted); font-size: 0.8rem;">Lokasi Aktif</p>
                    <h2 style="margin-top: 5px;">24 Titik</h2>
                </div>
                <div class="card">
                    <p style="color: var(--text-muted); font-size: 0.8rem;">Juru Parkir</p>
                    <h2 style="margin-top: 5px;">156 Orang</h2>
                </div>
            </div>

            <div class="table-container">
                <div style="padding: 20px; font-weight: bold; border-bottom: 1px solid #f1f5f9;">Transaksi Terakhir</div>
                <table>
                    <thead>
                        <tr>
                            <th>Petugas</th>
                            <th>Lokasi</th>
                            <th>Kendaraan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Rudi Tabuti</td>
                            <td>Jl. Merdeka No. 10</td>
                            <td>Mobil (B 1234 ABC)</td>
                            <td><span class="badge badge-blue">Selesai</span></td>
                            <td><button style="border: none; background: none; color: blue; cursor: pointer;">Detail</button></td>
                        </tr>
                        </tbody>
                </table>
            </div>
        </div>
    </div>

</body>

</html>