<?php

include 'meta.class.php';
include 'file.class.php';
include 'persistence.class.php';

include 'db_action.class.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (isset($_SERVER['HTTP_ORIGIN'])) {
	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header("Access-Control-Allow-Origin: *");
	header('Access-Control-Allow-Credentials: true');
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

	exit(0);
}


try {

	if ($_GET['callback']) {

		echo $_GET['callback'] . '(' . json_encode($_GET) . ')'; //jsonp only get requests

	} else if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$insert = new Persistence($_POST);

		$action = new Bd_action();
		$action->insertPost($insert);

		echo $insert->toJSON();

	} else {

		// to return information

	}

} catch (Exception $e) {
	echo "Erro: " . $e->getMessage();
}