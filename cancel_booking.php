<?php
include 'db_connect.php';
session_start();

// Ensure customer is logged in
$role = $_SESSION['role'] ?? null;
if ($role !== 'customer') {
    echo "<script>alert('Access denied. Only customers can cancel bookings.'); window.location.href='login.html';</script>";
    exit;
}

$booking_id = $_GET['id'] ?? null;
$customer_name = $_SESSION['user_name'] ?? null;

if (!$booking_id || !$customer_name) {
    echo "<script>alert('Invalid request.'); window.location.href='customer_view_bookings.php';</script>";
    exit;
}

// ✅ Check if booking belongs to this customer and is active
$sql = "SELECT * FROM bookings WHERE id = ? AND customer_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $booking_id, $customer_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Booking not found or unauthorized access.'); window.location.href='customer_view_bookings.php';</script>";
    exit;
}

$booking = $result->fetch_assoc();
$today = date('Y-m-d');

// ✅ Only allow cancellation if check-in is still in future and status is Active/Confirmed
if ($booking['check_in'] <= $today || !in_array($booking['status'], ['Active', 'Confirmed'])) {
    echo "<script>alert('This booking cannot be cancelled now.'); window.location.href='customer_view_bookings.php';</script>";
    exit;
}

// ✅ Update booking status to Cancelled
$update = "UPDATE bookings SET status = 'Cancelled' WHERE id = ?";
$stmt = $conn->prepare($update);
$stmt->bind_param("i", $booking_id);

if ($stmt->execute()) {
    echo "<script>alert('✅ Booking cancelled successfully.'); window.location.href='customer_view_bookings.php';</script>";
} else {
    echo "<script>alert('❌ Error cancelling booking. Try again.'); window.location.href='customer_view_bookings.php';</script>";
}

$stmt->close();
$conn->close();
?>
