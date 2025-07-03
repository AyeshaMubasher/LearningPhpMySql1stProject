<?php

require_once 'config.php'; //constant 

$conn = new mysqli("localhost", "root", "", "dbfirstproject");

// Get user by ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    $user = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id    = $_POST['id'];
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $oldImageName = $_POST['old_image'];

    $newImageName = $oldImageName;

    // Handle new image upload
    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === 0) {
        $file = $_FILES['user_image'];
        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $newImageName = uniqid() . "_" . $originalName;
            $targetPath = UPLOAD_DIR . $newImageName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Delete old image if it exists
                $oldPath = UPLOAD_DIR . $oldImageName;
                if (!empty($oldImageName) && file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        }
    }

    // Update user record
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, image_path=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $newImageName, $id);

    if ($stmt->execute()) {
        header("Location: index.php?updated=1");
        exit();
    } else {
        echo "Update failed: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head><title>Edit User</title></head>
<body>

<h2>Edit User</h2>
<form method="POST" action="edit.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    <input type="hidden" name="old_image" value="<?= $user['image_path'] ?>">

    Name: <input type="text" name="name" value="<?= $user['name'] ?>" required><br><br>
    Email: <input type="email" name="email" value="<?= $user['email'] ?>" required><br><br>

    Current Image: <br>

    <img src="<?= UPLOAD_DIR . $user['image_path'] ?>" width="150"><br><br>

    Change Image: <input type="file" name="user_image" accept="image/*"><br><br>

    <input type="submit" value="Update">
</form>

</body>
</html>
