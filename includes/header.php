<?php

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redireciona para a página de login se não estiver autenticado
    exit(); // Encerra a execução do script
}

// Verificação do token de sessão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
        header("Location: login.php"); // Redireciona para a página de login
        exit(); // Encerra a execução do script
    }
}

// Gera um token de sessão se ainda não existir
if (!isset($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32)); // Gera um token seguro
}

$id = $_SESSION['id'];
$isAdmin = $_SESSION['admin'];

$dbans = new SQLite3("./api/.ansdb.db");
$adb = new SQLite3('./api/.adb.db');

$dbans->exec("CREATE TABLE IF NOT EXISTS ibo(id INTEGER PRIMARY KEY NOT NULL, mac_address VARCHAR(100), key VARCHAR(100), username VARCHAR(100), password VARCHAR(100), expire_date VARCHAR(100), dns VARCHAR(100), epg_url VARCHAR(100), title VARCHAR(100), url VARCHAR(100), type VARCHAR(100), id_user INT)");
$dbans->exec("CREATE TABLE IF NOT EXISTS playlist(id INTEGER PRIMARY KEY NOT NULL, mac_address VARCHAR(100), url VARCHAR(100), name VARCHAR(100))");
$dbans->exec("CREATE TABLE IF NOT EXISTS theme(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100), url VARCHAR(100))");

$res = $dbans->query("SELECT * FROM theme");
$rows = $dbans->query("SELECT COUNT(*) as count FROM theme");
$row = $rows->fetchArray();
$numRows = $row["count"];

$protocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https" : "http");
$baseUrl = $protocol . "://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]);
$HOSTa = $baseUrl . "/img/red.jpg";
$HOSTb = $baseUrl . "/img/blue.jpg";
$HOSTc = $baseUrl . "/img/green.jpg";
$HOSTa1 = $baseUrl . "/img/g1.gif";

$mac_count = $dbans->query("SELECT COUNT(*) as count FROM ibo WHERE id_user = $id")->fetchArray()["count"];
$expired_mac_count = $dbans->query("SELECT COUNT(*) as count FROM ibo WHERE id_user = $id AND (active = 0 OR expire_date < date('today'))")->fetchArray()["count"];

$dbpans = new SQLite3("./api/.anspanel.db");
$resans = $dbpans->query("SELECT * FROM USERS WHERE ID='1'");
$rowans = $resans->fetchArray();
$nameans = $rowans["NAME"];
$logoans = $rowans["LOGO"];

$jsondata111 = file_get_contents("./includes/ansibo.json");
$json111 = json_decode($jsondata111, true);
$col1 = $json111["info"];
$col2 = $col1["aa"];
$col3 = $col2;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="google" content="notranslate">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>9 TEMAS IBO REVENDA</title>
    <link rel="shortcut icon" href="./img2/logo.png?ver=<?= time(); ?>" type="image/png">
    <link rel="icon" href="./img2/logo.png?ver=<?= time(); ?>" type="image/png">
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-<?= $col2 ?>.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <style>
        .no-margin {
            margin-top: 0;
            margin-left: 5px;
            margin-bottom: 10px;
            padding: 0;
        }
    </style>
</head>
<body id="page-top">

<!-- Page Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-header-adm-rev sidebar sidebar-dark accordion" id="accordionSidebar">

        <?php if ($logoans != NULL): ?>
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="users.php">
                <div class="sidebar-brand-icon">
                    <img class="img-profile rounded-circle" width="65px" src="img2/logo.png?ver=<?= time(); ?>">
                </div>
            </a>
        <?php else: ?>
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="users.php">
                <div class="sidebar-brand-icon">
                    <img class="img-profile rounded-circle" width="65px" src="img2/logo.png?ver=<?= time(); ?>">
                </div>
            </a>
        <?php endif; ?>

        <hr class="sidebar-divider my-0">

        <!-- HEADER ADMIN -->
        <?php if ($isAdmin) { ?>
        <span class="text-menu-header">Menu</span>
        <li class="nav-item no-margin">
            <a class="nav-link" href="users.php">
            <i class="fa-solid fa-user-check"></i>
            <span>Meus Clientes</span></a>
            
            <a class="nav-link" href="replace.php">
            <i class="fa-solid fa-cloud"></i>
            <span>Atualizar Dominio</span></a>

            <a class="nav-link" href="all_users.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Todos Clientes</span></a>
            </li>
            
            <span class="text-menu-header">Menu Revenda</span>
            <li class="nav-item no-margin">
            <a class="nav-link" href="stores.php">
            <i class="fa-solid fa-coins"></i>
            <span> Revendas</span></a>
            </li>

            <span class="text-menu-header">Design</span>
            <li class="nav-item no-margin">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages3" aria-expanded="true" aria-controls="collapsePages3">
                    <i class="fa fa-paint-brush"></i>
                    <span>Personalizar</span>
                </a>
                <div id="collapsePages3" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                      <h6 class="collapse-header">Configuração design:</h6>
                      <a class="collapse-item" href="layouts.php"><i class="fas fa-image"></i><span> Temas</span></a>
                      <a class="collapse-item" href="logo.php"><i class="fas fa-smile"></i><span> Logo</span></a>
                      <a class="collapse-item" href="Image.php"><i class="fas fa-image"></i><span> Fundo</span></a>
                   </div>
                </div>
            </li>

            <span class="text-menu-header">Ajustes</span>
            <li class="nav-item no-margin">
                <a class="nav-link" href="chatbot.php">
                    <i class="fas fa-robot" style="color: #00FF7F;"></i>
                    <span>ChatBot</span></a>

                <a class="nav-link" href="autoads.php">
                    <i class="fas fa-ad"></i>
                    <span>Banners</span></a>

                <a class="nav-link" href="ads.php">
                    <i class="fas fa-bullhorn"></i>
                    <span>Banners Manual</span></a>

                <a class="nav-link" href="note.php">
                    <i class="fas fa-sms"></i>
                    <span>Mensagem</span></a>

                <a class="nav-link" href="qrcode.php">
                    <i class="fas fa-qrcode"></i>
                    <span>QR Code</span></a>

                <a class="nav-link" href="profile.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Perfil</span></a>
            <a class="nav-link" href="logout.php">
        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-red-400" style="color: #FF0000;"></i>
        <span>Sair</span></a>
            </li>
<?php } ?>
<?php if ($isAdmin) { ?>
    <!-- Código do painel do admin -->
<?php } else { // Se não for admin, então é revenda ?>
    <li class="nav-item no-margin">
        <a class="nav-link" href="users.php">
            <i class="fas fa-home" style="color: #FFFFFF;"></i>
            <span>Inicio</span></a>
    
        <a class="nav-link" href="replace.php">
                <i class="fa-solid fa-cloud"></i>
                <span>Atualizar Dominio</span></a>
                
    <a class="nav-link" href="logout.php">
        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-red-400" style="color: #FF0000;"></i>
        <span>Sair</span></a>
        </li>
        
<?php } ?>

    <li class="nav-item2" style="background-color: black; color: white; padding: 10px; text-align: center;">
    <a class="nav-link2">
    <span>SEU ID: <b style="color: red; font-weight: bold;"><?=$id ?></b></span></a>
    </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
        
        <footer class="sticky-footer">
            <div class="copyright text-center">
                <span></span>
            </div>
        </footer>
    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main Content -->
        <div id="content">

            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">

                <!-- Sidebar Toggle (Topbar) -->
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
    <i class="fa fa-bars"></i>
</button>

<?php
// Exibe o nome do usuário
echo "<div><h5 class=\"m-0 text-primary\">" . htmlspecialchars($nameans) . "</br></h5></div>" . "\n";

// Inicia a barra de navegação superior
echo "\n          <!-- Topbar Navbar -->\n";
echo "          <ul class=\"navbar-nav ml-auto\">\n";

echo "            <!-- Nav Item - Logout -->\n";
echo "            <li class=\"nav-item no-margin3 dropdown no-arrow mx-1\" style=\"border: none;\">\n";
echo "           <a class=\"nav-link\" href=\"logout.php\" style=\"border: none; background: none;\">\n";
echo "           <i class=\"fas fa-sign-out-alt fa-sm fa-fw mr-2 text-red-400\"></i>\n";
echo "           <span class=\"badge badge-danger\">Sair</span>\n";
echo "              </a>\n";
echo "            </li>\n";

echo "          </ul>\n";
echo "        </nav>\n";
echo "        <!-- End of Topbar -->\n";
?>