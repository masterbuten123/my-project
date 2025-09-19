<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/dbcon.php');

/**
 * Get current logged-in user's full info from accounts table
 */
function getAllPosts($con, $limit = 20) {
    $sql = "SELECT * FROM posts ORDER BY created_at DESC LIMIT ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result();
}

// Check if account has an active subscription
function isPremium($con, $account_id) {
    $stmt = $con->prepare("
        SELECT s.*, p.name AS plan_name 
        FROM subscriptions s 
        JOIN subscription_plans p ON s.plan_id = p.plan_id 
        WHERE s.account_id = ? AND s.status = 'active'
        ORDER BY s.end_date DESC LIMIT 1
    ");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result ?: false; // returns false if no active subscription
}

// Example: check if user is premium
function getPremiumType($con, $account_id) {
    $subscription = isPremium($con, $account_id);
    if(!$subscription) return 'free';
    
    return strtolower($subscription['plan_name']); // e.g., 'premium', 'pro'
}

/**
 * Get human-readable role name from role string
 */
function getRoleName($role)
{
    $roles = [
        'user' => 'User',
        'admin' => 'Superadmin',
        'artist' => 'Artist'
    ];
    return $roles[$role] ?? 'Unknown';
}

/**
 * Get all accounts excluding normal users and excluding current logged-in user
 */
function getAllAdminsAndArtists()
{
    global $con;

    if (!isset($_SESSION['auth_user']['account_id'])) {
        return [];
    }

    $currentId = $_SESSION['auth_user']['account_id'];

    // role != 'user' AND account_id != current user
    $stmt = $con->prepare("SELECT * FROM accounts WHERE role != 'user' AND account_id != ?");
    $stmt->bind_param("i", $currentId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all records from any table (use carefully)
 */
function getAll($table)
{
    global $con;

    // sanitize table name (allow only letters, numbers, underscore)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        return false;
    }
    $query = "SELECT * FROM $table";
    $result = mysqli_query($con, $query);

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function getArtistByIdd($artist_id) {
    global $con;

    $sql = "SELECT
                a.account_id,
                a.name,
                a.email,
                a.phone,
                ui.address,
                a.status,
                a.image,
                ui.gender,
                ui.location,
                ui.price_per_hour,
                ui.cover_image,
                ui.bio,
                ui.genre
            FROM accounts a
            INNER JOIN user_information ui ON ui.account_id = a.account_id
            WHERE a.role = 'artist' AND a.account_id = '".mysqli_real_escape_string($con, $artist_id)."'
            LIMIT 1";

    $res = mysqli_query($con, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        return mysqli_fetch_assoc($res); // single row
    }
    return null;
}
/**
 * Get all artists with extra details from user_information
 */
function getAllArtists() {
    global $con;

    $sql = "SELECT
                a.account_id,
                a.name,
                a.email,
                a.phone,
                ui.address,
                a.status,
                a.image,
                ui.gender,
                ui.location,
                ui.price_per_hour,
                ui.cover_image,
                ui.bio,
                ui.genre
            FROM accounts a
            INNER JOIN user_information ui ON ui.account_id = a.account_id
            WHERE a.role = 'artist'
            ORDER BY a.created_at DESC";

    $res = mysqli_query($con, $sql);
    $artists = [];
    if ($res && mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $artists[] = $row;
        }
    }
    return $artists;
}
function getCurrentSubscription($con, $accountId) {
    $sql = "SELECT sp.plan_id, sp.name, sp.price, sp.duration_days, 
                   s.start_date, s.end_date, s.status
            FROM subscriptions s
            JOIN subscription_plans sp ON s.plan_id = sp.plan_id
            WHERE s.account_id = ? AND s.status = 'active'
            ORDER BY s.end_date DESC 
            LIMIT 1";

    $stmt = $con->prepare($sql);
    if (!$stmt) {
        return null; // query failed
    }
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}
function fetchPlaylist($con, $account_id) {
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
            'recording_path' => 'uploads/audio/' . $row['recording_path'],
            'cover' => $row['cover'] ?: 'uploads/covers/default.jpg'
        ];
    }
    return $playlist;
}

function getRecordingsByArtist($artist_id)
 {
    global $con;

    $sql = "SELECT id, account_id, title, recording_path, cover, created_at
            FROM artist_recordings
            WHERE account_id = ? AND status='active'
            ORDER BY created_at DESC";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $artist_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getUserPlaylist($account_id, $con) {
    $playlist = [];
    $query = mysqli_query($con, "SELECT * FROM user_playlists WHERE account_id = '$account_id' ORDER BY created_at DESC");
    while ($row = mysqli_fetch_assoc($query)) {
        $playlist[] = $row;
    }
    return $playlist; // returns an array of rows
}


function getAllRecordings() {
    global $con;

    $sql = "SELECT id AS recording_id, account_id, title, recording_path, created_at
            FROM artist_recordings
            WHERE status='active'
            ORDER BY created_at DESC";

    $result = mysqli_query($con, $sql);
    $allRecordings = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $allRecordings[$row['account_id']][] = $row; // group by account_id
        }
    }

    return $allRecordings;
}


/**
 * Get record by ID from accounts table
 */
function getAccountById($account_id)
{
    global $con;

    $stmt = $con->prepare("SELECT * FROM accounts WHERE account_id = ? LIMIT 1");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * Get all active accounts (status = 'active')
 */
function getAllActiveAccounts()
{
    global $con;

    $query = "SELECT * FROM accounts WHERE status = 'active'";
    $result = mysqli_query($con, $query);

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Get all active artist accounts
 */
function getAllActiveArtists()
{
    global $con;

    $query = "SELECT * FROM accounts WHERE role = 'artist' AND status = 'active'";
    $result = mysqli_query($con, $query);

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Get orders with status = '0' (pending, for example)
 */
function getAllPendingOrders()
{
    global $con;

    $query = "SELECT * FROM orders WHERE status = '0' ORDER BY id ASC";
    $result = mysqli_query($con, $query);

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Check if a tracking number exists in orders table
 */
function checkTrackingNoValid($trackingNo)
{
    global $con;

    $stmt = $con->prepare("SELECT * FROM orders WHERE tracking_no = ? LIMIT 1");
    $stmt->bind_param("s", $trackingNo);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

/**
 * Get order history with statuses 2 or 3 (completed, cancelled, etc)
 */
function getOrdersHistory()
{
    global $con;

    $query = "SELECT * FROM orders WHERE status IN (2, 3) ORDER BY id DESC";
    $result = mysqli_query($con, $query);

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Get confirmed orders with status = '1'
 */
function getConfirmedOrders()
{
    global $con;

    $query = "SELECT * FROM orders WHERE status = '1' ORDER BY id DESC";
    $result = mysqli_query($con, $query);

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Redirect helper with session message
 */
function redirect($url, $message)
{
    $_SESSION['message'] = $message;
    header("Location: $url");
    exit();
}

/**
 * Get artist by account_id
 */
function getArtistById($account_id)
{
    global $con;

    $stmt = $con->prepare("SELECT * FROM accounts WHERE account_id = ? AND role = 'artist' LIMIT 1");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

/**
 * Get top artists with optional limit
 */
function getTopArtists($limit = 10)
{
    global $con;

    $stmt = $con->prepare("SELECT account_id, name, image, bio, genre FROM accounts WHERE role = 'artist' AND status = 'active' ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get active wallpapers (assuming wallpapers table)
 */
function getActiveWallpapers()
{
    global $con;

    $query = "SELECT * FROM wallpapers WHERE status = 'active'";
    $result = mysqli_query($con, $query);

    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Get latest posts joined with artist info
 */
function getLatestPosts($limit = 10)
{
    global $con;

    $stmt = $con->prepare("
        SELECT p.*, a.name AS artist_name, a.image AS artist_image
        FROM posts p
        JOIN accounts a ON p.account_id = a.account_id
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}
function userHasActiveSubscription($account_id, $con) {
    $today = date('Y-m-d');
    $query = "SELECT * FROM subscriptions WHERE account_id = ? AND status = 'active' AND end_date >= ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('is', $account_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    return ($result->num_rows > 0);
}
function getAllPostsLimited($con, $limit, $offset) {
    $sql = "SELECT * FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

function getPostCount($con) {
    $result = mysqli_query($con, "SELECT COUNT(*) as total FROM posts");
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}


function getActiveSubscription($con, $account_id) {
    $sql = "SELECT s.subscription_id, s.status, s.start_date, s.end_date,
                   p.name AS plan_name, p.price, p.duration_days, p.features
            FROM subscriptions s
            JOIN subscription_plans p ON s.plan_id = p.plan_id
            WHERE s.account_id = ? 
              AND s.status = 'active'
              AND CURDATE() BETWEEN s.start_date AND s.end_date
            LIMIT 1";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}

function subscribeUser($con, $account_id, $plan_id) {
    // Get plan duration
    $sqlPlan = "SELECT duration_days FROM subscription_plans WHERE plan_id = ?";
    $stmtPlan = $con->prepare($sqlPlan);
    $stmtPlan->bind_param("i", $plan_id);
    $stmtPlan->execute();
    $planResult = $stmtPlan->get_result();
    if ($plan = $planResult->fetch_assoc()) {
        $duration = $plan['duration_days'];

        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+$duration days"));
        $status = 'active';

        // Insert subscription record
        $sqlSub = "INSERT INTO subscriptions (account_id, plan_id, start_date, end_date, status, created_at, updated_at)
                   VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmtSub = $con->prepare($sqlSub);
        $stmtSub->bind_param("iisss", $account_id, $plan_id, $startDate, $endDate, $status);
        if ($stmtSub->execute()) {
            return true;
        }
    }
    return false;
}

function authorize($allowed_roles = []) {
    if (!isset($_SESSION['auth_user']['account_id'])) {
        // Not logged in
        header('Location: ../index.php');
        exit();
    }

    $user_role = $_SESSION['auth_user']['role'] ?? '';
    if (!in_array($user_role, $allowed_roles)) {
        // Not authorized
        echo "<h3 class='text-center text-danger mt-5'>Access Denied: You do not have permission to view this page.</h3>";
        exit();
    }
}


function updateSubscriptionStatus($con, $subscription_id, $new_status) {
    $allowed_statuses = ['active', 'expired', 'cancelled'];
    if (!in_array($new_status, $allowed_statuses)) {
        return false;
    }

    $sql = "UPDATE subscriptions SET status = ?, updated_at = NOW() WHERE subscription_id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("si", $new_status, $subscription_id);
    return $stmt->execute();
}

function getAllMessages($con) {
    $query = "SELECT m.*, s.name AS sender_name, r.name AS receiver_name 
              FROM messages m
              JOIN accounts s ON m.sender_id = s.account_id
              JOIN accounts r ON m.receiver_id = r.account_id
              ORDER BY m.created_at DESC";
    return mysqli_query($con, $query);
}

function getUserById($con, $account_id) {
    $stmt = $con->prepare("SELECT * FROM accounts WHERE account_id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}


?>









