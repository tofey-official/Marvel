<?php

session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}
include 'auth.php';
include "includes/header.php";
?>

<style>
    .custom-button {
        padding: 10px 20px;
    }
    #url-form {
        display: none;
    }
    .custom-input {
        color: blue;
    }
    .preview-image {
        width: 100%;
        height: auto;
        border-radius: 20px;
        display: block;
        margin: 0 auto;
    }
    @media (min-width: 1200px) {
        .preview-image {
            width: 50%;
        }
    }
    @media (min-width: 768px) and (max-width: 1199px) {
        .preview-image {
            width: 70%;
        }
    }
    @media (max-width: 767px) {
        .preview-image {
            width: 100%;
        }
    }
    .container-fluid {
        text-align: center;
    }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-1 text-gray-800">Image Background</h1>

    <div class="card border-left-primary shadow h-100 card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-cogs"></i> Choose</h6>
        </div>
        <div class="card-body">
            <?php
            $jsonFilex = './img/fundo/image_filenames.json';
            $jsonDatax = file_get_contents($jsonFilex);
            $imageDatax = json_decode($jsonDatax, true);
            $filenamex = $imageDatax[0]['ImageName'];
            $uploadmethord = $imageDatax[0]['Upload_type'];

            if ($uploadmethord == "by_file") {
                $string = $filenamex;
                $firstLetterRemoved = substr($string, 1);
                $imageFilex = "$firstLetterRemoved";
                $methord = "Upload Method";
            } elseif ($uploadmethord == "by_url") {
                $imageFilex = "$filenamex";
                $methord = "URL Method";
            } else {
                $imageFilex = "https://c4.wallpaperflare.com/wallpaper/159/71/731/errors-minimalism-typography-red-wallpaper-preview.jpg";
                $methord = "";
            }

            echo '<h3>Currently in use: ' . $methord . '</h3>';
            echo '<input type="radio" name="upload-type" id="upload-radio" checked> Set Background Using File &nbsp&nbsp';
            echo '<input type="radio" name="upload-type" id="url-radio"> Set Background Using URL';
            echo '<br>';
            echo '<img class="preview-image" src="' . $imageFilex . '" alt="Uploaded Image">';
            echo '<br><br>';


            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
                    header("Location: login.php");
                    exit();
                }

                // Processamento do upload de imagem
                if (isset($_POST['upload'])) {
                    $selectedFiles = ['logo.png', 'index.php', 'iimg.json', 'filenames.json', 'binding_dark.webp', 'bg.jpg', 'api.php', 'favicon.ico', 'logo_ne.png', '.htaccess'];
                    $folderPath = './img_custom/Img/';
                    $files = scandir($folderPath);

                    foreach ($files as $file) {
                        if ($file !== '.' && $file !== '..') {
                            $filePath = $folderPath . $file;

                            if (!in_array($file, $selectedFiles)) {
                                unlink($filePath); // Deleta o arquivo se não estiver na lista
                            }
                        }
                    }

                    if (isset($_FILES['image'])) {
                        $file = $_FILES['image'];
                        $fileType = $file['type'];
                        $fileTemp = $file['tmp_name'];

                        // Validação do tipo de arquivo
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (in_array($fileType, $allowedTypes)) {
                            // Define o caminho para armazenar a imagem
                            $uploadPath = './img_custom/Img/';
                            $fileName = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                            $destination = $uploadPath . $fileName;

                            // Move o arquivo enviado para o destino
                            if (move_uploaded_file($fileTemp, $destination)) {
                                // Atualiza o JSON com o novo nome de arquivo
                                $jsonFilePath = './img/fundo/image_filenames.json';
                                $jsonData = json_encode([["ImageName" => "../img_custom/Img/" . $fileName, 'Upload_type' => 'by_file']]);
                                file_put_contents($jsonFilePath);
                                echo "<script>window.location.href='Image.php';</script>";
                            } else {
                                echo 'Erro ao mover o arquivo enviado. Verifique as permissões do diretório.';
                            }
                        } else {
                            echo 'Tipo de arquivo inválido. Apenas JPEG, PNG e GIF são permitidos.';
                        }
                    }
                }

                // Processamento da URL da imagem
                if (isset($_POST['url-submit'])) {
                    $imageUrl = $_POST['image-url'];

                    // Validação da URL
                    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        $jsonFilePath = './img/fundo/image_filenames.json';
                        $newImageData = [
                            'ImageName' => $imageUrl,
                            'Upload_type' => 'by_url'
                        ];

                        // Lê os dados existentes do JSON
                        $jsonData = file_get_contents($jsonFilePath);
                        $imageData = json_decode($jsonData, true);
                        $imageData[0] = $newImageData;

                        // Salva os dados atualizados no JSON
                        if (file_put_contents($jsonFilePath, json_encode($imageData))) {
                            echo "<script>window.location.href='Image.php';</script>";
                        } else {
                            echo 'Falha ao salvar os dados da imagem no arquivo JSON.';
                        }
                    } else {
                        echo 'URL inválida.';
                    }
                }
            }
            ?>

            <form method="post" enctype="multipart/form-data" id="upload-form">
                <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token']; ?>">
                <label for="image">Select an Image to upload:</label>
                <input class="custom-button" type="file" name="image" id="image" accept="image/jpeg, image/png, image/gif">
                <button class="custom-button btn btn-success btn-icon-split" type="submit" name="upload">Upload</button>
            </form>

            <form method="post" id="url-form">
                <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token']; ?>">
                <label for="image-url">Select an Image URL:</label>
                <input class="custom-button" type="text" name="image-url" id="image-url" placeholder="https://example.com/image.jpg">
                <button class="custom-button btn btn-success btn-icon-split" type="submit" name="url-submit">Submit URL</button>
            </form>

            <script>
                const uploadRadio = document.getElementById('upload-radio');
                const urlRadio = document.getElementById('url-radio');
                const uploadForm = document.getElementById('upload-form');
                const urlForm = document.getElementById('url-form');

                uploadRadio.addEventListener('change', () => {
                    uploadForm.style.display = 'block';
                    urlForm.style.display = 'none';
                });

                urlRadio.addEventListener('change', () => {
                    uploadForm.style.display = 'none';
                    urlForm.style.display = 'block';
                });
            </script>
        </div>
    </div>
</div>

<?php
include "includes/footer.php"; // Inclui o rodapé da página
?>
</body>
</html>