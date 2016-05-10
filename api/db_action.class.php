<?php
require ('db_connection.php');

Class Bd_action {

	function insertPost ($insert)
	{

		$conn = connect(); // function from bd_connection returns $conn
		$timestamp = $insert->timestamp;
		$ip = $insert->ip;
		$useragent = $insert->useragent;
		$meta_info = $insert->post_meta;
		$files_info = $insert->post_files;

		$sql = "INSERT INTO posts (timestamp, ip, useragent) VALUES ('$timestamp','$ip','$useragent')";

		if ($conn->query($sql) === TRUE) {
			$post_id = $conn->insert_id; // get the insert id from $conn (id post)
			
			$sql = $this->prepareMeta($meta_info, $post_id);
			$sql .= $this->prepareFile($files_info, $post_id);

			if ($conn->multi_query($sql) !== TRUE) {
				//git gud
				echo "Erro: " . $conn->error;
			}
			
		} else {
			echo "Erro: " . $conn->error;
		}
		
		$conn->close();
	
	}

	function prepareMeta ($meta_info, $post_id)
	{

		$sql = "";

		foreach ($meta_info as $key => $meta) {
			$insert = "INSERT INTO meta (posts_id, name, value) VALUES ($post_id";
			$values = "";

			foreach ($meta as $name => $value) {
				$values .= ",'$value'";
			}

			$final = ");";

			$sql .= $insert . $values . $final;
		}

		return $sql;

	}

	function prepareFile ($files_info, $post_id) {

		$sql = "";

		foreach ($files_info as $key => $file) {

			$insert = "INSERT INTO files (posts_id, name, temp_path, path, size, origin, extension, type, width, height) VALUES ($post_id";

			$values = "";
			foreach ($file as $attr => $value) {
				($attr == "size" || $attr == "width" || $attr == "height") ? $values .= ", $value" : $values .= ", '$value'";
			}

			$final = ");";

			$sql .= $insert . $values . $final;

		}

		return $sql;
	}
}