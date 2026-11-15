<?php
include 'config.php';
session_start();

// Hitung Statistik
$countBuku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM buku"))['total'];
$countPinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman"))['total'];

// Ambil daftar Buku & Riwayat Peminjaman (Query bersih & cepat)
$daftarBuku = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC");

// PERUBAHAN DI SINI: Menyembunyikan status 'Dikembalikan' dan 'Ditolak' dari dashboard
$transaksi = mysqli_query($conn, "SELECT peminjaman.*, buku.judul, buku.gambar FROM peminjaman INNER JOIN buku ON peminjaman.id_buku = buku.id_buku WHERE peminjaman.status != 'Dikembalikan' AND peminjaman.status != 'Ditolak' ORDER BY peminjaman.id_pinjam DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan</title>
    <link rel="stylesheet" href="./src/output.css">
    <script>
        // Inline script untuk mencegah kedipan layar (Flash) saat reload halaman
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
                <a href="index.php" class="flex items-center gap-3 py-3 px-4 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 text-white font-bold shadow-md shadow-orange-500/20">Dashboard</a>
                
                <?php if(isset($_SESSION['admin'])): ?>
                    <a href="data_buku.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition text-slate-600 dark:text-slate-200 hover:text-orange-600 dark:hover:text-orange-400 font-semibold">Kelola Buku</a>
                <?php endif; ?>

                <?php if(isset($_SESSION['admin']) || isset($_SESSION['user'])): ?>
                    <a href="transaksi.php" class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800 transition text-slate-600 dark:text-slate-200 hover:text-orange-600 dark:hover:text-orange-400 font-semibold">Peminjaman</a>
                <?php endif; ?>
                
                <div class="md:hidden pt-4 mt-4 border-t border-slate-200 dark:border-gray-800">
                    <?php if(isset($_SESSION['admin']) || isset($_SESSION['user'])): ?>
                        <div class="bg-slate-100 dark:bg-gray-800/50 p-3 rounded-xl border border-slate-200 dark:border-gray-700 mb-2 text-xs text-center text-slate-600 dark:text-gray-400">Login sebagai: <span class="text-orange-600 dark:text-orange-400 font-bold"><?= isset($_SESSION['admin']) ? 'Admin' : 'User'; ?></span></div>
                        <a href="logout.php" class="block text-center py-2.5 px-4 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 font-semibold transition border border-red-200 dark:border-red-900/30">Log Out</a>
                    <?php else: ?>
                        <a href="login.php" class="block text-center py-2.5 px-4 rounded-xl bg-orange-600 text-white font-semibold transition shadow-lg shadow-orange-600/20">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>

        <div class="hidden md:block">
            <?php if(isset($_SESSION['admin']) || isset($_SESSION['user'])): ?>
                <div class="bg-slate-100 dark:bg-gray-800/50 p-3 rounded-xl border border-slate-200 dark:border-gray-700 mb-2 text-xs text-center text-slate-600 dark:text-gray-400">Login sebagai: <span class="text-orange-600 dark:text-orange-400 font-bold"><?= isset($_SESSION['admin']) ? 'Admin' : 'User'; ?></span></div>
                <a href="logout.php" class="block text-center py-2.5 px-4 rounded-xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 font-semibold transition border border-red-200 dark:border-red-900/30">Log Out</a>
            <?php else: ?>
                <a href="login.php" class="block text-center py-2.5 px-4 rounded-xl bg-orange-600 text-white hover:bg-orange-700 font-semibold transition shadow-lg shadow-orange-600/20">Login Admin</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 flex flex-col min-h-screen justify-between overflow-y-auto max-h-screen">
        <div class="p-4 sm:p-6 md:p-10">
            <div class="flex justify-center text-center items-center mb-8">
                <div>
                    <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white">Selamat Datang di Perpustakaan</h2>
                    <p class="text-sm text-slate-500 dark:text-gray-400 mt-1.5">Jelajahi buku favorit Anda dan pantau status peminjaman.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-10">
                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200 dark:border-gray-800 flex items-center justify-between shadow-sm transition-all duration-300">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Koleksi Buku</p>
                        <p class="text-3xl sm:text-4xl font-black text-orange-600 dark:text-orange-400 mt-1"><?= $countBuku; ?> <span class="text-sm text-slate-400 dark:text-gray-500 font-normal">Judul</span></p>
                    </div>
                    <span class="text-3xl sm:text-4xl bg-slate-50 dark:bg-gray-800 p-3 rounded-xl border border-slate-100 dark:border-gray-700">📚</span>
                </div>
                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-slate-200 dark:border-gray-800 flex items-center justify-between shadow-sm transition-all duration-300">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Total transaksi pinjaman</p>
                        <p class="text-3xl sm:text-4xl font-black text-amber-600 dark:text-amber-500 mt-1"><?= $countPinjam; ?> <span class="text-sm text-slate-400 dark:text-gray-500 font-normal">Kali</span></p>
                    </div>
                    <span class="text-3xl sm:text-4xl bg-slate-50 dark:bg-gray-800 p-3 rounded-xl border border-slate-100 dark:border-gray-700">🔄</span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8 pb-4 border-b border-slate-200 dark:border-gray-800">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-wide flex items-center gap-2">
                    Buku Terbaru Yang Tersedia
                </h3>
                
                <div class="relative w-full md:w-80 group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 dark:text-gray-400 group-focus-within:text-orange-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" id="buku-search" placeholder="Cari judul atau pengarang..." 
                           class="w-full bg-orange-50/40 dark:bg-orange-950/10 border border-orange-200 dark:border-orange-900/40 rounded-2xl pl-11 pr-10 py-4 text-sm text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-gray-500 focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/20 shadow-md shadow-orange-500/5 hover:border-orange-400 dark:hover:border-orange-500/60 transition-all duration-300 ring-2 ring-orange-500/5">
                    <button id="clear-search" class="hidden absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div id="book-grid" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-12">
                <?php if(mysqli_num_rows($daftarBuku) == 0): ?>
                    <p class="text-slate-400 dark:text-gray-500 col-span-full italic">Belum ada koleksi buku.</p>
                <?php endif; ?>
                <?php while($row = mysqli_fetch_assoc($daftarBuku)): ?>
                <div class="book-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 overflow-hidden shadow-sm hover:shadow-md hover:border-orange-400 dark:hover:border-orange-500/40 transition-all duration-300 group flex flex-col justify-between transform"
                     data-judul="<?= strtolower(htmlspecialchars($row['judul'] ?? '')); ?>"
                     data-pengarang="<?= strtolower(htmlspecialchars($row['pengarang'] ?? '')); ?>">
                    <div>
                        <div class="h-56 bg-slate-100 dark:bg-gray-950 relative overflow-hidden flex items-center justify-center border-b border-slate-100 dark:border-gray-800/50">
                            <img src="assets/<?= $row['gambar']; ?>" onerror="this.src='https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=400'" class="object-cover object-center h-full w-full group-hover:scale-105 transition duration-500" alt="Cover">
                            <span class="absolute top-2 right-2 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm text-orange-600 dark:text-orange-400 px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 dark:border-gray-700 shadow-sm">Stok: <?= $row['stok']; ?></span>
                        </div>
                        <div class="p-3.5">
                            <h4 class="book-title font-bold text-slate-800 dark:text-gray-100 line-clamp-1 group-hover:text-orange-500 transition"><?= htmlspecialchars($row['judul'] ?? ''); ?></h4>
                            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">👤 <?= htmlspecialchars($row['pengarang'] ?? ''); ?></p>
                        </div>
                    </div>
                    <div class="p-4 pt-0 border-t border-slate-100 dark:border-gray-800/50 mt-2">
                        <p class="text-[11px] text-slate-400 dark:text-gray-500 mt-2">Tahun Terbit: <span class="text-slate-600 dark:text-gray-300"><?= htmlspecialchars($row['tahun_terbit'] ?? ''); ?></span></p>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <div id="no-results" class="hidden col-span-full bg-slate-100 dark:bg-gray-900/30 border border-slate-200 dark:border-gray-800 p-12 rounded-2xl text-center text-slate-400 dark:text-gray-500 italic">
                    Buku yang Anda cari tidak ditemukan dalam koleksi.
                </div>
            </div>

            <h3 class="text-xl font-bold mb-6 text-slate-900 dark:text-white flex items-center gap-2 justify-center">
                Riwayat Peminjaman Terbaru
            </h3>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 overflow-hidden shadow-sm mb-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-800/50 text-slate-700 dark:text-orange-400 uppercase text-xs font-bold tracking-wider border-b border-slate-200 dark:border-gray-800">
                                <th class="p-4">Cover</th>
                                <th class="p-4">Nama Peminjam</th>
                                <th class="p-4">Judul Buku</th>
                                <th class="p-4">Tanggal Pinjam</th>
                                <th class="p-4 text-center">Jumlah Pinjam</th>
                            </tr>
                        </thead>
                        <tbody id="history-table-body" class="text-slate-600 dark:text-gray-300 text-sm divide-y divide-slate-100 dark:divide-gray-800">
                            <?php if(mysqli_num_rows($transaksi) == 0): ?>
                                <tr id="empty-row-db"><td colspan="5" class="p-4 text-center text-slate-400 dark:text-gray-500 italic">Belum ada data peminjaman aktif.</td></tr>
                            <?php endif; ?>
                            <?php while($t = mysqli_fetch_assoc($transaksi)): 
                                $peminjam = htmlspecialchars($t['nama_peminjaman'] ?? $t['nama_peminjam'] ?? '');
                                $judulBuku = htmlspecialchars($t['judul'] ?? '');
                            ?>
                            <tr class="history-row hover:bg-slate-50 dark:hover:bg-gray-800/30 transition" 
                                data-peminjam="<?= strtolower($peminjam); ?>" 
                                data-judul="<?= strtolower($judulBuku); ?>">
                                <td class="p-4">
                                    <img src="assets/<?= $t['gambar'] ?? 'default.jpg'; ?>" onerror="this.src='https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=100'" class="w-10 h-12 object-cover rounded-md shadow-sm bg-slate-100 dark:bg-gray-800">
                                </td>
                                <td class="p-4 font-semibold text-slate-900 dark:text-white"><?= $peminjam; ?></td>
                                <td class="p-4 text-slate-700 dark:text-gray-200"><?= $judulBuku; ?></td>
                                <td class="p-4 text-slate-500 dark:text-gray-400">📅 <?= isset($t['tanggal_pinjam']) ? date('d M Y', strtotime($t['tanggal_pinjam'])) : ''; ?></td>
                                <td class="p-4 text-center font-bold text-orange-600 dark:text-orange-400"><?= htmlspecialchars($t['jumlah_pinjam'] ?? 0); ?> Pcs</td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <tr id="no-history-results" class="hidden">
                                <td colspan="5" class="p-8 text-center text-slate-400 dark:text-gray-500 italic bg-slate-50 dark:bg-gray-900/10">
                                    Tidak ada data riwayat peminjaman yang cocok.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer class="w-full bg-white dark:bg-gray-900 border-t border-slate-200 dark:border-gray-800 py-5 px-6 mt-12 transition-colors duration-300">
            <div class="max-w-7xl mx-auto flex flex-row items-center justify-center gap-4 text-xs text-slate-400 dark:text-gray-500">
                <div class="text-center">
                    <span class="font-bold text-slate-600 dark:text-gray-400">Perpustakaan Orang Baik</span> &copy; <?= date('Y'); ?>. XI PPLG A.
                    <p class="font-bold text-orange-500 dark:text-orange-400 tracking-normal mt-1">By Yoseph Ekarunio Andika S.</p>
                </div> 
            </div>
        </footer>
    </div>

    <script>
        // --- 1. LOGIKA TOGGLE TEMA (UNTUK TAILWIND V4) ---
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

        // --- 2. FITUR SIDEBAR MOBILE ---
        const menuBtn = document.getElementById('menu-btn');
        const menuItems = document.getElementById('menu-items');
        if (menuBtn && menuItems) {
            menuBtn.addEventListener('click', () => { 
                menuItems.classList.toggle('hidden'); 
            });
        }

        // fitur live search
        const searchInput = document.getElementById('buku-search');
        const clearBtn = document.getElementById('clear-search');
        const bookCards = document.querySelectorAll('.book-card');
        const historyRows = document.querySelectorAll('.history-row');
        const noResults = document.getElementById('no-results');
        const noHistoryResults = document.getElementById('no-history-results');

        // Fungsi untuk mengontrol visibilitas berdasarkan pencarian dan batas default
        function filterData() {
            const value = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const isSearching = value.length > 0;

            if (clearBtn) {
                if (isSearching) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }

            // Filter & Batasi Kartu Buku
            let displayedCardsCount = 0;
            let hasCards = false;

            bookCards.forEach(card => {
                const judul = card.getAttribute('data-judul') || '';
                const pengarang = card.getAttribute('data-pengarang') || '';

                if (judul.includes(value) || pengarang.includes(value)) {
                    if (!isSearching) {
                        // Jika tidak sedang mencari, hanya tampilkan sampai maksimal 8 item terbaru
                        if (displayedCardsCount < 8) {
                            card.style.display = 'flex';
                            displayedCardsCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    } else {
                        // Jika sedang mencari, tampilkan semua yang cocok tanpa batas masimal
                        card.style.display = 'flex';
                    }
                    hasCards = true;
                } else {
                    card.style.display = 'none';
                }
            });

            // Filter & Batasi Baris Riwayat
            let displayedRowsCount = 0;
            let hasRows = false;

            historyRows.forEach(row => {
                const peminjam = row.getAttribute('data-peminjam') || '';
                const judulBuku = row.getAttribute('data-judul') || '';

                if (peminjam.includes(value) || judulBuku.includes(value)) {
                    if (!isSearching) {
                        // Jika tidak sedang mencari, hanya tampilkan sampai maksimal 5 item terbaru
                        if (displayedRowsCount < 5) {
                            row.style.display = '';
                            displayedRowsCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    } else {
                        // Jika sedang mencari, tampilkan semua yang cocok tanpa batas maksimal
                        row.style.display = '';
                    }
                    hasRows = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Tampilkan pesan jika hasil tidak ditemukan saat melakukan pencarian
            if (noResults) {
                if (!hasCards && isSearching) noResults.classList.remove('hidden');
                else noResults.classList.add('hidden');
            }

            if (noHistoryResults) {
                if (!hasRows && isSearching && historyRows.length > 0) noHistoryResults.classList.remove('hidden');
                else noHistoryResults.classList.add('hidden');
            }
        }

        // Jalankan filter pertama kali saat halaman dimuat (untuk memotong data menjadi 8 dan 5)
        document.addEventListener('DOMContentLoaded', filterData);
        filterData(); 

        if (searchInput) {
            searchInput.addEventListener('input', filterData);
        }

        if (clearBtn && searchInput) {
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                filterData();
                searchInput.focus();
            });
        }
    </script>
</body>
</html>