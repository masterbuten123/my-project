<?php
// ---------------------------
// MySQL Database Config
// ---------------------------

$host = "127.0.0.1";           // safer than localhost on Windows
$username = "root";            // MySQL username
$password = "";                // MySQL password (empty if default XAMPP)
$database = "thesis_b";        // MySQL database name
$port = 3307;                  // IMPORTANT: your MySQL is running on 3307

// Create connection
$con = mysqli_connect($host, $username, $password, $database, $port);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// ✅ Set PHP timezone
date_default_timezone_set('Asia/Manila');

// ✅ Set MySQL timezone for this session
mysqli_query($con, "SET time_zone = '+08:00'");
?>
