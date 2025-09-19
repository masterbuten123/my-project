<?php
session_start();
include('functions/myfunctions.php');
include('includes/header.php');

if (!isset($_SESSION['auth_user']['account_id'])) {
    // Redirect or show error
    header('Location: login.php');
    exit();
}

$artist_id = $_SESSION['auth_user']['account_id']; 
$orders = getArtistOrders($artist_id);
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-danger">
            <h4 class="text-white">Orders
                <a href="order_history.php" class="btn btn-light float-end">
                    <i class="fa fa-history me-1"></i>Order History
                </a>
            </h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Tracking No.</th>
                        <th>Price</th>
                        <th>Date Ordered</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['user_name']); ?></td>
                                <td><?= htmlspecialchars($order['tracking_no']); ?></td>
                                <td>₱ <?= number_format($order['total_price'], 2); ?></td>
                                <td><?= htmlspecialchars(date('F j, Y, g:i A', strtotime($order['created_at']))); ?></td>
                                <td>
                                    <a href="view-order.php?t=<?= urlencode($order['tracking_no']); ?>" class="btn btn-success btn-sm">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No Orders Yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
include('artist-chat-widget.php'); 
include('includes/footer.php'); 
?>