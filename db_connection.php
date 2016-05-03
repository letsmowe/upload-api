<?php
$server = "localhost";
$user = "usuario";
$pass = "123abc!@#ABC";
$dbname = "upload-api-h";

// Create connection
$conn = mysqli_connect($server, $user, $pass, $dbname);
// Check connection
if (!$conn) {
	die("Falha na conexão: " . mysqli_connect_error() . "<br>");
}

// Change charset to utf8
mysqli_set_charset($conn,"utf8");

echo "Conexão estabelecida.<br>";