<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// DB config
$host = "localhost";
$username = "root";
$password = "";
$database = "mydb";

// Connect
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function redirect_with_error($msg) {
    header("Location: ../web-pages/signup.html?error=" . urlencode($msg));
    exit();
}

function redirect_to_members() {
    header("Location: ../web-pages/members.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['usrnm'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['psw'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';
    $birthday = trim($_POST['birthday'] ?? '');
    $city = trim($_POST['cities'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Basic validations
    if (!$username || !$email || !$password || !$confirmPassword || !$birthday || !$city || !$phone) {
        $conn->close();
        redirect_with_error("Please fill all required fields.");
    }
    if ($password !== $confirmPassword) {
        $conn->close();
        redirect_with_error("Passwords do not match.");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $conn->close();
        redirect_with_error("Invalid email address.");
    }

    // Phone validation
    $digitsOnly = preg_replace('/\D/', '', $phone);
    if (strlen($digitsOnly) < 7) {
        $conn->close();
        redirect_with_error("Phone number must have at least 7 digits.");
    }
    if (!preg_match("/^[0-9+\-\(\)\s]+$/", $phone)) {
        $conn->close();
        redirect_with_error("Invalid phone number format.");
    }

    // Check existing email
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE email = ?");
    if (!$stmt) {
        $conn->close();
        redirect_with_error("DB prepare error: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        redirect_with_error("Email already registered.");
    }
    $stmt->close();

    // ⚠️ Store password as plain text (NOT recommended for production)
    $plainPassword = $password;

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, birthday, city, phone) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $conn->close();
        redirect_with_error("DB prepare error: " . $conn->error);
    }
    $stmt->bind_param("ssssss", $username, $email, $plainPassword, $birthday, $city, $phone);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();

        // Keep user logged in
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $username;

        redirect_to_members();
    } else {
        $errorMsg = "Error saving user: " . $stmt->error;
        $stmt->close();
        $conn->close();
        redirect_with_error($errorMsg);
    }
} else {
    $conn->close();
    echo "Invalid request method.";
}
