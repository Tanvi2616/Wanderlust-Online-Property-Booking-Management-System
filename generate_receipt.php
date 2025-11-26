<?php
require('fpdf186/fpdf.php');
include 'db_connect.php';
session_start();

$booking_id = $_GET['id'] ?? null;

if (!$booking_id) {
    die('Invalid booking ID.');
}

// ✅ Fetch booking + property + payment info
$sql = "
SELECT 
    b.id AS booking_id, b.customer_name, b.customer_email, b.customer_phone,
    b.check_in, b.check_out, b.booking_date, b.status,
    p.name AS property_name, p.location, p.price, p.host_name,
    pay.payment_method, pay.amount, pay.payment_date
FROM bookings b
JOIN properties p ON b.property_id = p.id
LEFT JOIN payments pay ON pay.booking_id = b.id
WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) die('Booking not found.');

class PDF extends FPDF {
    function Header() {
        // Red header bar
        $this->SetFillColor(255, 92, 92); // #ff5c5c
        $this->Rect(0, 0, 210, 25, 'F');

        // Add logo
        $this->Image('images/wanderlust_logo.png', 10, 4, 50); // x, y, width

        // Move below header
        $this->Ln(20);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 10, 'Thank you for choosing Wanderlust!', 0, 0, 'C');
    }
}

// Create PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->Ln(10);

// Section: Booking Details
$pdf->SetTextColor(33,37,41);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Booking Receipt', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 8, 'Booking ID:', 0, 0);
$pdf->Cell(80, 8, $booking['booking_id'], 0, 1);

$pdf->Cell(50, 8, 'Customer Name:', 0, 0);
$pdf->Cell(80, 8, $booking['customer_name'], 0, 1);

$pdf->Cell(50, 8, 'Email:', 0, 0);
$pdf->Cell(80, 8, $booking['customer_email'], 0, 1);

$pdf->Cell(50, 8, 'Phone:', 0, 0);
$pdf->Cell(80, 8, $booking['customer_phone'], 0, 1);

$pdf->Cell(50, 8, 'Property Name:', 0, 0);
$pdf->Cell(80, 8, $booking['property_name'], 0, 1);

$pdf->Cell(50, 8, 'Location:', 0, 0);
$pdf->Cell(80, 8, $booking['location'], 0, 1);

$pdf->Cell(50, 8, 'Host Name:', 0, 0);
$pdf->Cell(80, 8, $booking['host_name'], 0, 1);

$pdf->Cell(50, 8, 'Check-In:', 0, 0);
$pdf->Cell(80, 8, $booking['check_in'], 0, 1);

$pdf->Cell(50, 8, 'Check-Out:', 0, 0);
$pdf->Cell(80, 8, $booking['check_out'], 0, 1);

$pdf->Cell(50, 8, 'Booking Date:', 0, 0);
$pdf->Cell(80, 8, $booking['booking_date'], 0, 1);

$pdf->Cell(50, 8, 'Status:', 0, 0);
$pdf->Cell(80, 8, $booking['status'], 0, 1);
$pdf->Ln(5);

// Section: Payment
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Payment Details', 0, 1);
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(50, 8, 'Payment Method:', 0, 0);
$pdf->Cell(80, 8, $booking['payment_method'] ?? 'N/A', 0, 1);

$pdf->Cell(50, 8, 'Amount (₹):', 0, 0);
$pdf->Cell(80, 8, number_format($booking['amount'] ?? $booking['price'], 2), 0, 1);

$pdf->Cell(50, 8, 'Payment Date:', 0, 0);
$pdf->Cell(80, 8, $booking['payment_date'] ?? 'N/A', 0, 1);

$pdf->Output('I', 'Wanderlust_Receipt_'.$booking['booking_id'].'.pdf');
?>
