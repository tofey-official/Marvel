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
$res = $db->query("SELECT * FROM USERS WHERE id='" . $_GET["update"] . "'");
$row = $res->fetchArray();
$id = $row["id"];
$name = $row["NAME"];
$mac_amount = $row["mac_amount"];
$username = $row["USERNAME"];
$password = $row["PASSWORD"]; // Mantém a senha original para comparação

if (isset($_POST["submit"])) {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
        header("Location: login.php");
        exit(); 
    }

    // Se a senha não for preenchida, mantenha a senha antiga
    if (!empty($_POST["password"])) {
        $password = $_POST["password"];
    } else {
        // Caso a senha não seja alterada, mantenha a senha que já está no banco
        $password = $row["PASSWORD"];
    }

    // Atualiza o banco de dados
    $db->exec("UPDATE USERS SET NAME='" . $_POST['name'] . "', USERNAME='" . $_POST['username'] . "', PASSWORD='$password', mac_amount = '" . $_POST['mac_amount'] . "' WHERE id=$id");
    $db->close();
    header("Location: stores.php");
}

include "includes/header.php";
?>

<div class="container-fluid">
    <h1 class="h3 mb-1 text-gray-800"> Atualizar Usuário</h1>

    <div class="card border-left-primary shadow h-100 card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user"></i> Editar</h6>
        </div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token']; ?>">
                <input type="hidden" name="id" value="<?=$id?>">       
                
                <div class="form-group ">
                    <label class="control-label " for="name">
                        <strong>Nome</strong> 
                    </label>
                    <div class="input-group">
                        <input class="form-control text-primary" name="name" value="<?=$name?>" type="text" required/>
                    </div>
                </div>

                <div class="form-group ">
                    <label class="control-label " for="mac_amount">
                        <strong>MACs</strong> 
                    </label>
                    <div class="input-group">
                        <input class="form-control text-primary" name="mac_amount" placeholder="Enter MAC amount" type="number" min="1" value="<?= $mac_amount ?>" required/>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label " for="username">
                        <strong>Usuário</strong> 
                    </label>
                    <div class="input-group">
                        <input class="form-control text-primary" name="username" value="<?=$username?>" type="text" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="password">
                        <strong>Senha</strong> 
                    </label>
                    <div class="input-group">
                        <input class="form-control text-primary" name="password" id="password" type="password" placeholder="DIGITE A NOVA SENHA (DEIXE EM BRANCO PARA MANTER A ATUAL)" />
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div>
                        <button class="btn btn-success btn-icon-split" name="submit" type="submit">
                            <span class="icon text-white-50"><i class="fas fa-check"></i></span><span class="text">Atualizar</span>
                        </button>
                    </div>
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
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {
        // Alterna o tipo do input
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Alterna o ícone
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
});
</script>

</body>
</html>