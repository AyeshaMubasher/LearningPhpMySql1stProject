<?php

require_once 'config.php'; //constant 

$conn = new mysqli("localhost", "root", "", "dbfirstproject");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    //fetch and delete image file
    $result = $conn->query("SELECT image_path FROM users WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        $imagePath =  UPLOAD_DIR . $row['image_path'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete from database
    $conn->query("DELETE FROM users WHERE id = $id");

    // Delete mobilenumbers
    $conn->query("DELETE FROM mobilenumber WHERE userId = $id");
}

$conn->close();
header("Location: index.php?deleted=1");
exit();
