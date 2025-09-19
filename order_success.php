<?php
session_start();
require 'config/dbcon.php';
// Determine account_id
if (isset($_SESSION['auth_user']['account_id'])) {
    $account_id = $_SESSION['auth_user']['account_id'];
} elseif (isset($_SESSION['new_user_id'])) {
    $account_id = $_SESSION['new_user_id'];
} else {
    die("Please log in or register first.");
}

// Get order_id and subscription_id from URL
$order_id = $_GET['order_id'] ?? null;
$subscription_id = $_GET['subscription_id'] ?? null;

$order = null;
$subscription = null;
$isArtist = false;

// --- Handle Order ---
if ($order_id) {
    $stmt = $con->prepare("UPDATE orders SET order_status='processing', payment_status='paid' WHERE order_id=? AND account_id=?");
    $stmt->bind_param("ii", $order_id, $account_id);
    $stmt->execute();
    $stmt->close();

    $update_cart = $con->prepare("UPDATE cart SET status='checked_out' WHERE account_id=? AND status='active'");
    $update_cart->bind_param("i", $account_id);
    $update_cart->execute();
    $update_cart->close();

    $stmt2 = $con->prepare("SELECT * FROM orders WHERE order_id=? AND account_id=?");
    $stmt2->bind_param("ii", $order_id, $account_id);
    $stmt2->execute();
    $order = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();
}


// --- Handle Subscription ---
if ($subscription_id) {
    // Update subscription to paid
    $stmtSubUpdate = $con->prepare("UPDATE subscriptions SET payment_status='paid' WHERE subscription_id=? AND account_id=?");
    $stmtSubUpdate->bind_param("ii", $subscription_id, $account_id);
    $stmtSubUpdate->execute();
    $stmtSubUpdate->close();

    // Fetch subscription details + plan info
    $stmtSub = $con->prepare("
        SELECT s.*, p.name AS plan_name, p.price, s.end_date AS expiry_date
        FROM subscriptions s
        JOIN subscription_plans p ON s.plan_id = p.plan_id
        WHERE s.subscription_id=? AND s.account_id=?");
    $stmtSub->bind_param("ii", $subscription_id, $account_id);
    $stmtSub->execute();
    $subscription = $stmtSub->get_result()->fetch_assoc();
    $stmtSub->close();
    
        // DEBUG: Show subscription data

    // Promote user based on plan name
   if ($subscription && $subscription['payment_status'] === 'paid') {
    $plan = strtolower($subscription['plan_name']);

    if (strpos($plan, 'artist') !== false) {
        // Promote to artist role
        $updateRole = $con->prepare("UPDATE accounts SET role='artist' WHERE account_id=?");
        $updateRole->bind_param("i", $account_id);
        $updateRole->execute();
        $updateRole->close();

        $_SESSION['auth_user']['role'] = 'artist';
        $_SESSION['role'] = 'artist';
        $isArtist = true;
    }
    elseif (strpos($plan, 'premium') !== false) {
        // Upgrade subscription plan
        $updatePlan = $con->prepare("UPDATE accounts SET plan='premium' WHERE account_id=?");
        $updatePlan->bind_param("i", $account_id);
        $updatePlan->execute();
        $updatePlan->close();

        $_SESSION['auth_user']['plan'] = 'premium';
        $_SESSION['plan'] = 'premium';
    }
}
}
// DEBUG: Show session 
// Clean temporary session
if (isset($_SESSION['recent_order_account'])) {
    unset($_SESSION['recent_order_account']);
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Payment Success - TFUCS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="card shadow-sm p-4">
    <h3 class="text-success">🎉 Payment Successful!</h3>
    <p class="text-muted">Thank you! Here are your details:</p>

    <!-- Order Section -->
    <?php if ($order): ?>
      <table class="table table-bordered mt-3">
        <tr><th>Order ID</th><td>#<?= htmlspecialchars($order['order_id']) ?></td></tr>
        <tr><th>Tracking Number</th><td><?= htmlspecialchars($order['tracking_no']) ?></td></tr>
        <tr><th>Total Amount</th><td>₱<?= number_format($order['total_amount'], 2) ?></td></tr>
        <tr><th>Payment Status</th>
          <td><span class="badge <?= $order['payment_status']==='paid' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($order['payment_status']) ?></span></td>
        </tr>
        <tr><th>Order Status</th>
          <td>
            <?php
            switch($order['order_status']){
                case 'pending': echo '<span class="badge bg-secondary">Pending</span>'; break;
                case 'processing': echo '<span class="badge bg-info text-dark">Processing</span>'; break;
                case 'completed': echo '<span class="badge bg-success">Completed</span>'; break;
                default: echo '<span class="badge bg-danger">Cancelled</span>';
            }
            ?>
          </td>
        </tr>
        <tr><th>Created At</th><td><?= htmlspecialchars($order['created_at']) ?></td></tr>
      </table>

      <?php if ($order['payment_status'] === 'paid'): ?>
        <div class="alert alert-success mt-3">
          ✅ We have received your payment. Your order is now being processed.
        </div>
      <?php endif; ?>
    <?php endif; ?>

<!-- Subscription Section -->
<?php if ($subscription): ?>
  <table class="table table-bordered mt-3">
    <tr><th>Subscription Plan</th><td><?= htmlspecialchars($subscription['plan_name']) ?></td></tr>
    <tr><th>Price</th><td>₱<?= number_format($subscription['price'], 2) ?></td></tr>
    <tr><th>Payment Status</th>
      <td><span class="badge <?= $subscription['payment_status']==='paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
        <?= htmlspecialchars($subscription['payment_status']) ?>
      </span></td>
    </tr>
    <tr><th>Expiry Date</th><td><?= htmlspecialchars($subscription['expiry_date']) ?></td></tr>
  </table>

  <?php if ($subscription['payment_status'] === 'paid'): ?>
    <div class="alert alert-success mt-3">
      ✅ We have received your payment. Your subscription is now active.
    </div>

    <?php if ($isArtist): ?>
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Congrats!',
          html: 'You are now an <b>Artist</b> on TFUCS!<br>Go to your dashboard to start uploading.',
          confirmButtonText: 'Go to Dashboard'
        }).then(() => {
          window.location.href = 'admin-artist/index.php';
        });
      </script>
    <?php endif; ?>
  <?php endif; ?>
<?php endif; ?>

    <a href="index1.php" class="btn btn-primary mt-3">Continue Shopping</a>
  </div>
</div>
</body>
</html>
