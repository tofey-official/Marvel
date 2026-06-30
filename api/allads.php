<?php
// Caminho do banco de dados SQLite
$db_path = __DIR__ . '/../api/.adb.db';

// Verificar se o arquivo do banco de dados existe
if (!file_exists($db_path)) {
    die("Banco de dados não encontrado no caminho especificado: " . $db_path);
}

// Conectar ao banco de dados SQLite
$adb = new SQLite3($db_path);

// Verificar se a conexão foi bem-sucedida
if (!$adb) {
    die("Erro ao conectar ao banco de dados: " . $adb->lastErrorMsg());
}

// Consulta para obter todos os anúncios
$res = $adb->query("SELECT * FROM ads");

// Criar um array para armazenar as URLs das imagens
$image_urls = array();
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $image_urls[] = $row['url'];
}

// Fechar a conexão com o banco de dados
$adb->close();

// Verificar se as URLs foram recuperadas
if (empty($image_urls)) {
    die("Nenhuma imagem encontrada na tabela de anúncios.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotating Ads</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        #ads-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        #ads-image {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div id="ads-container">
        <img id="ads-image" src="" alt="Advertisement">
    </div>

    <script>
        var imageUrls = <?php echo json_encode($image_urls); ?>;
        var currentIndex = 0;

        function showNextImage() {
            var imgElement = document.getElementById('ads-image');
            imgElement.src = imageUrls[currentIndex];
            currentIndex = (currentIndex + 1) % imageUrls.length;
        }

        setInterval(showNextImage, 5000); // Mudar a imagem a cada 5 segundos

        // Mostrar a primeira imagem imediatamente
        showNextImage();
    </script>
</body>
</html>
