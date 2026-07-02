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

$id = $_SESSION['id'];
$db = new SQLite3("./api/.ansdb.db");

// Generate a random activation code by default
$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$generated_code = "";
for ($i = 0; $i < 8; $i++) {
    $generated_code .= $chars[rand(0, strlen($chars) - 1)];
}

if (isset($_POST["submit"])) {
    $address1 = strtoupper(trim($_POST["mac_address"]));

    if (empty($address1)) {
        die("الرمز لا يمكن أن يكون فارغاً!");
    }

    if (strpos($address1, ':') !== false) {
        die("كود التفعيل لا يمكن أن يحتوي على علامة النقطتين (:) لمنع التداخل مع الماك أدريس.");
    }

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

    header("Location: codes.php");
    exit();
}

include "includes/header.php";
?>

<style>
    .alert-box {
        display: none;
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        padding: 15px;
        border-radius: 5px;
        z-index: 1000;
        opacity: 1;
        transition: opacity 0.5s ease, top 0.5s ease;
    }
    .alert-error {
        background-color: red;
        color: white;
    }
    .alert-success {
        background-color: green;
        color: white;
    }
</style>

<div id="alert-box" class="alert-box alert-error">الرجاء إدخال رابط M3U8 صحيح!</div>
<div id="success-box" class="alert-box alert-success">تم استخراج البيانات بنجاح!</div>

<div class="container-fluid mt-4">
    <center><h1 class="h3 mb-2 text-gray-800">إنشاء كود تفعيل مجاني جديد</h1></center>
    <div class="card border-left-primary shadow h-100 card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-key"></i> إضافة كود تفعيل (Novo Código)</h6>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="form-group">
                    <label class="control-label" for="mac_address"><strong><i class="fa fa-key"></i> كود التفعيل (Activation Code)</strong></label>
                    <div class="input-group">
                        <input class="form-control text-primary" id="mac_address" name="mac_address" type="text" required maxlength="50" value="<?php echo $generated_code; ?>" placeholder="مثال: IBO-XXXXXX"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="title"><strong><i class="fa fa-user"></i> اسم المستخدم/العميل</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary" name="title" id="description" required placeholder="اسم للتعرف على الكود" />
                    </div>
                </div>

                <div class="form-group">
                    <strong class="text-primary" style="font-size: 1.2em;"><i class="fa fa-exchange-alt"></i> طريقة إدخال البيانات:</strong>
                    <select class="form-control type" id="type" name="type" style="border: 2px solid #007bff; box-shadow: 0px 0px 10px rgba(0, 123, 255, 0.5);">
                        <option value="0">Xtream Codes (DNS)</option>
                        <option value="1">رابط M3U (Link)</option>
                    </select>
                </div>

                <div class="form-group" id="m3u_address_group">
                    <label class="control-label" for="m3u_address"><strong><i class="fa fa-link"></i> رابط M3U</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary" id="m3u_address" placeholder="ضع رابط M3U هنا للاستخراج التلقائي" />
                        <div class="input-group-append">
                            <button class="btn btn-primary" onclick="extract(event)">استخراج</button>
                        </div>
                    </div>
                </div>

                <div class="active1">
                    <div class="form-group">
                        <label class="control-label" for="dns"><strong><i class="fas fa-globe"></i> الهوست / DNS</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control text-primary" name="dns" id="dns" placeholder="http://domain.com:port" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="username"><strong><i class="fa fa-user-circle"></i> اسم المستخدم (Username)</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control text-primary" name="username" id="username" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="password"><strong><i class="fa fa-key"></i> كلمة المرور (Password)</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control text-primary" name="password" id="password" />
                        </div>
                    </div>
                </div>

                <div class="active2" style="display: none;">
                    <div class="form-group">
                        <label class="control-label" for="url"><strong><i class="fa fa-globe"></i> رابط قائمة التشغيل (URL)</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control text-primary" name="url" id="url" />
                        </div>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="control-label" for="expire_date"><strong><i class="fa fa-calendar"></i> تاريخ الانتهاء</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary" name="expire_date" placeholder="YYYY-MM-DD" id="datetimepicker" value="2050-09-25" />
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="control-label" for="id_user"><strong><i class="fa fa-code-branch"></i> ID المستخدم</strong></label>
                    <div class="input-group">
                        <input type="text" class="form-control text-primary" name="id_user" id="id_user" pattern="[0-9]*" required value="<?php echo htmlspecialchars($id); ?>" readonly/>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <input type="text" class="form-control text-primary" name="active" id="active" value="1" />
                    <input type="text" name="key" value="" />
                </div>

                <div class="form-group">
                    <div>
                        <button class="btn btn-success btn-icon-split" name="submit" type="submit">
                            <span class="icon text-white-50"><i class="fas fa-check"></i></span>
                            <span class="text"> إنشاء الكود</span>
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
    $('.type').change(function() {
        if ($('.type').val() == "0") {
            $('.active1').show();
            $('.active2').hide();
            $('#m3u_address_group').show();
        } else {
            $('.active2').show();
            $('.active1').hide();
            $('#m3u_address_group').hide();
        }
    }).trigger('change');

    $('#active').val('1');
    $('#active').hide();
});

function extract(event) {
    event.preventDefault();

    var m3uLink = document.getElementById("m3u_address").value;

    if (!m3uLink) {
        showAlert('الرجاء إدخال رابط M3U8!');
        return;
    }

    var urlParts = m3uLink.split("/get.php");
    var serverUrl = urlParts[0];
    var params = urlParts[1];

    document.getElementById("url").value = serverUrl + "/get.php" + (params ? params : "");
    document.getElementById("dns").value = serverUrl;

    var username = getParameterByName("username", m3uLink);
    document.getElementById("username").value = username;

    var password = getParameterByName("password", m3uLink);
    document.getElementById("password").value = password;

    showSuccess('تم استخراج البيانات بنجاح!');
}

function showAlert(message) {
    const alertBox = document.getElementById('alert-box');
    alertBox.innerText = message;
    alertBox.style.display = 'block';
    alertBox.style.opacity = 1;
    alertBox.style.top = '20px';

    setTimeout(() => {
        alertBox.style.opacity = 0;
        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 250);
    }, 1500);
}

function showSuccess(message) {
    const successBox = document.getElementById('success-box');
    successBox.innerText = message;
    successBox.style.display = 'block';
    successBox.style.opacity = 1;
    successBox.style.top = '20px';

    setTimeout(() => {
        successBox.style.opacity = 0;
        setTimeout(() => {
            successBox.style.display = 'none';
        }, 250);
    }, 1500);
}

function getParameterByName(name, url) {
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return "";
    if (!results[2]) return "";
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
</script>
