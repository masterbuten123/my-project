<?php
session_start();
include('../config/dbcon.php'); // Adjust path if needed

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['auth_user']['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit();
}

$account_id = $_SESSION['auth_user']['account_id'];

// Helper: error response
function errorResponse($msg, $debug = null) {
    $resp = ['success' => false, 'message' => $msg];
    if ($debug) $resp['debug'] = $debug;
    echo json_encode($resp);
    exit();
}

// Fetch current user
$stmt = $con->prepare("
    SELECT a.account_id, a.name, a.email, a.phone, ui.address, a.image
    FROM accounts a
    LEFT JOIN user_information ui ON a.account_id = ui.account_id
    WHERE a.account_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    errorResponse('User not found.');
}

try {
    // UPDATE PROFILE ONLY
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $image_to_save = $user['image'];

        if ($name === '' || $phone === '' || $address === '') {
            errorResponse('All fields are required.');
        }

        // Handle profile image upload
if (!empty($_FILES['image']['name'])) {
    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif'];

    if (!in_array($ext, $allowed)) {
        errorResponse('Invalid image type. Allowed: jpg, jpeg, png, gif.');
    }

    // Generate a unique filename
    $new_filename = uniqid('profile_', true) . '.' . $ext;
    $upload_path = "../uploads/profiles/" . $new_filename;

    if (!move_uploaded_file($tmp, $upload_path)) {
        errorResponse('Failed to upload image.');
    }

    $image_to_save = "uploads/profiles/" . $new_filename;
}

        // Update accounts table
        $stmt = $con->prepare("UPDATE accounts SET name=?, phone=?, image=? WHERE account_id=?");
        $stmt->bind_param("sssi", $name, $phone, $image_to_save, $account_id);
        if (!$stmt->execute()) {
            errorResponse('Failed to update profile.', $stmt->error);
        }

        // Update or insert address in user_information table
        $stmt = $con->prepare("SELECT account_id FROM user_information WHERE account_id=?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;

        if ($exists) {
            $stmt = $con->prepare("UPDATE user_information SET address=? WHERE account_id=?");
            $stmt->bind_param("si", $address, $account_id);
        } else {
            $stmt = $con->prepare("INSERT INTO user_information (account_id, address) VALUES (?, ?)");
            $stmt->bind_param("is", $account_id, $address);
        }

        if (!$stmt->execute()) {
            errorResponse('Failed to update address.', $stmt->error);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'image' => $image_to_save
        ]);
        exit();
    }

} catch (Exception $e) {
    errorResponse('Exception occurred.', $e->getMessage());
}

// Default response if nothing matched
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
