<?php
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
} // Inicia a sessão para armazenar mensagens de sucesso

// Verifica se o usuário já está logado
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Conectando ao banco de dados SQLite
$db = new SQLite3('./api/.ansdb.db');
$db->exec('CREATE TABLE IF NOT EXISTS ibo(id INTEGER PRIMARY KEY NOT NULL, mac_address VARCHAR(100), username VARCHAR(100), password VARCHAR(100), expire_date VARCHAR(100), url VARCHAR(100), title VARCHAR(100))');

// Inicializa variáveis
$mac_address = "";
$existingLists = [];

// Processa o formulário de login
if (isset($_POST['login'])) {
    $mac_address = strtoupper(trim($_POST['mac_address']));
    $password = trim($_POST['password']);

    // Verifica as credenciais (você pode adicionar lógica adicional aqui para verificar o dispositivo no banco de dados)
    // Para este exemplo, vamos apenas marcar como logado se o login for feito com sucesso.
    $_SESSION['logged_in'] = true;
    $_SESSION['mac_address'] = $mac_address;

    // Redireciona para a mesma página para evitar reenvio do formulário
    header("Location: add.php");
    exit();
}

// Processa o logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: add.php");
    exit();
}

// Se o usuário estiver logado, obtenha listas associadas ao MAC Address armazenado
if ($is_logged_in) {
    $mac_address = $_SESSION['mac_address'];

    $stmt = $db->prepare("SELECT * FROM ibo WHERE mac_address = :mac_address");
    $stmt->bindValue(':mac_address', $mac_address, SQLITE3_TEXT);
    $existingLists = $stmt->execute();
}

// Processa o formulário de adição de lista
if (isset($_POST['submit'])) {
    $m3u_address = trim($_POST['m3u_address']);
    $mac_address = strtoupper(trim($_POST['mac_address']));
    $title = trim($_POST['title']);

    // Define a data de expiração automaticamente para 7 dias a partir de hoje
    $expire_date = date('Y-m-d', strtotime('+7 days'));

    function getParameterByName($name, $url) {
        $name = preg_quote($name, '/');
        $regex = "/[?&]$name=([^&#]*)/";
        preg_match($regex, $url, $matches);
        return $matches[1] ? urldecode($matches[1]) : null;
    }

    $username = getParameterByName('username', $m3u_address);
    $password = getParameterByName('password', $m3u_address);
    $dns = explode('/get.php', $m3u_address)[0];

    $stmt = $db->prepare("INSERT INTO ibo (mac_address, username, password, expire_date, url, title) VALUES (:mac_address, :username, :password, :expire_date, :url, :title)");
    $stmt->bindValue(':mac_address', $mac_address, SQLITE3_TEXT);
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->bindValue(':expire_date', $expire_date, SQLITE3_TEXT);
    $stmt->bindValue(':url', $m3u_address, SQLITE3_TEXT);
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->execute();
    
    $_SESSION['message'] = "Playlist adicionada com sucesso!";
}

// Se o usuário estiver logado, exibe apenas a interface de gerenciamento de playlists
if ($is_logged_in):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Playlists</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('https://i.imgur.com/iT4DRsE.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Roboto', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 900px;
            width: 100%;
            margin-top: 50px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
        }
        h3 {
            text-align: center;
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .form-group label {
            font-weight: 600;
            color: #555;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: none;
        }
        .btn-success {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        .table {
            margin-top: 30px;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
        }
        .table th {
            background-color: #007bff;
            color: white;
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
        }
        .table td {
            background-color: #ffffff;
            color: #333;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
            max-width: 200px;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-warning:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        .logout-btn {
            float: right;
            margin-bottom: 20px;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .modal-content {
            border-radius: 10px;
            padding: 20px;
        }
        .modal-header {
            border-bottom: none;
        }
        .modal-title {
            font-weight: 700;
            color: #333;
        }
        .modal-footer {
            border-top: none;
        }
        .modal-footer .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <a href="?logout=true" class="btn btn-danger logout-btn">Deslogar</a>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?php
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <h3 class="mt-5 text-center">Existing Playlists</h3>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Title</th>
                <th>Mac Address</th>
                <th>Username</th>
                <th>Expiration</th>
                <th>DNS</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $existingLists->fetchArray(SQLITE3_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['mac_address']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td> <!-- Exibindo o username -->
                    <td><?php echo htmlspecialchars($row['expire_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['url']); ?></td>
                    <td>
                        <button class="btn btn-success btn-sm edit-button" style="color: white;" data-id="<?php echo $row['id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>" data-url="<?php echo htmlspecialchars($row['url']); ?>">Upload / Edit</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for editing playlist -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST" action="users_update1.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Playlist</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label for="edit-title">Title</label>
                        <input type="text" name="title" id="edit-title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-url">M3U Address</label>
                        <input type="text" name="url" id="edit-url" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Handle edit button click
    $(document).on('click', '.edit-button', function () {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var url = $(this).data('url');
        
        // Fill modal with data
        $('#edit-id').val(id);
        $('#edit-title').val(title);
        $('#edit-url').val(url);
        
        // Show modal
        $('#editModal').modal('show');
    });
</script>
</body>
</html>

<?php else: ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('https://i.imgur.com/iT4DRsE.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Roboto', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 10px;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .login-container h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-weight: 700;
        }
        .form-group {
            width: 100%;
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: 600;
            color: #555;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: none;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<div class="login-container">
    <h3>Enter Mac Address and Device Key</h3>
    <form method="POST">
        <div class="form-group">
            <label for="mac_address">Mac Address</label>
            <input type="text" name="mac_address" id="mac_address" class="form-control" placeholder="E.x. 20:20:03:ET:00:19" maxlength="17" required>
        </div>
        <div class="form-group">
            <label for="password">Device Key</label>
            <input type="text" name="password" id="password" class="form-control" placeholder="E.x. 123456" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary">Logar</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    document.getElementById('mac_address').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^a-fA-F0-9]/g, '').toUpperCase(); // Remove caracteres não hexadecimais e converte em maiúsculas
        let formattedValue = '';

        for (let i = 0; i < value.length; i += 2) {
            formattedValue += value.substr(i, 2);
            if (i < 10) {
                formattedValue += ':';
            }
        }

        e.target.value = formattedValue.substr(0, 17); // Limita o valor a 17 caracteres
    });
</script>
</body>
</html>
<?php endif; ?>
