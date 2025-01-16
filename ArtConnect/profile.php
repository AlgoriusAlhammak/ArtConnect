<?php
session_start();
global $conn;
include 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate the user ID in the URL
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo "Invalid or missing user ID.";
    exit();
}

$profile_user_id = intval($_GET['user_id']);
$logged_in_user_id = $_SESSION['user_id'];

// Fetch profile user info
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $profile_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$profile_user = $user_result->fetch_assoc();

if (!$profile_user) {
    echo "User not found.";
    exit();
}

// Fetch follower and following counts
$follower_count_sql = "SELECT COUNT(*) AS follower_count FROM followers WHERE followed_id = ?";
$follower_count_stmt = $conn->prepare($follower_count_sql);
$follower_count_stmt->bind_param("i", $profile_user_id);
$follower_count_stmt->execute();
$follower_count_result = $follower_count_stmt->get_result();
$follower_count = $follower_count_result->fetch_assoc()['follower_count'];

$following_count_sql = "SELECT COUNT(*) AS following_count FROM followers WHERE follower_id = ?";
$following_count_stmt = $conn->prepare($following_count_sql);
$following_count_stmt->bind_param("i", $profile_user_id);
$following_count_stmt->execute();
$following_count_result = $following_count_stmt->get_result();
$following_count = $following_count_result->fetch_assoc()['following_count'];

// Fetch the profile user's posts
$posts_sql = "SELECT * FROM images WHERE user_id = ? ORDER BY created_at DESC";
$posts_stmt = $conn->prepare($posts_sql);
$posts_stmt->bind_param("i", $profile_user_id);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile_user['username']); ?>'s Profile</title>
    <link rel="stylesheet" href="ProfileStyles.css">
</head>
<body>

<div class="profile-container">
    <header>
        <h1><?php echo htmlspecialchars($profile_user['username']); ?>'s Profile</h1>
        <div class="stats">
            <p>Followers: <?php echo $follower_count; ?></p>
            <p>Following: <?php echo $following_count; ?></p>
        </div>
    </header>

    <!-- Follow/Unfollow Button -->
    <?php if ($logged_in_user_id != $profile_user_id): ?>
        <?php
        // Check if the logged-in user is already following the profile user
        $check_follow_sql = "SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?";
        $check_follow_stmt = $conn->prepare($check_follow_sql);
        $check_follow_stmt->bind_param("ii", $logged_in_user_id, $profile_user_id);
        $check_follow_stmt->execute();
        $check_follow_result = $check_follow_stmt->get_result();
        ?>
        <div class="follow-btn">
            <?php if ($check_follow_result->num_rows > 0): ?>
                <form action="unfollow.php" method="POST">
                    <input type="hidden" name="followed_id" value="<?php echo $profile_user_id; ?>">
                    <button type="submit" class="btn unfollow-btn">Unfollow</button>
                </form>
            <?php else: ?>
                <form action="follow.php" method="POST">
                    <input type="hidden" name="followed_id" value="<?php echo $profile_user_id; ?>">
                    <button type="submit" class="btn follow-btn">Follow</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="posts-section">
        <h2>Posts</h2>
        <?php if ($posts_result->num_rows > 0): ?>
            <div class="posts-gallery">
                <?php while ($post = $posts_result->fetch_assoc()): ?>
                    <div class="post-card">
                        <!-- Display the post image -->
                        <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image">

                        <?php
                        // Fetch the number of likes for this post (image)
                        $likes_sql = "SELECT COUNT(*) AS like_count FROM likes WHERE image_id = ?";
                        $likes_stmt = $conn->prepare($likes_sql);
                        $likes_stmt->bind_param("i", $post['id']);
                        $likes_stmt->execute();
                        $likes_result = $likes_stmt->get_result();
                        $like_count = $likes_result->fetch_assoc()['like_count'];
                        ?>

                        <!-- Display the like count -->
                        <p>Likes: <?php echo $like_count; ?></p>

                        <?php
                        // Fetch the comments for this post (image)
                        $comments_sql = "SELECT comment FROM comments WHERE image_id = ? ORDER BY created_at DESC";
                        $comments_stmt = $conn->prepare($comments_sql);
                        $comments_stmt->bind_param("i", $post['id']);
                        $comments_stmt->execute();
                        $comments_result = $comments_stmt->get_result();
                        ?>

                        <h3>Comments:</h3>
                        <?php if ($comments_result->num_rows > 0): ?>
                            <ul class="comments-list">
                                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                                    <li><?php echo htmlspecialchars($comment['comment']); ?></li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p>No comments yet.</p>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No posts yet.</p>
        <?php endif; ?>
    </section>


</div>

</body>
</html>



