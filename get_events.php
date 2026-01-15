<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit();
}

$conn = new mysqli("localhost", "root", "", "mydb");
if ($conn->connect_error) {
    http_response_code(500);
    exit();
}

$user_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? '';

if (!$date) {
    echo json_encode([]);
    exit();
}

$stmt = $conn->prepare(
    "SELECT event_time, event_text
     FROM calendar_events
     WHERE user_id = ? AND event_date = ?
     ORDER BY event_time"
);

$stmt->bind_param("is", $user_id, $date);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

$stmt->close();
$conn->close();

header("Content-Type: application/json");
echo json_encode($events);
