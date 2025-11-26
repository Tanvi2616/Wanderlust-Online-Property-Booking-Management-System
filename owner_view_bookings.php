<?php
include 'db_connect.php';
session_start();

// ✅ Auto-update booking status if stay period is over
$today = date('Y-m-d');
$updateStatus = "UPDATE bookings 
                 SET status = 'Completed' 
                 WHERE check_out < ? AND status = 'Active'";
$stmtUpdate = $conn->prepare($updateStatus);
$stmtUpdate->bind_param("s", $today);
$stmtUpdate->execute();

// Ensure only owners can access
$host_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$host_id || $role !== 'owner') {
    echo "<script>alert('Access denied. Only owners can view bookings.'); window.location.href='login.html';</script>";
    exit;
}

// ✅ Fetch bookings including 'status' column
$sql = "
    SELECT 
        b.id AS booking_id,
        b.customer_name,
        b.customer_email,
        b.customer_phone,
        b.check_in,
        b.check_out,
        b.booking_date,
        b.status,
        p.name AS property_name,
        p.location,
        p.price
    FROM bookings b
    JOIN properties p ON b.property_id = p.id
    WHERE p.host_id = ?
    ORDER BY b.booking_date DESC
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
  <title>Owner View Bookings - Wanderlust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">

  <style>
    body {
      background-color: #f8f9fa;
    }
    .table-container {
      max-width: 1100px;
      margin: 50px auto;
      background: white;
      padding: 25px 35px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    th {
      background-color: #007bff;
      color: white;
    }
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      text-transform: capitalize;
    }
    .status-pending {
      background-color: #ffc107;
      color: #212529;
    }
    .status-confirmed {
      background-color: #28a745;
      color: white;
    }
    .status-cancelled {
      background-color: #dc3545;
      color: white;
    }
    .status-completed {
      background-color: #17a2b8;
      color: white;
    }
  </style>
</head> 
<body>

  <?php include 'navbar.html'; ?>

  <div class="container table-container">
    <h3 class="text-center text-primary mb-4"><i class="fa-solid fa-calendar-check"></i> Your Property Bookings</h3>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
          <thead>
            <tr>
              <th>Booking ID</th>
              <th>Property</th>
              <th>Customer Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Check-In</th>
              <th>Check-Out</th>
              <th>Booking Date</th>
              <th>Price (₹)</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
              <?php 
                $status = strtolower($row['status']);
                $badgeClass = "status-pending";
                if ($status == "confirmed") $badgeClass = "status-confirmed";
                elseif ($status == "cancelled") $badgeClass = "status-cancelled";
                elseif ($status == "completed") $badgeClass = "status-completed";
              ?>
              <tr>
                <td><?= $row['booking_id'] ?></td>
                <td><?= htmlspecialchars($row['property_name']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['customer_email']) ?></td>
                <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                <td><?= htmlspecialchars($row['check_in']) ?></td>
                <td><?= htmlspecialchars($row['check_out']) ?></td>
                <td><?= htmlspecialchars($row['booking_date']) ?></td>
                <td class="text-end">₹<?= number_format($row['price'], 2) ?></td>
                <td><span class="status-badge <?= $badgeClass; ?>"><?= htmlspecialchars($row['status']); ?></span></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center text-muted mt-4">No bookings yet for your properties.</p>
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
