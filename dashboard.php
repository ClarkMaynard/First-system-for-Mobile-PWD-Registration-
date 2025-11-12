<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit;
}
include 'db_connection.php';

$PWD_ID = $_SESSION['PWD_ID'];
$stmt = $conn->prepare("
    SELECT FullName, DateOfBirth, Gender, DisabilityType, QR_Path, ID_Image 
    FROM PWD_User WHERE PWD_ID = ?
");
$stmt->bind_param("i", $PWD_ID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>PWD Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://unpkg.com/html5-qrcode"></script>
  <style>
    .main input[type="file"] { display:none; }
    #dropZone {
      border:2px dashed #333; 
      padding:28px; 
      text-align:center; 
      cursor:pointer; 
      margin-bottom:16px;
      background:#f9f9f9;
      transition:.2s;
    }
    #dropZone.dragover {
      background: #e0dffd;
      border-color: #552be1;
    }
    #scanner { width:320px;height:240px; border:1px solid #333; margin-top:18px; }
    #scanResult { margin:15px 0;}
  </style>
</head>
<body>
<div class="layout">
  <div class="sidebar">
    <h2>PWD Portal</h2>
    <a href="dashboard.php">Home</a>
    <a href="about.html">About</a>
    <a href="verify.html">Verify</a>
    <a href="deals.html">Deals</a>
    <a href="settings.html">Settings</a>
    <hr>
    <a href="logout.php" style="color:#ffcccc;">Logout</a>
  </div>
  <div class="main">
    <h1>Welcome, <?php echo htmlspecialchars($user['FullName']); ?>!</h1>
    <div class="card">
      <p><b>Date of Birth:</b> <?php echo htmlspecialchars($user['DateOfBirth']); ?></p>
      <p><b>Gender:</b> <?php echo htmlspecialchars($user['Gender']); ?></p>
      <p><b>Disability Type:</b> <?php echo htmlspecialchars($user['DisabilityType']); ?></p>
      <?php if ($user['QR_Path']): ?>
        <p><b>Your Verification QR:</b><br>
          <img src="<?php echo htmlspecialchars($user['QR_Path']); ?>" width="150">
        </p>
      <?php endif; ?>
      <?php if (!empty($user['ID_Image'])): ?>
        <p><b>Your ID:</b><br>
          <img src="<?php echo htmlspecialchars($user['ID_Image']); ?>" style="max-width:150px;">
        </p>
      <?php endif; ?>
    </div>
    <hr>

    <h2>Upload/Update ID Image</h2>
    <form id="uploadForm" action="upload_id.php" method="POST" enctype="multipart/form-data">
      <div id="dropZone">
        <p>
          Drag &amp; drop your ID image here,<br>
          or <span style="font-weight:bold;color:#144aec;cursor:pointer;text-decoration:underline;" id="filePickerText">browse</span> for a file.<br>
        </p>
        <input type="file" name="id_image" id="id_image" accept="image/*" />
      </div>
      <button type="submit" class="btn">Upload</button>
    </form>

    <hr>
    <h2>Scan PWD QR Code</h2>
    <div id="scanner"></div>
    <p id="scanResult"></p>
    <button onclick="window.location.href='verify.html'" class="btn">Manual Verification Page</button>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // D&D for upload
  const dropZone = document.getElementById('dropZone');
  const fileInput = document.getElementById('id_image');
  const filePickerTxt = document.getElementById('filePickerText');
  dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
  dropZone.addEventListener('dragleave', e => { e.preventDefault(); dropZone.classList.remove('dragover'); });
  dropZone.addEventListener('drop', e => {
    e.preventDefault(); dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length > 0) { fileInput.files = e.dataTransfer.files; }
  });
  filePickerTxt.addEventListener('click', () => fileInput.click());
  dropZone.addEventListener('click', (e) => {
    if (e.target !== fileInput && e.target !== filePickerTxt) fileInput.click();
  });

  // QR Scanner
  if (window.Html5Qrcode) {
    const resultElem = document.getElementById('scanResult');
    const qr = new Html5Qrcode("scanner");
    Html5Qrcode.getCameras().then(devices => {
      if (devices && devices.length) {
        qr.start(
          devices[0].id,
          { fps: 10, qrbox: 180 },
          qrCodeMessage => {
            resultElem.innerHTML =
              `<b>QR Token:</b> ${qrCodeMessage}<br>
                <a href="verify.html?token=${encodeURIComponent(qrCodeMessage)}" class="btn">Verify This Token</a>`;
            qr.stop();
          }
        );
      }
    }).catch(err => {
      resultElem.textContent = "No camera found or unable to start QR scan.";
    });
  }
});

if (sessionStorage.getItem('justLoggedIn') === 'true') {
  alert('Welcome! Your profile has been successfully loaded.');
  sessionStorage.removeItem('justLoggedIn');
}
</script>
</body>
</html>
