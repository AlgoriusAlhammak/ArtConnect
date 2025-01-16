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
    $image_id = $_POST['image_id'];
    $comment = $_POST['comment'];

    
    if (empty($image_id) || empty($comment)) {
        echo "Error: All fields are required.";
        exit();
    }

    
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

    
    $sql = "INSERT INTO comments (user_id, image_id, comment) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("iis", $user_id, $image_id, $comment);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Unable to post your comment. Please try again later.";
        }
    } else {
        echo "Error: " . $conn->error;
    }
}


