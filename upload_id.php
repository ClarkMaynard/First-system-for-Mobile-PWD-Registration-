<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['PWD_ID'])) {
    header("Location: login.html");
    exit();
}

$targetDir = "uploads/";
if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

$PWD_ID = $_SESSION['PWD_ID'];
$fileName = $PWD_ID . "_id_" . basename($_FILES["id_image"]["name"]);
$targetFile = $targetDir . $fileName;

if (move_uploaded_file($_FILES["id_image"]["tmp_name"], $targetFile)) {
    echo "<script>alert('ID uploaded successfully!'); window.location.href='dashboard.php';</script>";
} else {
    echo "<script>alert('File upload failed. Try again.'); window.location.href='dashboard.php';</script>";
}
?>
