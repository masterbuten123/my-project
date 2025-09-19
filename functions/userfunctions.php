<?php
ob_start();
include('config/dbcon.php');

// Ensure the session is set and user is authenticated before running queries
function checkAuthUser() {
    if (!isset($_SESSION['auth_user'])) {
        redirect('login.php', 'Please log in first.');
    }
}

// Get all information for a logged-in user
function getAllInfo($table) {
    global $con;
    checkAuthUser();
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM $table WHERE id='$userId'";
    return mysqli_query($con, $query);
}

// Get all popular items from a given table
function getAllActive($table) {
    global $con;
    $query = "SELECT * FROM $table WHERE popular = '1'";
    return mysqli_query($con, $query);
}

// Get trending products
function getAllTrending() {
    global $con;
    $query = "SELECT * FROM products WHERE status = '1'";
    return mysqli_query($con, $query);
}

// Get specific item by slug
function getSlugActive($table, $slug) {
    global $con;
    $query = "SELECT * FROM $table WHERE slug = '$slug' AND status = '0' LIMIT 1";
    return mysqli_query($con, $query);
}

// Get products by category
function getProdByCategory($category_id) {
    global $con;
    $query = "SELECT * FROM products WHERE category_id = '$category_id' AND status = '0'";
    return mysqli_query($con, $query);
}

// Get item by ID
function getIDActive($table, $id) {
    global $con;
    $query = "SELECT * FROM $table WHERE id = '$id' AND status = '0'";
    return mysqli_query($con, $query);
}

// Get cart items for current user
function getCartItems() {
    global $con;
    checkAuthUser();
    $user_id = $_SESSION['auth_user']['user_id'];

    $query = "SELECT * FROM cart_items WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $query);
    if (!$result) {
        die('Query failed: ' . mysqli_error($con));
    }
    return $result;
}

// Orders
function getOrders() {
    global $con;
    checkAuthUser();
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE user_id='$userId' AND status='0' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

function getConfirmedOrders() {
    global $con;
    checkAuthUser();
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE user_id='$userId' AND status='1' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

function getCompletedOrders() {
    global $con;
    checkAuthUser();
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE user_id='$userId' AND status='2' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

function getCanceledOrders() {
    global $con;
    checkAuthUser();
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE user_id='$userId' AND status='3' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

function checkTrackingNoValid($trackingNo) {
    global $con;
    checkAuthUser();
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE tracking_no='$trackingNo' AND user_id='$userId'";
    return mysqli_query($con, $query);
}

function getByID($table, $userId) {
    global $con;
    $query = "SELECT * FROM $table WHERE id = '$userId'";
    return mysqli_query($con, $query);
}

/** =====================
 * ARTIST-RELATED FUNCTIONS
 * ===================== */
function getAllArtists() {
    global $con;

    // Ensure the audio folder exists
    $audioFolder = __DIR__ . '/uploads/audio';
    if (!is_dir($audioFolder)) {
        mkdir($audioFolder, 0777, true);
    }

    // Fetch only artists from accounts
    $query = "SELECT * FROM accounts WHERE role ='artist' AND status='active'";
    $result = mysqli_query($con, $query);

    $artists = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $artists[] = $row;
        }
    }
    return $artists;
}

function getTopArtists() {
    global $con;
    $query = "SELECT * FROM accounts WHERE role='artist' AND status='active'";
    return mysqli_query($con, $query);
}

function getSongsByArtist($artist_id) {
    global $con;
    $query = "SELECT * FROM songs WHERE artist_id = '$artist_id' AND status = 1";
    return mysqli_query($con, $query);
}

ob_flush();
?>
