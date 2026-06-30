<?php
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
} // Inicia a sessão

// Verificação de sessão do administrador
if (!isset($_SESSION['ADMIN']) || $_SESSION['ADMIN'] !== 1) {
    header("Location: login.php"); // Se não estiver logado, redireciona para a página de login
    exit(); // Encerra a execução do script
}

// Configurações de erro
ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(32767);

// Conexão com o banco de dados
$db = new SQLite3("./api/.ansdb.db");
$db->exec("CREATE TABLE IF NOT EXISTS theme(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(100), url VARCHAR(100))");

// Contagem de temas
$res = $db->query("SELECT * FROM theme");
$rows = $db->query("SELECT COUNT(*) as count FROM theme");
$row = $rows->fetchArray();
$numRows = $row["count"];

// Geração de URLs
$HOSTa = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/img/red.jpg";
$HOSTb = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/img/blue.jpg";
$HOSTc = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/img/green.jpg";
$HOSTa1 = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off" ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/img/g1.gif";

// Insere temas padrão se a tabela estiver vazia
if ($numRows == 0) {
    $db->exec("INSERT INTO theme(name,url) VALUES('Red','" . $HOSTa . "'),('Blue','" . $HOSTb . "'),('Green','" . $HOSTc . "'),('Gif Edition','" . $HOSTa1 . "')");
}

// Lógica de exclusão
if (isset($_GET["delete"])) {
    $themeId = intval($_GET["delete"]); // Converte o ID para evitar injeção SQL
    $db->exec("DELETE FROM theme WHERE id=" . $themeId);
    header("Location: theme.php"); // Redireciona após a exclusão
    exit();
}

// Inclui o cabeçalho
include "includes/header.php";

// Exibição do conteúdo
echo "<div class=\"modal fade\" id=\"confirm-delete\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
    <div class=\"modal-dialog\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h2>Confirm</h2>
            </div>
            <div class=\"modal-body\">
                Do you really want to delete?
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Cancel</button>
                <a class=\"btn btn-danger btn-ok\">Delete</a>
            </div>
        </div>
    </div>
</div>
<main role=\"main\" class=\"col-15 pt-4 px-5\">
    <div class=\"row justify-text-center\">
        <div id=\"main\">
            <h1 class=\"h3 mb-1 text-gray-800\">Themes</h1>
            <a button class=\"btn btn-success btn-icon-split\" id=\"button\" href=\"./theme_create.php\">
                <span class=\"icon text-white-50\"><i class=\"fas fa-check\"></i></span><span class=\"text\">Create</span>
            </button></a>
        </div>
        <div class=\"table-responsive\">
            <table class=\"table table-striped table-sm\">
                <thead class=\"text-primary\">
                    <tr>
                        <th>Name</th>
                        <th>Image Url</th>
                        <th>Image Preview</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>";

while ($row = $res->fetchArray()) {
    $NName = htmlspecialchars($row["name"]);
    $UUrl = htmlspecialchars($row["url"]);
    $IIdd = intval($row["id"]);
    echo "<tr>
            <td>$NName</td>
            <td>$UUrl</td>
            <td><img src=\"$UUrl\" alt=\"\" border=3 height=80 width=120></td>
            <td><a class=\"btn btn-icon\" href=\"./theme_update.php?update=$IIdd\"><span class=\"icon text-white-50\"><i class=\"fa fa-pencil\" style=\"font-size:24px;color:blue\"></i></span></a></td>
            <td>
                <a class=\"btn btn-icon\" href=\"./theme.php?delete=$IIdd\" onclick=\"return confirm('Do you really want to delete?');\">
                    <span class=\"icon text-white-50\"><i class=\"fa fa-trash\" style=\"font-size:24px;color:red\"></i></span>
                </a>
            </td>
        </tr>";
}

echo "</tbody>
        </table>
    </div>
</main>
<br><br><br>";

include "includes/footer.php";
?>

<script>
    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
    });
</script>
</body>
</html>