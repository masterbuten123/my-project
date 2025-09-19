<?php
// Get the current script name, e.g. 'index.php'
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<style>
    .bg-navyblue {
  background-color: #1a1a2e !important; /* Dark navy blue */
  color: #fff;
}

.nav-link {
  color: #ddd;
}

.nav-link:hover {
  color: #fff;
  background-color: #16213e;
}

.nav-link.active {
  background-color: #0f3460 !important; /* Darker blue for active */
  color: #fff !important;
  font-weight: bold;
}
</style>
<div class="d-flex flex-column flex-shrink-0 p-3 bg-navyblue" style="width: 250px; height: 100vh;">
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= ($currentPage == 'index.php') ? 'active' : '' ?>">Dashboard</a>
        </li>
        <li>
            <a href="my-profile.php" class="nav-link <?= ($currentPage == 'my-profile.php') ? 'active' : '' ?>">My information</a>
        </li>
         <li>
            <a href="recordings.php" class="nav-link <?= ($currentPage == 'recordings.php') ? 'active' : '' ?>">My Recordings</a>
        </li>
        <li>
            <a href="all-booking.php" class="nav-link <?= ($currentPage == 'all-booking.php') ? 'active' : '' ?>">My Bookings</a>
        </li>
        <li>
            <a href="all-orders.php" class="nav-link <?= ($currentPage == 'all-orders.php') ? 'active' : '' ?>">Orders</a>
        </li>
        <li>
            <a href="all-products.php" class="nav-link <?= ($currentPage == 'all-products.php') ? 'active' : '' ?>">My Products</a>
        </li>
    </ul>
</div>
