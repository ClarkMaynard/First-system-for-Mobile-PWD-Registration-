<?php
header('Content-Type: application/json');
include 'db_connection.php';
$result = $conn->query("SELECT * FROM PWD_User");
$rows = [];
while ($r = $result->fetch_assoc()) $rows[] = $r;
echo json_encode($rows);
?>
