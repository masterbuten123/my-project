<?php
session_start();
include('include/header.php');
include('../config/dbcon.php');

// Redirect if not logged in
if (!isset($_SESSION['auth_user']['account_id'])) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'You need to be logged in to view this page.'
    ];
    header('Location: index.php');
    exit();
}

// --- Fetch Totals ---
// Total Users + Artists
$userQuery = "SELECT COUNT(*) AS total_users FROM accounts WHERE role IN ('user', 'artist')";
$userResult = mysqli_query($con, $userQuery);
$totalUsers = mysqli_fetch_assoc($userResult)['total_users'];

// Total Products
$productQuery = "SELECT COUNT(*) AS total_products FROM products";
$productResult = mysqli_query($con, $productQuery);
$totalProducts = mysqli_fetch_assoc($productResult)['total_products'];

// Total Orders
$orderQuery = "SELECT COUNT(*) AS total_orders FROM bookings";
$orderResult = mysqli_query($con, $orderQuery);
$totalOrders = mysqli_fetch_assoc($orderResult)['total_orders'];

// --- Prepare Last 6 Months ---
$months = [];
$monthLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i month"));
    $monthLabels[] = date('F', strtotime($month . '-01'));
    $months[] = $month;
}

// --- Users + Artists per Month ---
$userData = [];
foreach ($months as $month) {
    $query = "SELECT COUNT(*) AS total FROM accounts 
              WHERE role IN ('user','artist') 
              AND DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $result = mysqli_query($con, $query);
    $userData[] = mysqli_fetch_assoc($result)['total'];
}

// --- Orders per Month ---
$orderData = [];
foreach ($months as $month) {
    $query = "SELECT COUNT(*) AS total FROM bookings WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'";
    $result = mysqli_query($con, $query);
    $orderData[] = mysqli_fetch_assoc($result)['total'];
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-10 mt-4">
            <h2 class="mb-4">Dashboard</h2>

            <!-- Widgets -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text fs-4"><?= $totalUsers ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Products</h5>
                            <p class="card-text fs-4"><?= $totalProducts ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Orders</h5>
                            <p class="card-text fs-4"><?= $totalOrders ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            User Registrations - Last 6 Months
                        </div>
                        <div class="card-body">
                            <canvas id="userChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            Orders - Last 6 Months
                        </div>
                        <div class="card-body">
                            <canvas id="orderChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthLabels = <?= json_encode($monthLabels) ?>;
const userData = <?= json_encode($userData) ?>;
const orderData = <?= json_encode($orderData) ?>;

// User Chart
const ctxUser = document.getElementById('userChart').getContext('2d');
new Chart(ctxUser, {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Users Registered',
            data: userData,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: { y: { beginAtZero: true, ticks: { stepSize: 5 } } }
    }
});

// Order Chart
const ctxOrder = document.getElementById('orderChart').getContext('2d');
new Chart(ctxOrder, {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Orders',
            data: orderData,
            backgroundColor: 'rgba(255, 159, 64, 0.7)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: { y: { beginAtZero: true, ticks: { stepSize: 5 } } }
    }
});
</script>
<?php if (isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
<script>
Swal.fire({
  title: '<?= $_SESSION['status']; ?>',
  icon: '<?= $_SESSION['status_code']; ?>',
  confirmButtonText: 'OK'
});
</script>
<?php unset($_SESSION['status']); unset($_SESSION['status_code']); ?>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include('include/footer.php') ?>
</body>
</html>
