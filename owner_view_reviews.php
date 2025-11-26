<?php
include 'db_connect.php';
session_start();

// ✅ Ensure only owners can access
$host_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$host_id || $role !== 'owner') {
    echo "<script>alert('Access denied. Only owners can view property reviews.'); window.location.href='login.html';</script>";
    exit;
}

// ✅ Fetch all reviews for properties owned by this owner
$sql = "
    SELECT 
        r.id AS review_id,
        r.customer_name,
        r.rating,
        r.comment,
        r.review_date,
        p.name AS property_name,
        p.image AS property_image,
        p.location
    FROM reviews r
    JOIN properties p ON r.property_id = p.id
    WHERE p.host_id = ?
    ORDER BY r.review_date DESC
";

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
  <title>Customer Reviews - Wanderlust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a2e0e6b5b1.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="css/style.css">

  <style>
    body {
      background-color: #f8f9fa;
    }
    .review-card {
      border: 1px solid #ddd;
      border-radius: 12px;
      padding: 20px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      transition: transform 0.2s;
    }
    .review-card:hover {
      transform: scale(1.02);
    }
    .property-image {
      width: 100%;
      height: 200px;
      border-radius: 10px;
      object-fit: cover;
    }
    .stars i {
      color: #ffc107;
    }
    .review-date {
      font-size: 0.9rem;
      color: #6c757d;
    }
    .customer-name {
      font-weight: 600;
      color: #0d6efd;
    }
  </style>
</head>
<body>

  <?php include 'navbar.html'; ?>

  <div class="container py-5">
    <h2 class="text-center text-primary mb-4">Customer Reviews for Your Properties</h2>

    <?php if ($result->num_rows > 0): ?>
      <div class="row g-4">
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="review-card">
              <img src="<?= htmlspecialchars($row['property_image']) ?>" class="property-image mb-3" alt="<?= htmlspecialchars($row['property_name']) ?>">
              <h5 class="text-primary"><?= htmlspecialchars($row['property_name']) ?></h5>
              <p class="text-muted mb-1"><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
              <p class="customer-name">By: <?= htmlspecialchars($row['customer_name']) ?></p>
              <div class="stars mb-2">
                <?= str_repeat('<i class="fa-solid fa-star"></i>', $row['rating']) ?>
                <?= str_repeat('<i class="fa-regular fa-star"></i>', 5 - $row['rating']) ?>
              </div>
              <p><?= nl2br(htmlspecialchars($row['comment'])) ?></p>
              <p class="review-date">Reviewed on <?= htmlspecialchars($row['review_date']) ?></p>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-muted">No customer reviews yet for your properties.</p>
    <?php endif; ?>
  </div>

  <div id="footer"></div>

  <script src="js/loadComponents.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
