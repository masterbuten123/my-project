<?php
// Check if the session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('../config/dbcon.php');

// Function to fetch all wallpapers from the database
function softDeleteWallpaper($wallpaper_id)
{
    global $con;

    // Set the status to 1 (deleted) for the specified wallpaper
    $query = "UPDATE wallpapers SET status = 1 WHERE id = '$wallpaper_id'";

    if (mysqli_query($con, $query)) {
        // Successfully updated the status to 'deleted'
        return true;
    } else {
        // Query failed
        return false;
    }
}


function getAllWallpapers()
{
    global $con;
    $query = "SELECT * FROM wallpapers ORDER BY created_at DESC";
    return mysqli_query($con, $query);
}

function getAllInfo($table)
{
    global $con;
    $userId = $_SESSION['auth_user']['user_id'];
    $query = "SELECT * FROM $table WHERE id='$userId'";
    return mysqli_query($con, $query);
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
    $userId = $_SESSION['auth_user']['user_id'];
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
    $query = "SELECT * FROM $table WHERE status = '0'";
    return mysqli_query($con, $query);
}

function getAllOrders() {
    global $con;

    $query = "
        SELECT o.*, 
               u.name AS account_name
        FROM orders o
        JOIN accounts u ON o.account_id = u.account_id
        ORDER BY o.created_at DESC
    ";

    $result = mysqli_query($con, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($con));
    }

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Optionally format order info or add derived data here
        $orders[] = $row;
    }

    return $orders;
}

function getArtistReports($con, $artist_id) {
    $query = "SELECT r.reason, r.created_at, u.name as reporter 
              FROM reports r 
              LEFT JOIN users u ON r.user_id = u.id
              WHERE r.artist_id = ?
              ORDER BY r.created_at DESC";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $artist_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    return $reports;
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

function getConfirmedOrders()
{
    global $con;
    $query = "SELECT * FROM orders WHERE status='1' ORDER BY id DESC";
    return mysqli_query($con, $query);
}

function redirect($url, $message)
{
    $_SESSION['message'] = $message;
    header('Location: '.$url);
    exit();
}
function getAllArtists($sort = 'date_desc') {
    global $con;

    // --- Determine sorting column and order ---
    switch ($sort) {
        case 'date_asc':  $sort_column = 'a.created_at'; $sort_order = 'ASC'; break;
        case 'date_desc': $sort_column = 'a.created_at'; $sort_order = 'DESC'; break;
        case 'name_asc':  $sort_column = 'a.name';       $sort_order = 'ASC'; break;
        case 'name_desc': $sort_column = 'a.name';       $sort_order = 'DESC'; break;
        default:          $sort_column = 'a.created_at'; $sort_order = 'DESC'; break;
    }

    // Whitelist check for safety
    $allowedColumns = ['a.created_at', 'a.name'];
    $allowedOrders  = ['ASC', 'DESC'];
    if (!in_array($sort_column, $allowedColumns)) $sort_column = 'a.created_at';
    if (!in_array($sort_order, $allowedOrders)) $sort_order = 'DESC';

    // --- Query artist info with user_information ---
    $query = "
        SELECT 
            a.account_id,
            a.name,
            a.email,
            a.phone,
            a.image,
            a.status AS account_status,
            a.created_at AS artist_created_at,
            ui.gender,
            ui.location,
            ui.price_per_hour,
            ui.website,
            ui.facebook,
            ui.instagram,
            ui.youtube,
            ui.tiktok,
            ui.bio,
            ui.genre
        FROM accounts a
        LEFT JOIN user_information ui ON a.account_id = ui.account_id
        WHERE a.role = 'artist'
        ORDER BY $sort_column $sort_order
    ";

    $result = mysqli_query($con, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($con));
    }

    $artists = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Set defaults
        $row['phone'] = $row['phone'] ?? 'N/A';
        $row['image'] = $row['image'] ?? '';
        $row['gender'] = $row['gender'] ?? 'N/A';
        $row['location'] = $row['location'] ?? 'N/A';
        $row['price_per_hour'] = $row['price_per_hour'] ?? 0;
        $row['bio'] = $row['bio'] ?? 'No bio available';
        $row['genre'] = $row['genre'] ?? 'N/A';
        $row['account_status'] = $row['account_status'] ?? 'inactive';

        // --- Fetch all subscriptions for this artist ---
        $subQuery = "
            SELECT 
                s.subscription_id,
                sp.name AS plan_name,
                sp.price AS plan_price,
                sp.duration_days,
                s.start_date,
                s.end_date,
                s.status AS subscription_status
            FROM subscriptions s
            INNER JOIN subscription_plans sp ON s.plan_id = sp.plan_id
            WHERE s.account_id = {$row['account_id']}
            ORDER BY s.start_date DESC
        ";
        $subResult = mysqli_query($con, $subQuery);
        $subscriptions = [];
        if ($subResult) {
            while ($subRow = mysqli_fetch_assoc($subResult)) {
                $subscriptions[] = $subRow;
            }
        }
        $row['subscriptions'] = $subscriptions;

        // --- Optionally get the latest active subscription separately ---
        $activeSub = array_filter($subscriptions, fn($s) => $s['subscription_status'] === 'active');
        $latestActive = !empty($activeSub) ? array_shift($activeSub) : null;
        $row['latest_subscription'] = $latestActive;

        $artists[] = $row;
    }

    return $artists;
}



function get_products() {
    global $con;
    $query = "SELECT * FROM products ORDER BY created_at DESC";
    return mysqli_query($con, $query);
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


function GetAllcategories($categories) {
    global $con; // Using the database connection from config/dbcon.php
    
    // SQL query to fetch all categories
    $query = "SELECT * FROM categories";
    $result = mysqli_query($con, $query);

    // Check if there are any results
    if (mysqli_num_rows($result) > 0) {
        // Fetch the categories into an associative array
        $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
        return $categories;
    } else {
        return []; // Return an empty array if no categories are found
    }
}

function getBookingsByArtist($artistId, $status = null) {
    global $con;
    $query = "SELECT * FROM bookings WHERE artist_id = ?";
    if ($status) {
        $query .= " AND status = ?";
    }
    $stmt = $con->prepare($query);
    if ($status) {
        $stmt->bind_param("is", $artistId, $status);
    } else {
        $stmt->bind_param("i", $artistId);
    }
    $stmt->execute();
    return $stmt->get_result();
}




/**
 * Render bookings table by status
 *
 * @param mysqli $con MySQL connection
 * @param string $status Booking status: 'pending', 'accepted', 'rejected', 'completed'
 */
function renderBookingsTable($con, $status) {
    // Query without role restrictions
    $query = "
        SELECT b.*, 
               u.name AS user_name, 
               a.name AS artist_name
        FROM bookings b
        LEFT JOIN accounts u ON b.account_id = u.account_id
        LEFT JOIN accounts a ON b.artist_id = a.account_id
        WHERE b.status = ?
        ORDER BY b.booking_start DESC
    ";

    $stmt = $con->prepare($query);
    if (!$stmt) {
        echo "Error preparing statement: " . $con->error;
        return;
    }

    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped">';
    echo '<thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Artist</th>
                <th>Booking Start</th>
                <th>Booking End</th>
                <th>Duration (hrs)</th>
                <th>Attendees</th>
                <th>Venue</th>
                <th>Status</th>
            </tr>
          </thead>
          <tbody>';

    if ($result && $result->num_rows > 0) {
        $count = 1;
        while ($row = $result->fetch_assoc()) {
            // Determine badge class
            $badgeClass = match($row['status']) {
                'pending' => 'bg-warning',
                'accepted' => 'bg-info',
                'rejected' => 'bg-danger',
                'completed' => 'bg-success',
                default => 'bg-secondary',
            };

            echo '<tr>
                    <td>'. $count++ .'</td>
                    <td>'. htmlspecialchars($row['user_name'] ?? 'N/A') .'</td>
                    <td>'. htmlspecialchars($row['artist_name'] ?? 'N/A') .'</td>
                    <td>'. htmlspecialchars($row['booking_start']) .'</td>
                    <td>'. htmlspecialchars($row['booking_end']) .'</td>
                    <td>'. htmlspecialchars($row['duration_hours']) .'</td>
                    <td>'. htmlspecialchars($row['attendees']) .'</td>
                    <td>'. htmlspecialchars($row['venue']) .'</td>
                    <td><span class="badge '. $badgeClass .'">'. ucfirst($row['status']) .'</span></td>
                  </tr>';
        }
    } else {
        echo '<tr>
                <td colspan="9" class="text-center">No '. htmlspecialchars($status) .' bookings found.</td>
              </tr>';
    }

    echo '</tbody></table></div>';

    $stmt->close();
}



if (isset($_GET['action']) && $_GET['action'] === 'get_reports' && isset($_GET['artist_id'])) {
    $artist_id = intval($_GET['artist_id']);
    $reports = getArtistReports($con, $artist_id);

    header('Content-Type: application/json');
    echo json_encode($reports);
    exit();
}
?>
