<?php
include 'db_connect.php';
session_start();

// Ensure only logged-in owners can access
$host_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? null;

if (!$host_id || $role !== 'owner') {
    echo "<script>alert('Access denied. Only owners can edit properties.'); window.location.href='login.html';</script>";
    exit;
}

// Get property ID from URL
$property_id = $_GET['id'] ?? null;
if (!$property_id) {
    echo "<script>alert('Invalid property ID.'); window.location.href='manage_listings.php';</script>";
    exit;
}

// Fetch property details
$sql = "SELECT * FROM properties WHERE id = ? AND host_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $property_id, $host_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Property not found or you do not have permission.'); window.location.href='manage_listings.php';</script>";
    exit;
}

$property = $result->fetch_assoc();

// Update property if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $property_type = $_POST['property_type'];
    $location = $_POST['location'];
    $price = $_POST['price'];

    // Optional: handle image update
    $image = $property['image'];
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $image = $targetFile;
        }
    }

    $updateSql = "UPDATE properties 
                  SET name=?, description=?, property_type=?, location=?, price=?, image=? 
                  WHERE id=? AND host_id=?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssssdsii", $name, $description, $property_type, $location, $price, $image, $property_id, $host_id);
    
    if ($stmt->execute()) {
        echo "<script>
                alert('✅ Property updated successfully!');
                window.location.href = 'manage_listings.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('❌ Error updating property. Please try again.');</script>";
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Property - Wanderlust</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .edit-container {
      max-width: 700px;
      margin: 50px auto;
      background: white;
      padding: 25px 35px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .form-control, .form-select {
      border-radius: 10px;
    }
  </style>
</head>
<body class="bg-light">

  <!-- Navbar -->
  <div id="navbar"></div>

  <div class="edit-container">
    <h3 class="text-center text-primary mb-4">Edit Your Property</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Property Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($property['name']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($property['description']) ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Property Type</label>
        <input type="text" name="property_type" class="form-control" value="<?= htmlspecialchars($property['property_type']) ?>" required>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Location</label>
          <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($property['location']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Price (₹)</label>
          <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($property['price']) ?>" step="0.01" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Current Image</label><br>
        <img src="<?= $property['image'] ?>" alt="Property Image" width="200" class="rounded mb-2"><br>
        <input type="file" name="image" class="form-control">
      </div>

      <div class="text-center">
        <button type="submit" class="btn btn-success px-4">Save Changes</button>
        <a href="manage_listings.php" class="btn btn-secondary px-4">Cancel</a>
      </div>
    </form>
  </div>

  <div id="footer" class="mt-5"></div>

  <script src="js/loadComponents.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
