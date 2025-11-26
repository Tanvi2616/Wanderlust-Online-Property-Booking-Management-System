<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $property_id = $_POST['property_id'];
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_phone = $_POST['customer_phone'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $payment_method = $_POST['payment_method'];

    // ✅ Fetch property price
    $priceQuery = "SELECT price FROM properties WHERE id = ?";
    $stmt = $conn->prepare($priceQuery);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();

    if (!$property) {
        echo "Property not found!";
        exit;
    }

    $price = $property['price'];

    // ✅ Insert booking into bookings table
    $sql = "INSERT INTO bookings (property_id, customer_name, customer_email, customer_phone, check_in, check_out, booking_date, status)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $property_id, $customer_name, $customer_email, $customer_phone, $check_in, $check_out);
    
    if ($stmt->execute()) {
        // Get the inserted booking ID
        $booking_id = $conn->insert_id;

        // ✅ Insert payment record
        $insertPayment = "INSERT INTO payments (booking_id, customer_name, amount, payment_method, payment_status)
                          VALUES (?, ?, ?, ?, 'Completed')";
        $payStmt = $conn->prepare($insertPayment);
        $payStmt->bind_param("isds", $booking_id, $customer_name, $price, $payment_method);
        $payStmt->execute();

        echo "✅ Booking confirmed and payment successful!";
    } else {
        echo "❌ Error: Could not complete booking.";
    }

    $stmt->close();
    $conn->close();
}
?>
