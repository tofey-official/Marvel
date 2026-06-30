<?php

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}
include 'auth.php';


if (!isset($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32));
}

error_reporting(32767);

if (isset($_GET["delete"])) {

    if (!isset($_GET['token']) || !hash_equals($_SESSION['admin_token'], $_GET['token'])) {
        header("Location: login.php"); 
        exit();
    }

    $db = new SQLite3("./api/.anspanel.db");

    $delete_id = $_GET['delete'];
    $db->exec("DELETE FROM USERS WHERE id= '$delete_id'");
    $db->close();

    $db = new SQLite3("api/.ansdb.db");
    $db->exec("DELETE FROM ibo WHERE id_user = '$delete_id'");
    $db->close();

    header("Location: stores.php");
}
include "includes/header.php";
?>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirmar</h2>
            </div>
            <div class="modal-body">
                Tem certeza de que deseja excluir?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>

                <a class="btn btn-danger btn-ok" data-href="./stores.php?delete=<?php echo $id; ?>&token=<?php echo $_SESSION['admin_token']; ?>">Excluir</a>
            </div>
        </div>
    </div>
</div>

<main role="main" class="col-15 pt-4 px-5">
    <div class="row justify-text-center">
        <div id="main">
            <!-- Cabeçalho da Página -->
            <br>
            <h2>Painel de Revendas</h2>
            <br>
            <div class="input-group ">
                <a button class="btn btn-success btn-icon-split" id="button" href="stores_create.php">
                    <span class="icon text-white-50"><i class="fas fa-check"></i></span><span class="text">Criar</span></a>
                &nbsp;&nbsp;&nbsp;
                <input class="form-control" type="text" id="search" placeholder="Pesquisar" name="search_value"/>
            </div>
        </div>
        <div class="table-responsive">
            <table id="myTable" class="table table-striped table-sm">
                <thead class="text-primary">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>Senha</th>
                        <th>MACs</th>
                        <th>Editar</th>
                        <th>Excluir</th>
                    </tr>
                </thead>
<?php
$db = new SQLite3("./api/.anspanel.db");
$db2 = new SQLite3("./api/.ansdb.db");
$res = $db->query("SELECT * FROM USERS WHERE (ADMIN != 1 OR ADMIN IS NULL) AND store_type = 1");
while ($row = $res->fetchArray()) {
    $id = $row["id"];
    $name = $row["NAME"];
    $username = $row["USERNAME"];
    $password = $row["PASSWORD"]; // Acessando a senha do banco
    $macs = $row["mac_amount"];
    
    $macsUsed = $db2->query("SELECT COUNT(*) as count FROM ibo WHERE id_user = '$id' AND active = 1 AND expire_date > date('now')");
    $macsUsed = $macsUsed->fetchArray()['count'];
    $macsAvailable = $macs - $macsUsed;
    
    echo "              <tbody class=\" text-primary\">\n";
    echo "                  <td>" . $id . "</td>" . "\n";
    echo "                  <td>" . $name . "</td>" . "\n";
    echo "                  <td>" . $username . "</td>" . "\n";
    echo "                  <td>" . $password . "</td>" . "\n";
    echo "                  <td>" . $macs . "</td>" . "\n";
    echo "                  <td><a class=\"btn btn-icon\" href=\"./stores_update.php?update=" . $id . "\"><span class=\"icon text-white-50\"><img src=\"./icons/edit.png\" style=\"width:24px;height:24px;\" alt=\"Edit\"></span></a></td>" . "\n";
    echo "                  <td><a class=\"btn btn-icon\" href=\"#\" data-href=\"./stores.php?delete=" . $id . "&token=" . $_SESSION['admin_token'] . "\" data-toggle=\"modal\" data-target=\"#confirm-delete\"><span class=\"icon text-white-50\"><img src=\"./icons/delete.png\" style=\"width:24px;height:24px;\" alt=\"Delete\"></span></a></td>" . "\n";
    echo "\t\t\t\t</tr>\n\t\t\t</tbody>\n";
}
echo "\t\t\t</table>\n\t\t</div>\n</main>\n\n    <br><br><br>\n";
include "includes/footer.php";
?>

<script>
$("#search").keyup(function () {
    var value = this.value.toLowerCase().trim();

    $("table tr").each(function (index) {
        if (!index) return; // Ignorar cabeçalho
        $(this).find("td").each(function () {
            var id = $(this).text().toLowerCase().trim();
            var not_found = (id.indexOf(value) == -1);
            $(this).closest('tr').toggle(!not_found);
            return not_found;
        });
    });
});

$('#confirm-delete').on('show.bs.modal', function(e) {
    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
});
</script>
</body>
</html>