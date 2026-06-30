<?php

session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}


if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== 1) {
    header("Location: login.php");
    exit();
}


if (!isset($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
        header("Location: login.php");
        exit();
    }
}
?>