<?php

$conn = new mysqli("localhost", "root", "", "dbfirstproject");


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['email'])) {
    $email = $conn->real_escape_string($_POST['email']);

    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    echo ($result->num_rows > 0) ? 'exists' : 'available';
}

$conn->close();
?>
