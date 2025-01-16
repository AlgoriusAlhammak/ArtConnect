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
    $follower_id = $_SESSION['user_id'];
    $followed_id = $_POST['followed_id'];

    // Validate input to ensure it's a valid user ID
    if (filter_var($followed_id, FILTER_VALIDATE_INT) === false || $followed_id === $follower_id) {
        echo "Invalid request.";
        exit();
    }

    if (!$conn) {
        echo "Database connection failed.";
        exit();
    }

    // Check if the follow relationship already exists
    $check_sql = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $follower_id, $followed_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "You are already following this user.";
        exit();
    }

    // Add the follow relationship to the database
    $sql = "INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ii", $follower_id, $followed_id);

        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing the statement: " . $conn->error;
    }
} else {
    echo "Invalid request method.";
    exit();
}


