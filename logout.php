<?php
// logout.php
session_start();

// Clear PHP session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Also clear sessionStorage for frontend logic (via small script)
echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="2;url=index.html">
  <title>Logging Out...</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column justify-content-center align-items-center vh-100">
  <div class="text-center">
    <h3 class="mb-3 text-danger">You have been logged out!</h3>
    <div class="spinner-border text-primary" role="status"></div>
    <p class="mt-3 text-muted">Redirecting to Home Page...</p>
  </div>

  <script>
    // Also clear client-side sessionStorage (used by JS dashboard logic)
    sessionStorage.clear();
  </script>
</body>
</html>
';
?>
