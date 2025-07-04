<?php
require_once 'config.php'; // contains UPLOAD_DIR or other constants

$conn = new mysqli("localhost", "root", "", "dbfirstproject");

// Fetch user data and mobile numbers
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    $user = $result->fetch_assoc();

    // Fetch mobile numbers
    $mobileNumbers = [];
    $existingMobileIds = [];
    $mobileResult = $conn->query("SELECT id, number FROM mobilenumber WHERE userId = $id");
    while ($row = $mobileResult->fetch_assoc()) {
        $mobileNumbers[] = $row;
        $existingMobileIds[] = $row['id'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $oldImageName = $_POST['old_image'];

    $mobileIds = $_POST['mobile_ids'] ?? [];
    $mobiles = $_POST['mobiles'] ?? [];

    $newImageName = $oldImageName;

    // Handle image upload
    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === 0) {
        $file = $_FILES['user_image'];
        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $newImageName = uniqid() . "_" . $originalName;
            $targetPath = UPLOAD_DIR . $newImageName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Delete old image
                $oldPath = UPLOAD_DIR . $oldImageName;
                if (!empty($oldImageName) && file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        }
    }

    // Update user
    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, image_path=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $newImageName, $id);
    if (!$stmt->execute()) {
        echo "User update failed: " . $stmt->error;
    }
    $stmt->close();

    // Fetch current mobile IDs again (in case of fresh page load)
    $existingMobileIds = [];
    $mobileResult = $conn->query("SELECT id FROM mobilenumber WHERE userId = $id");
    while ($row = $mobileResult->fetch_assoc()) {
        $existingMobileIds[] = $row['id'];
    }

    // Prepare for insert/update
    $remainingIds = $existingMobileIds;

    foreach ($mobiles as $index => $number) {
        $number = trim($number);
        $mobileId = $mobileIds[$index] ?? '';

        if ($number === '') continue; // skip empty entries

        if (!empty($mobileId)) {
            // Update
            $stmt = $conn->prepare("UPDATE mobilenumber SET number = ? WHERE id = ? AND userId = ?");
            $stmt->bind_param("sii", $number, $mobileId, $id);
            $stmt->execute();
            $stmt->close();

            // Remove from deletion list
            $key = array_search($mobileId, $remainingIds);
            if ($key !== false) {
                unset($remainingIds[$key]);
            }
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO mobilenumber (userId, number) VALUES (?, ?)");
            $stmt->bind_param("is", $id, $number);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Delete removed numbers
    foreach ($remainingIds as $deleteId) {
        $stmt = $conn->prepare("DELETE FROM mobilenumber WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php?updated=1");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        .mobileInput { margin-bottom: 10px; }
    </style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function addMobile() {
        const html = `
            <div class="mobileInput">
                <input type="hidden" name="mobile_ids[]" value="">
                <input type="text" name="mobiles[]" placeholder="Enter mobile number">
                <button type="button" class="removeMobile">Remove</button>
            </div>
        `;
        $('#mobileContainer').append(html);
    }

    $(document).on('click', '.removeMobile', function () {
        $(this).closest('.mobileInput').remove();
    });

    $(document).ready(function () {
        $('#addMobileBtn').on('click', addMobile);
    });
</script>

</head>
<body>

<h2>Edit User</h2>
<form method="POST" action="edit.php?id=<?= $user['id'] ?>" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $user['id'] ?>">
    <input type="hidden" name="old_image" value="<?= $user['image_path'] ?>">

    Name: <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>
    Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

    Current Image:<br>
    <img src="<?= UPLOAD_DIR . htmlspecialchars($user['image_path']) ?>" width="150"><br><br>

    Change Image: <input type="file" name="user_image" accept="image/*"><br><br>

    <h3>Mobile Numbers</h3>
    <div id="mobileContainer">
        <?php foreach ($mobileNumbers as $mobile): ?>
            <div class="mobileInput">
                <input type="hidden" name="mobile_ids[]" value="<?= $mobile['id'] ?>">
                <input type="text" name="mobiles[]" value="<?= htmlspecialchars($mobile['number']) ?>">
                <button type="button" class="removeMobile">Remove</button>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" id="addMobileBtn">Add Mobile</button><br><br>

    <input type="submit" value="Update">
</form>

</body>
</html>
