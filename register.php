<?php
header('Content-Type: application/json');
include 'db_connect.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if(!$name || !$email || !$password || !$role){
    echo json_encode(['success'=>false,'message'=>'All fields are required.']);
    exit;
}

$table = ($role === 'owner') ? 'owners' : 'customers';

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM $table WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0){
    echo json_encode(['success'=>false,'message'=>'Email already registered.']);
    exit;
}

// Insert user
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO $table (name,email,password) VALUES (?,?,?)");
$stmt->bind_param("sss",$name,$email,$hashed);
if($stmt->execute()){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'message'=>'Registration failed.']);
}

$conn->close();
?>
