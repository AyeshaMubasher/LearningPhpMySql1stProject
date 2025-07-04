<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'config.php'; //constant 

$Name= $_REQUEST["name"];
$Email= $_REQUEST["email"];
exit;
echo "Name = ".$Name."<br>";
echo "Email = ".$Email."<br>";

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
echo "Connected successfully";
// Handle form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = $_POST['name'];
    $email = $_POST['email'];

    $mobileNumbers = $_POST['mobile_numbers'];

    // Handle image
    $image = $_FILES['user_image'];
    $imageName = basename($image['name']);
    $imageNameToSave = uniqid() . "_" . $imageName;
    $targetFile =  UPLOAD_DIR. $imageNameToSave ;
   

    // Check file type (basic)
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowedTypes)) {
        if (move_uploaded_file($image["tmp_name"], $targetFile)) {
            // Insert into DB with image path
            $stmt = $conn->prepare("INSERT INTO users (name, email, image_path) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $imageNameToSave);
            
            if ($stmt->execute()) {
                /*
                header("Location: index.php?success=1");
                exit();
                */
            } else {
                echo "DB Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Invalid image type. Only JPG, PNG, GIF allowed.";
    }
}


$userId = $conn->insert_id;

foreach ($mobileNumbers as $number) {
    //echo "\nmobile numbers array data\n", $number;
    $cleanNumber = trim($number);
    //echo "\nmobile numbers array data after trim\n", $cleanNumber;
    if (!empty($cleanNumber)) {
        //echo "\n i if data not empty \n", $cleanNumber;
        $stmt = $conn->prepare("INSERT INTO mobilenumber (number, userId) VALUES (?, ?)");
        $stmt->bind_param("si", $cleanNumber, $userId); // "s" = string, "i" = integer
        $stmt->execute();
    }
}

    if ($stmt->execute()) {
                
        header("Location: index.php?success=1");
        exit();
                
    } else {
        echo "DB Error: " . $stmt->error;
    }



$conn->close();
?>