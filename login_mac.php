<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(32767);
session_start();
if (file_exists(__DIR__ . '/includes/github_sync.php')) {
    include_once __DIR__ . '/includes/github_sync.php';
}
$jsondata111 = file_get_contents("./includes/ansibo.json");
$json111 = json_decode($jsondata111, true);
$col1 = $json111["info"];
$col2 = $col1["aa"];
$db = new SQLite3("api/.ansdb.db");
$db2 = new SQLite3("api/.anspanel.db");

$res_login = $db2->query("SELECT * \n\t\t\t\t  FROM USERS \n\t\t\t\t  WHERE id='1'");
$row_login = $res_login->fetchArray();
$name_login = "MY FULL";
$logo_login = $row_login["LOGO"];
$corp_login = $row_login["CORP"];
$db2->close();

if (isset($_POST["login"])) {
    $store_id = '';
    if (isset($_POST["store"])){
        $store_id = $_POST["store"];
    }
    $mac = strtoupper($_POST['mac']);
    $macRegex = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})|([0-9a-fA-F]{4}.[0-9a-fA-F]{4}.[0-9a-fA-F]{4})$/';
    $validMac = preg_match($macRegex, $mac);
    if($validMac){
        $sql_check = "SELECT COUNT(*) as count from ibo where mac_address='$mac' AND store_type = 2";
        $ret_check = $db->query($sql_check);
        $count = $ret_check->fetchArray()['count'];
        
        $expire = date('Y-m-d', strtotime('+7 days'));
        
        $limited = false;
        if($count == 0){
            if($store_id){
                $dbUsers = new SQLite3("./api/.anspanel.db");
                $res = $dbUsers->query("SELECT mac_amount FROM USERS WHERE id = '$store_id' ");
                $macCount = $res->fetchArray()['mac_amount'];
                $dbUsers->close();
                
                $res = $db->query("SELECT COUNT(*) as count FROM ibo WHERE id_user = '$store_id' AND active = 1 AND expire_date > date('now')");
                $count = $res->fetchArray()['count'];
        
                $limited = $count >= $macCount;
            }
            
            if(!$limited){
                $db->exec("INSERT INTO ibo (title, mac_address, expire_date, id_user, store_type) VALUES ('Playlist $mac', '$mac', '$expire', '$store_id' ,2)");
            }
        }
        
        $sql_check = "SELECT * from ibo where mac_address='$mac' AND store_type = 2";
        $ret_check = $db->query($sql_check);
        
        while ($row_check = $ret_check->fetchArray()) {
            $id_check = $row_check["id"];
            $mac_address = $row_check['mac_address'];
            $key = $row_check['key'];
        }
        if (empty($id_check)) {
            if($limited){
                echo '<div class="alert alert-danger">Limite de MACs excedidos para a loja</div>';
            }else {
                echo '<div class="alert alert-danger">Erro. Tente novamente</div>';
            }
        } else {
            $_SESSION["admin"] = false;
            $_SESSION["id"] = $id_check;
            $_SESSION["mac_address"] = $mac_address;

            header("Location: playlists_mac.php");
        }
        $db->close();
        
    }else {
        echo '<div class="alert alert-danger">Invalid MAC</div>';
    }
}
$date = date("d-m-Y H:i:s");
$IPADDRESS = real_ip();
// $db1 = new SQLite3("./api/.logs.db");
// $db1->exec("CREATE TABLE IF NOT EXISTS logs(id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date TEXT, ipaddress TEXT)");
// $db1->exec("INSERT INTO logs(date,ipaddress) VALUES('" . $date . "','" . $IPADDRESS . "')");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel Usuário</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-<?php echo $col2; ?>.css" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.1/js/all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.transit/0.9.12/jquery.transit.js" integrity="sha256-mkdmXjMvBcpAyyFNCVdbwg4v+ycJho65QLDwVE3ViDs=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
    <link rel="stylesheet" href="css/style.css">
    
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>

<body class="bg-gradient-primary">
    <div id="container">
        <div id="inviteContainer">
            <!--<div class="logoContainer">-->
            <!--    <img class="logo" src="<?php echo $logo_login; ?>"/><br>-->
            <!--    <p class="text" style="transition-delay: 0.2s"><?php echo $name_login; ?></p><br>-->
            <!--    <a href="" target="_blank">-->
            <!--        <img class="logo" src="img/corp.png" alt="&#169; Loja e Apps" title="&#169; Loja e Apps"/>-->
            <!--    </a>-->
            <!--</div>-->
            <div class="acceptContainer">
                <form method="POST">
                    <img class="img-mac-login" src="<?php echo $logo_login; ?>"/>
                    <p class="text" style="transition-delay: 0.4s"><br>
                        <h1>PAINEL DO MAC</h1>
                        DIGITE O SEU MAC CORRETAMENTE
                    </p>
                    <div class="formContainer">
                        <div class="formDiv" style="transition-delay: 0.2s">
                            <p>ENDEREÇO DO MAC</p>
                            <input type="text" class="form-control text-primary" id="mac" name="mac" maxlength='17' required="" autofocus/>
                        </div>
                        <div class="formDiv" style="transition-delay: 0.6s">
                            <button class="dacceptBtn btn btn-lg btn btn-primary btn-block" name="login" type="submit">ENTRAR</button>
                        </div>
                        <p class="text-center text-warning">DATA EXATA: "<i><?php echo date("d-m-Y H:i:s"); ?></i>"
                            <br> ENDEREÇO IP: "<i><?php echo real_ip(); ?> </i>"</p><br>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="">
            <div class="container">
            </div>
        </footer>
    </div>
    <!-- partial -->
    <script src="js/script.js"></script>
    <?php require "includes/ans.php"; ?>
</body>

</html>

<?php
function real_ip()
{
    $ip = "undefined";
    if (isset($_SERVER)) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            }
        }
    } else {
        $ip = getenv("REMOTE_ADDR");
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else {
            if (getenv("HTTP_CLIENT_IP")) {
                $ip = getenv("HTTP_CLIENT_IP");
            }
        }
    }
    $ip = htmlspecialchars($ip, ENT_QUOTES, "UTF-8");
    return $ip;
}

?>

<script type="text/javascript">
    let element = document.getElementById("mac"); 
    element.addEventListener('keydown', function() { 
      var mac = element.value;
      var macs = mac.split(':').join('');
      macs = chunk(macs, 2).join(':');
      element.value = macs.toString();
    });

    function chunk(str, n) {
        var ret = [];
        var i;
        var len;

        for(i = 0, len = str.length; i < len; i += n) {
           ret.push(str.substr(i, n));
        }

        return ret;
    };
</script>