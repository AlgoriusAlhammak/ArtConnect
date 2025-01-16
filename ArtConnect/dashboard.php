<?php
session_start();
global $conn;

// Redirect to login page if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Include database connection
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtConnect</title>
    <link rel="stylesheet" href="DashStyles.css">
    <script src="script.js" defer></script>
</head>
<body>
<div class="container">
    <!-- Sidebar Section -->
    <div class="sidebar">
        <div class="logo">ArtConnect</div>
        <nav>
            <a href="upload.php" class="sidebar-button">Upload</a>
            <a href="logout.php" class="sidebar-button">Logout</a>
        </nav>
    </div>

    <!-- Main Content Section -->
    <div class="main-content">
        <div class="header">
            <!-- Display the logged-in user's name dynamically -->
            <h2>Welcome to our platform!</h2>
            <div class="header-buttons">
                <!-- Link to the logged-in user's profile -->
                <a href="profile.php?user_id=<?= $_SESSION['user_id']; ?>" class="header-button">View Profile</a>
            </div>
        </div>

        <div class="posts">
<!--            <h3>Posts from Artists You Follow:</h3>-->
            <?php
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT images.*, users.username 
                        FROM images 
                        JOIN followers ON images.user_id = followers.followed_id 
                        JOIN users ON images.user_id = users.id 
                        WHERE followers.follower_id = ? 
                        ORDER BY images.created_at DESC";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image_id = $row['id'];
                        ?>
                        <div class="post-card">
                            <div class="post-header">
                                <h4><a href="profile.php?user_id=<?= htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?></a></h4>
                            </div>
                            <img src="<?= htmlspecialchars($row['image_path'], ENT_QUOTES, 'UTF-8') ?>" class="post-image" alt="Post image">

                            <div class="post-actions">
                                <!-- Like button -->
                                <form action="like.php" method="POST" class="like-form">
                                    <input type="hidden" name="image_id" value="<?= htmlspecialchars($image_id, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="like-btn">Like</button>
                                </form>

                                <!-- Comment button and text input -->
                                <button class="comment-btn" onclick="toggleCommentForm(<?= $image_id ?>)">Comment</button>
                                <form action="comment.php" method="POST" class="comment-form" id="comment-form-<?= $image_id ?>" style="display: none;">
                                    <input type="hidden" name="image_id" value="<?= htmlspecialchars($image_id, ENT_QUOTES, 'UTF-8') ?>">
                                    <label>
                                        <input type="text" name="comment" class="comment-input" placeholder="Add a comment..." required>
                                    </label>
                                    <button type="submit" class="post-comment-btn">Post</button>
                                </form>
                            </div>

                            <!-- Like count -->
                            <?php
                            $like_count_sql = "SELECT COUNT(*) AS like_count FROM likes WHERE image_id = ?";
                            $like_stmt = $conn->prepare($like_count_sql);
                            if ($like_stmt) {
                                $like_stmt->bind_param("i", $image_id);
                                $like_stmt->execute();
                                $like_result = $like_stmt->get_result()->fetch_assoc();
                                echo "<div class='like-count'>" . htmlspecialchars($like_result['like_count'], ENT_QUOTES, 'UTF-8') . " Likes</div>";
                                $like_stmt->close();
                            }
                            ?>

                            <!-- Comments Section Toggle -->
                            <div class="comments-section">
                                <button class="view-comments-btn" onclick="toggleComments(<?= $image_id ?>)">View Comments</button>
                                <div class="comments-list" id="comments-list-<?= $image_id ?>" style="display: none;">
                                    <?php
                                    // Fetch comments with their date
                                    $comment_sql = "SELECT comments.comment, comments.created_at, users.username 
                                                    FROM comments 
                                                    JOIN users ON comments.user_id = users.id 
                                                    WHERE comments.image_id = ? 
                                                    ORDER BY comments.created_at DESC"; // Order by latest comments first
                                    $comment_stmt = $conn->prepare($comment_sql);
                                    if ($comment_stmt) {
                                        $comment_stmt->bind_param("i", $image_id);
                                        $comment_stmt->execute();
                                        $comment_result = $comment_stmt->get_result();

                                        if ($comment_result->num_rows > 0) {
                                            while ($comment_row = $comment_result->fetch_assoc()) {
                                                // Display comment with username and date
                                                echo "<p><strong>" . htmlspecialchars($comment_row['username'], ENT_QUOTES, 'UTF-8') . ":</strong> " .
                                                    htmlspecialchars($comment_row['comment'], ENT_QUOTES, 'UTF-8') .
                                                    "<br><span class='comment-date'>" .
                                                    htmlspecialchars(date("F j, Y, g:i a", strtotime($comment_row['created_at'])), ENT_QUOTES, 'UTF-8') .
                                                    "</span></p>";
                                            }
                                        } else {
                                            echo "<p>No comments yet.</p>";
                                        }
                                        $comment_stmt->close();
                                    }
                                    ?>
                                </div>
                            </div>

                        </div>
                        <?php
                    }
                } else {
                    echo "<p>No posts from users you follow.</p>";
                }
                $stmt->close();
            }
            ?>
        </div>

        <!-- Follow Users Section -->
        <div class="follow-users">
            <h3>Follow Users</h3>
            <?php
            // Fetch all users except the logged-in user
            $user_sql = "SELECT * FROM users WHERE id != ?";
            $user_stmt = $conn->prepare($user_sql);

            if ($user_stmt) {
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();

                if ($user_result->num_rows > 0) {
                    while ($user = $user_result->fetch_assoc()) {
                        $followed_id = $user['id'];

                        // Check if already following
                        $check_follow_sql = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
                        $check_follow_stmt = $conn->prepare($check_follow_sql);

                        if ($check_follow_stmt) {
                            $check_follow_stmt->bind_param("ii", $user_id, $followed_id);
                            $check_follow_stmt->execute();
                            $check_follow_result = $check_follow_stmt->get_result();

                            // Display follow or unfollow button
                            echo "<p>" . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
                            if ($check_follow_result->num_rows > 0) {
                                echo " <form action='unfollow.php' method='POST' style='display: inline;'>
                                        <input type='hidden' name='followed_id' value='" . htmlspecialchars($followed_id, ENT_QUOTES, 'UTF-8') . "'>
                                        <button type='submit'>Unfollow</button>
                                      </form>";
                            } else {
                                echo " <form action='follow.php' method='POST' style='display: inline;'>
                                        <input type='hidden' name='followed_id' value='" . htmlspecialchars($followed_id, ENT_QUOTES, 'UTF-8') . "'>
                                        <button type='submit'>Follow</button>
                                      </form>";
                            }
                            echo "</p>";
                            $check_follow_stmt->close();
                        }
                    }
                } else {
                    echo "<p>No other users to follow.</p>";
                }
                $user_stmt->close();
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>












