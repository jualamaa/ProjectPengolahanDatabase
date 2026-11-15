<?php
include 'config.php';
session_start();

// BAGIAN PROTEKSI YANG MEMBUAT TIDAK BISA MASUK KE FORM LOGIN SUDAH DIHAPUS

$error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // enkripsi password
        $password_md5 = md5($password);
        $password_sha256 = hash('sha256', $password);

        if (
            $password === $row['password'] ||          
            $password_md5 === $row['password'] ||    
            $password_sha256 === $row['password'] ||   
            password_verify($password, $row['password'])
        ) {
            // Memeriksa level hak akses dari database
            if (isset($row['level']) && $row['level'] === 'admin') {
                $_SESSION['admin'] = $row['username'];
            } else {
                $_SESSION['user'] = $row['username'];
            }
            
            header("Location: index.php");
            exit;
        }
    }
    $error = 'Username atau Password salah!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="stylesheet" href="./src/output.css">
</head>
<body class="bg-gray-800 flex items-center justify-center h-screen text-gray-100">
    <div class="bg-gray-900/80 p-8 rounded-2xl shadow-2xl w-96 border border-gray-500/20 backdrop-blur-sm">
        <div class="text-center mb-6">
            <span class="text-4xl">📚</span>
            <h2 class="text-2xl font-black mt-2 text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-500">SISTEM LOGIN</h2>
            <p class="text-xs text-gray-400 mt-1">Sistem Informasi Perpustakaan</p>
        </div>
        
        <?php if($error): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-3 rounded-xl mb-4 text-sm text-center font-medium"><?= $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-orange-400 mb-2">Username</label>
                <input type="text" name="username" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-orange-500 transition text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-orange-400 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-orange-500 transition text-white">
            </div>
            <button type="submit" name="login" class="w-full bg-gradient-to-r from-orange-500 to-amber-600 text-white font-bold py-3 rounded-xl hover:from-orange-600 hover:to-amber-700 shadow-lg shadow-orange-500/20 transition duration-200">Login</button>
        </form>
        <div class="text-center mt-4">
            <a href="index.php" class="text-xs text-gray-400 hover:text-orange-400 transition">← Kembali ke Katalog Umum</a>
        </div>
    </div>
</body>
</html>