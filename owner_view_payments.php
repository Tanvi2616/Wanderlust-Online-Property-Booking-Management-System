<?php
include 'db_connect.php';
session_start();

// Ensure only owners can access
$host_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$host_id || $role !== 'owner') {
    echo "<script>alert('Access denied. Only owners can view payments.'); window.location.href='login.html';</script>";
    exit;
}

// ✅ Fetch payments for properties owned by this host
$sql = "
    SELECT 
        pay.id AS payment_id,
        pay.amount,
        pay.payment_method,
        pay.payment_date,
        b.customer_name,
        p.name AS property_name
    FROM payments pay
    JOIN bookings b ON pay.booking_id = b.id
    JOIN properties p ON b.property_id = p.id
    WHERE p.host_id = ?
    ORDER BY pay.payment_date DESC
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
  <title>Owner Payments - Wanderlust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">

  <style>
    body {
      background-color: #f8f9fa;
    }
    .table-container {
      max-width: 900px;
      margin: 60px auto;
      background: white;
      padding: 30px 40px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    th {
      background-color: #0d6efd;
      color: white;
    }
    .summary-box {
      background: #e9f2ff;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      text-align: center;
    }
  </style>
</head>
<body>

  <?php include 'navbar.html'; ?>

  <div class="container table-container">
    <h3 class="text-center text-primary mb-4"><i class="fa-solid fa-wallet"></i> Payments Summary</h3>

    <?php
    // ✅ Calculate total amount
    $total_sql = "
        SELECT SUM(pay.amount) AS total_earnings
        FROM payments pay
        JOIN bookings b ON pay.booking_id = b.id
        JOIN properties p ON b.property_id = p.id
        WHERE p.host_id = ?
    ";
    $stmt2 = $conn->prepare($total_sql);
    $stmt2->bind_param("i", $host_id);
    $stmt2->execute();
    $total_result = $stmt2->get_result()->fetch_assoc();
    $total_earnings = $total_result['total_earnings'] ?? 0;
    ?>

    <div class="summary-box">
      <h5>Total Earnings: <strong class="text-success">₹<?= number_format($total_earnings, 2) ?></strong></h5>
    </div>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
          <thead>
            <tr>
              <th>Payment ID</th>
              <th>Property</th>
              <th>Customer</th>
              <th>Amount (₹)</th>
              <th>Method</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['payment_id'] ?></td>
                <td><?= htmlspecialchars($row['property_name']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td class="text-end">₹<?= number_format($row['amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['payment_method']) ?></td>
                <td><?= htmlspecialchars($row['payment_date']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center text-muted mt-4">No payments received yet.</p>
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
