<?php
include('functions/myfunctions.php');  // Include your functions

// Check if the action and order_id are passed
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $action = $_GET['action'];
    $order_id = $_GET['order_id'];

    // Validate action and order_id
    if ($action == 'accept' || $action == 'decline') {
        // Set the appropriate status
        $new_status = ($action == 'accept') ? 'processing' : 'canceled';

        // Update the order status in the database
        $query = "UPDATE orders SET status = '$new_status' WHERE order_id = $order_id";

        if (mysqli_query($con, $query)) {
            // Success, redirect back to orders page
            header("Location: all-orders.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($con);
        }
    } else {
        echo "Invalid action!";
    }
} else {
    echo "Invalid parameters!";
}
?>
