<?php
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}

include 'auth.php';


if (!isset($_SESSION['admin_token'])) {
    $_SESSION['admin_token'] = bin2hex(random_bytes(32));
}

include ('includes/header.php');


define('UPLOAD_DIR', 'uploads/');
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PAINEL IBO PRO</title> 
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        /* Estilo para botões verdes */
        .btn-green {
            background-color: #1cc88a; /* Cor de fundo verde */
            color: white; /* Cor do texto branco */
            padding: 10px 20px; /* Espaçamento interno */
            border: none; /* Sem borda */
            border-radius: 5px; /* Bordas arredondadas */
            cursor: pointer; /* Cursor de mão ao passar */
            text-decoration: none; /* Sem sublinhado */
        }
        /* Estilo para botões verdes ao passar o mouse */
        .btn-green:hover {
            background-color: #17A673; /* Cor de fundo mais escura */
        }
        /* Estilo para botão de excluir */
        .btn-delete {
            background-color: #e74c3c; /* Cor de fundo vermelha */
            color: white; /* Cor do texto branco */
            padding: 10px 20px; /* Mesmo espaçamento interno */
            border: none; /* Sem borda */
            border-radius: 5px; /* Bordas arredondadas */
            cursor: pointer; /* Cursor de mão ao passar */
        }
        .btn-delete:hover {
            background-color: #c0392b; /* Cor de fundo mais escura ao passar o mouse */
        }
        /* Estilo para a tabela */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px; /* Margem superior para separar do formulário */
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }
        td {
            background-color: #fff;
            color: #555;
        }
        /* Estilo para linhas alternadas */
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        /* Estilo para hover */
        tr:hover {
            background-color: #f5f5f5;
            transition: background-color 0.3s ease;
        }
        .btn-cell {
            text-align: center; 
        }
        .image-cell {
            text-align: center; 
        }
        .image-cell img {
            display: block; 
            margin: 0 auto; 
        }
        .title-cell {
            text-align: center; 
        }
        .message {
            display: none; /* Inicialmente oculto */
            margin-bottom: 15px;
        }
        /* Estilo para a barra de rolagem horizontal */
        .table-responsive {
            overflow-x: auto; /* Adiciona rolagem horizontal */
        }
    </style>
</head>
<body>

<div class="col-lg-12">
    <div class="card border-left-primary shadow h-100 card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> GERENCIAR QR-CODE</h6>
        </div>
        <div class="card-body">
            <div class="message alert alert-success" id="message"></div>

            <button class="btn-green" onclick="showForm('add')">ADICIONAR NOVO</button>

            <div class="card-header py-3" id="bannerForm" style="display: none;">
                <h1 class="h3 mb-1 text-gray-800">QRCODE</h1>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token']; ?>">
                    <label for="title">Título:</label>
                    <input type="text" name="title" id="title" required><br>
                    <input type="file" name="banner" id="banner" accept=".jpg,.jpeg,.png,.gif" required>
                    <input class="btn-green" type="submit" value="Enviar" name="upload">
                </form>
            </div>
            <br>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Título</th>
                            <th style="text-align: center;">Imagem</th>
                            <th style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    if (is_dir(UPLOAD_DIR) && is_readable(UPLOAD_DIR)) {
                        $banner_files = glob(UPLOAD_DIR . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);

                        foreach ($banner_files as $file) {
                            $filename = pathinfo($file, PATHINFO_FILENAME);
                            $file_extension = pathinfo($file, PATHINFO_EXTENSION);
                            $full_filename = $filename . '.' . $file_extension;

                            echo "<tr>";
                            echo "<td class='title-cell'>" . htmlspecialchars($filename) . "</td>";
                            echo "<td class='image-cell'><img src='$file' alt='$filename' width='100'></td>";
                            echo "<td class='btn-cell'>";
                            echo "<button class='btn-green' onclick='showEditForm(\"$filename\", \"$full_filename\")'>Editar</button>";
                            echo "<button class='btn-delete' onclick='confirmDelete(\"$full_filename\")'>Excluir</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>O diretório de uploads não existe ou não é acessível.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="card-header py-3" id="editBannerForm" style="display: none;">
                <h2>Editar Banner</h2>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['admin_token']; ?>">
                    <label for="editTitle">Novo Título:</label>
                    <input type="text" name="editTitle" id="editTitle" required><br>
                    Selecione a Nova Imagem:
                    <input type="file" name="editBanner" id="editBanner" accept=".jpg,.jpeg,.png,.gif" required>
                    <input type="hidden" name="old_filename" id="old_filename">
                    <input type="submit" value="Salvar" name="submit" class="btn-green">
                </form>
            </div>

            <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>ATENÇÃO</h2>
                        </div>
                        <div class="modal-body">
                            DESEJA REALMENTE EXCLUIR ESTE BANNER?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
                            <a class="btn btn-danger btn-ok">Excluir</a>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                let filenameToDelete = '';

                function showForm(action) {
                    if (action === 'add') {
                        document.getElementById("editBannerForm").style.display = "none"; // Esconde o formulário de edição
                        document.getElementById("bannerForm").style.display = "block"; // Mostra o formulário de adicionar
                    } else {
                        document.getElementById("bannerForm").style.display = "none"; // Esconde o formulário de adicionar                        
                        document.getElementById("editBannerForm").style.display = "block"; // Mostra  formulário de edição
                    }
                }

                function showEditForm(filename, full_filename) {
                    document.getElementById("editTitle").value = filename;
                    document.getElementById("old_filename").value = full_filename;
                    showForm('edit'); // Mostra o formulário de edição e esconde o de adicionar
                }

                function confirmDelete(filename) {
                    filenameToDelete = filename; // Armazena o nome do arquivo a ser excluído
                    $('#confirm-delete').modal('show'); // Exibe o modal
                }

                $('.btn-ok').on('click', function() {
                    window.location.href = "qrcode.php?delete=" + filenameToDelete; // Redireciona para a exclusão
                });

                function showMessage(message, type) {
                    var messageDiv = document.getElementById('message');
                    messageDiv.className = 'message alert alert-' + type; // Define o tipo de alerta
                    messageDiv.innerHTML = message; // Define a mensagem
                    messageDiv.style.display = 'block'; // Exibe a mensagem
                    setTimeout(function() {
                        messageDiv.style.display = 'none'; // Esconde a mensagem após 5 segundos
                    }, 5000); // Aumentamos para 5000 milissegundos (5 segundos)
                }
            </script>

            <?php
            // Lógica de upload de nova imagem com validação
            if (isset($_POST['upload'])) {
                // Verificação do token CSRF
                if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
                    header("Location: login.php");
                    exit();
                }

                $title = basename(trim($_POST['title'])); // Remover espaços em branco
                $new_image = $_FILES['banner'];

                // Verifica se o arquivo foi enviado
                if ($new_image['error'] == UPLOAD_ERR_OK) {
                    // Valida o tipo de arquivo
                    $file_info = getimagesize($new_image['tmp_name']);
                    if (!in_array($file_info['mime'], ALLOWED_TYPES)) {
                        echo "<script>showMessage('Tipo de arquivo não permitido.', 'danger');</script>";
                    } else {
                        $new_image_name = $title . '_' . time() . '.' . pathinfo($new_image['name'], PATHINFO_EXTENSION);
                        $target_path = UPLOAD_DIR . $new_image_name;

                        if (move_uploaded_file($new_image['tmp_name'], $target_path)) {
                            echo "<script>showMessage('Imagem $title ADICIONADA COM SUCESSO!', 'success');</script>";
                            echo "<meta http-equiv='refresh' content='0'>";
                        } else {
                            echo "<script>showMessage('ERRO AO ENVIAR O ARQUIVO.', 'danger');</script>";
                        }
                    }
                } else {
                    echo "<script>showMessage('ERRO AO ENVIAR A IMAGEM.', 'danger');</script>";
                }
            }

            // Lógica para edição do banner
            if (isset($_POST['submit'])) {

                if (!isset($_POST['token']) || !hash_equals($_SESSION['admin_token'], $_POST['token'])) {
                    header("Location: login.php");
                    exit();
                }

                $old_filename = basename($_POST['old_filename']);
                $new_title = trim($_POST['editTitle']);
                $new_image = $_FILES['editBanner'];

                if ($new_image['error'] == UPLOAD_ERR_OK) {
                    $file_info = getimagesize($new_image['tmp_name']);
                    if (!in_array($file_info['mime'], ALLOWED_TYPES)) {
                        echo "<script>showMessage('Tipo de arquivo não permitido.', 'danger');</script>";
                    } else {
                        $new_image_name = $new_title . '_' . time() . '.' . pathinfo($new_image['name'], PATHINFO_EXTENSION);
                        $target_path = UPLOAD_DIR . $new_image_name;

                        if (move_uploaded_file($new_image['tmp_name'], $target_path)) {
                            if (file_exists(UPLOAD_DIR . $old_filename)) {
                                unlink(UPLOAD_DIR . $old_filename);
                            }
                            echo "<script>showMessage('Imagem editada com sucesso para $new_image_name.', 'success');</script>";
                            echo "<meta http-equiv='refresh' content='0'>";
                        } else {
                            echo "<script>showMessage('ERRO AO ENVIAR O ARQUIVO.', 'danger');</script>";
                        }
                    }
                } else {
                    echo "<script>showMessage('ERRO AO ENVIAR A IMAGEM.', 'danger');</script>";
                }
            }

            // Lógica para exclusão do banner
            if (isset($_GET['delete'])) {
                $filename = basename($_GET['delete']);
                $filepath = UPLOAD_DIR . $filename;

                if (file_exists($filepath)) {
                    unlink($filepath);
                    echo "<script>showMessage('Banner $filename excluído com sucesso.', 'success');</script>";
                    echo "<meta http-equiv='refresh' content='0'>";
                    exit;
                } 
            }
            ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>