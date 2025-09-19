<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../config/dbcon.php'); // Include the DB connection
header('Content-Type: application/json');

// Block non-artists from sending messages
if (!isset($_SESSION['auth']) || !isset($_SESSION['role_as']) || $_SESSION['role_as'] !== 'artist') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// 1. Get messages for a specific user by email
if (isset($_GET['user_email'])) {
    $userEmail = mysqli_real_escape_string($con, $_GET['user_email']);
    $query = "SELECT * FROM messages WHERE email = '$userEmail' ORDER BY timestamp ASC";
    $result = mysqli_query($con, $query);
    $messages = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'sender' => $row['sender'],
            'message' => $row['message'],
            'timestamp' => $row['timestamp']
        ];
    }

    echo json_encode($messages);
    exit;
}

// 2. Get list of artist emails for admin
if (isset($_GET['user_list'])) {
    $query = "SELECT DISTINCT email FROM messages WHERE sender = 'artist' ORDER BY email ASC";
    $result = mysqli_query($con, $query);
    $emails = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $emails[] = $row['email'];
    }

    echo json_encode($emails);
    exit;
}

// 3. Post admin reply to artist chat
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['message']) && isset($input['email'])) {
        $message = mysqli_real_escape_string($con, $input['message']);
        $userEmail = mysqli_real_escape_string($con, $input['email']);

        $query = "INSERT INTO messages (sender, email, message, timestamp, status) 
                  VALUES ('admin', '$userEmail', '$message', NOW(), 'unread')";
        
        $result = mysqli_query($con, $query);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
    exit;
}
?>
