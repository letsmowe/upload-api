<?php

function connect() {
	// Create connection
	$server = "localhost";
	$user = "usuario-v";
	$pass = "abc123ABC!@#";
	$dbname = "upload-api-v";

	static $conn;

	if ($conn === NULL)
		$conn = mysqli_connect($server, $user, $pass, $dbname);

	// Check connection
	if (!$conn) {
		die("Falha na conexão: " . mysqli_connect_error() . "<br>");
	}

	// Change charset to utf8
	mysqli_set_charset($conn,"utf8");

	return $conn;
}