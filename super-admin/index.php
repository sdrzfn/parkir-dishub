<?php
include '../config/db.php';
include '../config/auth.php';
// checkLogin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Dashboard Super Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 flex">

    <?php include '../components/sidebar.php'; ?>

    <div class="main-content">
        <header class="header">
            <div class="search-bar">
                <input type="text" placeholder="Cari data transaksi...">
            </div>
            <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
                <div style="text-align: right;">
                    <div style="font-weight: 600; font-size: 0.9rem;">Administrator</div>
                    <div style="font-size: 0.7rem; color: gray;">PETUGAS DINAS</div>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin" style="width: 40px; border-radius: 50%;">
            </div>
        </header>

        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem;">Ringkasan Data</h1>
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