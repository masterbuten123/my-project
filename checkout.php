<?php
session_start();
require 'config/dbcon.php';

$account_id = null;

if (isset($_SESSION['auth_user']) && isset($_SESSION['auth_user']['account_id'])) {
    $account_id = $_SESSION['auth_user']['account_id'];
} elseif (isset($_SESSION['new_user_id'])) {
    $account_id = $_SESSION['new_user_id'];
}

// --- Handle subscription plan if chosen ---
if (isset($_POST['plan_id'])) {
    $plan_id = intval($_POST['plan_id']);

    // Get plan details
    $stmt = $con->prepare("SELECT plan_id, name, price, duration_days 
                           FROM subscription_plans WHERE plan_id = ? LIMIT 1");
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($plan) {
        // Expire old active subscriptions
        $expireStmt = $con->prepare("UPDATE subscriptions SET status='expired' WHERE account_id=? AND status='active'");
        $expireStmt->bind_param("i", $account_id);
        $expireStmt->execute();
        $expireStmt->close();

        // Insert new subscription
        $start_date = date('Y-m-d');
        $end_date   = date('Y-m-d', strtotime("+{$plan['duration_days']} days"));
        $status     = 'active';

        $stmt = $con->prepare("INSERT INTO subscriptions (account_id, plan_id, start_date, end_date, status) VALUES (?,?,?,?,?)");
        $stmt->bind_param("iisss", $account_id, $plan['plan_id'], $start_date, $end_date, $status);
        $stmt->execute();
        $stmt->close();

        // Promote user if artist plan
        if (strtolower($plan['name']) === 'artist') {
            $updateRole = $con->prepare("UPDATE accounts SET role='artist' WHERE account_id=?");
            $updateRole->bind_param("i", $account_id);
            $updateRole->execute();
            $updateRole->close();

            $_SESSION['auth_user']['role'] = 'artist';
            $_SESSION['role'] = 'artist';
        }
    }
}

// --- Fetch products in cart ---
$stmt = $con->prepare("
    SELECT c.*, p.name AS product_name, p.price
    FROM cart c
    JOIN products p ON c.product_id=p.product_id
    WHERE c.account_id=? AND c.status='active'
");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Fetch active subscriptions ---
$stmt = $con->prepare("
    SELECT s.*, sp.name AS plan_name, sp.price
    FROM subscriptions s
    JOIN subscription_plans sp ON s.plan_id=sp.plan_id
    WHERE s.account_id=? AND s.status='active'
");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$subscription_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Calculate totals ---
$total_amount = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));
$subscription_total = array_sum(array_column($subscription_items, 'price'));
$grand_total = $total_amount + $subscription_total;

// --- Fetch user info ---
$stmt = $con->prepare("SELECT name,email,phone FROM accounts WHERE account_id=?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$name = $user['name'] ?? 'Customer';
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
<title>Checkout</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
<h3>Checkout Summary</h3>

<?php if(empty($cart_items) && empty($subscription_items)): ?>
    <div class="alert alert-warning">No items to checkout.</div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-body">
            <?php if(!empty($cart_items)): ?>
                <h5>Products</h5>
                <ul>
                <?php foreach($cart_items as $item): ?>
                    <li><?= htmlspecialchars($item['product_name']) ?> x <?= $item['quantity'] ?> - ₱<?= number_format($item['price'] * $item['quantity'],2) ?></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if(!empty($subscription_items)): ?>
                <h5>Subscriptions</h5>
                <ul>
                <?php foreach($subscription_items as $sub): ?>
                    <li><?= htmlspecialchars($sub['plan_name']) ?> - ₱<?= number_format($sub['price'],2) ?></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <hr>
            <h5>Grand Total: ₱<?= number_format($grand_total,2) ?></h5>
        </div>
    </div>

    <form action="create_checkout_session.php" method="POST">
        <input type="hidden" name="amount" value="<?= $grand_total ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($name) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

        <?php foreach($cart_items as $item): ?>
            <input type="hidden" name="item_ids[]" value="<?= $item['product_id'] ?>">
            <input type="hidden" name="quantities[]" value="<?= $item['quantity'] ?>">
        <?php endforeach; ?>

        <?php foreach($subscription_items as $sub): ?>
            <input type="hidden" name="sub_ids[]" value="<?= $sub['plan_id'] ?>">
            <input type="hidden" name="sub_names[]" value="<?= htmlspecialchars($sub['plan_name']) ?>">
            <input type="hidden" name="sub_prices[]" value="<?= $sub['price'] ?>">
        <?php endforeach; ?>

        <label>Select Payment Method</label>
        <select name="mode" class="form-select mb-3" required>
            <?php if(!empty($cart_items) && empty($subscription_items)): ?>
                <option value="gcash">GCash</option>
                 <option value="card">Credit/Debit Card</option>
                <option value="cod">Cash on Delivery</option>
            <?php elseif(empty($cart_items) && !empty($subscription_items)): ?>
                <option value="gcash">GCash</option>
                 <option value="card">Credit/Debit Card</option>
            <?php elseif(!empty($cart_items) && !empty($subscription_items)): ?>
                <option value="gcash">GCash</option>
                 <option value="card">Credit/Debit Card</option>
            <?php endif; ?>
        </select>

        <button type="submit" class="btn btn-success w-100">Proceed to Payment</button>
    </form>
<?php endif; ?>
</div>
</body>
</html>
