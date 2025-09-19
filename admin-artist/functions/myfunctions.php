<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include('../config/dbcon.php');

function getUserProfile($con, $account_id) {
    $query = "
        SELECT a.account_id, a.name, a.email, a.phone, a.address, 
               a.role, a.status, a.is_verified, a.image, 
               ui.gender, ui.location, ui.price_per_hour, 
               ui.bank_name, ui.bank_account, ui.gcash_number, 
               ui.cover_image, ui.recording, ui.website, 
               ui.facebook, ui.instagram, ui.youtube, ui.tiktok, 
               ui.bio, ui.genre
        FROM accounts a
        LEFT JOIN user_information ui ON a.account_id = ui.account_id
        WHERE a.account_id = ?
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}


function getAllInfo($table)
{
    global $con;

 
    if (!isset($_SESSION['auth_user']['account_id'])) {
        return false;
    }

    $account_id = $_SESSION['auth_user']['account_id'];

    // Make sure we query using the correct column
    if ($table === 'artists') {
        $query = "SELECT * FROM $table WHERE account_id='$account_id'";
    } else {
        // fallback for other table usage, optional
        $query = "SELECT * FROM $table WHERE id='$account_id'";
    }

    $result = mysqli_query($con, $query);

    if (!$result) {
        echo "Query Error: " . mysqli_error($con); // Debug only
    }

    return $result;
}



function getAllProducts() {
    global $con;

    $query = "SELECT * FROM products";
    $result = mysqli_query($con, $query);

    if (!$result) {
        die("Query Failed: " . mysqli_error($con));
    }

    $products = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    return $products;
}

function getRoleName($role_as)
{
    $roles = [
        0 => "User",
        1 => "Superadmin",
        2 => "Artist"
    ];

    return $roles[$role_as] ?? "Unknown";
}

function getAllInfos($table)
{
    global $con;
    $userId = $_SESSION['auth_user']['account_id'];
    $query = "SELECT * FROM $table WHERE role_as != '0' AND id != '$userId'";
    return mysqli_query($con, $query);
}

function getAll($table)
{
    global $con;
    $query = "SELECT * FROM $table";
    return mysqli_query($con, $query);
}

function getByID($table, $id)
{
    global $con;
    $query = "SELECT * FROM $table WHERE id = '$id'";
    return mysqli_query($con, $query);
}

function getAllActive($table)
{
    global $con;
    $query = "SELECT * FROM $table WHERE status = '1'"; // Assuming 1 indicates active
    return mysqli_query($con, $query);
}

function getAllOrders()
{
    global $con;
    $query = "
        SELECT orders.*, users.name AS user_name 
        FROM orders
        INNER JOIN users ON orders.user_id = users.id 
        ORDER BY orders.order_id ASC
    "; // Assuming 'order_id' is the correct column
    return mysqli_query($con, $query);
}

function checkTrackingNoValid($trackingNo)
{
    global $con;
    $query = "SELECT * FROM orders WHERE tracking_no='$trackingNo'";
    return mysqli_query($con, $query);
}

function getOrdersHistory()
{
    global $con;
    $query = "SELECT * FROM orders WHERE status IN(2,3) ORDER BY id DESC";
    return mysqli_query($con, $query);
}
function getConfirmedOrders() {
    global $con;
    $query = "
        SELECT * 
        FROM orders
        WHERE status = 'processing' 
        ORDER BY order_id ASC
    ";
    return mysqli_query($con, $query);
}


function redirect($url, $message)
{
    $_SESSION['message'] = $message;
    header('Location: '.$url);
    exit();
}

function getAllCategories()
{
    global $con;
    $query = "SELECT * FROM categories WHERE status = '1'";
    return mysqli_query($con, $query);
}

function getBookingsByArtist($artistId) {
    global $con;
    $query = "
        SELECT 
            b.booking_id,
            b.booking_start,
            b.booking_end,
            b.event_type,
            b.duration_hours,
            b.attendees,
            b.payment_status,
            b.venue,
            b.additional_notes,
            b.status,
            b.phone,
            b.total_price,
            b.created_at,
            b.updated_at,
            a.role
        FROM bookings b
        JOIN accounts a ON b.artist_id = a.account_id
        WHERE b.artist_id = ?
        ORDER BY b.created_at DESC
    ";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $artistId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
}


// functions/notification_functions.php

function getArtistNotifications($con, $artistId) {
    // Fetch all notifications for the artist
    $notifQuery = $con->prepare("SELECT * FROM notifications WHERE artist_id = ? ORDER BY created_at DESC");
    $notifQuery->bind_param("i", $artistId);
    $notifQuery->execute();
    $notifications = $notifQuery->get_result()->fetch_all(MYSQLI_ASSOC);

    // Count unread notifications
    $unreadCountQuery = $con->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE artist_id = ? AND is_read = '0'");
    $unreadCountQuery->bind_param("i", $artistId);
    $unreadCountQuery->execute();
    $unreadCount = $unreadCountQuery->get_result()->fetch_assoc()['unread_count'];

    return [
        'notifications' => $notifications,
        'unread_count' => $unreadCount
    ];
}


function updateBookingStatus($bookingId, $newStatus, $artistId) {
    global $con;

    $query = "UPDATE bookings SET status = ? WHERE booking_id = ? AND account_id = ?";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sii", $newStatus, $bookingId, $artistId);
        return mysqli_stmt_execute($stmt);
    }

    return false;
}


// Fetch products for a specific artist with category name
function get_artist_products($con, $account_id, $sort_by = 'name', $sort_order = 'ASC') {
    // Validate sort column
    $sort_column = ($sort_by === 'price') ? 'price' : (($sort_by === 'quantity') ? 'stock' : 'name');
    $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

    // Prepare statement
    $stmt = mysqli_prepare($con, "SELECT p.*, c.name AS category_name 
                                 FROM products p 
                                 LEFT JOIN categories c ON p.category_id = c.category_id
                                 WHERE p.account_id = ? 
                                 ORDER BY $sort_column $sort_order");
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "i", $account_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    return $products;
}

// Fetch all active categories
function get_active_categories($con) {
    $query = "SELECT category_id, name FROM categories WHERE status='active' ORDER BY name ASC";
    $result = mysqli_query($con, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


// Get Orders for the specific artist
function getArtistOrders($account_id) {
    global $con;

    $query = "
        SELECT DISTINCT 
    o.order_id,
    a.name AS user_name,
    o.total_amount,
    o.payment_status,
    o.order_status,
    o.created_at
FROM orders o
JOIN order_items oi ON oi.order_id = o.order_id
JOIN products p ON oi.product_id = p.product_id
JOIN accounts a ON o.account_id = a.account_id
WHERE p.account_id = ?
ORDER BY o.created_at DESC
    ";

    $stmt = mysqli_prepare($con, $query);
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, "i", $account_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        $orders = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
        return $orders;
    } else {
        die("Query Failed: " . mysqli_error($con));
    }
}




function getArtistById($id) {
    global $con;
    $query = "SELECT * FROM artists WHERE account_id = '$id' LIMIT 1";  // Changed from 'id' to 'artist_id'
    $result = mysqli_query($con, $query);
    return mysqli_fetch_assoc($result);
}




?>
