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

	public $arg;
	public $req;


	/**
	 * Response constructor
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

Class Persistence {
	public $timestamp;
	public $ip;
	public $useragent;
	public $post_meta; //array com metadados da requisição
	public $post_files; //array com (possíveis vários) arquivos

	/**
	 * Persistence constructor
	 * @param {Array} $reqs ($_POST informations)
	 */
	public function __construct($reqs)
	{

		if (count($_POST)) {

			$timestamp = $_SERVER['REQUEST_TIME'];
			$ip = $_SERVER['REMOTE_ADDR'];
			$useragent = $_SERVER['HTTP_USER_AGENT'];

			$this->timestamp = $timestamp;
			$this->ip = $ip;
			$this->useragent = $useragent;
			/*
						$this->origin = $reqs['file']['name'];
						$this->temp_path = $reqs['file']['tmp_name'];
						$this->handleFile($this->origin, $this->temp_path);
			*/		} else {
			$this->returnFile($reqs['name']);
		}

	}

	/**
	 * Handle $_POST info to get meta info
	 * @param {Array} $reqs ($_POST informations)
	 */
	public function handlePost($reqs) {

		$metadados = array(); /* == $this->$post_meta */
		$i = 0;

		foreach ($reqs as $name => $value)
		{
			if ($name != 'file['. $i .']') { //or 'file' only, if not array

				$meta = new Meta($name,$value);
				$metadados[] = $meta->toJSON();
				$i++;

			} else {
				//here come file handle
			}
		}
		//if there is post_meta
		$this->post_meta = $metadados;
		//if there is post_files
		//$this->post_file = $files;

	}

}

Class Meta {
	public $meta_name;
	public $meta_value;

	/**
	 * @param {String} $name (field name)
	 * @param {String} $value (field value)
	 */
	public function __construct($name, $value)
	{
		$this->meta_name = $name;
		$this->meta_value = $value;
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
	 * @param {Array} $reqs
	 */
	public function __construct($reqs)
	{

		if (count($reqs['file'])) {
			$this->origin = $reqs['file']['name'];
			$this->temp_path = $reqs['file']['tmp_name'];
			$this->handleFile($this->origin, $this->temp_path);
		} else {
			$this->returnFile($reqs['name']);
		}

	}

	/**
	 * @param {} $origin
	 * @param {} $temp_path
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
	 * Return JSON string with information about file
	 * Get file name through url GET method and search local uploaded files folder for it
	 * @param $name
	 */
	public function returnFile($name)
	{
		$dir = '/var/www/static/';
		$files = scandir($dir);
		$filepath = false;

		$i = 0;

		do {

			if(!is_dir($files[$i]) && $files[$i] != ".." && $files[$i] != ".") {

				$info = pathinfo($files[$i]);

				if ($info['filename'] == $name)
					$filepath = $info['basename'];
				else
					$i++;

			} else {
				$i++;
			}

		} while ($files[$i] != $filepath);

		if ($filepath) {
			$about = array('name' => $name, 'origin' => $filepath, 'path' => $dir . $filepath);

			echo json_encode($about);
		} else {
			echo json_encode(array());
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
	else if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$insert = new Persistence($_POST);

		//$file = new File($_FILES); //files retorna um array

		//Response

		$file->getFileInfo();
	} else {
		$file = new File($_GET);
	}
} catch (Exception $e) {
	echo "Erro: " . $e->getMessage();
}

//	if ($_POST["file"] != "undefined")
//		echo json_encode(array($_FILES, $_POST));