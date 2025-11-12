<?php
// recent_logs.php
// Display recent system/user logs for dashboard sidebar

session_start();
include 'db_connection.php';

// Fetch latest 10 logs (customize columns as needed)
$result = $conn->query("SELECT log_message, logged_at FROM system_logs ORDER BY logged_at DESC LIMIT 10");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recent Logs</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .log-list { margin: 0; padding: 0; list-style: none; font-size: 15px; }
    .log-list li { padding: 8px 6px; border-bottom: 1px solid #ddd; }
    .log-list time { font-size: 12px; color: #888; margin-right: 10px; }
    .log-list li:last-child { border-bottom: none; }
  </style>
</head>
<body>
  <main class="container" style="width:310px;">
    <h2>Recent Logs</h2>
    <ul class="log-list">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <li>
            <time><?php echo htmlspecialchars($row['logged_at']); ?></time>
            <?php echo htmlspecialchars($row['log_message']); ?>
          </li>
        <?php endwhile; ?>
      <?php else: ?>
        <li>No recent logs found.</li>
      <?php endif; ?>
    </ul>
    <p><a href="dashboard.html" class="btn">Back to Dashboard</a></p>
  </main>
</body>
</html>
<?php
$conn->close();
?>
