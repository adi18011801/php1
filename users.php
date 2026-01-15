<?php
session_start();

// Protect the page
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// DB connection
$host = 'localhost';
$db = 'mydb'; // <-- use your actual database name here
$user = 'root'; 
$pass = '';    

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: users.php");
    exit();
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $id = (int)$_POST['id'];
    $new_pass = $_POST['new_password'];
    $conn->query("UPDATE users SET password = '$new_pass' WHERE id = $id");
    header("Location: users.php");
    exit();
}

// Get all users
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head><title>User Management</title></head>
<body>
    <h2>User Management</h2>
    <table border="1" cellpadding="8">
        <tr><th>ID</th><th>Email</th><th>Password</th><th>Actions</th></tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['password']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="text" name="new_password" placeholder="New password" required>
                    <button type="submit" name="update_password">Update</button>
                </form>
                <a href="users.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>
