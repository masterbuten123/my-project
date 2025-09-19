<?php
include('config/dbcon.php');

$account_id = $_GET['account_id'] ?? 1; // fallback for testing

// ===================== GET PLAYLIST =====================
if(isset($_GET['get'])){
    $stmt = $con->prepare("SELECT id AS playlist_id, title, recording_path, cover FROM user_playlists WHERE account_id=? ORDER BY created_at DESC");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $playlist = [];

    while($row = $res->fetch_assoc()){
        $playlist[] = [
            'playlist_id' => $row['playlist_id'],
            'title' => $row['title'],
            'recording_path' => 'uploads/audio/' . $row['recording_path'], // <-- prepend folder path
            'cover' => $row['cover'] ?: 'uploads/covers/default.jpg'
        ];
    }

    echo json_encode(['status'=>'success','playlist'=>$playlist]);
    exit;
}

// ===================== ADD TO PLAYLIST =====================
if (isset($_POST['add'])) {
    $recording_id = intval($_POST['recording_id'] ?? 0);
    if (!$recording_id) { 
        echo json_encode(['status'=>'error','message'=>'Invalid recording']);
        exit;
    }

    // Check if already in playlist
    $check = $con->prepare("SELECT id FROM user_playlists WHERE account_id=? AND recording_id=?");
    $check->bind_param("ii", $account_id, $recording_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows == 0) {
        // Fetch recording info first
        $stmt = $con->prepare("SELECT title, recording_path, cover FROM artist_recordings WHERE id=?");
        $stmt->bind_param("i", $recording_id);
        $stmt->execute();
        $song = $stmt->get_result()->fetch_assoc();

        // Insert into user_playlists with full info
        $insert = $con->prepare("INSERT INTO user_playlists (account_id, recording_id, title, recording_path, cover) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("iisss", $account_id, $recording_id, $song['title'], $song['recording_path'], $song['cover']);
        $insert->execute();

        // Prepare song info for JS
        $song['playlist_id'] = $insert->insert_id;
        $song['recording_path'] = 'uploads/audio/' . $song['recording_path']; // <-- prepend folder path
        $song['cover'] = $song['cover'] ?: 'uploads/covers/default.jpg';

        echo json_encode(['status'=>'success','message'=>'Added to playlist','song'=>$song]);
    } else {
        echo json_encode(['status'=>'info','message'=>'Already in playlist']);
    }
    exit;
}

// ===================== REMOVE FROM PLAYLIST =====================
if (isset($_POST['remove'])) {
    $playlist_id = intval($_POST['playlist_id'] ?? 0);
    $delete = $con->prepare("DELETE FROM user_playlists WHERE id=? AND account_id=?");
    $delete->bind_param("ii", $playlist_id, $account_id);
    $delete->execute();
    echo json_encode(['status'=>'success','message'=>'Removed from playlist']);
    exit;
}

// ===================== ALWAYS RETURN PLAYLIST =====================
$sql = "SELECT up.id AS playlist_id, ar.id AS recording_id, ar.title, ar.recording_path, ar.cover
        FROM user_playlists up
        JOIN artist_recordings ar ON up.recording_id = ar.id
        WHERE up.account_id = ?
        ORDER BY up.created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$res = $stmt->get_result();

$playlist = [];
while($row = $res->fetch_assoc()){
    $playlist[] = [
        'playlist_id' => $row['playlist_id'],
        'recording_id' => $row['recording_id'],
        'title' => $row['title'],
        'recording_path' => 'uploads/audio/' . $row['recording_path'], // <-- prepend folder
        'cover' => $row['cover'] ?: 'uploads/covers/default.jpg'
    ];
}

echo json_encode(['status'=>'success','playlist'=>$playlist]);
exit;
