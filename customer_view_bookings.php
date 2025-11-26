<?php
include 'db_connect.php';
session_start();


$today = date('Y-m-d');
$updateStatus = "UPDATE bookings 
                 SET status = 'Completed' 
                 WHERE check_out < ? AND status = 'Active'";
$stmtUpdate = $conn->prepare($updateStatus);
$stmtUpdate->bind_param("s", $today);
$stmtUpdate->execute();

// ✅ Ensure the user is logged in and is a customer
$role = $_SESSION['role'] ?? null;
if ($role !== 'customer') {
    echo "<script>alert('Access denied. Only customers can view bookings.'); window.location.href='login.html';</script>";
    exit;
}

$customer_name = $_SESSION['user_name'] ?? null;
if (!$customer_name) {
    echo "<script>alert('Please login again.'); window.location.href='login.html';</script>";
    exit;
}

// ✅ Fetch customer's bookings + payment details
$sql = "
    SELECT 
        b.id AS booking_id,
        b.customer_name,
        b.check_in,
        b.check_out,
        b.booking_date,
        b.status,
        p.name AS property_name,
        p.location,
        p.price,
        p.host_name,
        pay.payment_method,
        pay.payment_status,
        pay.amount
    FROM bookings b
    JOIN properties p ON b.property_id = p.id
    LEFT JOIN payments pay ON pay.booking_id = b.id
    WHERE b.customer_name = ?
    ORDER BY b.booking_date DESC
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
  <title>Your Bookings - Wanderlust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .table-container {
      max-width: 1200px;
      margin: 50px auto;
      background: white;
      padding: 25px 35px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    th {
      background-color: #0d6efd;
      color: white;
    }
    td, th {
      vertical-align: middle !important;
    }
  </style>
</head>

<body class="bg-light">

  <?php include 'navbar.html'; ?>

  <div class="container table-container">
    <h3 class="text-center text-primary mb-4">Your Bookings & Payments</h3>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Property</th>
              <th>Location</th>
              <th>Host</th>
              <th>Check-In</th>
              <th>Check-Out</th>
              <th>Booked On</th>
              <th>Price (₹)</th>
              <th>Status</th>
              <th>Payment Method</th>
              <th>Payment Status</th>
              <th>Action</th> <!-- Cancel Button -->
            </tr>
          </thead>
          <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
              <?php
                $today = date('Y-m-d');
                $canCancel = ($row['status'] === 'Active' && $row['check_in'] > $today);
              ?>
              <tr>
                <td><?= $row['booking_id'] ?></td>
                <td><?= htmlspecialchars($row['property_name']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['host_name']) ?></td>
                <td><?= htmlspecialchars($row['check_in']) ?></td>
                <td><?= htmlspecialchars($row['check_out']) ?></td>
                <td><?= htmlspecialchars($row['booking_date']) ?></td>
                <td class="text-end">₹<?= number_format($row['price'], 2) ?></td>
                <td>
                  <?php if ($row['status'] === 'Active'): ?>
                    <span class="badge bg-success">Active</span>
                  <?php elseif ($row['status'] === 'Cancelled'): ?>
                    <span class="badge bg-danger">Cancelled</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['payment_method'] ?? '-') ?></td>
                <td>
                  <?php if ($row['payment_status'] === 'Completed'): ?>
                    <span class="badge bg-success">Completed</span>
                  <?php elseif ($row['payment_status'] === 'Pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                  <?php elseif ($row['payment_status'] === 'Failed'): ?>
                    <span class="badge bg-danger">Failed</span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if ($canCancel): ?>
                    <a href="cancel_booking.php?id=<?= $row['booking_id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Are you sure you want to cancel this booking?')">
                       Cancel
                    </a><br>
                 <?php endif; ?>
                <a href="generate_receipt.php?id=<?= $row['booking_id'] ?>"
                class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="fa fa-download"></i> Receipt
                    </a>
                </td>
                
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center text-muted">You have not made any bookings yet.</p>
    <?php endif; ?>
  </div>

  <?php include 'footer.html'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/loadComponents.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
