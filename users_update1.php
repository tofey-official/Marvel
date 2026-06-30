<?php
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
} // Certifica-se de que a sessão está ativa

// Conectando ao banco de dados SQLite
$db = new SQLite3('./api/.ansdb.db');

// Verifica se o formulário foi enviado
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $url = trim($_POST['url']);

    // Atualiza os dados da playlist no banco de dados
    $stmt = $db->prepare("UPDATE ibo SET title = :title, url = :url WHERE id = :id");
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->bindValue(':url', $url, SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();

    // Redireciona de volta à página principal com um parâmetro de query indicando que o modal deve ser aberto
    header("Location: add.php?edit_success=1&id=" . urlencode($id));
    exit();
}
?>



// Mensagem de sucesso
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); // Remove a mensagem após exibição
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Playlist</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fc;
        }
        .container-fluid {
            margin-top: 30px;
        }
        .card {
            border-left: 0.25rem solid #4e73df;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="h3 mb-1 text-gray-800">Edit Playlist</h1>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card border-left-primary shadow h-100 card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Playlist Information</h6>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label for="m3u_address">
                        <strong>M3U Address</strong>
                    </label>
                    <input type="text" class="form-control" name="m3u_address" id="m3u_address" value="<?php echo htmlspecialchars($url); ?>" required/>
                </div>
                <div class="form-group">
                    <label for="title">
                        <strong>Title</strong>
                    </label>
                    <input type="text" class="form-control" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" required/>
                </div>
                <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($edit_id); ?>"/>
                <button type="submit" class="btn btn-success" name="update">Update Playlist</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
