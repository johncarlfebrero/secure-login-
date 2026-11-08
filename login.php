<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

const MAX_ATTEMPTS    = 5;
const LOCKOUT_MINUTES = 15;
const GENERIC_ERROR   = 'Invalid username or password.';

$error    = '';
$username = '';

// One-time success message from register.php
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_check()) {
        $error = GENERIC_ERROR;
    } else {
        // Prepared statement: user input is never part of the SQL text
        $stmt = $pdo->prepare(
            'SELECT id, username, password_hash, role, failed_attempts,
                    (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
             FROM users
             WHERE username = ?
             LIMIT 1'
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            // Unknown user: still run password_verify so the response time is similar
            password_verify($password, '$2y$10$u49CBd0UjOlltE1nsiHaDudn/nVxmIBaKJH/DyFHHE9wz0na08xqW');
            $error = GENERIC_ERROR;
        } elseif ($user['is_locked']) {
            // Locked: refuse without checking the password, same generic message
            $error = GENERIC_ERROR;
        } elseif (password_verify($password, $user['password_hash'])) {
            // ---- Success ----
            // Reset lockout counters
            $pdo->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?')
                ->execute([$user['id']]);

            // Upgrade the hash if PHP's default algorithm/cost changed
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }

            // New session ID to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_id']  = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header('Location: dashboard.php');
            exit;
        } else {
            // ---- Wrong password: count it, lock after MAX_ATTEMPTS ----
            $attempts = (int) $user['failed_attempts'] + 1;

            if ($attempts >= MAX_ATTEMPTS) {
                $pdo->prepare(
                    'UPDATE users
                     SET failed_attempts = 0,
                         locked_until = DATE_ADD(NOW(), INTERVAL ' . LOCKOUT_MINUTES . ' MINUTE)
                     WHERE id = ?'
                )->execute([$user['id']]);
            } else {
                $pdo->prepare('UPDATE users SET failed_attempts = ? WHERE id = ?')
                    ->execute([$attempts, $user['id']]);
            }
            $error = GENERIC_ERROR;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="blob three"></div>

<main class="card">
    <div class="brand">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <h1>Welcome Back</h1>
    <p class="subtitle">Login to continue to your account</p>

    <?php if ($flash): ?>
        <div class="alert success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
            <span><?= e($flash) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert error shake">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <input type="text" id="username" name="username" maxlength="30" placeholder="Username"
                   value="<?= e($username) ?>" required autofocus autocomplete="username">
            <label for="username">Username</label>
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>

        <div class="field">
            <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
            <label for="password">Password</label>
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <button type="button" class="toggle-pass" data-target="password" aria-label="Show password">
                <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19M1 1l22 22"/></svg>
            </button>
        </div>

        <div class="row">
            <label class="check"><input type="checkbox" name="remember"> Remember me</label>
            <a href="#" title="Password reset is not part of this activity">Forgot password?</a>
        </div>

        <button type="submit">
            <span class="label">Login</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </form>


    <p class="muted">Don't have an account? <a href="register.php">Create one</a></p>
</main>
<script src="app.js"></script>
</body>
</html>
