<?php

// Require connection to bd
require("db_connection.php");

// Create an sql statement with table (fields of the table) and values of the specified fields
$sql = "INSERT INTO fields (author, age, gender) VALUES ('carla romena astolfa',22,'f')";

if ($conn->query($sql) === TRUE) { // If connection is stabilished
	$last_id = $conn->insert_id; // Set last id from insertions (the last one is from the sql statement)
	echo "Informação inserida com sucesso. Last ID:" . $last_id;
} else {
	echo "Erro: " . $sql . "\n" . $conn->error;
}

$conn->close(); // Close db connection

/*
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