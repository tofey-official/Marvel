<?php

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}

// Verificar se o usuário está autenticado
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Redireciona para a página de login se não estiver autenticado
    exit(); // Encerra a execução do script
}

$id_store = $_SESSION['id'];

if ($_SESSION['store_type'] == 2) {
    header("Location: users_mac.php");
    exit(); // Adicionar exit após redirecionamento
}

ini_set("display_errors", 1); // Ativar a exibição de erros para depuração
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

// Conectar ao banco de dados
$db = new SQLite3("./api/.ansdb.db");

// Verificar se o formulário de atualização de domínio foi enviado
if (isset($_POST["update_domain"])) {
    $dominio_antigo = trim($_POST['old_domain']); // Remover espaços
    $dominio_novo = trim($_POST['new_domain']);   // Remover espaços

    // Verificar se o domínio antigo existe no banco de dados
    $id_user = $_SESSION['id']; // ID do revendedor logado
    $result = $db->query("SELECT COUNT(*) as count FROM ibo WHERE dns LIKE '%$dominio_antigo%' AND id_user = '$id_user'");
    $row = $result->fetchArray();

    if ($row['count'] > 0) {
        // Atualizar DNS e URL em massa apenas para os usuários do revendedor
        $db->exec("UPDATE ibo SET
            dns = REPLACE(dns, '$dominio_antigo', '$dominio_novo'),
            url = REPLACE(url, '$dominio_antigo', '$dominio_novo')
            WHERE id_user = '$id_user'");

        // Adicionar mensagem de sucesso à sessão
        $_SESSION['success_message'] = "DOMÍNIO ATUALIZADO COM SUCESSO!";

    } else {
        // Adicionar mensagem de erro à sessão
        $_SESSION['error_message'] = "O DOMÍNIO (ANTIGO) INFORMADO NÃO EXISTE NO BANCO DE DADOS.";
    }
    
    // Redirecionar para recarregar a página
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Incluir cabeçalho
include "includes/header.php";

// Exibir mensagem de sucesso, se existir
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            ' . $_SESSION['success_message'] . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
    unset($_SESSION['success_message']); // Limpar a mensagem após exibi-la
}

// Exibir mensagem de erro, se existir
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            ' . $_SESSION['error_message'] . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>';
    unset($_SESSION['error_message']); // Limpar a mensagem após exibi-la
}

// Formulário para atualizar domínio em massa
echo '<div class="container">';
echo '<h2>Atualizar Domínio em Massa</h2>';
echo '<form method="post" action="">
        <div class="form-group">
            <label for="old_domain"><strong>Domínio Antigo</strong></label>
            <input type="text" class="form-control" name="old_domain" placeholder="Insira o domínio antigo" required>
        </div>
        <div class="form-group">
            <label for="new_domain"><strong>Novo Domínio</strong></label>
            <input type="text" class="form-control" name="new_domain" placeholder="Insira o novo domínio" required>
        </div>
        <button type="submit" name="update_domain" class="btn btn-warning">Atualizar Domínio</button>
      </form>';
echo '</div>';

// Fechar a conexão
$db->close();
include "includes/footer.php";
?>

<script>
$(document).ready(function() {
    // Animação para o alerta de sucesso
    $("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
        $(this).alert('close');
    });
    
    // Animação para o alerta de erro
    $("#error-alert").fadeTo(2000, 500).slideUp(500, function(){
        $(this).alert('close');
    });
});
</script>