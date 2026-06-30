<?php

function getIPAddress()
{
    $ipAddress = 'undefined';

    if (isset($_SERVER)) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        }
    } else {
        $ipAddress = getenv('REMOTE_ADDR');

        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ipAddress = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ipAddress = getenv('HTTP_CLIENT_IP');
        }
    }

    $ipAddress = htmlspecialchars($ipAddress, ENT_QUOTES, 'UTF-8');
    return $ipAddress;
}

session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}
$jsondata111 = file_get_contents("./includes/ansibo.json");
$json111 = json_decode($jsondata111, true);
$col1 = $json111["info"];
$col2 = $col1["aa"];
$db_check1 = new SQLite3("api/.anspanel.db");
$db_check1->exec("CREATE TABLE IF NOT EXISTS USERS(id INT PRIMARY KEY, NAME TEXT, USERNAME TEXT, PASSWORD TEXT, LOGO TEXT, ADMIN INTEGER, store_type INTEGER)");

$rows = $db_check1->query("SELECT COUNT(*) as count FROM USERS");
$row = $rows->fetchArray();
$numRows = $row["count"];

if ($numRows == 0) {
    $db_check1->exec("INSERT INTO USERS(id, NAME, USERNAME, PASSWORD, LOGO, ADMIN, store_type) VALUES('1','Seu Nome','admin','admin','img/logo.png', 1, 1)");
}

$res_login = $db_check1->query("SELECT * FROM USERS WHERE id='1'");
$row_login = $res_login->fetchArray();
$name_login = $row_login["NAME"];
$logo_login = $row_login["LOGO"];

if (isset($_POST["login"])) {
    if (!$db_check1) {
        echo $db_check1->lastErrorMsg();
    }


    $stmt = $db_check1->prepare("SELECT * FROM USERS WHERE USERNAME = :username");
    $stmt->bindValue(':username', $_POST["username"], SQLITE3_TEXT);
    $ret_check = $stmt->execute();

    $user = $ret_check->fetchArray();


    if ($user && $_POST["password"] === $user["PASSWORD"]) {
        $_SESSION["admin"] = $user['ADMIN'];
        $_SESSION["N"] = $user["id"];
        $_SESSION["id"] = $user["id"];
        $_SESSION["store_type"] = $user["store_type"];


        $_SESSION['admin_token'] = bin2hex(random_bytes(32));


        $path = "users";
        if ($user["store_type"] == 2) {
            $path .= '_mac';
        }

        header("Location: $path.php");
        exit();
    } else {
        $message = "<div class=\"alert alert-danger balloon\" id=\"flash-msg\"><h4>USUÁRIO OU SENHA INCORRETO!</h4></div>";
        echo $message;
    }

    $db_check1->close();
}

$date = date("d-m-Y H:i:s");
$IPADDRESS = getIPAddress();

$jsonFilex = './img/logo/logo_filenames.json';
$jsonDatax = file_get_contents($jsonFilex);
$imageDatax = json_decode($jsonDatax, true);
$filenamex = $imageDatax[0]['ImageName'];
$uploadmethord = $imageDatax[0]['Upload_type'];

if ($uploadmethord == "by_file") {
    $string = $filenamex;
    $firstLetterRemoved = substr($string, 1);
    $imageFilex = "$firstLetterRemoved";
    $methord = "   Upload Method";
} elseif ($uploadmethord == "by_url") {
    $imageFilex = "$filenamex";
    $methord = "   URL Method";
} else {
    $imageFilex = "https://c4.wallpaperflare.com/wallpaper/159/71/731/errors-minimalism-typography-red-wallpaper-preview.jpg";
    $methord = "";
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAINEL IBO REVENDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">
    <link href="css/sb-admin-<?php echo $col2; ?>.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="img2/logo.png" type="image/x-icon">
    <style>
        body {
            background-color: black; /* Define o fundo como preto */
            color: white; /* Define a cor do texto como branco */
            font-family: 'Roboto', sans-serif;
        }

        @media (max-width: 767px) {
            body {
                padding-top: 40px;
            }

            .container {
                padding: 0 20px;
            }
        }

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 87vh; /* Altura da tela */
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            border-top: 5px solid #4e73df;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            width: 100%;
            max-width: 400px;
            transform: translateX(-100%); /* Começando fora da tela à esquerda */
            opacity: 0; /* Começando invisível */
            transition: transform 0.5s ease, opacity 0.5s ease; /* Animações de entrada */
        }

        .form-container.visible {
            transform: translateX(0); /* Posição final na tela */
            opacity: 1; /* Tornando visível */
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .btn {
            padding: 5px 20px;
            font-size: 16px;
        }

        .btn2 {
            padding: 10px 20px;
            font-size: 20px;
            background-color: #4e73df; /* Cor de fundo */
            color: white; /* Cor do texto */
            border: none; /* Sem borda */
            border-radius: 5px; /* Bordas arredondadas */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1); /* Sombra para efeito 3D */
            transition: all 0.2s ease; /* Transições suaves */
        }

        .btn2:hover {
            background-color: #5a8fd6; /* Cor de fundo ao passar o mouse */
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2); /* Sombra mais intensa ao passar o mouse */
        }

        .btn2:active {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); /* Sombra reduzida ao clicar */
            transform: translateY(2px); /* Movimento para baixo ao clicar */
        }

        .password-toggle-icon {
            cursor: pointer;
            user-select: none;
        }

        .outside-image {
            width: 50%; /* Aumentando a largura da imagem */
            height: auto; /* Mantém a proporção da imagem */
            max-width: 100%;
            margin-bottom: 20px;
        }

        .form-title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            background-color: #4e73df;
            color: white;
            padding: 10px;
            border-radius: 0px;
            width: calc(100% + 40px);
            margin-left: -20px;
            margin-right: -20px;
            box-sizing: border-box;
        }

        .image-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        #flash-msg {
            position: relative;
            animation: fadeInOut 1.8s forwards; /* Animação de entrada e saída */
            margin-top: 20px; /* Espaçamento acima do balão */
            border-radius: 5px; /* Bordas arredondadas menores */
            padding: 5px; /* Reduzir o espaçamento interno */
            text-align: center; /* Centraliza o texto */
            max-width: 410px; /* Define uma largura máxima */
            width: 100%; /* Faz com que o balão ocupe até a largura máxima especificada */
            margin-left: auto; /* Centraliza horizontalmente */
            margin-right: auto; /* Centraliza horizontalmente */
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateY(-20px); /* Começa acima */
            }

            10% {
                opacity: 1; /* Totalmente visível */
                transform: translateY(0); /* Retorna à posição normal */
            }

            90% {
                opacity: 1; /* Mantém totalmente visível */
            }

            100% {
                opacity: 0; /* Desaparece */
                transform: translateY(-20px); /* Sobe um pouco o desaparecer */
            }
        }

        /* Estilo do balão */
        .balloon {
            background-color: #f8d7da; /* Cor de fundo do balão */
            border: 1px solid #f5c6cb; /* Borda do balão */
            color: #721c24; /* Cor do texto */
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="form-container" id="login-form-container">
            <div class="image-container">
                <img src="<?= htmlspecialchars($logo_login) ?>" alt="Descrição da Imagem" class="img-fluid outside-image">
            </div>
            <div class="form-title">ACESSO RESTRITO</div>
            <br>
            <form method="POST">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder=" Usuário" name="username" required autofocus />
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <input type="password" class="form-control" placeholder="Senha" name="password" required />
                        <button type="button" class="btn btn-secondary password-toggle-icon" onclick="togglePasswordVisibility()">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <button class="btn2 btn-lg btn btn-primary btn-block btn-pulse" name="login" type="submit">ENTRAR</button>
            </form>
            <br>
            <br>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Adiciona a classe visible após um pequeno atraso
            setTimeout(function() {
                $('#login-form-container').addClass('visible'); // Adiciona a classe para animar a entrada
            }, 100); // Atraso de 100ms para iniciar a animação
            
            // Se a mensagem de erro existir, inicia a animação
            if ($('#flash-msg').length) {
                setTimeout(function() {
                    $('#flash-msg').fadeOut(180, function() {
                        $(this).remove(); // Remove a mensagem após a animação
                    });
                }, 1800); // A mensagem ficará visível por 1.8 segundos
            }
        });

        function togglePasswordVisibility() {
            const passwordInput = document.querySelector('[name="password"]');
            const passwordIcon = document.querySelector('.password-toggle-icon i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye-slash';
            }
        }
    </script>
</body>

</html>