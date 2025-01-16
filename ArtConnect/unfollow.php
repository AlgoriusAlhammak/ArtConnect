<?php
session_start();
global $conn;
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $follower_id = $_SESSION['user_id'];
    $followed_id = filter_input(INPUT_POST, 'followed_id', FILTER_VALIDATE_INT);

    if ($followed_id === false || $followed_id === null) {
        echo "Invalid request.";
        exit();
    }

    // Prepare the SQL query to remove the follow relationship
    $sql = "DELETE FROM followers WHERE follower_id = ? AND followed_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $follower_id, $followed_id);

        if ($stmt->execute()) {
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error executing query: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        echo "Error preparing query: " . htmlspecialchars($conn->error);
    }
}

$conn->close();

