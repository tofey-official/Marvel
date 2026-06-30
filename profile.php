<?php 
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}

include 'auth.php';


if (!isset($_SESSION['id'])) {
    header("Location: login.php"); 
    exit(); 
}


if (!isset($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32));
}

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(E_ALL);

$db = new SQLite3("./api/.anspanel.db");


$stmt = $db->prepare("SELECT * FROM USERS WHERE ID = :id");
$stmt->bindValue(':id', 1, SQLITE3_INTEGER);
$res = $stmt->execute();
$row = $res->fetchArray();
$message = "";


if (isset($_POST["submit"])) {

    if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
        header("Location: login.php");
        exit(); 
    }

    $target_dir = "img2/";
    $target_file = $target_dir . "logo.png";


    if (!empty($_FILES["logo"]["tmp_name"])) {
        move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file);
    }


    $stmt = $db->prepare("UPDATE USERS SET NAME = :name, USERNAME = :username, PASSWORD = :password, LOGO = :logo WHERE ID = :id");
    $stmt->bindValue(':name', htmlspecialchars(trim($_POST["name"])), SQLITE3_TEXT);
    $stmt->bindValue(':username', htmlspecialchars(trim($_POST["username"])), SQLITE3_TEXT);
    $stmt->bindValue(':password', htmlspecialchars(trim($_POST["password"])), SQLITE3_TEXT);
    $stmt->bindValue(':logo', $target_file, SQLITE3_TEXT);
    $stmt->bindValue(':id', 1, SQLITE3_INTEGER);
    
    $result = $stmt->execute();


    if ($result) {

        $_SESSION["name"] = htmlspecialchars(trim($_POST["username"]));
        $_SESSION["logo"] = $target_file . "?t=" . time();
        $message = "<div class=\"alert alert-success\" id=\"flash-msg\"><h4>PERFIL ATUALIZADO!</h4></div>";
        header("Location: profile.php?m=" . urlencode($message));
        exit();
    } else {
        echo "Erro na atualização: " . $db->lastErrorMsg();
    }
}


$name = $row["NAME"];
$user = $row["USERNAME"];
$pass = $row["PASSWORD"];
$logo = isset($_SESSION["logo"]) ? $_SESSION["logo"] : $row["LOGO"];

include "includes/header.php";
?>


<div class="container-fluid">
    <?php if (isset($_GET["m"])) echo urldecode($_GET["m"]); ?>
    <h1 class="h3 mb-1 text-gray-800">ATUALIZAR DADOS</h1>
    

    <div class="row">
        <div class="col-lg-12">
            <div class="card border-left-primary shadow h-100 card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-user"></i> ATUALIZAR PERFIL</h6>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="token" value="<?= $_SESSION['admin_token']; ?>">
                        <div class="form-group">
                            <label class="control-label" for="name"><strong>Nome</strong></label>
                            <input type="text" class="form-control text-primary" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Digite o Nome" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="username"><strong>Usuário</strong></label>
                            <input type="text" class="form-control text-primary" name="username" value="<?= htmlspecialchars($user) ?>" placeholder="Digite o Nome de Usuário">
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="password"><strong>Senha</strong></label>
                            <input type="text" class="form-control text-primary" name="password" value="<?= htmlspecialchars($pass) ?>" placeholder="Digite a Senha" required>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="logo"><strong>Logo</strong></label>
                            <input type="file" class="form-control text-primary" name="logo" placeholder="Upload da imagem do Perfil">
                        </div>
                        <div class="form-group">
                            <button class="btn btn-success btn-icon-split" name="submit" type="submit">
                                <span class="icon text-white-50"><i class="fas fa-check"></i></span>
                                <span class="text">ATUALIZAR</span>
                            </button>
                        </div>
                        <img type="image" width="100px" src="<?= htmlspecialchars($logo) ?>" alt="imagem" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include "includes/footer.php";
require "includes/ans.php";
?>
</body>
</html>