<?php

session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}
include 'auth.php';
include "includes/header.php"; 

if (!isset($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32));
}

$jsonData = file_get_contents('./api/theme_change/Setting.json');
$data = json_decode($jsonData, true);

$tema_atual = "";

foreach ($data as $item) {
    if (isset($item["RTXSetting"]) && $item["RTXSetting"] === "mLayout") {
        $tema_atual = $item["PanalData"];
        break;
    }
}

$temas = [
    "theme_d" => "Tema 1",
    "theme_2" => "Tema 2",
    "theme_3" => "Tema 3",
    "theme_4" => "Tema 4",
    "theme_5" => "Tema 5",
    "theme_6" => "Tema 6",
    "theme_7" => "Tema 7",
    "theme_8" => "Tema 8",
    "theme_9" => "Tema 9"
];

$tema_atual_escolhido = isset($temas[$tema_atual]) ? $temas[$tema_atual] : "Tema Desconhecido";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
        header("Location: login.php");
        exit();
    }

    $selectedOption = $_POST['options'];

    if (array_key_exists($selectedOption, $temas)) {
        $data[0]["RTXSetting"] = "mLayout";
        $data[0]["PanalData"] = $selectedOption;

        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents('./api/theme_change/Setting.json', $jsonData);

        $tema_atual_escolhido = $temas[$selectedOption];
        $tema_atual = $selectedOption;

        echo "<div class='alert alert-success'>O tema escolhido é: $tema_atual_escolhido</div>";
    } else {
        echo "<div class='alert alert-danger'>Tema inválido selecionado!</div>";
    }
}
?>

<style>
    .custom-button {
        padding: 10px 20px;
    }
    .image-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 20px;
    }
    .image-container {
        flex: 1 1 300px;
        max-width: 300px;
        margin: 10px;
        text-align: center;
        background-color: #FFF;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        padding: 10px;
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
    }
    .image-container img {
        width: 100%;
        height: auto;
        border-radius: 10px;
    }
    .image-wrapper {
        border: 5px solid transparent;
        border-radius: 10px;
        display: inline-block; /* Para que a borda envolva o conteúdo */
        padding: 10px; /* Espaçamento para a borda */
    }
    .image-wrapper.selected {
        animation: rgb-border 3s infinite; /* Aplica a animação da borda RGB */
    }
    .image-container img.selected {
        animation: pulsar 1.5s infinite; /* Aplica a animação de pulsação */
    }
    @keyframes pulsar {
        0% {
            transform: scale(1);
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.7);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.9);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.7);
        }
    }
    @keyframes rgb-border {
        0% {
            border: 5px solid red;
        }
        33% {
            border: 5px solid green;
        }
        66% {
            border: 5px solid blue;
        }
        100% {
            border: 5px solid red;
        }
    }
    label, select, input {
        background: #F8F9FC;
        padding: 10px 20px;
        margin-left: 10px;
        border: none;
        border-radius: 10px;
        box-shadow: 5px 5px 5px 0 rgba(0,0,0,0.35);
    }
    form {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
</style>

<div class="container-fluid">
    <center><h1 class="h3 mb-1 text-gray-800">Escolha o Tema</h1></center>
    <div class="card border-left-primary shadow h-100 card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-cogs"></i> Tema atual ( <?= $tema_atual_escolhido; ?> )</h6>
        </div>
        <div class="card-body">
            <form class="form-theme" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token']; ?>"> 
                <select name="options" id="options">
                    <?php
                    foreach ($temas as $key => $label) {
                        $selected = ($key === $tema_atual) ? 'selected' : '';
                        echo "<option value='$key' $selected>$label</option>";
                    }
                    ?>
                </select>
                <br><br>
                <input type="submit" class="btn btn-primary btn-icon-split custom-button" value="Ativar">
            </form>
            <br><br>
            <div class="image-row">
                <?php
                foreach ($temas as $key => $label) {
                    $selectedClass = ($key === $tema_atual) ? 'selected' : '';
                    $selectedImageClass = ($key === $tema_atual) ? 'selected' : '';
                    echo "<div class='image-container $selectedClass'>
                            <p>$label</p>
                            <div class='image-wrapper $selectedClass'>
                                <img src='./img_custom/layout/" . str_replace('theme_', '', $key) . ".jpg' alt='$key' class='$selectedImageClass'>
                            </div>
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
include "includes/footer.php";
?>
</body>
</html>