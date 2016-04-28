<?php

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

class Response {

	public $files;
	public $req;

	/**
	 * Response constructor.
	 * @param $files {array}
	 * @param $req {array}
	 */
	public function __construct($files, $req)
	{
		$this->files = $files;
		$this->req = $req;
	}

	/**
	 * @return string
	 */
	public function toJSON()
	{
		return json_encode($this);
	}
}

class File {
	public $origin;
	public $temp_path;
	public $name;

	/**
	 * File constructor.
	 * @param $origin
	 * @param $temp_path
	 */
	public function __construct($origin, $temp_path)
	{
		$this->origin = $origin;
		$this->temp_path = $temp_path;
		$this->handleFile($this->origin,$this->temp_path);
	}

	/**
	 * @param $origin (nome de origem, no envio)
	 * @param $temp_path (caminho, do diretório temporário)
	 */
	public function handleFile ($origin, $temp_path)
	{
		$updir = '/var/www/static/';
		$info = pathinfo($origin);
		$upext = $info['extension'];
		$upfile = random_int(0, 999999);

		if (move_uploaded_file($temp_path, $updir . $upfile . "." . $upext)) {
			$this->name = $upfile . "." . $upext;
			echo $this->toJSON();
		}
	}

	/**
	 * @return string
	 */
	public function toJSON()
	{
		return json_encode($this);
	}
}

try {
	if ($_GET['callback'])
		echo $_GET['callback'] . '(' . json_encode($_GET) . ')'; //jsonp apenas requisições get
	else if (count($_POST)) { //$_POST é um array
		//ignorando o campo texto por enquanto:
		$temppath = $_FILES["file"]["tmp_name"];
		$originname = $_FILES["file"]["name"];
		$file = new File($originname, $temppath);
	} else {
		//get? > listar arquivos
	}
} catch (Exception $e) {
	echo "Erro: " . $e->getMessage();
}

//	if ($_POST["file"] != "undefined")
//		echo json_encode(array($_FILES, $_POST));