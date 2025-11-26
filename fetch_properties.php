<?php
header('Content-Type: application/json; charset=UTF-8');
include 'db_connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$type = isset($_GET['type']) ? trim($_GET['type']) : '';

try {
    if ($type !== '') {
        // 🔹 Fetch properties by category with average rating and review count
        $sql = "
            SELECT 
                p.*, 
                COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
                COUNT(r.id) AS review_count
            FROM properties p
            LEFT JOIN reviews r ON p.id = r.property_id
            WHERE p.property_type = ?
            GROUP BY p.id
            ORDER BY p.id
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(["error" => "Prepare failed: " . $conn->error]);
            exit;
        }
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // 🔹 Fetch all properties with average rating and review count
        $sql = "
            SELECT 
                p.*, 
                COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
                COUNT(r.id) AS review_count
            FROM properties p
            LEFT JOIN reviews r ON p.id = r.property_id
            GROUP BY p.id
            ORDER BY p.id
        ";
        $result = $conn->query($sql);
        if (!$result) {
            echo json_encode(["error" => "Query failed: " . $conn->error]);
            exit;
        }
    }

    $properties = [];
    while ($row = $result->fetch_assoc()) {
        $properties[] = [
            "id" => $row["id"],
            "name" => $row["name"],
            "description" => $row["description"],
            "property_type" => $row["property_type"],
            "price" => $row["price"],
            "location" => $row["location"],
            "image" => $row["image"],
            "host_name" => $row["host_name"],
            "ratings" => $row["avg_rating"],
            "reviews" => $row["review_count"]
        ];
    }

    if (empty($properties)) {
        echo json_encode(["message" => "No properties found"]);
    } else {
        echo json_encode($properties);
    }

    if (isset($stmt)) $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
