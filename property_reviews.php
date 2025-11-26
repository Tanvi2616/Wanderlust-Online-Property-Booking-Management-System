<?php
include 'db_connect.php';
session_start();

// Get property ID from URL
$property_id = $_GET['id'] ?? null;

if (!$property_id) {
    echo "<script>alert('Invalid property selected.'); window.location.href='index.html';</script>";
    exit;
}

// Fetch property details
$sql = "SELECT name, location, price, image FROM properties WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

// Fetch all reviews for this property
$sql_reviews = "SELECT customer_name, rating, comment, review_date FROM reviews WHERE property_id = ? ORDER BY review_date DESC";
$stmt = $conn->prepare($sql_reviews);
$stmt->bind_param("i", $property_id);
$stmt->execute();
$reviews = $stmt->get_result();

// Check user role
$isCustomer = ($_SESSION['role'] ?? '') === 'customer';
$customer_name = $_SESSION['user_name'] ?? '';
$canReview = false;

// ✅ Check if this logged-in customer has booked this property before (and their stay is completed)
if ($isCustomer) {
    $sql_check = "SELECT * FROM bookings WHERE property_id = ? AND customer_name = ? AND check_out < CURDATE()";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("is", $property_id, $customer_name);
    $stmt->execute();
    $canReview = $stmt->get_result()->num_rows > 0;
}

// ✅ Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canReview) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $insert = "INSERT INTO reviews (property_id, customer_name, rating, comment, review_date)
                   VALUES (?, ?, ?, ?, CURDATE())";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("isis", $property_id, $customer_name, $rating, $comment);
        $stmt->execute();

        echo "<script>alert('✅ Review submitted successfully!'); window.location.href='property_reviews.php?id=$property_id';</script>";
        exit;
    } else {
        echo "<script>alert('Please provide a valid rating and comment.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($property['name']) ?> - Reviews | Wanderlust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/a2e0e6b5b1.js" crossorigin="anonymous"></script>
  <style>
    body {
      background-color: #f8f9fa;
    }
    .property-header {
      text-align: center;
      margin: 40px 0 20px;
    }
    .property-header img {
      width: 100%;
      max-width: 800px;
      height: 400px;
      object-fit: cover;
      border-radius: 10px;
    }
    .review-container {
      max-width: 900px;
      margin: 40px auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 30px 40px;
    }
    .review-item {
      border-bottom: 1px solid #eee;
      padding: 15px 0;
    }
    .review-item:last-child {
      border-bottom: none;
    }
    .star {
      color: #ffc107;
    }
  </style>
</head>
<body>

  <div id="navbar"></div>

  <div class="container">
    <!-- Property Info -->
    <div class="property-header">
      <img src="<?= htmlspecialchars($property['image']) ?>" alt="<?= htmlspecialchars($property['name']) ?>">
      <h2 class="mt-3 text-primary"><?= htmlspecialchars($property['name']) ?></h2>
      <p class="text-muted"><?= htmlspecialchars($property['location']) ?> • ₹<?= number_format($property['price'], 2) ?></p>
    </div>

    <!-- Reviews -->
    <div class="review-container">
      <h4 class="text-primary mb-4">Customer Reviews</h4>

      <?php if ($reviews->num_rows > 0): ?>
        <?php while($rev = $reviews->fetch_assoc()): ?>
          <div class="review-item">
            <strong><?= htmlspecialchars($rev['customer_name']) ?></strong>
            <div class="text-warning mb-1">
              <?= str_repeat('★', $rev['rating']) ?><span class="text-secondary"><?= str_repeat('☆', 5 - $rev['rating']) ?></span>
            </div>
            <p><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
            <small class="text-muted">Reviewed on <?= htmlspecialchars($rev['review_date']) ?></small>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted text-center">No reviews yet. Be the first to review this property!</p>
      <?php endif; ?>

      <!-- Review Form -->
      <?php if ($canReview): ?>
        <hr>
        <h5 class="text-primary mt-4">Leave a Review</h5>
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Rating</label>
            <select name="rating" class="form-select" required>
              <option value="">Select rating</option>
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>"><?= $i ?> ★</option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Your Comment</label>
            <textarea name="comment" class="form-control" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn btn-warning text-dark">Submit Review</button>
        </form>
      <?php elseif ($isCustomer): ?>
        <p class="text-muted mt-3">You can leave a review after your stay ends.</p>
      <?php else: ?>
        <p class="text-muted mt-3">Only logged-in customers can leave reviews.</p>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/loadComponents.js"></script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
