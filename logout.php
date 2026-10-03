<?php
require_once __DIR__ . '/includes/functions.php';

// Logout hanya lewat POST + CSRF agar tidak bisa dipicu dari link/gambar pihak lain
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    redirect(is_logged_in() ? 'dashboard.php' : 'login.php');
}

if (!empty($_SESSION['user_id'])) {
    forget_user($_SESSION['user_id']);   // hapus token "remember me"
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Mulai session baru hanya untuk menampilkan pesan sukses
session_start();
set_flash('success', 'Anda berhasil logout.');
redirect('login.php');
