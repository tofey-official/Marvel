<?php
include 'auth.php'; // Inclui o arquivo de autenticação
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}



ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(32767);

$db = new SQLite3("./api/.ansdb.db");
$db->exec("CREATE TABLE IF NOT EXISTS ibo(id INTEGER PRIMARY KEY NOT NULL, mac_address VARCHAR(100), username VARCHAR(100), password VARCHAR(100), dns VARCHAR(100), title VARCHAR(100), url VARCHAR(100), type VARCHAR(100), id_user INT)");

if (isset($_GET["delete"])) {
    $db->exec("DELETE FROM ibo WHERE id=" . $_GET["delete"]);
    $db->close();
    header("Location: all_users.php");
}

if (isset($_GET["delete_all"])) {
    $db->exec("DELETE FROM ibo");
    $db->close();
    header("Location: all_users.php");
}

include "includes/header.php";

echo "<div class=\"modal fade\" id=\"confirm-delete\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
    <div class=\"modal-dialog\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h2>Confirmar</h2>
            </div>
            <div class=\"modal-body\">
                Você realmente deseja excluir?
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Cancelar</button>
                <a class=\"btn btn-danger btn-ok\">Excluir</a>
            </div>
        </div>
    </div>
</div>

<div class=\"modal fade\" id=\"confirm-delete-all\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\">
    <div class=\"modal-dialog\">
        <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h2>Confirmar Exclusão de Todos</h2>
            </div>
            <div class=\"modal-body\">
                Você realmente deseja excluir todos os usuários?
            </div>
            <div class=\"modal-footer\">
                <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Cancelar</button>
                <a class=\"btn btn-danger btn-ok\">Excluir Todos</a>
            </div>
        </div>
    </div>
</div>

<main role=\"main\" class=\"col-15 pt-4 px-5\">
    <div class=\"row justify-text-center\">
        <br><h2>Todos os Usuários</h2>
        <div class=\"input-group\">
            <a button class=\"btn btn-success btn-icon-split\" id=\"button\" href=\"./users_create.php\">
                <span class=\"icon text-white-50\"><i class=\"fas fa-check\"></i></span>
                <span class=\"text\">Criar Novo</span></a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <button class=\"btn btn-danger\" data-toggle=\"modal\" data-target=\"#confirm-delete-all\" style=\"margin: 0 auto; display: block;\">
                Excluir Todos
            </button>
            <input class=\"form-control\" type=\"text\" id=\"search\" placeholder=\"Pesquisar...\" name=\"search_value\"/>
        </div>
        <div class=\"table-responsive\">
            <table id=\"myTable\" class=\"table table-striped table-sm\">
                <thead class=\"text-primary\">
                    <tr>
                        <th>ID</th>
                        <th>Mac</th>
                        <th>Usuário</th>
                        <th>Dns</th>
                        <th>Nome</th>
                        <th>Editar</th>
                        <th>Apagar</th>
                    </tr>
                </thead>
                <tbody class=\"text-primary\">";

$res = $db->query("SELECT * FROM ibo");
while ($row = $res->fetchArray()) {
    $iid = $row["id"];
    $imac = $row["mac_address"];
    $iusername = $row["username"];
    $idns = $row["dns"] ?? $row["url"];
    $ititle = $row["title"];
    
    if ($row['playlistpassword']) {
        $idns = "****";
        $ititle = "****";
    }

    echo "<tr>
            <td>{$iid}</td>
            <td>{$imac}</td>
            <td>{$iusername}</td>
            <td>{$idns}</td>
            <td>{$ititle}</td>
            <td><a class=\"btn btn-icon\" href=\"./users_update.php?update={$iid}\"><span class=\"icon text-white-50\"><img src=\"./icons/edit.png\" style=\"width:24px;height:24px;\" alt=\"Edit\"></span></a></td>
            <td><a class=\"btn btn-icon\" href=\"#\" data-href=\"./all_users.php?delete={$iid}\" data-toggle=\"modal\" data-target=\"#confirm-delete\"><span class=\"icon text-white-50\"><img src=\"./icons/delete.png\" style=\"width:24px;height:24px;\" alt=\"Delete\"></span></a></td>
          </tr>";
}

echo "                </tbody>
            </table>
        </div>
    </div>
</main>
<br><br><br>";

include "includes/footer.php";

echo "<script>
    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
    });
    
    $('#confirm-delete-all').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').attr('href', './all_users.php?delete_all=true');
    });
</script>
<script>
    $('#search').keyup(function () {
        var value = this.value.toLowerCase().trim();
        $('table tr').each(function (index) {
            if (!index) return;
            $(this).find('td').each(function () {
                var id = $(this).text().toLowerCase().trim();
                var not_found = (id.indexOf(value) == -1);
                $(this).closest('tr').toggle(!not_found);
                return not_found;
            });
        });
    });
</script>
</body>
</html>";
?>