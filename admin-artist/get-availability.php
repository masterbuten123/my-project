<?php
include('functions/myfunctions.php');
header('Content-Type: application/json');

// Check if it's a GET or POST request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request: Fetch availability

    // Get artist ID from session
    $artistId = $_SESSION['auth_user']['user_id'];

    // Fetch availability from the database
    $query = "SELECT * FROM artist_availability WHERE artist_id = $artistId";
    $result = mysqli_query($conn, $query);

    $availability = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $availability[] = [
            'title' => $row['availability'],
            'start' => $row['date'],
            'end' => $row['date'],
            'allDay' => true
        ];
    }

    echo json_encode($availability);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request: Update availability

    // Get posted data
    $data = json_decode(file_get_contents('php://input'), true);

    // Get artist ID and availability details
    $artistId = $data['artist_id'];
    $date = $data['date'];
    $availability = $data['availability'];

    // Insert or update the availability for the selected date
    $query = "INSERT INTO artist_availability (artist_id, date, availability) 
              VALUES ($artistId, '$date', '$availability')
              ON DUPLICATE KEY UPDATE availability = '$availability'";

    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    // Invalid request method
    echo json_encode(['error' => 'Invalid request method. Use GET or POST.']);
}
?>
