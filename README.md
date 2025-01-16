# ArtConnect

**ArtConnect** is an online platform where artists can publish their artwork and fans can engage with it. Artists can upload images of their work, receive likes, comments, and interact with fans. Fans can follow their favorite artists, view posts, and comment on artworks they like.

## Features

- **User Authentication:** Secure login system for artists and fans.
- **Profile Management:** Users can view and update their profile.
- **Artwork Upload:** Artists can upload images of their work for others to view.
- **Like and Comment System:** Users can like and comment on artworks they like.
- **Follow System:** Fans can follow their favorite artists to stay updated on their latest posts.
- **Interactive Dashboard:** The main dashboard allows users to view posts from artists they follow, comment, and interact with the content.

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Version Control:** Git, GitHub

## Installation

### Prerequisites

1. Install **XAMPP** or **MAMP** for local development to set up a PHP environment with MySQL.
2. Install **Git** to manage the project and version control.

### Steps to Run Locally

1. Clone the repository:
   ```bash
   git clone https://github.com/AlgoriousAlhammak/ArtConnect.git
   cd ArtConnect
2. Set up the database:

    1- Create a MySQL database (e.g., artconnect).
    2- create your own tables for users, images, comments, likes, and followers.
3. Configure the database connection:
     1- Open the db_connection file and update the database credentials
   ```bash
   $conn = new mysqli($servername, $username, $password, $dbname);

4. Run XAMPP or MAMP and start the Apache and MySQL services.
5. User Authentication
   
   1- Login Page: Users can log in using the login page (login.php).
   2- If a user is not logged in, they will be redirected to the login page.

7. Dashboard Features
   
   1- Posts from Artists You Follow: On the dashboard, you can see posts (images) from artists that you follow.
   2- Like and Comment on Posts: Fans can like and comment on artworks they enjoy.
   3- Follow/Unfollow Artists: Fans can follow or unfollow artists directly from the dashboard.
   
8. Project Structure
   
  index.php: Main dashboard view.
  
  upload.php: Page to upload images/artwork.
  
  profile.php: View user profile and details.
  
  login.php: Login page for users.
  
  logout.php: Log out functionality.
  
  comment.php: Handle comments on artwork.
  
  like.php: Handle likes on artwork.
  
  follow.php: Handle following artists.
  
  unfollow.php: Handle unfollowing artists.
  
  db_connection.php: Database connection setup.
  
  DashStyles.css: Stylesheet for the dashboard page.
  
  script.js: JavaScript for frontend interactivity.
