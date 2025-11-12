<?php
$servername = "localhost";
$username = "root";
$password = ""; // default is blank in XAMPP
$database = "pwd_verification_system"; // make sure this DB exists

$conn = new mysqli("127.0.0.1", "root", "", "pwd_verification_system");


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn->begin_transaction();

    // --- Sanitize input (basic) ---
    $FullName       = isset($_POST['FullName']) ? trim($_POST['FullName']) : '';
    $DateOfBirth    = isset($_POST['DateOfBirth']) ? trim($_POST['DateOfBirth']) : '';
    $Gender         = isset($_POST['Gender']) ? trim($_POST['Gender']) : '';
    $DisabilityType = isset($_POST['DisabilityType']) ? trim($_POST['DisabilityType']) : '';
    $Username       = isset($_POST['Username']) ? trim($_POST['Username']) : '';
    $Password       = isset($_POST['Password']) ? $_POST['Password'] : '';

    // Required fields check
    if (empty($FullName) || empty($DateOfBirth) || empty($Gender) || empty($DisabilityType) || empty($Username) || empty($Password)) {
        throw new Exception("All required fields must be filled.");
    }

    // Optionally validate Date format (basic)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $DateOfBirth)) {
        throw new Exception("Invalid date format. Use YYYY-MM-DD.");
    }

    // Check username uniqueness (unique constraint in DB should also exist)
    $checkUser = $conn->prepare("SELECT 1 FROM Account_Credentials WHERE Username = ?");
    $checkUser->bind_param("s", $Username);
    $checkUser->execute();
    $checkUser->store_result();
    if ($checkUser->num_rows > 0) {
        throw new Exception("Username already exists. Choose another username.");
    }
    $checkUser->close();

    // Hash password
    $PasswordHash = password_hash($Password, PASSWORD_BCRYPT);

    // Insert into PWD_User
    $stmt1 = $conn->prepare("INSERT INTO PWD_User (FullName, DateOfBirth, Gender, DisabilityType) VALUES (?, ?, ?, ?)");
    $stmt1->bind_param("ssss", $FullName, $DateOfBirth, $Gender, $DisabilityType);
    $stmt1->execute();
    $stmt1->close();

    // safe insert_id
    $PWD_ID = $conn->insert_id;

    // Generate secure verification token (store in Account_Credentials)
    $verification_token = bin2hex(random_bytes(24)); // 48 hex chars

    // Insert credentials (link to PWD_ID)
    $stmt2 = $conn->prepare("INSERT INTO Account_Credentials (PWD_ID, Username, PasswordHash, verification_token) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("isss", $PWD_ID, $Username, $PasswordHash, $verification_token);
    $stmt2->execute();
    $stmt2->close();

    // Optional: handle ID image upload (validate file)
    $idImagePath = null;
    $uploadOk = true;
    $maxFileSize = 2 * 1024 * 1024; // 2 MB max

    if (isset($_FILES['IDImage']) && $_FILES['IDImage']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['IDImage']['tmp_name'];
        // validate image
        $imgInfo = @getimagesize($tmpName);
        if ($imgInfo === false) {
            $uploadOk = false;
            error_log("Upload failed: file is not a valid image for PWD_ID=$PWD_ID");
        } elseif ($_FILES['IDImage']['size'] > $maxFileSize) {
            $uploadOk = false;
            error_log("Upload failed: file too large for PWD_ID=$PWD_ID");
        } else {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                $uploadOk = false;
                error_log("Upload failed: cannot create upload dir");
            } else {
                $origName = basename($_FILES['IDImage']['name']);
                $safeName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $origName);
                $targetName = $PWD_ID . '_id_' . time() . '_' . $safeName;
                $targetPath = $uploadDir . $targetName;
                if (!move_uploaded_file($tmpName, $targetPath)) {
                    $uploadOk = false;
                    error_log("Upload failed: move_uploaded_file failed for PWD_ID=$PWD_ID");
                } else {
                    $idImagePath = 'uploads/' . $targetName;
                }
            }
        }
    }

    // Generate QR image and update DB (do this BEFORE committing)
    try {
        $qrDir = __DIR__ . '/qrcodes/';
        if (!is_dir($qrDir) && !mkdir($qrDir, 0755, true)) {
            throw new Exception("Unable to create qrcodes directory.");
        }

        $qrFileName = $PWD_ID . '_verify.png';
        $qrFilePath = $qrDir . $qrFileName;
        $qrContent = $verification_token;

        // Save relative QR path in DB
        $qrDBPath = 'qrcodes/' . $qrFileName;
        if ($idImagePath) {
            $stmtQr = $conn->prepare("UPDATE PWD_User SET QR_Path = ?, ID_Image = ? WHERE PWD_ID = ?");
            $stmtQr->bind_param("ssi", $qrDBPath, $idImagePath, $PWD_ID);
        } else {
            $stmtQr = $conn->prepare("UPDATE PWD_User SET QR_Path = ? WHERE PWD_ID = ?");
            $stmtQr->bind_param("si", $qrDBPath, $PWD_ID);
        }
        $stmtQr->execute();
        $stmtQr->close();

        // Commit the transaction
        $conn->commit();

        // Display success message
        echo "<h2>✅ PWD Registration Successful!</h2>";
        echo "<p><strong>Full Name:</strong> " . htmlspecialchars($FullName, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><strong>Username:</strong> " . htmlspecialchars($Username, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><strong>Verification QR:</strong><br><img src='" . htmlspecialchars($qrDBPath, ENT_QUOTES, 'UTF-8') . "' style='max-width:200px;'></p>";
        echo "<p><a href='login.html'>Proceed to Login</a></p>";

    } catch (Exception $e) {
        // Rollback on any error to avoid partial inserts
        if ($conn && $conn->connect_errno === 0) {
            $conn->rollback();
        }
        echo "<h3 style='color:red;'>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</h3>";
        echo "<p><a href='index.html'>Back to registration</a></p>";
    } // end catch
    // EVERYTHING in first try should be inside here!
 // end first try
catch (Exception $e) {
    // General error catch in registration process
    if ($conn && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    echo "<h3 style='color:red;'>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</h3>";
    echo "<p><a href='index.html'>Back to registration</a></p>";
}
finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>
