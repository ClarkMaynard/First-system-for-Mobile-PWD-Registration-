<?php
include 'db_connection.php';

$FullName = $_POST['FullName'];
$DateOfBirth = $_POST['DateOfBirth'];
$Gender = $_POST['Gender'];
$DisabilityType = $_POST['DisabilityType'];
$Username = $_POST['Username'];
$Password = password_hash($_POST['Password'], PASSWORD_BCRYPT);

$conn->query("INSERT INTO PWD_User (FullName, DateOfBirth, Gender, DisabilityType)
              VALUES ('$FullName', '$DateOfBirth', '$Gender', '$DisabilityType')");

$PWD_ID = $conn->insert_id;

$conn->query("INSERT INTO Account_Credentials (PWD_ID, Username, PasswordHash)
              VALUES ('$PWD_ID', '$Username', '$Password')");

echo "<script>alert('PWD Account Registered Successfully'); window.location='index.html';</script>";

$conn->close();
?>
