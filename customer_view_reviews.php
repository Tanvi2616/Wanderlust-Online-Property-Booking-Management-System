<?php
include 'db_connect.php';
session_start();

// Ensure only customers can access
$role = $_SESSION['role'] ?? null;
if ($role !== 'customer') {
    echo "<script>alert('Access denied. Only customers can view their reviews.'); window.location.href='login.html';</script>";
    exit;
}

$customer_name = $_SESSION['user_name'] ?? null;
if (!$customer_name) {
    echo "<script>alert('Please login again.'); window.location.href='login.html';</script>";
    exit;
}

// Fetch all reviews written by this customer
$sql = "
    SELECT 
        r.id AS review_id,
        r.rating,
        r.comment,
        r.review_date,
        p.name AS property_name,
        p.location
    FROM reviews r
    JOIN properties p ON r.property_id = p.id
    WHERE r.customer_name = ?
    ORDER BY r.review_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $customer_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Reviews - Wanderlust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .review-container {
      max-width: 900px;
      margin: 60px auto;
      background: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .star {
      color: #ffc107;
      font-size: 1.1rem;
    }
    .review-card {
      border-bottom: 1px solid #e0e0e0;
      padding: 15px 0;
    }
  </style> 
</head>
<body class="bg-light">

  <?php include 'navbar.html'; ?>

  <div class="review-container">
    <h3 class="text-center text-primary mb-4">Your Submitted Reviews</h3>

    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="review-card">
          <h5 class="mb-1"><?= htmlspecialchars($row['property_name']) ?> 
            <small class="text-muted">(<?= htmlspecialchars($row['location']) ?>)</small>
          </h5>
          <div class="text-warning mb-2">
            <?php 
              $rating = intval($row['rating']);
              echo str_repeat('<i class="fa-solid fa-star star"></i>', $rating);
              echo str_repeat('<i class="fa-regular fa-star text-secondary"></i>', 5 - $rating);
            ?>
          </div>
          <p><?= nl2br(htmlspecialchars($row['comment'])) ?></p>
          <small class="text-muted">Reviewed on <?= $row['review_date'] ?></small>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center text-muted">You haven’t written any reviews yet.</p>
    <?php endif; ?>
  </div>

  <?php include 'footer.html'; ?>

  <script src="https://kit.fontawesome.com/a2e0e6b5b1.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
