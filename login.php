<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "mydb";

// Admin credentials
$adminEmail = "admin@gmail.com"; // changed to match your request
$adminPassword = "admin123";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        header("Location: ../web-pages/login.html?error=" . urlencode("Please enter email and password."));
        exit();
    }

    // Admin login (special case)
    if ($email === $adminEmail && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: users.php");
        exit();
    }

    // Fetch user info from database
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header("Location: ../web-pages/login.html?error=" . urlencode("The email address is not registered."));
        exit();
    }

    // Plain-text password check
    if ($password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_email'] = $email;

        header("Location: ../web-pages/members.html");
        exit();
    } else {
        header("Location: ../web-pages/login.html?error=" . urlencode("Incorrect password. Please try again."));
        exit();
    }
}

$conn->close();
?>
