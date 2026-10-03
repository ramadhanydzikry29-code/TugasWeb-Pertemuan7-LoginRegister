<?php
/** Layout atas. Variabel yang dipakai: $title, $user (opsional). */
require_once __DIR__ . '/functions.php';
$title = $title ?? 'Sistem Login';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> — Tugas Rutin 7</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php if (!empty($user)): ?>
<nav class="navbar">
    <a class="brand" href="dashboard.php">🔐 LoginSystem</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Edit Profil</a>
        <form action="logout.php" method="post" class="inline">
            <?= csrf_field() ?>
            <button type="submit" class="link-btn">Logout</button>
        </form>
    </div>
</nav>
<?php endif; ?>
<main class="container">
<?php foreach (get_flashes() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>" role="alert"><?= e($f['message']) ?></div>
<?php endforeach; ?>
