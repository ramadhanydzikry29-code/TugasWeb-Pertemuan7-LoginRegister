<?php
require_once __DIR__ . '/includes/functions.php';

$user   = require_login();
$errors = [];
$name   = $user['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim((string) ($_POST['name'] ?? ''));
    $current     = post_raw('current_password');
    $newPassword = post_raw('new_password');
    $confirm     = post_raw('confirm_password');
    $changes     = [];

    if (!csrf_valid()) {
        $errors[] = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
    }

    if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 50) {
        $errors[] = 'Nama harus diisi (2–50 karakter).';
    } else {
        $changes['name'] = $name;
    }

    // Ganti password (opsional)
    if ($newPassword !== '') {
        if (!password_verify($current, $user['password'])) {
            $errors[] = 'Password saat ini salah.';
        } elseif (strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
            $errors[] = 'Password baru minimal 8 karakter dan mengandung huruf serta angka.';
        } elseif ($newPassword !== $confirm) {
            $errors[] = 'Konfirmasi password baru tidak cocok.';
        } else {
            $changes['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
    }

    if (!$errors) {
        if (update_user($user['id'], $changes)) {
            set_flash('success', 'Profil berhasil diperbarui.');
            redirect('profile.php');
        }
        $errors[] = 'Gagal menyimpan perubahan.';
    }
}

$title = 'Edit Profil';
require __DIR__ . '/includes/header.php';
?>
<div class="card auth-card">
    <h1>Edit Profil</h1>

    <?php foreach ($errors as $msg): ?>
        <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>

        <label for="email">Email</label>
        <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>

        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" maxlength="50" required>

        <hr>
        <p class="muted">Kosongkan bagian di bawah jika tidak ingin mengganti password.</p>

        <label for="current_password">Password Saat Ini</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password">

        <label for="new_password">Password Baru</label>
        <input type="password" id="new_password" name="new_password" autocomplete="new-password">

        <label for="confirm_password">Konfirmasi Password Baru</label>
        <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">

        <button type="submit" class="btn">Simpan Perubahan</button>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
