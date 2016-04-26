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

	public $sent;

	protected $mailer;

	/**
	 * Response constructor.
	 * @param $post {array}
	 * @param $mailer {PHPMailer}
	 * @internal param bool $sent
	 */
	public function __construct($post)
	{
		$this->post = $post;
	}

	/**
	 * @param mixed $sent
	 */
	public function setSent($sent)
	{
		$this->sent = $sent;
	}

	/**
	 * @return string
	 */
	public function toJSON()
	{
		return json_encode($this);
	}

}

$response = new Response($_POST);

echo json_encode($_POST);