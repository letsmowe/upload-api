<?php
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