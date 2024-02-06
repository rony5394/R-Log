<?php
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	echo("<a href='Example/'>Click here</a>");
	exit();
}

include "./config.php";
$login = "";
//token the first: 0ec6c7c1fe3ba55e9793dbf83d6af4fa
header("Content-Type: application/json");
$tempDir = sys_get_temp_dir() . "/loginApi";
if (!is_dir($tempDir)) {
		mkdir($tempDir, 0755, true);
    }

function encryptToken($token){
    return preg_replace("/[^a-zA-Z0-9_]/", '', crypt($token, "TOKEN")); 
}

function error($error_code){
	echo "ERROR:". $error_code;
	$conn->close();
	exit();
}

function requestLog($token,$type)
{
	global $conn;
	$sql = "INSERT INTO REQUEST_HISTORY (token,request_type)
	VALUES ('$token', '$type')";
	if ($conn->query($sql) === false) {
  		echo "Error creating Request Log: " . $conn->error;
  		exit();
	}
}

function createApiUser($name){
	global $conn;
	$str=rand();
	$generated_token = md5($str);
	$hashed_token = encryptToken($generated_token);
	$sql = "INSERT INTO API_USERS (name, token)
	VALUES ('$name','$hashed_token')";

	if ($conn->query($sql) === false) {
  		echo "Error: " . $sql . "<br>" . $conn->error;
	}

	$sql = "CREATE TABLE $hashed_token (
	id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(30) NOT NULL,
	password VARCHAR(30) NOT NULL,
	email VARCHAR(50),
	permissions VARCHAR(10) DEFAULT 'user',
	reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE (username,email)
	)";

	if ($conn->query($sql) === false) {
  		echo "Error creating table: " . $conn->error;
  		exit();
	}
	return $generated_token;
}


function getUserInfo($api_token,$userid){
	global $conn;
	$api_token = mysqli_real_escape_string($conn,$api_token);
	$userid = mysqli_real_escape_string($conn,$userid);
	$sql = "SELECT id, username,email,permissions,reg_date FROM $api_token WHERE id=$userid";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()){
			echo(json_encode(array('userid' => $userid, 'username' => $row['username'], 'email' => $row['email'], 'permission' => $row['permissions'], 'reg_date' => $row['reg_date'])));
		}
		return true;
	}
	else{
		return false;
	}
}
function loginUser($api_token,$username,$password){
	global $conn;
	global $login;
	$api_token = mysqli_real_escape_string($conn,$api_token);
	$username = mysqli_real_escape_string($conn, $username);
	$password = mysqli_real_escape_string($conn, $password);
	$sql = "SELECT id, username, password FROM $api_token WHERE username='$username'";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
  		// output data of each row
  		while($row = $result->fetch_assoc()) {
    		if (crypt($password, "wtrefgreäôňä§qäqgňôqerg+ľuzšuioťuzžvžľbutdv") == $row["password"]) {
    			$login = $row["id"];
    			return $row['id'];
    		}
    		else{
    			return false;
    		}
  		}
	} else {
  		echo "0 results";
  		return false;
	}
}


function registerUser($api_token, $username, $password, $email){
    global $conn;
    $api_token = mysqli_real_escape_string($conn, $api_token);
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);
    $email = mysqli_real_escape_string($conn, $email);
    $hashed_password = crypt($password, "wtrefgreäôňä§qäqgňôqerg+ľuzšuioťuzžvžľbutdv");

    $sql = "SELECT id FROM $api_token WHERE username='$username' OR email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "ALERTY USED";
        return;
    }

    $sql = "INSERT INTO $api_token (username, password, email) VALUES ('$username', '$hashed_password', '$email')";

    if ($conn->query($sql) === false) {
        echo "Error: " . $sql . "<br>" . $conn->error;
        exit();
    }

    echo "REGISTERED";
}

function unregisterUser($api_token,$username,$email){
	global $conn;
	$api_token = mysqli_real_escape_string($conn,$api_token);
	$username = mysqli_real_escape_string($conn, $username);
	$email = mysqli_real_escape_string($conn, $email);

	$sql = "DELETE FROM $api_token WHERE username='$username' AND email='$email'";
	if ($conn->query($sql) === false) {
	  	echo "Error: " . $sql . "<br>" . $conn->error;
  		exit();
	}
	echo "UNREGISTERED";
}

function setUserPermission($api_token,$userid,$new_perm){
	global $conn;
	$api_token = mysqli_real_escape_string($api_token);
	$userid = mysqli_real_escape_string($userid);
	$new_perm = mysqli_real_escape_string($new_perm);
	$sql = "UPDATE $api_token SET permissions='$new_perm' WHERE id='$userid'";
	if ($conn->query($sql) === false) {
	  	echo "Error: " . $sql . "<br>" . $conn->error;
  		exit();
	}
	echo "[OK]";
}

function checkApiKey($api_token){
	global $conn;
	$api_token = mysqli_real_escape_string($conn,$api_token);
	$sql = "SELECT id, token, name FROM API_USERS WHERE token='$api_token'";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		return true;
	}
	else{
		return false;
	}
}
if(isset($_POST['command']) && (isset($_POST['token']) || $_SERVER['PHP_AUTH_USER'] == "token")) {
	$api_token = $_POST['token'];
	$command = $_POST['command'];
	if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == "token") {
		$api_token = $_SERVER['PHP_AUTH_PW'];
	}

	$api_token = encryptToken($api_token);

	if (checkApiKey($api_token) == false) {
		error(701);
	}

	switch ($command) {
		case 'REG':
			if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])){
			registerUser($api_token,$_POST['username'],$_POST['password'],$_POST['email']);
			requestLog($api_token,"REG");
			}else{
				error(702);
			}
		break;
		case 'LOG':
			if (isset($_POST['username']) && isset($_POST['password'])) {
				$login = loginUser($api_token,$_POST['username'],$_POST['password']);
				if(loginUser($api_token,$_POST['username'],$_POST['password']) != false) {
					echo json_encode(array('login' => true,'userid' => $login));
					requestLog($api_token,"LOG");
				}
				else{
					echo "[NO]";
				}
			}
		break;
		case 'UNREG':
			if (isset($_POST['username']) && isset($_POST['email'])) {
				unregisterUser($api_token,$_POST['username'],$_POST['email']);
				requestLog($api_token,"UNREG");
			}else{
				error(702);
			}
			break;
		case 'SPERM':
		if (isset($_POST['userid']) && isset($_POST['permission'])) {
				setUserPermission($api_token,$_POST['userid'],$_POST['permission']);
			}else{
				error(702);
			}
		break;
		case 'GETID':
			if (isset($_POST['userid'])) {
				if(getUserInfo($api_token,$_POST['userid']) == false)
				{
					error(706);
				}
				requestLog($api_token,"GETID");
			}
			else{
				error(702);
			}
		break;
		case 'CHECK':
			echo var_dump(checkApiKey($api_token));
		break;
		default:
			error(704);
			break;
	}
}
elseif (isset($_GET['maketoken']) && isset($_GET['name'])) {
	$maketoken  = $_GET['maketoken'];
	$name = $_GET['name'];

	if ($maketoken == "admin") {
		echo(createApiUser($name));	
	}
}
elseif (!isset($_POST['command']) && !isset($_POST['maketoken'])) {
	error(703);
}
elseif(isset($_POST['command']) && (!isset($_POST['token']) || $_SERVER['PHP_AUTH_USER'] != "token")){
	error(707);
}

$conn->close();
?>