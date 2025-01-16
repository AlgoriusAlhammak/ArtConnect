<?php
session_start();
global $conn;
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . uniqid() . "_" . $image_name; // Unique filename
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists (with the new unique filename, this is unlikely)
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Allow only specific file formats
    $allowed_file_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_file_types)) {
        echo "Sorry, only JPG, JPEG, PNG, & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Limit file size (e.g., max 2MB)
    if ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
        echo "Sorry, your file is too large. Max size: 2MB.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Attempt to move the uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Save file path in the database
            $sql = "INSERT INTO images (user_id, image_path) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("is", $user_id, $target_file);

                if ($stmt->execute()) {
                    echo "The file " . htmlspecialchars($image_name) . " has been uploaded.";
                } else {
                    echo "Error saving file information to the database.";
                }
                $stmt->close();
            } else {
                echo "Error preparing the SQL query: " . htmlspecialchars($conn->error);
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
    <link rel="stylesheet" href="UploadStyles.css">
</head>
<body>

<div class="upload-container">
    <h2>Upload Image</h2>
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <label for="image">Select image to upload:</label>
        <input type="file" name="image" accept="image/*" id="image" required>
        <button type="submit" name="submit">Upload</button>
    </form>
    <a href="dashboard.php" class="back-link">Back to Dashboard</a>
</div>

<!-- Link to external JS file -->
<script src="script.js" defer></script>

</body>
</html>


