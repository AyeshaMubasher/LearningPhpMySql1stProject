<?php

require_once 'config.php'; //constant 

$servername = "localhost";
$username = "root";
$password = "";
$databaseName = "dbfirstproject";

// Create connection
$conn = new mysqli($servername, $username, $password , $databaseName);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";
$sql = "SELECT id, name, email, image_path FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Image</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
        echo "<td><img src='" . UPLOAD_DIR . htmlspecialchars($row["image_path"]) . "' width='100'></td>";
        
        //Fetch mobile numbers for this user
        $userId = $row["id"];
        $mobileSql = "SELECT number FROM mobilenumber WHERE userId = ?";
        $stmt = $conn->prepare($mobileSql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $mobileResult = $stmt->get_result();

        echo "<td>";
        if ($mobileResult->num_rows > 0) {
            while ($mobile = $mobileResult->fetch_assoc()) {
                echo htmlspecialchars($mobile["number"]) . "<br>";
            }
        } else {
            echo "No numbers";
        }
        echo "</td>";










        echo "<td>
        <a href='edit.php?id=" . $row["id"] . "'>Edit</a> | 
        <a href='delete.php?id=" . $row["id"] . "' onclick=\"return confirm('Are you sure you want to delete this user?');\">Delete</a>
        </td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No records found.";
}

?>