<?php


session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}
session_destroy();
setcookie("auth", "");
header("location: login.php");

?>