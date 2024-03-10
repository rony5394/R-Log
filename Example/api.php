<?php
session_start();

// Config
$token = file_get_contents("./../secrets/api_key.txt"); 
$apiUrl = "http://192.168.1.175/loginApi/index.php";
$loginPopoutFeature = true; // Build in login Popout. Recomend using with R-Log js
//

if (isset($_POST['loginWithPopup']) && $loginPopoutFeature) {
    $login = new R_Log();
    $login->LoginUser($_POST['username'], $_POST['password']);
    echo "<script>window.opener.location.reload();window.close();</script>";
}

class User
{
    public $userid;
    public $username;
    public $email;
    public $permission;
    public $reg_date;

    public function SetPermission($perm){
    $requestData = array(
        'token' => $GLOBALS["token"],
        'command' => 'SPERM',
        'userid' => $this->userid,
        'permission' => $perm
    );
    makePostRequest($requestData);
    }

    public function hasPermission($perm){
        if($this->permission == $perm){
            return true;
        }else{
            return false;
        }
    }

    public function isLoginned()
    {
        if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
            return true;
        }
        else{
            return false;
        }
    }

    public function __construct($userid)
    {
        $requestData = array(
            'token' => $GLOBALS["token"],
            'command' => 'GETID',
            'userid' => $userid
        );

        $response = makePostRequest($requestData);
        $userinfo = json_decode($response,true);
        $this->userid = $userinfo["userid"];
        $this->username = $userinfo["username"];
        $this->email = $userinfo["email"];
        $this->permission = $userinfo["permission"];
        $this->reg_date = $userinfo["reg_date"];
    }
}

class R_Log
{
    public function isLoginned()
    {
        if (isset($_SESSION['login']) && $_SESSION['login'] == true) {
            return true;
        }
        else{
            return false;
        }
    }


    public function hasPermission($userid,$perm)
    {
        $userinfo = GetUserInfo($userid);
        $userperm = $userinfo['permission'];
        if($userperm == $perm){
            return true;
        }else{
            return false;
        }
    }

    public function GetUserInfo($userid)
    {
        $requestData = array(
            'token' => $GLOBALS["token"],
            'command' => 'GETID',
            'userid' => $userid
        );

        $response = makePostRequest( $requestData);
        return json_decode($response,true);
    }

    public function SetPermission($userid,$perm){
        $requestData = array(
            'token' => $GLOBALS["token"],
            'command' => 'SPERM',
            'userid' => $userid,
            'permission' => $perm
        );
        makePostRequest($requestData);
    }

    public function GetUserPermission($userid){
        $requestData = array(
            'token' => $GLOBALS["token"],
            'command' => 'GPERM',
            'userid' => $userid
        );
        return makePostRequest($requestData);
    }

    public function RegisterUser($username,$password,$email)
    {
        $requestData = array(
            'token' => $GLOBALS["token"],
            'command' => 'REG',
            'username' => $username,
            'password' => $password,
            'email' => $email
        );

        $response = makePostRequest( $requestData);
        if ($response = "ALERTY USED") {
            return false;
        }

    }

    public function UnregisterUser($username,$email){
        $requestData = array(
            'token' => $GLOBALS["token"],
            'command' => 'UNREG',
            'username' => $username,
            'email' => $email
        );

        $response = makePostRequest( $requestData);

    }

    public function LoginUser($username,$password)
    {
        if (!isset($_SESSION['login']) && $_SESSION['login'] != true) {
            $requestData = array(
                'token' => $GLOBALS["token"],
                'command' => 'LOG',
                'username' => $username,
                'password' => $password
            );
            $response = json_decode(makePostRequest( $requestData), true);
            if ($response['login'] == true) {
                $_SESSION['login'] = true;
                $_SESSION['userid'] = (int)$response['userid'];
                return true;
            }else{
                return false; 
            }
        }
    }

    public function LogOutUser()
    {
    session_destroy();
    header("Refresh:0");
    }

}

function makePostRequest($requestData) {
    // Create POST data string
    $postData = http_build_query($requestData);

    // Set the options for the stream context
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postData
        )
    );

    $context = stream_context_create($options);

    $response = file_get_contents($GLOBALS['apiUrl'], false, $context);
    //var_dump($response);

    // Return the response
    return $response;
}

$LoginGui = "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Audiowide'>
    <style type='text/css'>
        body {
            font-family: 'Audiowide', cursive;
            background-color: #f0f0f0;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-window {
            background-color: white;
            width: 50%;
            max-width: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #3495eb;
            border-radius: 5px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            border-color: #1e87f0;
        }

        .submit-btn {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            border: none;
            border-radius: 5px;
            background-color: #3495eb;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #1e87f0;
        }

        h3 {
            margin-bottom: 15px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class='login-window'>
        <form method='post' name='loginForm' action='" . $_SERVER['PHP_SELF'] . "'>
            <h3>LOGIN</h3>
            <input type='text' name='username' placeholder='Username' class='form-input'>
            <input type='password' name='password' placeholder='Password' class='form-input'>
            <input type='submit' class='submit-btn' value='Login'>
            <input type='hidden' name='loginWithPopup' value='true'>
        </form>
    </div>
</body>
</html>
";

if (isset($_GET['openLoginPopup']) && $loginPopoutFeature == true) {
    echo htmlspecialchars($LoginGui);
}
?>
