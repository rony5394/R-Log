<?php
require_once("api.php");
session_start();


// Enable beta auth
if($_SERVER['SERVER_PORT'] == 89)
{

    if(!isset($_SERVER['PHP_AUTH_USER'])){
        header('WWW-Authenticate: Basic realm="LoginTest"');
        header('HTTP/1.0 401 Unauthorized');
        exit();
    }

    $activeKeys = explode(";", file_get_contents("./../secrets/beta_keys.txt"));

    if (!in_array($_SERVER['PHP_AUTH_PW'], $activeKeys)){
        header('WWW-Authenticate: Basic realm="LoginTest"');
        header('HTTP/1.0 401 Unauthorized');
        exit();
    }

}

$R_Log = new R_Log();

if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
    $R_Log->RegisterUser($_POST['username'],$_POST['password'],$_POST['email']);
}

if (isset($_POST['username']) && isset($_POST['password']) && !$R_Log->isLoginned()) {
    $R_Log->LoginUser($_POST['username'], $_POST['password']);
}

if (isset($_POST['logout'])) {
    $R_Log->LogOutUser();
}
if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['unregister'])) {
    $R_Log->LogOutUser();
    $R_Log->UnregisterUser($_POST['username'],$_POST['email']);
}

$user = new User($_SESSION['userid']);
$adminPanel = "";

if ($R_Log->isLoginned() == true) {
    if ($user->username == "admin")
    {
        $user->SetPermission('admin');
    }
    if($user->hasPermission('admin') == true){
        $adminPanel = '<hr>ADMIN:<form method="post" action="index.php"><input type="text" name="username" placeholder="Username"><br><input type="email" name="email" placeholder="Email"><br><input type="submit" name="unregister" value="Unregister"></form>';
    }else{
        $adminPanel = "";
    }
    
}

//Just stuff only for my specific server

    $protocol = "";

    if(isset($_SERVER['HTTPS'])) {
        if ($_SERVER['HTTPS'] == "on") {
            $protocol = "https";
        }
    }
    else{
        $protocol = "http";
    }

    $myIp = $_SERVER['HTTP_HOST'];
    if (!strpos($myIp, ":89")) {
        $myIp = $myIp . ":89";
    }

    $js_import_url = "$protocol://$myIp/loginApi/api.js";
    $static_import_url = "$protocol://$myIp/static";
//

$login_web = str_replace(["<|js_import_url|>"], [$js_import_url], file_get_contents("./html/login.html"));

$panel_web = str_replace(["<|user->username|>","<|user->permission|>","<|user->userid|>","<|adminPanel|>", "<|static_import_url|>"], [$user->username,$user->permission,$user->userid,$adminPanel, $static_import_url], file_get_contents("./html/panel.html"));


if ($R_Log->isLoginned() == true) {
    echo htmlspecialchars($panel_web);
}
else{
    echo htmlspecialchars($login_web);
}
?>