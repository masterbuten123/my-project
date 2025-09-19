<?php
include('../config/dbcon.php');

header('Content-Type: application/json');

// Get total users
$userQuery = "SELECT COUNT(*) as total_users FROM users";
$userResult = mysqli_query($con, $userQuery);
$totalUsers = mysqli_fetch_assoc($userResult)['total_users'];

// Get total bookings
$bookingQuery = "SELECT COUNT(*) as total_bookings FROM bookings";
$bookingResult = mysqli_query($con, $bookingQuery);
$totalBookings = mysqli_fetch_assoc($bookingResult)['total_bookings'];

// Get total products
$productQuery = "SELECT COUNT(*) as total_products FROM products";
$productResult = mysqli_query($con, $productQuery);
$totalProducts = mysqli_fetch_assoc($productResult)['total_products'];

// Weekly sales (last 7 days)
$weeklySalesQuery = "SELECT SUM(total_price) as weekly_sales 
                     FROM bookings 
                     WHERE booking_date >= CURDATE() - INTERVAL 7 DAY";
$weeklySalesResult = mysqli_query($con, $weeklySalesQuery);
$weeklySales = mysqli_fetch_assoc($weeklySalesResult)['weekly_sales'] ?? 0.00;

// Monthly sales (last 30 days)
$monthlySalesQuery = "SELECT SUM(total_price) as monthly_sales 
                      FROM bookings 
                      WHERE booking_date >= CURDATE() - INTERVAL 30 DAY";
$monthlySalesResult = mysqli_query($con, $monthlySalesQuery);
$monthlySales = mysqli_fetch_assoc($monthlySalesResult)['monthly_sales'] ?? 0.00;

// User trend (last 7 days)
$userTrendQuery = "
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM users
    WHERE created_at >= CURDATE() - INTERVAL 7 DAY
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$userTrendResult = mysqli_query($con, $userTrendQuery);
$userTrend = [];
while ($row = mysqli_fetch_assoc($userTrendResult)) {
    $userTrend[$row['date']] = $row['count'];
}

// Booking trend (last 7 days)
$bookingTrendQuery = "
    SELECT DATE(booking_date) as date, COUNT(*) as count
    FROM bookings
    WHERE booking_date >= CURDATE() - INTERVAL 7 DAY
    GROUP BY DATE(booking_date)
    ORDER BY date ASC
";
$bookingTrendResult = mysqli_query($con, $bookingTrendQuery);
$bookingTrend = [];
while ($row = mysqli_fetch_assoc($bookingTrendResult)) {
    $bookingTrend[$row['date']] = $row['count'];
}

// Create aligned labels and values for the past 7 days
$labels = [];
$userData = [];
$bookingData = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = $date;
    $userData[] = $userTrend[$date] ?? 0;
    $bookingData[] = $bookingTrend[$date] ?? 0;
}

// Respond with dashboard data
echo json_encode([
    'totals' => [
        'users' => $totalUsers,
        'bookings' => $totalBookings,
        'products' => $totalProducts
    ],
    'sales' => [
        'weekly' => $weeklySales,
        'monthly' => $monthlySales
    ],
    'trend' => [
        'labels' => $labels,
        'users' => $userData,
        'bookings' => $bookingData
    ]
]);
?>
