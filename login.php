<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = post_raw('password');
    $remember = isset($_POST['remember']);

    if (!csrf_valid()) {
        $errors[] = 'Sesi form tidak valid. Muat ulang halaman lalu coba lagi.';
    } elseif ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        $user = find_user_by_email($email);

        // Pesan dibuat generik agar tidak membocorkan apakah email terdaftar
        if ($user === null || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            // Perbarui hash jika algoritma default sudah berubah
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                update_user($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
            }

            login_user($user);
            if ($remember) {
                remember_user($user['id']);
            }
            set_flash('success', 'Selamat datang kembali, ' . $user['name'] . '!');
            redirect('dashboard.php');
        }
    }
}

$title = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="card auth-card">
    <h1>Login</h1>
    <p class="muted">Masuk ke akun Anda.</p>

    <?php foreach ($errors as $msg): ?>
        <div class="alert alert-error"><?= e($msg) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate>
        <?= csrf_field() ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label class="checkbox">
            <input type="checkbox" name="remember"> Ingat saya (<?= REMEMBER_DAYS ?> hari)
        </label>

        <button type="submit" class="btn">Login</button>
    </form>
    <p class="muted center">Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
