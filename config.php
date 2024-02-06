<?php
$access = explode(";", file_get_contents("./secrets/sql_access.txt"));

$servername = $access[0];
$username = $access[1];
$password = $access[2];
$dbname = "API";

$conn = new mysqli($servername, $username, $password,$dbname);
?>