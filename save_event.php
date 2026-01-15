<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Not logged in");
}

$conn = new mysqli("localhost", "root", "", "mydb");
if ($conn->connect_error) {
    http_response_code(500);
    exit("DB error");
}

$user_id    = $_SESSION['user_id'];
$event_date = $_POST['event_date'] ?? '';
$event_time = $_POST['event_time'] ?? '';
$event_text = $_POST['event_text'] ?? '';

if ($event_date && $event_time) {

    $stmt = $conn->prepare(
        "SELECT id FROM calendar_events
         WHERE user_id=? AND event_date=? AND event_time=?"
    );
    $stmt->bind_param("iss", $user_id, $event_date, $event_time);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $up = $conn->prepare(
            "UPDATE calendar_events SET event_text=? WHERE id=?"
        );
        $up->bind_param("si", $event_text, $row['id']);
        $up->execute();
        $up->close();
    } else {
        $in = $conn->prepare(
            "INSERT INTO calendar_events (user_id,event_date,event_time,event_text)
             VALUES (?,?,?,?)"
        );
        $in->bind_param("isss", $user_id, $event_date, $event_time, $event_text);
        $in->execute();
        $in->close();
    }
}

$conn->close();
echo "Saved";
