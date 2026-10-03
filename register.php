<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim((string) ($_POST['name'] ?? ''));
    $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = post_raw('password');
    $confirm  = post_raw('confirm_password');

    if (!csrf_valid()) {
        $errors[] = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
    }

    // Nama
    if ($name === '') {
        $errors['name'] = 'Nama wajib diisi.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 50) {
        $errors['name'] = 'Nama harus 2–50 karakter.';
    }

    // Email (filter_var)
    $rawEmail = trim((string) ($_POST['email'] ?? ''));
    if ($rawEmail === '') {
        $errors['email'] = 'Email wajib diisi.';
    } elseif (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL) || strlen($rawEmail) > 100) {
        $errors['email'] = 'Format email tidak valid.';
    }

    // Password
    if ($password === '') {
        $errors['password'] = 'Password wajib diisi.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password harus mengandung huruf dan angka.';
    }

    if ($confirm !== $password) {
        $errors['confirm_password'] = 'Konfirmasi password tidak cocok.';
    }

    // Cek duplikasi email
    if (!isset($errors['email']) && find_user_by_email($rawEmail) !== null) {
        $errors['email'] = 'Email sudah terdaftar. Silakan login atau gunakan email lain.';
    }

    if (!$errors) {
        $users   = load_users();
        $users[] = [
            'id'               => 'u_' . bin2hex(random_bytes(5)),
            'name'             => $name,
            'email'            => strtolower($rawEmail),
            'password'         => password_hash($password, PASSWORD_DEFAULT),
            'created_at'       => date('Y-m-d H:i:s'),
            'remember_hash'    => null,
            'remember_expires' => null,
        ];

        if (save_users($users)) {
            set_flash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
        }
        $errors[] = 'Gagal menyimpan data. Pastikan folder data/ dapat ditulis (writable).';
    }
}

$title = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="card auth-card">
    <h1>Buat Akun</h1>
    <p class="muted">Daftar untuk mulai menggunakan sistem.</p>

    <?php foreach ($errors as $key => $msg): if (is_int($key)): ?>
        <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endif; endforeach; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>

        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" maxlength="50" required>
        <?php if (isset($errors['name'])): ?><small class="field-error"><?= e($errors['name']) ?></small><?php endif; ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="100" required>
        <?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <small class="hint">Minimal 8 karakter, kombinasi huruf dan angka.</small>
        <?php if (isset($errors['password'])): ?><small class="field-error"><?= e($errors['password']) ?></small><?php endif; ?>

        <label for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        <?php if (isset($errors['confirm_password'])): ?><small class="field-error"><?= e($errors['confirm_password']) ?></small><?php endif; ?>

        <button type="submit" class="btn">Daftar</button>
    </form>
    <p class="muted center">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
