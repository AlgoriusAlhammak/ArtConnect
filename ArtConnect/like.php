<?php
session_start();
global $conn;
include 'db_connection.php';
// Redirect to log in if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $image_id = $_POST['image_id'];

    // Validate input to ensure it's a valid image ID
    if (filter_var($image_id, FILTER_VALIDATE_INT) === false) {
        echo "Invalid request.";
        exit();
    }

    // Check if the user has already liked the image
    $check_sql = "SELECT * FROM likes WHERE user_id = ? AND image_id = ?";
    $stmt = $conn->prepare($check_sql);

    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $image_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If already liked, remove the like
            $delete_sql = "DELETE FROM likes WHERE user_id = ? AND image_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);

            if ($delete_stmt) {
                $delete_stmt->bind_param("ii", $user_id, $image_id);
                if ($delete_stmt->execute()) {
                    // Optional: Add a message indicating the like was removed
                    echo "Unlike.";
                } else {
                    echo "Error removing like: " . $conn->error;
                    exit();
                }
                $delete_stmt->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
                exit();
            }
        } else {
            // If not liked, add a like
            $insert_sql = "INSERT INTO likes (user_id, image_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);

            if ($insert_stmt) {
                $insert_stmt->bind_param("ii", $user_id, $image_id);
                if ($insert_stmt->execute()) {
                    // Optional: Add a message indicating the like was added
                    echo "Like.";
                } else {
                    echo "Error adding like: " . $conn->error;
                    exit();
                }
                $insert_stmt->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
                exit();
            }
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }
} else {
    echo "Invalid request method.";
    exit();
}

// Redirect back to the dashboard
header("Location: dashboard.php");
exit();

