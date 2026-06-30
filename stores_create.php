<?php 

session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}
include 'auth.php';


if (!isset($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32));
}


$db = new SQLite3("./api/.anspanel.db");


$db->busyTimeout(5000);


$error_message = '';


if (isset($_POST["submit"])) {

    if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
        header("Location: login.php");
        exit();
    }

    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $mac_amount = $_POST['mac_amount'];


    $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM USERS WHERE NAME = :name");
    $checkStmt->bindValue(':name', $name, SQLITE3_TEXT);
    $checkResult = $checkStmt->execute();
    $row = $checkResult->fetchArray();

    if ($row['count'] > 0) {
        $error_message = "ERRO: O NOME DO REVENDEDOR JÁ EXISTE. ESCOLHA UM NOME DIFERENTE!";
    } else {

        $stmt = $db->prepare("INSERT INTO USERS (NAME, USERNAME, PASSWORD, mac_amount) VALUES (:name, :username, :password, :mac_amount)");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $stmt->bindValue(':mac_amount', $mac_amount, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            header("Location: stores.php");
            exit();
        } else {
            $error_message = "ERRO AO CRIAR REVENDEDOR: " . $db->lastErrorMsg();
        }
    }
}

include "includes/header.php";
?>

<div class="container-fluid">
    <h1 class="h3 mb-1 text-gray-800"> CRIAR REVENDA</h1>

    <?php if ($error_message): ?>
        <div id="alert" class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <div class="card border-left-primary shadow h-100 mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user"></i> REVENDEDOR</h6>
        </div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token']; ?>">
                
                <div class="form-group">
                    <label class="control-label" for="name"><strong>Nome</strong></label>
                    <div class="input-group">
                        <input class="form-control text-primary" name="name" placeholder="Nome do Revendedor" type="text" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="mac_amount"><strong>Quantidade de MAC</strong></label>
                    <div class="input-group">
                        <input class="form-control text-primary" name="mac_amount" placeholder="Quantidade de MAC" type="number" min="1" value="10000" required />
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label" for="username"><strong>Usuário</strong></label>
                    <div class="input-group">
                        <input class="form-control text-primary" name="username" placeholder="Insira o Usuário" type="text" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="password"><strong>Senha</strong></label>
                    <div class="input-group">
                        <input class="form-control text-primary" name="password" placeholder="Insira a Senha" type="password" required />
                    </div>
                </div>
                
                <div class="form-group">
                    <button class="btn btn-success btn-icon-split" name="submit" type="submit">
                        <span class="icon text-white-50"><i class="fas fa-check"></i></span>
                        <span class="text">CRIAR</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include "includes/footer.php";
?>

<script>
$(document).ready(function() {
    // Se houver um alerta, faça-o desaparecer após 1.8 segundos
    if ($('#alert').length) {
        $('#alert').show(); // Mostra o alerta
        setTimeout(function() {
            $('#alert').fadeOut('slow');
        }, 1800);
    }
});
</script>

<style>
.alert {
    margin-top: 20px; /* Espaço acima do alerta */
}
</style>
</body>
</html>