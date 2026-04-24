<?php
include '../config/auth.php';
include '../config/db.php';
checkLogin();
allowRole(['super-admin']);

$current_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id != '$current_id' ORDER BY role ASC, nama ASC");

$total_admin = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin'")->fetch_assoc()['total'];
$total_kepala = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='kepala-dinas'")->fetch_assoc()['total'];
$total_bendahara = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='bendahara'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Retribusi Parkir | Dishub Kab. Sidoarjo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 flex">

    <?php include '../components/navbar.php'; ?>

    <div class="app-body">
        <?php include '../components/sidebar.php'; ?>

        <main class="main-content">
            <div class="max-w-6xl mx-auto">

                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Manajemen Pengguna</h1>
                        <p class="text-slate-500 text-sm">Kelola hak akses Admin dan Kepala Dishub dalam sistem.</p>
                    </div>
                    <button onclick="openAddModal()"
                        class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition shadow-lg shadow-blue-900/20">
                        <span>+ Tambah User Baru</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 text-xl">
                            👤</div>
                        <div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Admin</div>
                            <div class="text-2xl font-bold text-slate-800"><?= $total_admin; ?> <span
                                    class="text-sm font-normal text-slate-400">Personel</span></div>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 text-xl">
                            🎓</div>
                        <div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kepala Dishub</div>
                            <div class="text-2xl font-bold text-slate-800"><?= $total_kepala; ?> <span
                                    class="text-sm font-normal text-slate-400">Pejabat</span></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-bottom border-slate-200">
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">User</th>
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="p-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-4 flex items-center gap-3">
                                        <!-- <img src="<?= $row['foto'] ? '../assets/img/users/' . $row['foto'] : 'https://ui-avatars.com/api/?name=' . $row['nama_lengkap'] . '&background=003366&color=fff'; ?>" 
                                         class="w-10 h-10 rounded-full object-cover"> -->
                                        <div>
                                            <div class="font-bold text-slate-700"><?= $row['nama']; ?></div>
                                            <div class="text-xs text-slate-400">@<?= $row['username']; ?></div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span
                                            class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide
                                        <?= $row['role'] == 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'; ?>">
                                            <?= str_replace('_', ' ', $row['role']); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button onclick='openEditModal(<?= json_encode($row); ?>)'
                                                class="p-2 hover:bg-amber-50 text-amber-600 rounded-lg transition"
                                                title="Edit">✏️</button>
                                            <a href="../store/proses_user.php?action=delete&id=<?= $row['id']; ?>"
                                                onclick="return confirm('Hapus user ini?')"
                                                class="p-2 hover:bg-red-50 text-red-600 rounded-lg transition"
                                                title="Hapus">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="userModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div
            class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 id="modalTitle" class="font-bold text-slate-800 text-lg">Tambah User</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>
            <form action="../store/proses_user.php" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id" id="form_id">
                <input type="hidden" name="action" id="form_action" value="add">

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="form_nama" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Username</label>
                        <input type="text" name="username" id="form_username" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Role</label>
                        <select name="role" id="form_role"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm bg-white">
                            <option value="admin">Admin</option>
                            <option value="kepala-dinas">Kepala Dishub</option>
                        </select>
                    </div>
                </div>
                <!-- <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email</label>
                    <input type="email" name="email" id="form_email"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                </div> -->
                <div id="pass_field">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Password</label>
                    <input type="password" name="password" id="form_password"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                        placeholder="********">
                    <p id="pass_note" class="text-[10px] text-slate-400 mt-1 hidden">*Kosongkan jika tidak ingin
                        mengganti password</p>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeModal()"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-blue-900 text-white font-bold text-sm">Simpan
                        Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('userModal');

        function openAddModal() {
            document.getElementById('modalTitle').innerText = "Tambah User Baru";
            document.getElementById('form_action').value = "add";
            document.getElementById('form_id').value = "";
            document.getElementById('pass_note').classList.add('hidden');
            document.getElementById('form_password').required = true;
            modal.style.display = 'flex';
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = "Edit Data User";
            document.getElementById('form_action').value = "edit";
            document.getElementById('form_id').value = data.id;
            document.getElementById('form_nama').value = data.nama_lengkap;
            document.getElementById('form_username').value = data.username;
            // document.getElementById('form_email').value = data.email;
            document.getElementById('form_role').value = data.role;
            document.getElementById('pass_note').classList.remove('hidden');
            document.getElementById('form_password').required = false;
            modal.style.display = 'flex';
        }

        function closeModal() {
            modal.style.display = 'none';
        }
    </script>
</body>

</html>