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


Class Meta {
	public $meta_name;
	public $meta_value;

	/**
	 * Receive fields name and value about post "metadata" (form fields)
	 * @param string $name field name
	 * @param string $value field value
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
	 *
	 * Receive a request as param from Persistence.handlePost
	 * and verify if the request is a file array (or contain elements
	 * like so) and get information about the specific file ($numfile param)
	 *
	 * @param array $reqs $_FILES object array
	 * @param int $numfile number of the file
	 */
	public function __construct($reqs, $numfile)
	{

		if(count($reqs['file'])) { // same as $_FILES['file']

			$this->handleFile($reqs, $numfile); // or $reqs['file'] as param, but suppressed for later clarity

		} else {

			// function to return information

		}

	}

	/**
	 * Get params from constructor, handle file information and store it on folder
	 *
	 * @param array $reqs $_FILES object array
	 * @param int $i number of the file
	 */
	public function handleFile ($reqs, $i)
	{

		/** tmp_name, size, name and type are parsed from $_FILE object */
		$this->temp_path = $reqs['file']['tmp_name'][$i]; // temp filename on server
		$this->size = $reqs['file']['size'][$i]; // file size in bytes
		$this->origin = $reqs['file']['name'][$i]; // original name on client machine
		$this->type = $reqs['file']['type'][$i]; // mime type of the file (ex. "image/jpeg")

		$updir = '/var/www/static/';
		$info = pathinfo($this->origin);
		$upext = $info['extension']; /** extension is get through the temp uploaded file */
		$this->extension = $upext;
		$upname = $this->generateSafeString(11); /** create a safe string to name file on static folder */

		try {

			/*
			 * Try to move uploaded file from tmp folder to a new static folder,
			 * if success, set other infos as path of the uploaded file
			 * on the static folder and its new created name
			 */
			if (move_uploaded_file($this->temp_path, $updir . $upname . "." . $upext)) {
				$this->name = $upname;
				$this->path = $updir . $upname . "." . $upext;
			}

		} catch (Exception $er_move) {
			echo $er_move->getMessage();
		}

		try {

			/*
			 * With path information set, check if the file is an image, if it is,
			 * get infos as width and height
			*/
			if (exif_imagetype($this->path)) {
				$dimension = getimagesize($this->path);
				$this->width = $dimension[0]; // getimagesize returns an array, 0 is width
				$this->height = $dimension[1]; // 1 is height
			} else {
				$this->width = $this->height = 0; //if not image, set both 0
			}

		} catch (Exception $er_type) {
			echo $er_type->getMessage();
		}

	}

	/**
	 * create a safe string of said size and returns it
	 *
	 * Create an array of chars from other array of chars (defined),
	 *  and return implode the array ("glue" array elements like a string)
	 *
	 * @param int $sizeid size of string
	 * @return string random char-number string
	 */
	public function generateSafeString($sizeid)
	{

		$chars = "ABCDEFGHJKLMPQRSTUVWXYZ";
		$chars .= "abcdefghkmnpqrstuvwxyz";
		$chars .= "0123456789-_";

		$id = array();

		for ($i = 0; $i < $sizeid; $i++)
			$id[$i] = $chars[mt_rand(0 , strlen($chars) - 1)];

		return implode("",$id);

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
	public $post_meta; // array with request metadata
	public $post_files; // array with (may be more than one) file

	/**
	 * Persistence constructor
	 *
	 * Called to manipulate and store infos and files upload from form
	 *
	 * @param array $reqs $_POST info
	 */
	public function __construct($reqs)
	{

		if (count($_POST)) {

			$this->handlePost($reqs);

		} else {

			// function to return information

		}

	}

	/**
	 * set info about post metadata and file data
	 *
	 * Handle $_POST info to get metadata (form fields values)
	 *  and $_FILES info to get information about the file(s)
	 *
	 * @param array $reqs $_POST informations
	 */
	public function handlePost($reqs)
	{

		/*
		 * get info about request (if any);
		 * timestamp, ip and useragent are parsed from header request
		*/
		$this->timestamp = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
		$this->ip = $_SERVER['REMOTE_ADDR'];
		$this->useragent = $_SERVER['HTTP_USER_AGENT'];

		/* Create an array for metadata fields and files data */
		$metadados = array();
		$datafile = array();

		/* Populate metadados array with info about form fields (VERTICAL) */
		foreach ($reqs as $name => $value)
		{

			$meta = new Meta($name,$value);
			$metadados[] = $meta;

		}

		/* Populate datafile array with info about files */
		$i = 0;
		while ($_FILES['file']['name'][$i] != null) {

			$file = new File($_FILES, $i); //['file']['name'][$i] (appends fields to an array, not to [file])
			$datafile[] = $file;
			$i++;

		}

		$this->post_meta = $metadados;
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

		echo $_GET['callback'] . '(' . json_encode($_GET) . ')'; //jsonp only get requests

	} else if ($_SERVER["REQUEST_METHOD"] == "POST") {

		$insert = new Persistence($_POST);
		echo $insert->toJSON();

	} else {

		// to return information

	}

} catch (Exception $e) {
	echo "Erro: " . $e->getMessage();
}