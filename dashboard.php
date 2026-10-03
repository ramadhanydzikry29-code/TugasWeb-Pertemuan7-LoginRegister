<?php
require_once __DIR__ . '/includes/functions.php';

$user  = require_login();   // redirect ke login jika belum login
$title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h1>Halo, <?= e($user['name']) ?> 👋</h1>
    <p class="muted">Ini adalah halaman dashboard yang hanya bisa diakses setelah login.</p>

    <div class="stats">
        <div class="stat">
            <span class="stat-label">Email</span>
            <span class="stat-value"><?= e($user['email']) ?></span>
        </div>
        <div class="stat">
            <span class="stat-label">Bergabung sejak</span>
            <span class="stat-value"><?= e($user['created_at']) ?></span>
        </div>
        <div class="stat">
            <span class="stat-label">Total pengguna</span>
            <span class="stat-value"><?= count(load_users()) ?></span>
        </div>
    </div>

    <a class="btn btn-inline" href="profile.php">Edit Profil</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
