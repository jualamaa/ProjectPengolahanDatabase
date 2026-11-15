<?php
include 'config.php';
session_start();

// Validasi status login
$isAdmin = isset($_SESSION['admin']) || isset($_SESSION['username']);

// variable form supaya default kosong
$mode_edit = false;
$edit_id = $edit_judul = $edit_pengarang = $edit_tahun = $edit_stok = '';

// proses ambil data untuk edit
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id_ambil = mysqli_real_escape_string($conn, $_GET['id']);
    $query = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku = '$id_ambil'");
    $data = mysqli_fetch_assoc($query);
    
    if ($data) {
        $mode_edit      = true;
        $edit_id        = $data['id_buku'];
        $edit_judul     = $data['judul'];
        $edit_pengarang = $data['pengarang'];
        $edit_tahun     = $data['tahun_terbit'];
        $edit_stok      = $data['stok'];
    }
}
// Fungsi supaya foto lama ikut terhapus
function hapusGambarLama(mysqli $conn, $id) {
    $query = mysqli_query($conn, "SELECT gambar FROM buku WHERE id_buku = '$id'");
    $data = mysqli_fetch_assoc($query);
    if ($data && $data['gambar'] != 'default.jpg' && file_exists('assets/' . $data['gambar'])) {
        unlink('assets/' . $data['gambar']);
    }
}

// proses simpan/update data
if (isset($_POST['simpan_buku'])) {
    $id        = mysqli_real_escape_string($conn, $_POST['id_buku'] ?? '');
    $judul     = mysqli_real_escape_string($conn, $_POST['judul']);
    $pengarang = mysqli_real_escape_string($conn, $_POST['pengarang']);
    $tahun     = intval($_POST['tahun_terbit']);
    $stok      = intval($_POST['stok']);
    
    $nama_gambar = $_FILES['gambar']['name'] ?? '';
    $tmp_gambar  = $_FILES['gambar']['tmp_name'] ?? '';

    if (!empty($id)) {
        // Mode Update Buku
        if ($nama_gambar != '') {
            $gambar_baru = time() . '_' . $nama_gambar;
            hapusGambarLama($conn, $id);
            move_uploaded_file($tmp_gambar, 'assets/' . $gambar_baru);
            
            mysqli_query($conn, "UPDATE buku SET judul='$judul', pengarang='$pengarang', tahun_terbit='$tahun', stok='$stok', gambar='$gambar_baru' WHERE id_buku='$id'");
        } else {
            mysqli_query($conn, "UPDATE buku SET judul='$judul', pengarang='$pengarang', tahun_terbit='$tahun', stok='$stok' WHERE id_buku='$id'");
        }
        header("Location: data_buku.php?status=edit_sukses");
        exit();
    } else {
        // Mode Tambah Buku Baru
        $gambar_baru = 'default.jpg';
        if ($nama_gambar != '') {
            $gambar_baru = time() . '_' . $nama_gambar;
            move_uploaded_file($tmp_gambar, 'assets/' . $gambar_baru);
        }
        mysqli_query($conn, "INSERT INTO buku (judul, pengarang, tahun_terbit, stok, gambar) VALUES ('$judul', '$pengarang', '$tahun', '$stok', '$gambar_baru')");
        header("Location: data_buku.php?status=tambah_sukses");
        exit();
    }
}

// proses hapus buku
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($conn, $_GET['hapus']);
    hapusGambarLama($conn, $id_hapus);
    mysqli_query($conn, "DELETE FROM buku WHERE id_buku = '$id_hapus'");
    header("Location: data_buku.php?status=hapus_sukses");
    exit();
}

// LOGIKA PROSES FITUR SEARCH (MURNI PHP GET)
$search = '';
$where_clause = '';
if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    // Mencari berdasarkan judul buku ATAU nama pengarang
    $where_clause = "WHERE judul LIKE '%$search%' OR pengarang LIKE '%$search%'";
}

// ambil data untuk tabel buku dengan menyisipkan filter search jika ada
$daftarBuku = mysqli_query($conn, "SELECT * FROM buku $where_clause ORDER BY id_buku DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Perpustakaan</title>
    <link rel="stylesheet" href="./src/output.css">
    <script>
        // Inline script untuk mencegah flash-screen putih saat reload di mode dark
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-gray-950 text-slate-800 dark:text-gray-100 flex flex-col md:flex-row min-h-screen transition-colors duration-300">

    <div class="w-full md:w-64 bg-white dark:bg-gray-900 border-b md:border-b-0 md:border-r border-slate-200 dark:border-gray-800 p-4 md:p-5 flex flex-col justify-between shrink-0 z-50 shadow-sm transition-colors duration-300">
        <div>
            <div class="flex items-center justify-between md:justify-start gap-3 px-2 mb-4 md:mb-8">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📚</span>
                    <h1 class="text-xl font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-amber-600">Perpustakaan Orang Baik</h1>
                </div>
                
                <div class="flex items-center gap-2">
                    <button id="theme-toggle" type="button" class="p-2 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-amber-400 hover:ring-2 hover:ring-orange-500/20 focus:outline-none transition-all">
                        <span id="theme-toggle-icon">☀️</span>
                    </button>

                    <button id="menu-btn" class="md:hidden block text-slate-500 dark:text-gray-400 hover:text-orange-500 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <nav id="menu-items" class="hidden md:block space-y-1 mb-4 md:mb-0">
                <a href="index.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-600 dark:text-slate-200 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-slate-100 dark:hover:bg-gray-800 font-semibold transition">Dashboard</a>
                
                <a href="data_buku.php" class="flex items-center gap-3 py-3 px-4 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 text-white font-bold shadow-md shadow-orange-500/20">Kelola Buku</a>
                
                <a href="transaksi.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-600 dark:text-slate-200 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-slate-100 dark:hover:bg-gray-800 font-semibold transition">Peminjaman</a>
                
                <div class="md:hidden pt-4 mt-4 border-t border-slate-200 dark:border-gray-800">
                    <div class="bg-slate-100 dark:bg-gray-800/50 p-3 rounded-xl border border-slate-200 dark:border-gray-700 mb-2 text-xs text-center text-slate-600 dark:text-gray-400">Login sebagai: <span class="text-orange-600 dark:text-orange-400 font-bold">Admin</span></div>
                    <a href="logout.php" class="block text-center py-2.5 px-4 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 font-semibold transition border border-red-200 dark:border-red-900/30">Log Out</a>
                </div>
            </nav>
        </div>

        <div class="hidden md:block">
            <div class="bg-slate-100 dark:bg-gray-800/50 p-3 rounded-xl border border-slate-200 dark:border-gray-700 mb-2 text-xs text-center text-slate-600 dark:text-gray-400">Login sebagai: <span class="text-orange-600 dark:text-orange-400 font-bold">Admin</span></div>
            <a href="logout.php" class="block text-center py-2.5 px-4 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 font-semibold transition border border-red-200 dark:border-red-900/30">Log Out</a>
        </div>
    </div>

    <div class="flex-1 p-4 sm:p-6 md:p-10 overflow-y-auto max-h-screen">
        <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white mb-8 text-center">Panel Pengelolaan Buku</h2>

        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] == 'tambah_sukses'): ?>
                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/40 text-emerald-600 dark:text-emerald-400 p-4 rounded-xl mb-6 text-sm">✓ Buku baru berhasil didaftarkan ke sistem!</div>
            <?php elseif($_GET['status'] == 'edit_sukses'): ?>
                <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/40 text-blue-600 dark:text-blue-400 p-4 rounded-xl mb-6 text-sm">✓ Perubahan data buku berhasil disimpan!</div>
            <?php elseif($_GET['status'] == 'hapus_sukses'): ?>
                <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/40 text-rose-600 dark:text-rose-400 p-4 rounded-xl mb-6 text-sm">✓ Buku telah dihapus dari sistem.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm mb-10 transition-colors duration-300">
            <h3 class="text-orange-600 dark:text-orange-400 font-bold mb-4 flex items-center gap-2">
                <span><?= $mode_edit ? '📝 Edit Data Buku Koleksi' : 'Tambah Data Buku Ke Sistem'; ?></span>
            </h3>
            
            <form action="data_buku.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id_buku" value="<?= htmlspecialchars($edit_id); ?>">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Judul Buku</label>
                        <input type="text" name="judul" value="<?= htmlspecialchars($edit_judul); ?>" placeholder="Prince Of Sword" required class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-3 mt-1 text-slate-800 dark:text-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Nama Pengarang</label>
                        <input type="text" name="pengarang" value="<?= htmlspecialchars($edit_pengarang); ?>" required placeholder="Joseph" class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-3 mt-1 text-slate-800 dark:text-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($edit_tahun); ?>" placeholder="2026" required class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-3 mt-1 text-slate-800 dark:text-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Jumlah Stok Buku</label>
                        <input type="number" name="stok" value="<?= htmlspecialchars($edit_stok); ?>" placeholder="20" min="0" required class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-3 mt-1 text-slate-800 dark:text-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Upload Cover Buku <span class="text-slate-400 dark:text-gray-500 font-normal">(Kosongkan jika tidak ingin mengubah cover)</span></label>
                    <input type="file" name="gambar" class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-2.5 mt-1 text-sm text-slate-500 dark:text-gray-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-200 dark:file:bg-gray-800 file:text-slate-700 dark:file:text-orange-400 hover:file:opacity-80 outline-none transition-all">
                </div>

                <div class="flex justify-end gap-2">
                    <?php if ($mode_edit): ?>
                        <a href="data_buku.php" class="bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-gray-400 font-bold px-5 py-3 rounded-xl hover:opacity-80 transition">Batal</a>
                    <?php endif; ?>
                    <button type="submit" name="simpan_buku" class="bg-gradient-to-r from-orange-500 to-amber-600 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-orange-500/20 hover:opacity-90 transition">
                        <?= $mode_edit ? 'Simpan Perubahan' : 'Masukkan Data'; ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-6">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight text-center sm:text-left">Daftar Koleksi Perpustakaan</h3>

            <form action="data_buku.php" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-72">
                    <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Cari judul atau pengarang..." class="w-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 rounded-xl py-2.5 px-4 pr-10 text-sm text-slate-800 dark:text-white focus:border-orange-500 outline-none placeholder:text-slate-400 shadow-sm transition-all">
                    <?php if(isset($_GET['search']) && $_GET['search'] != ''): ?>
                        <a href="data_buku.php" class="absolute right-3 top-3.5 text-slate-400 hover:text-orange-500 text-xs transition">✕</a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:border-orange-500 text-slate-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-white font-semibold px-5 py-3 rounded-xl text-sm transition shadow-sm shrink-0">Cari</button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 overflow-hidden shadow-sm transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-gray-800/50 text-slate-700 dark:text-orange-400 uppercase text-xs font-bold tracking-wider border-b border-slate-200 dark:border-gray-800">
                            <th class="p-4">Cover</th>
                            <th class="p-4">Judul</th>
                            <th class="p-4">Pengarang</th>
                            <th class="p-4">Stok</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 dark:text-gray-300 text-sm divide-y divide-slate-100 dark:divide-gray-800">
                        <?php if(mysqli_num_rows($daftarBuku) == 0): ?>
                            <tr><td colspan="5" class="p-4 text-center text-slate-400 dark:text-gray-500 italic">Belum ada koleksi buku di database atau hasil pencarian tidak ditemukan.</td></tr>
                        <?php endif; ?>
                        <?php while($b = mysqli_fetch_assoc($daftarBuku)): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition">
                            <td class="p-4">
                                <img src="assets/<?= $b['gambar']; ?>" onerror="this.src='https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=100'" class="w-10 h-14 object-cover rounded-md shadow-sm bg-slate-100 dark:bg-gray-800">
                            </td>
                            <td class="p-4 font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($b['judul']); ?></td>
                            <td class="p-4 text-slate-500 dark:text-gray-400"><?= htmlspecialchars($b['pengarang']); ?> (<span class="font-medium"><?= $b['tahun_terbit']; ?></span>)</td>
                            <td class="p-4 font-bold text-orange-600 dark:text-orange-400"><?= $b['stok']; ?> unit</td>
                            
                            <td class="p-4 text-center space-x-3">
                                <a href="data_buku.php?action=edit&id=<?= $b['id_buku']; ?>" class="text-amber-600 dark:text-amber-500 hover:text-orange-500 font-semibold transition">Edit</a>
                                <a href="data_buku.php?hapus=<?= $b['id_buku']; ?>" onclick="return confirm('Anda Yakin mau hapus buku judulnya <?= htmlspecialchars(addslashes($b['judul'])); ?>?')" class="text-red-600 dark:text-red-400 hover:text-red-500 font-semibold transition">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="w-full bg-white dark:bg-gray-900 border-t border-slate-200 dark:border-gray-800 py-5 px-6 mt-12 transition-colors duration-300">
            <div class="max-w-9xl mx-auto flex flex-row items-center justify-center gap-4 text-xs text-slate-400 dark:text-gray-500">
                <div class="text-center">
                    <span class="font-bold text-slate-600 dark:text-gray-400">Perpustakaan Orang Baik</span> &copy; <?= date('Y'); ?>. XI PPLG A.
                    <p class="font-bold text-orange-500 dark:text-orange-400 tracking-normal mt-1">By Yoseph Ekarunio Andika S.</p>
                </div> 
            </div>
        </footer>
    </div>

    <script>
        // toggle theme
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleIcon = document.getElementById('theme-toggle-icon');
        const htmlTag = document.documentElement;

        function applyTheme() {
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                htmlTag.classList.add('dark');
                if (themeToggleIcon) themeToggleIcon.textContent = '🌙';
            } else {
                htmlTag.classList.remove('dark');
                if (themeToggleIcon) themeToggleIcon.textContent = '☀️';
            }
        }
        applyTheme();

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                if (htmlTag.classList.contains('dark')) {
                    htmlTag.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    if (themeToggleIcon) themeToggleIcon.textContent = '☀️';
                } else {
                    htmlTag.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    if (themeToggleIcon) themeToggleIcon.textContent = '🌙';
                }
            });
        }

        // side bar responsif
        const menuBtn = document.getElementById('menu-btn');
        const menuItems = document.getElementById('menu-items');
        if (menuBtn && menuItems) {
            menuBtn.addEventListener('click', () => { menuItems.classList.toggle('hidden'); });
        }
    </script>
</body>
</html>