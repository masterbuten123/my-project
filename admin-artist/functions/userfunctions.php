<?php

session_start();
include('config/dbcon.php');

// Ensure the session is set and user is authenticated before running queries
function checkAuthUser() {
    if (!isset($_SESSION['auth_user'])) {
        // Redirect to login if the user is not authenticated
        redirect('login.php', 'Please log in first.');
    }
}

// Get all information for a user
function getAllInfo($table) {
    global $con;
    checkAuthUser(); // Ensure the user is logged in
    
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM $table WHERE id='$userId'";
    return mysqli_query($con, $query); // No need for exit here
}

// Get all active items from the given table
function getAllActive($categories) {
    global $con;
    $query = "SELECT * FROM $categories WHERE popular = '1'";
    return mysqli_query($con, $query); // No need for exit here
}

// Get all trending products
function getAllTrending() {
    global $con;
    $query = "SELECT * FROM products WHERE trending = '1'";
    return mysqli_query($con, $query); // No need for exit here
}

// Get a specific item by slug
function getSlugActive($table, $slug) {
    global $con;
    $query = "SELECT * FROM $table WHERE slug = '$slug' AND status = '0' LIMIT 1";
    return mysqli_query($con, $query); // No need for exit here
}

// Get products by category
function getProdByCategory($category_id) {
    global $con;
    $query = "SELECT * FROM products WHERE category_id = '$category_id' AND status = '0'";
    return mysqli_query($con, $query); // No need for exit here
}

// Get item by ID from any table
function getIDActive($table, $id) {
    global $con;
    $query = "SELECT * FROM $table WHERE id = '$id' AND status = '0'";
    return mysqli_query($con, $query); // No need for exit here
}

// Get items in the cart for the current user
function getCartItems() {
    global $con; // Use $con instead of $conn for consistency
    checkAuthUser(); // Ensure the user is logged in

    $user_id = $_SESSION['auth_user']['user_id']; // Use the correct session key

    $query = "SELECT * FROM cart_items WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $query);

    if (!$result) {
        die('Query failed: ' . mysqli_error($con));
    }

    return $result;
}

// Get all orders for the current user
function getOrders() {
    global $con;
    checkAuthUser(); // Ensure the user is logged in
    
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE user_id='$userId' AND status='0' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

// Get confirmed orders for the current user
function getConfirmedOrders() {
    global $con;
    checkAuthUser(); // Ensure the user is logged in
    
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE user_id='$userId' AND status='1' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

// Get completed orders for the current user
function getCompletedOrders() {
    global $con;
    checkAuthUser(); // Ensure the user is logged in
    
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE user_id='$userId' AND status='2' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

// Get canceled orders for the current user
function getCanceledOrders() {
    global $con;
    checkAuthUser(); // Ensure the user is logged in
    
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE user_id='$userId' AND status='3' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

// Check if tracking number is valid for the current user
function checkTrackingNoValid($trackingNo) {
    global $con;
    checkAuthUser(); // Ensure the user is logged in
    
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM orders WHERE tracking_no='$trackingNo' AND user_id='$userId'";
    return mysqli_query($con, $query);
}

// Get a user by ID from any table
function getByID($table, $userId) {
    global $con;
    $query = "SELECT * FROM $table WHERE id = '$userId'";
    return mysqli_query($con, $query); // No need for exit here
}

// Redirect with a message
function redirect($url, $message) {
    $_SESSION['message'] = $message;
    header('Location: '.$url);
    exit();
}

?>
