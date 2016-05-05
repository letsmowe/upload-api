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
	public $name; // only the file name on server
	public $temp_path; // temp file path on temp folder
	public $path;
	public $size;
	public $origin; // name + extension on client machine
	public $extension;
	public $type;
	public $width;
	public $height;

	/**
	 * File constructor
	 * Receive a request as param from Persistence.handlePost($reqs)
	 * and verify if the request is a file array (or contain elements
	 * like so)
	 *
	 * @param {Array} $reqs (like $_POST or $FILES)
	 * @param {int} $numfile (number of the file)
	 */
	public function __construct($reqs, $numfile)
	{

		if(count($reqs['file'])) { // same as $_FILES['file']

			$this->handleFile($reqs, $numfile); // or $reqs['file'] as param, but ignored for clarity

		} else {
			$this->returnFile($reqs['name']);
			echo 'yep';
		}

	}

	/**
	 * @param {} $reqs
	 * @param {} $i
	 */
	public function handleFile ($reqs, $i)
	{

		$this->temp_path = $reqs['file']['tmp_name'][$i]; // temp filename on server
		$this->size = $reqs['file']['size'][$i]; // file size in bytes
		$this->origin = $reqs['file']['name'][$i]; // original name on client machine
		$this->type = $reqs['file']['type'][$i]; // mime type of the file (ex. "image/jpeg")


		$updir = '/var/www/static/';
		$info = pathinfo($this->origin);
		$upext = $info['extension'];
		$upname = random_int(0, 999999);

		$this->extension = $upext;

		try {

			if (move_uploaded_file($this->temp_path, $updir . $upname . "." . $upext)) {
				$this->name = $upname;
				$this->returnFile($this->name);
			}

		} catch (Exception $er_move) {
			echo $er_move->getMessage();
		}

		try {

			if (exif_imagetype($this->path)) {
				$dimension = getimagesize($this->path);
				$this->width = $dimension[0]; // getimagesize returns an array, 0 is width
				$this->height = $dimension[1]; // 1 is height
			} else {
				$this->width = $this->height = 0;
			}

		} catch (Exception $er_type) {
			echo $er_type->getMessage();
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
		$completename = false;

		$i = 0;

		do {

			if(!is_dir($files[$i]) && $files[$i] != ".." && $files[$i] != ".") {

				$info = pathinfo($files[$i]);

				if ($info['filename'] == $name)
					$completename = $info['basename'];
				else
					$i++;

			} else {
				$i++;
			}

		} while ($files[$i] != $completename);

		if ($completename) {
			$this->path = $dir . $completename;
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
			$this->handlePost($reqs);
		} else {
			//$this->returnFile($reqs['name']);
		}

	}

	/**
	 * Handle $_POST info to get meta info
	 * @param {Array} $reqs ($_POST informations)
	 */
	public function handlePost($reqs) {

		$this->timestamp = $_SERVER['REQUEST_TIME'];
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->useragent = $_SERVER['HTTP_USER_AGENT'];

		$metadados = array(); /* == $this->post_meta */
		$datafile = array(); /* == $this->post_files */

		foreach ($reqs as $name => $value)
		{
			$meta = new Meta($name,$value);
			$metadados[] = $meta;
		}

		$i = 0;
		while ($_FILES['file']['name'][$i] != null) {
			$file = new File($_FILES, $i); //['file']['name'][$i] (appends fields to an array, not to files)
			$datafile[] = $file;
			$i++;
		}

		//if there is post_meta
		$this->post_meta = $metadados;
		//if there is post_files
		$this->post_files = $datafile;

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
	if ($_GET['callback']) {

		echo $_GET['callback'] . '(' . json_encode($_GET) . ')'; //jsonp apenas requisições get

	} else if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$insert = new Persistence($_POST);
		echo $insert->toJSON();

	} else {

		$file = new File($_GET);

	}
} catch (Exception $e) {
	echo "Erro: " . $e->getMessage();
}

//	if ($_POST["file"] != "undefined")
//		echo json_encode(array($_FILES, $_POST));