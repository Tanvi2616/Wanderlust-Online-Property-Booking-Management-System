<?php
session_start();
header('Content-Type: application/json');
include 'db_connect.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if(!$email || !$password){
    echo json_encode(['success'=>false,'message'=>'All fields are required.']);
    exit;
}

$tables = ['customers','owners'];
$found = false;

foreach($tables as $table){
    $stmt = $conn->prepare("SELECT id, password, name FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()){
        if(password_verify($password, $row['password'])){
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['role'] = ($table === 'owners') ? 'owner' : 'customer';
            $found = true;
            break;
        }
    }
}

if($found){
    // return role & name keys that frontend expects
    echo json_encode([
        'success' => true,
        'role' => $_SESSION['role'],
        'name' => $_SESSION['user_name']
    ]);
} else {
    echo json_encode(['success'=>false,'message'=>'Invalid email or password.']);
}

$conn->close();
?>
