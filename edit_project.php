<?php
include 'db_connect.php';
$query = "SELECT * FROM project_list WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $_GET['id']);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

foreach ($row as $k => $v) {
	$$k = $v;
}

include 'new_project.php';

?>
