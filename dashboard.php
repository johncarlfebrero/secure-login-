<?php
require __DIR__ . '/includes/session.php';

// Protect this page: nobody logged in -> back to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Don't let the browser cache this page (so "Back" after logout won't show it)
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="blob three"></div>

<main class="card">
    <div class="avatar"><?= e(substr($_SESSION['username'], 0, 1)) ?></div>
    <h1>Hello, <?= e($_SESSION['username']) ?>!</h1>
    <p class="subtitle">You are securely logged in 🎉</p>

    <dl class="info">
        <div><dt>User ID</dt><dd>#<?= (int) $_SESSION['user_id'] ?></dd></div>
        <div><dt>Username</dt><dd><?= e($_SESSION['username']) ?></dd></div>
        <div><dt>Role</dt><dd><span class="badge"><?= e($_SESSION['role']) ?></span></dd></div>
    </dl>

    <a class="button" href="logout.php">
        <span class="label">Log out</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
    </a>
</main>
</body>
</html>
