<?php

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);
error_reporting(32767);
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$db = new SQLite3("./api/.ansdb.db");

if (isset($_POST["submit"])) {
    $address1 = strtoupper($_POST["mac_address"]);

    if (!preg_match('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/', $address1)) {
        die("Endereço MAC inválido");
    }

    // Remover a verificação existente
    // $existingRecord = $db->querySingle("SELECT COUNT(*) FROM ibo WHERE mac_address = '$address1'");
    // if ($existingRecord > 0) {
    //     $db->exec("DELETE FROM ibo WHERE mac_address = '$address1'");
    // }

    $line = $_POST["url"];
    $playlistpassword = isset($_POST["playlistpassword"]) ? $_POST["playlistpassword"] : "";
    $active = $_POST["active"] == "1" ? 1 : 0;
    $expire_date = ($active === '0') 
        ? date('Y-m-d', strtotime('-1 day')) 
        : date("Y-m-d", strtotime($_POST["expire_date"]));

    $stmt = $db->prepare("INSERT INTO ibo (
        mac_address, key, expire_date, username, password, dns, epg_url, title, url, type, playlistpassword, id_user, active
    ) VALUES (
        :mac_address, :key, :expire_date, :username, :password, :dns, :epg_url, :title, :url, :type, :playlistpassword, :id_user, :active
    )");

    $stmt->bindValue(':mac_address', $address1, SQLITE3_TEXT);
    $stmt->bindValue(':key', $_POST["key"], SQLITE3_TEXT);
    $stmt->bindValue(':expire_date', $expire_date, SQLITE3_TEXT);
    $stmt->bindValue(':username', $_POST["username"], SQLITE3_TEXT);
    $stmt->bindValue(':password', $_POST["password"], SQLITE3_TEXT);
    $stmt->bindValue(':dns', $_POST["dns"], SQLITE3_TEXT);
    $stmt->bindValue(':epg_url', $_POST["epg_url"], SQLITE3_TEXT);
    $stmt->bindValue(':title', $_POST["title"], SQLITE3_TEXT);
    $stmt->bindValue(':url', $line, SQLITE3_TEXT);
    $stmt->bindValue(':type', "1", SQLITE3_TEXT);
    $stmt->bindValue(':playlistpassword', $playlistpassword, SQLITE3_TEXT);
    $stmt->bindValue(':id_user', $_POST["id_user"], SQLITE3_INTEGER);
    $stmt->bindValue(':active', $active, SQLITE3_INTEGER);
    
    $stmt->execute();

    if (isset($_SESSION['macs'])) {
        $res = $db->query("SELECT * FROM ibo WHERE mac_address = '$address1'");
        $row = $res->fetchArray();
        $_SESSION['macs'][] = $row;
    }

    header("Location: users.php");
    exit();
}

include "includes/header.php";
?>

<style>
    .alert-box {
        display: none; /* Inicialmente escondido */
        position: fixed;
        top: 20px; /* Posição no topo da página */
        left: 50%;
        transform: translateX(-50%); /* Centraliza horizontalmente */
        padding: 15px; /* Espaçamento interno */
        border-radius: 5px; /* Cantos arredondados */
        z-index: 1000; /* Fica acima de outros elementos */
        opacity: 1; /* Opacidade inicial */
        transition: opacity 0.5s ease, top 0.5s ease; /* Transições suaves */
    }
    .alert-error {
        background-color: red; /* Cor de fundo vermelha */
        color: white; /* Cor do texto branco */
    }
    .alert-success {
        background-color: green; /* Cor de fundo verde */
        color: white; /* Cor do texto branco */
    }
</style>

<div id="alert-box" class="alert-box alert-error">POR FAVOR, INSIRA UM LINK M3U8!</div>
<div id="success-box" class="alert-box alert-success">DADOS EXTRAÍDOS COM SUCESSO!</div>

<div class="container-fluid">
    <center><h1 class="h3 mb-1 text-gray-800">ADICIONAR NOVO USUÁRIO</h1></center>
    <div class="card border-left-primary shadow h-100 card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-cogs"></i> ADICIONAR USUÁRIO</h6>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label class="control-label" for="mac_address"><strong><i class="fa fa-laptop"></i> MAC</strong></label>
                    <div class="input-group">
                        <input class="form-control text-primary" id="mac_address" name="mac_address" type="text" required maxlength="17" pattern="^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$" title="Digite um endereço MAC válido no formato 00:00:00:00:00:00"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="title"><strong><i class="fa fa-user"></i> NOME DO CLIENTE</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary" name="title" id="description" required />
                    </div>
                </div>

                <div class="form-group">
                    <strong class="text-primary" style="font-size: 1.2em;"><i class="fa fa-exchange-alt"></i> SELECIONE O MODO DE LOGIN:</strong>
                    <select class="form-control type" id="type" name="type" style="border: 2px solid #007bff; box-shadow: 0px 0px 10px rgba(0, 123, 255, 0.5);">
                        <option value="0">Xtream Codes (DNS)</option>
                        <option value="1">Lista M3U8 (LINK)</option>
                    </select>
                </div>

                <div class="form-group" id="m3u_address_group">
                    <label class="control-label" for="m3u_address"><strong><i class="fa fa-link"></i> LINK M3U8</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary" id="m3u_address" placeholder="Cole o Link M3U8 aqui" />
                        <div class="input-group-append">
                            <button class="btn btn-primary" onclick="extract(event)">EXTRAIR</button>
                        </div>
                    </div>
                </div>

                <div class="active1">
                    <div class="form-group">
                        <label class="control-label" for="dns"><strong><i class="fas fa-globe"></i> DNS</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control text-primary" name="dns" id="dns" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="username"><strong><i class="fa fa-user-circle"></i> USUÁRIO</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control text-primary" name="username" id="username" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="password"><strong><i class="fa fa-key"></i> SENHA</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control text-primary" name="password" id="password" />
                        </div>
                    </div>
                </div>

                <div class="active2" style="display: none;">
                    <div class="form-group">
                        <label class="control-label" for="url"><strong><i class="fa fa-globe"></i> URL</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control text-primary" name="url" id="url" />
                        </div>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="control-label" for="expire_date"><strong><i class="fa fa-calendar"></i> Vencimento</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary" name="expire_date" placeholder="YYYY-MM-DD" id="datetimepicker" value="2050-09-25" />
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="control-label" for="id_user"><strong><i class="fa fa-code-branch"></i> Seu ID</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary" name="id_user" id="id_user" pattern="[0-9]*" title="Apenas números" required value="<?php echo htmlspecialchars($id); ?>" readonly/>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <input type="text" class="form-control text-primary" name="active" id="active" value="1" />
                </div>

                <div class="form-group">
                    <div>
                        <button class="btn btn-success btn-icon-split" name="submit" type="submit">
                            <span class="icon text-white-50"><i class="fas fa-check"></i></span>
                            <span class="text"> CRIAR</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script>
$(document).ready(function() {
    // Hide/show fields based on selection
    $('.type').change(function() {
        if ($('.type').val() == "0") {
            $('.active1').show();
            $('.active2').hide();
            $('#m3u_address_group').show(); // Mostra o campo de Link M3U8
        } else {
            $('.active2').show();
            $('.active1').hide();
            $('#m3u_address_group').hide(); // Oculta o campo de Link M3U8
        }
    }).trigger('change'); // Trigger change on page load

    // Set the status to Active and hide the dropdown
    $('#active').val('1'); // Define o valor como 'Ativo'
    $('#active').hide(); // Oculta o campo de status

    // Format MAC address
    $('#mac_address').on('input', function() {
        var mac = $(this).val().toUpperCase().replace(/[^A-F0-9]/g, '');
        if (mac.length > 12) mac = mac.substr(0, 12);
        mac = mac.match(/.{1,2}/g).join(':');
        $(this).val(mac);
    });
});

// Função para extrair dados
function extract(event) {
    event.preventDefault(); // Evita a atualização da página

    var m3uLink = document.getElementById("m3u_address").value;

    // Verifica se o link não está vazio
    if (!m3uLink) {
        showAlert('POR FAVOR, INSIRA UM LINK M3U8!'); // Exibe a mensagem de alerta
        return; // Se não houver link, não faz nada
    }

    // Extrai a parte do servidor e os parâmetros da URL
    var urlParts = m3uLink.split("/get.php");
    var serverUrl = urlParts[0]; // Parte antes de /get.php
    var params = urlParts[1]; // Parte depois de /get.php (se existir)

    // Atualiza o campo URL com a URL completa
    document.getElementById("url").value = serverUrl + "/get.php" + (params ? params : ""); // Adiciona os parâmetros se existirem

    // Extrai o DNS
    var dns = serverUrl; // Aqui você pode definir como deseja extrair o DNS
    document.getElementById("dns").value = dns;

    // Extrai o nome de usuário
    var username = getParameterByName("username", m3uLink);
    document.getElementById("username").value = username;

    // Extrai a senha
    var password = getParameterByName("password", m3uLink);
    document.getElementById("password").value = password;

    // Mostra a mensagem de sucesso
    showSuccess('DADOS EXTRAÍDOS COM SUCESSO!');
}

// Função para mostrar o alerta
function showAlert(message) {
    const alertBox = document.getElementById('alert-box');
    alertBox.innerText = message; // Define o texto da mensagem
    alertBox.style.display = 'block'; // Torna o balão visível
    alertBox.style.opacity = 1; // Define a opacidade inicial
    alertBox.style.top = '20px'; // Define posição inicial

    // Remove a mensagem após 3 segundos
    setTimeout(() => {
        alertBox.style.opacity = 0; // Começa a desvanecer
        setTimeout(() => {
            alertBox.style.display = 'none'; // Esconde o balão após a animação
        }, 250); // Tempo de espera para a animação de desvanecimento
    }, 1500); // Duração da exibição do alerta
}

// Função para mostrar a mensagem de sucesso
function showSuccess(message) {
    const successBox = document.getElementById('success-box');
    successBox.innerText = message; // Define o texto da mensagem
    successBox.style.display = 'block'; // Torna o balão visível
    successBox.style.opacity = 1; // Define a opacidade inicial
    successBox.style.top = '20px'; // Define posição inicial

    // Remove a mensagem após 3 segundos
    setTimeout(() => {
        successBox.style.opacity = 0; // Começa a desvanecer
        setTimeout(() => {
            successBox.style.display = 'none'; // Esconde o balão após a animação
        }, 250); // Tempo de espera para a animação de desvanecimento
    }, 1500); // Duração da exibição do alerta
}

// Função para obter parâmetros da URL
function getParameterByName(name, url) {
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return "";
    if (!results[2]) return "";
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
</script>