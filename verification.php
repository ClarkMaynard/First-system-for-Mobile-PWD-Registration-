<?php
// verify.php
// Accepts a verification token via GET (e.g. verify.php?token=abc...)
// In practice merchant QR scanner should POST the token or call this endpoint in a secure way.

include 'db_connection.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    echo "<p>No token provided. Scan a QR or pass ?token=...</p>";
    exit;
}

// Lookup account by token
$stmt = $conn->prepare(
    "SELECT ac.PWD_ID, pu.FullName, pu.DateOfBirth, pu.Gender, pu.DisabilityType, pu.ID_Image, pu.QR_Path
     FROM Account_Credentials ac
     JOIN PWD_User pu ON ac.PWD_ID = pu.PWD_ID
     WHERE ac.verification_token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo "<h3 style='color:red;'>Invalid or expired token.</h3>";
    exit;
}
$row = $res->fetch_assoc();

echo "<h2>Verification Result</h2>";
echo "<p><strong>Name:</strong> " . htmlspecialchars($row['FullName']) . "</p>";
echo "<p><strong>DOB:</strong> " . htmlspecialchars($row['DateOfBirth']) . "</p>";
echo "<p><strong>Gender:</strong> " . htmlspecialchars($row['Gender']) . "</p>";
echo "<p><strong>Disability:</strong> " . htmlspecialchars($row['DisabilityType']) . "</p>";
if (!empty($row['ID_Image'])) echo "<p><img src='" . htmlspecialchars($row['ID_Image']) . "' style='max-width:200px;'></p>";

$stmt->close();
$conn->close();
?>
