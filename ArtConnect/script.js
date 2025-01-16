// Function to toggle the visibility of the comment section
function toggleComments(imageId) {
    const commentsList = document.getElementById(`comments-list-${imageId}`);
    if (commentsList.style.display === "none" || commentsList.style.display === "") {
        commentsList.style.display = "block"; // Show comments
    } else {
        commentsList.style.display = "none"; // Hide comments
    }
}

// Function to toggle the visibility of the comment form (for posting a comment)
function toggleCommentForm(imageId) {
    const commentForm = document.getElementById(`comment-form-${imageId}`);
    if (commentForm.style.display === "none" || commentForm.style.display === "") {
        commentForm.style.display = "block"; // Show the comment form
    } else {
        commentForm.style.display = "none"; // Hide the comment form
    }
}


