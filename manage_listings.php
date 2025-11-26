<?php
include 'db_connect.php';
session_start();

// Get owner ID and name from session
$host_id = $_SESSION['user_id'] ?? null;
$host_name = $_SESSION['user_name'] ?? 'Unknown Owner';
$role = $_SESSION['role'] ?? null;

// Ensure only owners can access
if (!$host_id || $role !== 'owner') {
    echo "<script>alert('Access denied. Only owners can manage properties.'); window.location.href='login.html';</script>";
    exit;
}

// Fetch properties for this owner
$sql = "SELECT * FROM properties WHERE host_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Listings - Wanderlust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Smaller cards for 3 per row */
    .property-card {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .property-card img {
        height: 180px;
        object-fit: cover;
    }
    .card-buttons a {
        flex: 1;
        margin: 0 2px;
    }
  </style>
</head>
<body class="bg-light">

  <!-- Navbar -->
  <div id="navbar"></div>

  <div class="container mt-5">
    <h2 class="text-center text-primary mb-4">Manage Your Properties</h2>

    <?php if ($result->num_rows > 0): ?>
      <div class="row g-3">
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="col-md-4"> <!-- 3 cards per row -->
            <div class="card shadow-sm bg-white rounded-3 property-card">
              <img src="<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                <p><strong>Type:</strong> <?= htmlspecialchars($row['property_type']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
                <p><strong>Price:</strong> ₹<?= number_format($row['price'], 2) ?></p>

                <div class="d-flex card-buttons mt-3">
                  <a href="edit_property.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                  <a href="delete_property.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this property?')">Delete</a>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-muted">You have not added any properties yet.</p>
    <?php endif; ?>

  </div>

  <div id="footer" class="mt-5"></div>

  <script src="js/loadComponents.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
