<?php
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}

if (isset($_SESSION['id'])) {
	header("Location: users.php");
}else{
	header("Location: login.php");
}

?>