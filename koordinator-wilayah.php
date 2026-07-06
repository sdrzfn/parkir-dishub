<?php
include 'config/db.php';
include 'config/auth.php';
checkLogin();
$user = current_user();
allowRole(['admin']);

$sql = "SELECT * FROM koordinator_wilayah ORDER BY wilayah ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<?php include 'components/header.php'; ?>

<body class="font-sans text-slate-800 antialiased min-h-screen pt-24" style="background: radial-gradient(100% 100% at 100% 0%, #fef3c7 0%, #f8fafc 100%);">

    <?php include 'components/navbar.php'; ?>

    <main class="container mx-auto" style="max-width:1400px;">
        <?php include 'components/breadcrumb.php'; ?>
                <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1 class="page-title">Manajemen Koordinator Wilayah</h1>
                        <p class="page-subtitle">Daftar penanggung jawab wilayah Sidoarjo</p>
                    </div>
                    <button class="btn-primary" onclick="openTambahModal()" style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-plus"></i> Tambah Koordinator
                    </button>
                </div>

                <div class="table-container relative max-h-[65vh] overflow-y-auto overflow-x-auto w-full rounded-xl border border-slate-200 shadow-sm mt-4">
                    <table class="custom-table w-full whitespace-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th class="sticky left-0 bg-slate-50 z-20 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">Wilayah</th>
                                <th>Nama Koordinator</th>
                                <th>No. Telepon</th>
                                <th>Email</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0):
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                            ?>
                                    <tr>
                                        <td data-label="No"><?= $no++; ?></td>
                                        <td data-label="Wilayah" class="sticky left-0 bg-white z-10 shadow-[2px_0_5px_rgba(0,0,0,0.05)]"><strong><?= $row['wilayah']; ?></strong></td>
                                        <td data-label="Nama Koordinator">
                                            <div class="truncate max-w-[200px]" title="<?= htmlspecialchars($row['nama_korwil']); ?>">
                                                <?= $row['nama_korwil']; ?>
                                            </div>
                                        </td>
                                        <td data-label="No. Telepon"><?= htmlspecialchars($row['no_telp'] ?? '-'); ?></td>
                                        <td data-label="Email"><?= htmlspecialchars($row['email'] ?? '-'); ?></td>
                                        <td data-label="Aksi" style="text-align: center;">
                                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                                <button class="btn-action btn-edit"
                                                    onclick='openEditModal(<?= json_encode($row) ?>)'
                                                    style="padding: 6px 12px; font-weight: bold;">
                                                    <i class="fas fa-edit" style="margin-right: 4px;"></i> Edit
                                                </button>
                                                <button class="btn-action btn-delete"
                                                    onclick="hapusData(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nama_korwil']); ?>')"
                                                    style="padding: 6px 12px; font-weight: bold;">
                                                    <i class="fas fa-trash" style="margin-right: 4px;"></i> Hapus
                                                </button>
                                                <a href="detail-korwil.php?id=<?= $row['id']; ?>" class="btn-action"
                                                    style="background: #0ea5e9; color: white; padding: 6px 12px; font-weight: bold; border-radius: 6px; text-decoration: none;">
                                                    <i class="fas fa-eye" style="margin-right: 4px;"></i> Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon"><i class="fas fa-users"></i></div>
                                            <p class="empty-state-title">Belum ada Koordinator Wilayah</p>
                                            <p class="empty-state-desc">Silakan klik tombol "Tambah Koordinator" untuk menambahkan data baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
    </main>

    <!-- MODAL TAMBAH / EDIT KORWIL -->
    <div id="modalKorwil" class="modal-backdrop" style="display: none;">
        <div class="modal-content">
            <button type="button" onclick="closeModal()" class="btn-close-modal" aria-label="Tutup Modal"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3 id="modalTitle" style="margin: 0; font-size: 1.1rem; font-weight: 700;">Tambah Koordinator Wilayah</h3>
            </div>
            <form id="formKorwil" action="store/proses_korwil.php?action=add" method="POST">
                <input type="hidden" name="id" id="id_field">

                <div class="modal-body">
                    <div style="margin-bottom: 15px;">
                        <label class="form-label">Kecamatan/Wilayah</label>
                        <input type="text" name="wilayah" id="wilayah" class="form-input" required placeholder="Contoh: Sidoarjo">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="form-label">Nama Koordinator</label>
                        <input type="text" name="nama_korwil" id="nama_korwil" class="form-input" required placeholder="Masukkan nama lengkap">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="no_telp" id="no_telp" class="form-input" placeholder="08xxxx">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-input" placeholder="opsional@email.com">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modalKorwil');
        const form = document.getElementById('formKorwil');
        const title = document.getElementById('modalTitle');

        function openTambahModal() {
            form.reset();
            form.action = 'store/proses_korwil.php?action=add';
            title.innerText = 'Tambah Koordinator Wilayah';
            document.getElementById('id_field').value = '';
            modal.style.display = 'flex';
        }

        function openEditModal(data) {
            form.reset();
            form.action = 'store/proses_korwil.php?action=edit';
            title.innerText = 'Edit Koordinator Wilayah';
            
            document.getElementById('id_field').value = data.id;
            document.getElementById('wilayah').value = data.wilayah;
            document.getElementById('nama_korwil').value = data.nama_korwil;
            document.getElementById('no_telp').value = data.no_telp || '';
            document.getElementById('email').value = data.email || '';
            
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function hapusData(id, nama) {
            Swal.fire({
                title: 'Hapus Koordinator?',
                text: `Apakah Anda yakin ingin menghapus "${nama}"? Data petugas parkir yang terikat dengan koordinator ini harus dipindahkan terlebih dahulu.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#e2e8f0',
                confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    cancelButton: 'swal-cancel-dark',
                    popup: 'swal-popup-custom',
                },
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'store/proses_korwil.php?action=delete&id=' + id;
                }
            });
        }

        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        // TOAST NOTIFIKASI
        (function () {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');
            const msg = params.get('msg');

            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            if (status === 'success_add') {
                toast.fire({ icon: 'success', title: 'Koordinator berhasil ditambahkan!' });
            } else if (status === 'success_edit') {
                toast.fire({ icon: 'success', title: 'Data koordinator berhasil diperbarui!' });
            } else if (status === 'success_delete') {
                toast.fire({ icon: 'success', title: 'Koordinator berhasil dihapus!' });
            } else if (status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg || 'Terjadi kesalahan pada server.',
                    confirmButtonColor: '#2563eb',
                });
            }

            if (status) {
                const url = new URL(window.location);
                url.searchParams.delete('status');
                url.searchParams.delete('msg');
                window.history.replaceState({}, '', url);
            }
        })();
    </script>
</body>

</html>