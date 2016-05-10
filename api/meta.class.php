<?php
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