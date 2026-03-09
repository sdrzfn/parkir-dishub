<div class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-blue-700">Masuk</h2>
            <p class="text-gray-500 mt-2">Sistem Informasi Perparkiran</p>
        </div>
        <form action="store/proses_login.php" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input type="text" name="username" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>
            <button type="submit" class="w-full bg-blue-700 text-white py-3 rounded-xl font-bold hover:bg-blue-800 transition shadow-lg">Login</button>
        </form>
    </div>
</div>