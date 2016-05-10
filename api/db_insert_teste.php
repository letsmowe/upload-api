<?php

echo "teste2";
// Require connection to bd
require("db_connection.php");
$conn = connect();
// Create an sql statement with table (fields of the table) and values of the specified fields
//$sql = "INSERT INTO fields (author, age, gender) VALUES ('carla romena astolfa',22,'f')";
$sql = "INSERT INTO files VALUES ('', 55, 'HBfcwLQbm2G', '/tmp/phpC0Nlrv', '/var/www/static/HBfcwLQbm2G.jpg', 45435, '12189033_704307966366020_7681960844109177428_n.jpg', 'jpg', 'image/jpeg', 540, 720)";
if ($conn->query($sql) === TRUE) { // If connection is stabilished
	$last_id = $conn->insert_id; // Set last id from insertions (the last one is from the sql statement)
	echo "Informação inserida com sucesso. Last ID:" . $last_id;
} else {
	echo "Erro: " . $sql . "\n" . $conn->error;
}



$conn->close(); // Close db connection
/*
INSERT INTO files (posts_id, name, temp_path, path, size, origin, extension, type, width, height) VALUES (31, 'EfL5ptU-xf_', '/tmp/php8wV8Ti', '/var/www/static/EfL5ptU-xf_.jpg', 45435, '12189033_704307966366020_7681960844109177428_n.jpg', 'jpg', 'image/jpeg', 540, 720);

 * Insert multiple statements in one query
 * 
$sql = "INSERT INTO fields (author, age, gender) VALUES ('carla maria oioi',31,'f');";
$sql += "INSERT INTO fields (author, age, gender) VALUES ('roberto daglia moreira',51,'m');";
$sql += "INSERT INTO fields (author, age, gender) VALUES ('corsival manidas dargo',22,'m');";

if ($conn->multi_query($sql) === TRUE) {
	echo "Todos os dados inseridos com sucesso\n";
} else {
	echo "Erro: " . $sql . "<br>" . $conn->error;
}

// http://www.w3schools.com/PHP/php_mysql_prepared_statements.asp
*/