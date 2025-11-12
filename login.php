<?php
// login.php
session_start();
include 'db_connection.php';

// Sanitize input
$Username = isset($_POST['Username']) ? trim($_POST['Username']) : '';
$Password = isset($_POST['Password']) ? $_POST['Password'] : '';

if (empty($Username) || empty($Password)) {
    echo "<script>alert('Please enter username and password.'); window.location.href='login.html';</script>";
    exit();
}

$stmt = $conn->prepare("SELECT Account_ID, PWD_ID, PasswordHash FROM Account_Credentials WHERE Username = ?");
$stmt->bind_param("s", $Username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($Password, $row['PasswordHash'])) {
        // Success — set session and redirect
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $Username;
        $_SESSION['PWD_ID'] = $row['PWD_ID'];

        // Redirect to dashboard.html (client-side SPA)
        echo "<script>
            sessionStorage.setItem('justLoggedIn', 'true');
            window.location.href='dashboard.html';
        </script>";
        exit();
    }
}
echo "<script>alert('Invalid credentials'); window.location.href='login.html';</script>";
?>
