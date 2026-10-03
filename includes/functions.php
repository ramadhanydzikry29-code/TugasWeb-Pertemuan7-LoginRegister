<?php
declare(strict_types=1);

/**
 * Fungsi-fungsi pembantu: session, JSON storage, sanitasi, flash message, CSRF, remember me.
 */

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => is_https(),
    ]);
    session_start();
}

const USERS_FILE      = __DIR__ . '/../data/users.json';
const REMEMBER_COOKIE = 'remember_me';
const REMEMBER_DAYS   = 30;

/* ---------- Sanitasi ---------- */

/** Escape output agar aman dari XSS (htmlspecialchars). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ambil input POST: trim lalu sanitasi dengan htmlspecialchars. */
function post(string $key): string
{
    return htmlspecialchars(trim((string) ($_POST[$key] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Password tidak boleh diubah/di-escape; cukup diambil apa adanya. */
function post_raw(string $key): string
{
    return (string) ($_POST[$key] ?? '');
}

/* ---------- Penyimpanan JSON ---------- */

function load_users(): array
{
    if (!is_file(USERS_FILE)) {
        return [];
    }
    $data = json_decode((string) file_get_contents(USERS_FILE), true);
    return is_array($data) ? $data : [];
}

function save_users(array $users): bool
{
    $json = json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return $json !== false && file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

function find_user_by_email(string $email): ?array
{
    $email = strtolower($email);
    foreach (load_users() as $user) {
        if (strtolower($user['email']) === $email) {
            return $user;
        }
    }
    return null;
}

function find_user_by_id(string $id): ?array
{
    foreach (load_users() as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }
    return null;
}

/** Update satu user berdasarkan id. */
function update_user(string $id, array $changes): bool
{
    $users = load_users();
    foreach ($users as $i => $user) {
        if ($user['id'] === $id) {
            $users[$i] = array_merge($user, $changes);
            return save_users($users);
        }
    }
    return false;
}

/* ---------- Flash message ---------- */

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

/* ---------- Auth ---------- */

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

function is_logged_in(): bool
{
    if (!empty($_SESSION['user_id'])) {
        return true;
    }
    return login_from_remember_cookie();
}

function current_user(): ?array
{
    return is_logged_in() ? find_user_by_id($_SESSION['user_id']) : null;
}

/** Halaman yang diproteksi: redirect ke login jika belum login. */
function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        unset($_SESSION['user_id']);
        set_flash('error', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
        redirect('login.php');
    }
    return $user;
}

/* ---------- Remember Me (bonus) ---------- */

function set_remember_cookie(string $value, int $expires): void
{
    setcookie(REMEMBER_COOKIE, $value, [
        'expires'  => $expires,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => is_https(),
    ]);
}

/** Buat token acak; simpan hash-nya di JSON, kirim token asli lewat cookie. */
function remember_user(string $userId): void
{
    $validator = bin2hex(random_bytes(32));
    $expires   = time() + REMEMBER_DAYS * 86400;

    update_user($userId, [
        'remember_hash'    => hash('sha256', $validator),
        'remember_expires' => $expires,
    ]);
    set_remember_cookie($userId . ':' . $validator, $expires);
}

function forget_user(string $userId): void
{
    update_user($userId, ['remember_hash' => null, 'remember_expires' => null]);
    set_remember_cookie('', time() - 3600);
    unset($_COOKIE[REMEMBER_COOKIE]);
}

function login_from_remember_cookie(): bool
{
    $cookie = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if (!is_string($cookie) || !str_contains($cookie, ':')) {
        return false;
    }
    [$id, $validator] = explode(':', $cookie, 2);
    $user = find_user_by_id($id);

    if (
        $user !== null
        && !empty($user['remember_hash'])
        && (int) $user['remember_expires'] > time()
        && hash_equals($user['remember_hash'], hash('sha256', $validator))
    ) {
        login_user($user);
        remember_user($user['id']); // rotasi token
        return true;
    }

    set_remember_cookie('', time() - 3600); // cookie tidak valid -> hapus
    return false;
}
