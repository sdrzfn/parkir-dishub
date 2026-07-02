<?php
include 'config/db.php';
include 'config/auth.php';
checkLogin();
$user = current_user();
allowRole(['admin']);

include 'api/fetch_korwil.php';
?>

<!DOCTYPE html>
<html lang="id">
<?php include 'components/header.php'; ?>

<body class="font-sans text-slate-800 antialiased min-h-screen pt-24" style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">
    <?php include 'components/navbar.php'; ?>

    <main class="container mx-auto" style="max-width:1400px;">
        <?php include 'components/breadcrumb.php'; ?>
        <div class="detail-container">
            <div class="header-section">
                <div class="header-title-box">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">Detail Koordinator Wilayah</h2>
                </div>
            </div>

            <div class="detail-grid">
                <div class="card" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <div class="profile-header" style="text-align: center; margin-bottom: 15px;">
                        <div class="avatar-circle" style="margin: 0 auto 15px auto; width: 80px; height: 80px; font-size: 2rem;">
                            <?= substr($korwil['nama_korwil'], 0, 1) ?>
                        </div>
                        <h3 style="margin: 0 0 5px 0; color: #1e293b; font-size: 1.25rem; font-weight: 700;">
                            <?= $korwil['nama_korwil'] ?>
                        </h3>
                        <span class="badge-role" style="display: inline-block;">Koordinator Wilayah</span>
                    </div>
                </div>

                <div class="card" style="position: relative;">
                    <div class="info-group">
                        <div class="info-item">
                            <span class="info-label">Kecamatan/Wilayah</span>
                            <span class="info-value" style="font-size: 1.1rem; color: var(--primary); font-weight: 700;"><?= $korwil['wilayah'] ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?= !empty($korwil['email']) ? $korwil['email'] : '-' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">No. Telepon</span>
                            <span class="info-value"><?= !empty($korwil['no_telp']) ? $korwil['no_telp'] : '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-bottom: 28px;">
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="background: #e0e7ff; color: var(--primary); width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <p style="margin: 0; color: #64748b; font-size: 13px; font-weight: 600;">Petugas Parkir Utama</p>
                        <h3 style="margin: 0; font-size: 24px; color: #1e293b; font-weight: 800;"><?= $jumlah_jukir_utama['total_jukir_utama'] ?> <span style="font-size: 14px; font-weight: 600; color: #94a3b8;">Orang</span></h3>
                    </div>
                </div>
                <div class="card" style="display: flex; align-items: center; gap: 20px;">
                    <div style="background: #f1f5f9; color: #64748b; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p style="margin: 0; color: #64748b; font-size: 13px; font-weight: 600;">Petugas Parkir Pembantu</p>
                        <h3 style="margin: 0; font-size: 24px; color: #1e293b; font-weight: 800;"><?= $jumlah_jukir_pembantu['total_jukir_pembantu'] ?> <span style="font-size: 14px; font-weight: 600; color: #94a3b8;">Orang</span></h3>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 28px;">
                <div style="padding: 24px; border-bottom: 1px solid #f1f5f9;">
                    <h4 style="margin: 0; color: #1e293b; font-size: 16px;">Daftar Petugas Parkir Utama</h4>
                </div>
                <div class="table-container" style="box-shadow: none; border-radius: 0; border: none; margin: 0; max-height: none;">
                    <table class="custom-table w-full whitespace-nowrap">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 12px 24px; text-transform: uppercase; font-size: 12px; color: #64748b;">Nama Petugas Parkir Utama</th>
                                <th style="padding: 12px 24px; text-transform: uppercase; font-size: 12px; color: #64748b;">NIK</th>
                                <th style="padding: 12px 24px; text-transform: uppercase; font-size: 12px; color: #64748b;">Lokasi Penugasan</th>
                                <th style="padding: 12px 24px; text-transform: uppercase; font-size: 12px; color: #64748b;">No. Telp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($res_utama) > 0): while ($u = mysqli_fetch_assoc($res_utama)): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 14px 24px;"><strong><?= $u['nama_lengkap']; ?></strong></td>
                                    <td style="padding: 14px 24px;"><?= $u['nik']; ?></td>
                                    <td style="padding: 14px 24px;"><span style="color: var(--primary); font-weight: 600;"><?= $u['nama_lokasi']; ?></span></td>
                                    <td style="padding: 14px 24px;"><?= $u['no_telp']; ?></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="4" style="padding: 30px; text-align: center; color: #94a3b8;">Tidak ada petugas parkir utama di wilayah ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 28px;">
                <div style="padding: 24px; border-bottom: 1px solid #f1f5f9;">
                    <h4 style="margin: 0; color: #1e293b; font-size: 16px;">Daftar Petugas Parkir Pembantu</h4>
                </div>
                <div class="table-container" style="box-shadow: none; border-radius: 0; border: none; margin: 0; max-height: none;">
                    <table class="custom-table w-full whitespace-nowrap">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 12px 24px; text-transform: uppercase; font-size: 12px; color: #64748b;">Nama Petugas Parkir Pembantu</th>
                                <th style="padding: 12px 24px; text-transform: uppercase; font-size: 12px; color: #64748b;">Membantu Petugas Parkir</th>
                                <th style="padding: 12px 24px; text-transform: uppercase; font-size: 12px; color: #64748b;">Alamat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($res_pembantu) > 0): while ($p = mysqli_fetch_assoc($res_pembantu)): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 14px 24px; font-weight: 500; color: #1e293b;"><?= $p['nama_pembantu']; ?></td>
                                    <td style="padding: 14px 24px;"><small style="color: #64748b;">Utama:</small> <?= $p['nama_induk']; ?></td>
                                    <td style="padding: 14px 24px; white-space: normal;"><?= $p['alamat_pembantu']; ?></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="3" style="padding: 30px; text-align: center; color: #94a3b8;">Tidak ada petugas parkir pembantu di wilayah ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>

</html>