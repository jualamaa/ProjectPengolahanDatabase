<?php
include 'config.php';
session_start();

// Deteksi otomatis nama kolom peminjam di database
function dapatkanKolomPeminjam($conn) {
    if ($cek = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman")) {
        while ($row = mysqli_fetch_assoc($cek)) {
            $field = $row['Field'];
            $type = strtolower($row['Type']);
            if ((strpos($type, 'varchar') !== false || strpos($type, 'text') !== false) && strpos(strtolower($field), 'buku') === false) {
                return $field;
            }
        }
    }
    return 'nama_peminjam';
}

$kolom_peminjam = dapatkanKolomPeminjam($conn);

// PROSES TAMBAH PINJAMAN
if (isset($_POST['simpan_transaksi'])) {
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $id_buku        = $_POST['id_buku'];
    $jumlah_pinjam  = intval($_POST['jumlah_pinjam']);
    $nilai_peminjam = mysqli_real_escape_string($conn, $_POST['peminjam'] ?? '');

    $cek_buku  = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = '$id_buku'");
    $data_buku = mysqli_fetch_assoc($cek_buku);

    if ($data_buku && $data_buku['stok'] >= $jumlah_pinjam) {
        $status_awal = isset($_SESSION['admin']) ? 'Dipinjam' : 'Menunggu Persetujuan';
        if (isset($_SESSION['admin'])) {
            mysqli_query($conn, "UPDATE buku SET stok = stok - $jumlah_pinjam WHERE id_buku = '$id_buku'");
        }
        
        mysqli_query($conn, "INSERT INTO peminjaman ($kolom_peminjam, tanggal_pinjam, id_buku, jumlah_pinjam, status) VALUES ('$nilai_peminjam', '$tanggal_pinjam', '$id_buku', '$jumlah_pinjam', '$status_awal')");
        header("Location: transaksi.php?status=" . (isset($_SESSION['admin']) ? 'sukses' : 'menunggu_admin'));
        exit();
    }
    header("Location: transaksi.php?status=stok_kurang");
    exit();
}

// PROTEKSI AKSES ADMIN UNTUK AKSI GET
if (isset($_GET['setujui']) || isset($_GET['tolak']) || isset($_GET['kembalikan'])) {
    if (!isset($_SESSION['admin'])) {
        header("Location: transaksi.php?status=akses_ditolak");
        exit();
    }
}

// PROSES PERSETUJUAN OLEH ADMIN
if (isset($_GET['setujui'])) {
    $id_pinjam = $_GET['setujui'];
    $data_pinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_buku, jumlah_pinjam FROM peminjaman WHERE id_pinjam = '$id_pinjam'"));

    if ($data_pinjam) {
        $id_buku = $data_pinjam['id_buku'];
        $jumlah_pinjam = $data_pinjam['jumlah_pinjam'];
        $data_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = '$id_buku'"));

        if ($data_buku && $data_buku['stok'] >= $jumlah_pinjam) {
            mysqli_query($conn, "UPDATE buku SET stok = stok - $jumlah_pinjam WHERE id_buku = '$id_buku'");
            mysqli_query($conn, "UPDATE peminjaman SET status = 'Dipinjam' WHERE id_pinjam = '$id_pinjam'");
            header("Location: transaksi.php?status=disetujui_sukses");
            exit();
        }
    }
    header("Location: transaksi.php?status=stok_kurang");
    exit();
}

// PROSES PENOLAKAN OLEH ADMIN
if (isset($_GET['tolak'])) {
    mysqli_query($conn, "UPDATE peminjaman SET status = 'Ditolak' WHERE id_pinjam = '" . $_GET['tolak'] . "'");
    header("Location: transaksi.php?status=ditolak_sukses");
    exit();
}

// PROSES PENGEMBALIAN BUKU
if (isset($_GET['kembalikan'])) {
    $id_pinjam = $_GET['kembalikan'];
    $data_pinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_buku, jumlah_pinjam FROM peminjaman WHERE id_pinjam = '$id_pinjam'"));

    if ($data_pinjam) {
        mysqli_query($conn, "UPDATE buku SET stok = stok + {$data_pinjam['jumlah_pinjam']} WHERE id_buku = '{$data_pinjam['id_buku']}'");
        mysqli_query($conn, "UPDATE peminjaman SET status = 'Dikembalikan', tanggal_kembali = '" . date('Y-m-d') . "' WHERE id_pinjam = '$id_pinjam'");
        header("Location: transaksi.php?status=kembali_sukses");
        exit();
    }
}

// Ambil data untuk komponen form & tabel
$pilihanBuku = mysqli_query($conn, "SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC");

$where_clause = '';
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clause = "WHERE peminjaman.$kolom_peminjam LIKE '%$search%'";
}

$daftarTransaksi = mysqli_query($conn, "SELECT peminjaman.*, buku.judul, buku.gambar FROM peminjaman JOIN buku ON peminjaman.id_buku = buku.id_buku $where_clause ORDER BY peminjaman.id_pinjam DESC");
$kolom_tampil_tabel = $kolom_peminjam; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Pinjam - Perpustakaan</title>
    <link class="js-meta-tag" rel="stylesheet" href="./src/output.css">
    <script>
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
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
            
            <nav id="menu-items" class="hidden md:block space-y-1 mb-4 md:mb-0">
                <a href="index.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-600 dark:text-slate-200 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-slate-100 dark:hover:bg-gray-800 font-semibold transition">Dashboard</a>
                
                <?php if (isset($_SESSION['admin'])): ?>
                    <a href="data_buku.php" class="flex items-center gap-3 py-3 px-4 rounded-xl text-slate-600 dark:text-slate-200 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-slate-100 dark:hover:bg-gray-800 font-semibold transition">Kelola Buku</a>
                <?php endif; ?>
                
                <a href="transaksi.php" class="flex items-center justify-between py-3 px-4 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 text-white font-bold shadow-md shadow-orange-500/20">
                    <span>Peminjaman</span><?= !isset($_SESSION['admin']) ? '<span class="text-xs"></span>' : '' ?>
                </a>
                
                <div class="md:hidden pt-4 mt-4 border-t border-slate-200 dark:border-gray-800">
                    <div class="bg-slate-100 dark:bg-gray-800/50 p-3 rounded-xl border border-slate-200 dark:border-gray-700 mb-2 text-xs text-center text-slate-600 dark:text-gray-400">Login sebagai: <span class="text-orange-600 dark:text-orange-400 font-bold"><?= isset($_SESSION['admin']) ? 'Admin' : (isset($_SESSION['user']) ? 'User' : 'Tamu'); ?></span></div>
                    <?php if (isset($_SESSION['admin']) || isset($_SESSION['user'])): ?>
                        <a href="logout.php" class="block text-center py-2.5 px-4 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 font-semibold transition border border-red-200 dark:border-red-900/30">Log Out</a>
                    <?php else: ?>
                        <a href="login.php" class="block text-center py-2.5 px-4 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold transition">Login Admin</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>

        <div class="hidden md:block">
            <div class="bg-slate-100 dark:bg-gray-800/50 p-3 rounded-xl border border-slate-200 dark:border-gray-700 mb-2 text-xs text-center text-slate-600 dark:text-gray-400">Login sebagai: <span class="text-orange-600 dark:text-orange-400 font-bold"><?= isset($_SESSION['admin']) ? 'Admin' : (isset($_SESSION['user']) ? 'User' : 'Tamu'); ?></span></div>
            <?php if (isset($_SESSION['admin']) || isset($_SESSION['user'])): ?>
                <a href="logout.php" class="block text-center py-2.5 px-4 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 font-semibold transition border border-red-200 dark:border-red-900/30">Log Out</a>
            <?php else: ?>
                <a href="login.php" class="block text-center py-2.5 px-4 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 text-white font-semibold transition">Login Admin</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 p-4 sm:p-6 md:p-10 overflow-y-auto max-h-screen">
        <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white mb-8 text-center">Panel Input Peminjaman Baru</h2>

        <?php if(isset($_GET['status'])): ?>
            <?php 
                $alerts = [
                    'sukses' => ['bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900/40 text-emerald-600 dark:text-emerald-400', '✓ Transaksi peminjaman berhasil dicatat oleh Admin!'],
                    'menunggu_admin' => ['bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900/40 text-amber-600 dark:text-amber-400', '⏳ Permintaan peminjaman berhasil diajukan. Menunggu validasi Admin!'],
                    'disetujui_sukses' => ['bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900/40 text-emerald-600 dark:text-emerald-400', '✔️ Peminjaman disetujui! Stok buku otomatis dikurangi.'],
                    'ditolak_sukses' => ['bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/40 text-rose-600 dark:text-rose-400', '❌ Permintaan peminjaman telah ditolak.'],
                    'kembali_sukses' => ['bg-blue-50 dark:bg-blue-950/40 border-blue-200 dark:border-blue-900/40 text-blue-600 dark:text-blue-400', '↩ Buku berhasil dikembalikan dan stok telah diperbarui!'],
                    'stok_kurang' => ['bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/40 text-rose-600 dark:text-rose-400', '⚠ Gagal! Jumlah peminjaman melebihi stok yang tersedia.'],
                    'akses_ditolak' => ['bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900/40 text-rose-600 dark:text-rose-400', '⚠ Akses Ditolak! Hanya Admin yang berwenang melakukan tindakan ini.']
                ];
                if (array_key_exists($_GET['status'], $alerts)):
            ?>
                <div class="<?= $alerts[$_GET['status']][0] ?> p-4 rounded-xl mb-6 text-sm"><?= $alerts[$_GET['status']][1] ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm mb-10 transition-colors duration-300">
            <h3 class="text-orange-600 dark:text-orange-400 font-bold mb-4 flex items-center gap-2">
                <span>Form Peminjaman Buku Koleksi</span>
            </h3>
            
            <form action="" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Nama Anggota / Peminjam</label>
                        <input type="text" name="peminjam" placeholder="Contoh: Joseph" required class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-3 mt-1 text-slate-800 dark:text-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Tanggal Peminjaman</label>
                        <input type="date" name="tanggal_pinjam" required class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-3 mt-1 text-slate-800 dark:text-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Pilih Buku Koleksi</label>
                        <select name="id_buku" required class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-3 mt-1 text-slate-800 dark:text-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 outline-none transition-all">
                            <option value="">-- Pilih Buku --</option>
                            <?php while($bk = mysqli_fetch_assoc($pilihanBuku)): ?>
                                <option value="<?= $bk['id_buku']; ?>"><?= htmlspecialchars($bk['judul'] ?? ''); ?> (Stok: <?= $bk['stok']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 dark:text-gray-400 font-semibold">Kuantitas Pinjam</label>
                        <input type="number" name="jumlah_pinjam" min="1" placeholder="Pcs" required class="w-full bg-slate-50 dark:bg-gray-950 border border-slate-200 dark:border-gray-800 rounded-xl p-3 mt-1 text-slate-800 dark:text-white focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10 outline-none transition-all">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" name="simpan_transaksi" class="bg-gradient-to-r from-orange-500 to-amber-600 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-orange-500/20 hover:opacity-90 transition">
                        Ajukan Pinjaman
                    </button>
                </div>
            </form>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-6">
            <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight text-center sm:text-left">Riwayat Peminjaman Buku</h3>

            <form action="transaksi.php" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-72">
                    <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Cari nama peminjam..." class="w-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 rounded-xl py-2.5 px-4 pr-10 text-sm text-slate-800 dark:text-white focus:border-orange-500 outline-none placeholder:text-slate-400 shadow-sm transition-all">
                    <?php if(!empty($_GET['search'])): ?>
                        <a href="transaksi.php" class="absolute right-3 top-3.5 text-slate-400 hover:text-orange-500 text-xs transition">✕</a>
                    <?php endif; ?>
                </div>
                <button type="submit" class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 hover:border-orange-500 text-slate-600 dark:text-gray-300 hover:text-orange-500 dark:hover:text-white font-semibold px-5 py-3 rounded-xl text-sm transition shadow-sm shrink-0">Cari</button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 overflow-hidden shadow-sm transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-gray-800/50 text-slate-700 dark:text-orange-400 uppercase text-xs font-bold tracking-wider border-b border-slate-200 dark:border-gray-800">
                            <th class="p-4 w-20">Cover</th>
                            <th class="p-4">Peminjam</th>
                            <th class="p-4">Buku</th>
                            <th class="p-4">Tanggal Pinjam</th>
                            <th class="p-4">Jumlah</th>
                            <th class="p-4 text-center">Aksi / Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 dark:text-gray-300 text-sm divide-y divide-slate-100 dark:divide-gray-800">
                        <?php if (mysqli_num_rows($daftarTransaksi) == 0): ?>
                            <tr><td colspan="6" class="p-4 text-center text-slate-400 dark:text-gray-500 italic">Belum ada data transaksi peminjaman atau hasil pencarian tidak ditemukan.</td></tr>
                        <?php endif; ?>
                        <?php while ($t = mysqli_fetch_assoc($daftarTransaksi)): 
                            $nama_tampil = $t[$kolom_tampil_tabel] ?? 'Orang Baik';
                            $status_transaksi = $t['status'] ?? 'Dipinjam';
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition">
                            <td class="p-4">
                                <img src="assets/<?= $t['gambar'] ?? 'default.jpg'; ?>" onerror="this.src='https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=100'" class="w-10 h-14 object-cover rounded-md shadow-sm bg-slate-100 dark:bg-gray-800">
                            </td>
                            <td class="p-4 font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($nama_tampil); ?></td>
                            <td class="p-4 text-slate-500 dark:text-gray-400"><?= htmlspecialchars($t['judul'] ?? ''); ?></td>
                            <td class="p-4 text-slate-500 dark:text-gray-400">📅 <?= isset($t['tanggal_pinjam']) ? date('d-m-Y', strtotime($t['tanggal_pinjam'])) : ''; ?></td>
                            <td class="p-4 font-bold text-orange-600 dark:text-orange-400"><?= $t['jumlah_pinjam'] ?? 0; ?> unit</td>
                            <td class="p-4 text-center">
                                
                                <?php if ($status_transaksi == 'Menunggu Persetujuan'): ?>
                                    <?php if (isset($_SESSION['admin'])): ?>
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="transaksi.php?setujui=<?= $t['id_pinjam']; ?>" onclick="return confirm('Setujui peminjaman oleh <?= htmlspecialchars(addslashes($nama_tampil)); ?>?')" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 font-semibold transition">Setuju</a>
                                            <a href="transaksi.php?tolak=<?= $t['id_pinjam']; ?>" onclick="return confirm('Tolak peminjaman oleh <?= htmlspecialchars(addslashes($nama_tampil)); ?>?')" class="text-red-600 dark:text-red-400 hover:text-red-500 font-semibold transition">Tolak</a>
                                        </div>
                                    <?php else: ?>
                                        <span class="bg-amber-100 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30 px-3 py-1 rounded-full text-xs font-bold">⏳ Menunggu Persetujuan</span>
                                    <?php endif; ?>

                                <?php elseif ($status_transaksi == 'Dipinjam'): ?>
                                    <?php if (isset($_SESSION['admin'])): ?>
                                        <a href="transaksi.php?kembalikan=<?= $t['id_pinjam']; ?>" onclick="return confirm('Apakah buku ini sudah dikembalikan?')" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 font-semibold transition">Kembalikan</a>
                                    <?php else: ?>
                                        <span class="bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/30 px-3 py-1 rounded-full text-xs font-bold">🔷 Dipinjam</span>
                                    <?php endif; ?>

                                <?php elseif ($status_transaksi == 'Ditolak'): ?>
                                    <span class="bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900/30 px-3 py-1 rounded-full text-xs font-bold">❌ Ditolak</span>

                                <?php elseif ($status_transaksi == 'Dikembalikan'): ?>
                                    <div class="flex flex-col items-center gap-0.5">
                                        <span class="bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 px-3 py-1 rounded-full text-xs font-bold">🟢 Dikembalikan</span>
                                        <span class="text-[10px] text-slate-400 dark:text-gray-500 font-medium">Kembali: <?= date('d-m-Y', strtotime($t['tanggal_kembali'])); ?></span>
                                    </div>
                                <?php endif; ?>

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
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleIcon = document.getElementById('theme-toggle-icon');
        const htmlTag = document.documentElement;

        function applyTheme() {
            const isDark = localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            htmlTag.classList.toggle('dark', isDark);
            if (themeToggleIcon) themeToggleIcon.textContent = isDark ? '🌙' : '☀️';
        }
        applyTheme();

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                const willBeDark = !htmlTag.classList.contains('dark');
                htmlTag.classList.toggle('dark', willBeDark);
                localStorage.setItem('theme', willBeDark ? 'dark' : 'light');
                if (themeToggleIcon) themeToggleIcon.textContent = willBeDark ? '🌙' : '☀️';
            });
        }

        const menuBtn = document.getElementById('menu-btn');
        const menuItems = document.getElementById('menu-items');
        if (menuBtn && menuItems) {
            menuBtn.addEventListener('click', () => { menuItems.classList.toggle('hidden'); });
        }
    </script>
</body>
</html>