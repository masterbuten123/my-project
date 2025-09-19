  <?php
  session_start();
  require 'config/dbcon.php';
  require 'functions/myfunctions.php';
  require 'includes/navbar.php';

  // Redirect if not logged in
  if (!isset($_SESSION['new_user_id'])) {
      $_SESSION['alert'] = [
          'type' => 'error',
          'message' => 'Please register or login first.'
      ];
      header('Location: index.php');
      exit();
  }

  $accountId = $_SESSION['new_user_id']; // use account_id
  $currentSub = getCurrentSubscription($con, $accountId);
  // Fetch plans
  $plans = [];
  $result = $con->query("SELECT plan_id, name, price, duration_days FROM subscription_plans ORDER BY plan_id ASC");
  while ($row = $result->fetch_assoc()) {
      $plans[] = $row;
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Choose Subscription Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body {
        background: #f9fafc;
      }
      .pricing-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
      }
      .pricing-card:hover {
        transform: translateY(-8px);
        box-shadow: 0px 10px 25px rgba(0,0,0,0.1);
      }
      .pricing-header {
        border-bottom: 1px solid #eee;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
      }
      .price {
        font-size: 2rem;
        font-weight: bold;
        color: #0d6efd;
      }
      .duration {
        font-size: 0.9rem;
        color: #777;
      }
      .btn-choose {
        border-radius: 50px;
        padding: 10px 25px;
        font-weight: 500;
      }
    </style>
  </head>
  <body>
  <div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Choose Your Subscription</h1>
        <p class="text-muted">Get the best experience by upgrading your plan.</p>
    </div>

    <!-- Current Subscription -->
    <?php if ($currentSub): ?>
        <div class="alert alert-info text-center mb-5">
            <h5 class="mb-1">Your Current Subscription</h5>
            <p class="mb-0">
                <strong><?= htmlspecialchars($currentSub['name']) ?></strong><br>
                <?= $currentSub['price'] == 0 ? 'Free' : '₱' . number_format($currentSub['price'], 2) ?>
                (Valid until <?= htmlspecialchars(date("F j, Y", strtotime($currentSub['end_date']))) ?>)
            </p>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center mb-5">
            You don’t have an active subscription. Choose one below!
        </div>
    <?php endif; ?>

    <div class="row justify-content-center g-4">
<?php foreach ($plans as $plan): ?>
    <?php 
        $isCurrent = $currentSub && $currentSub['plan_id'] == $plan['plan_id'];
        $isFree = $plan['price'] == 0;
    ?>
    <div class="col-md-4">
        <div class="card pricing-card shadow-sm text-center h-100
            <?= $isCurrent ? 'border-primary' : '' ?>
            <?= $isFree ? 'bg-light border-success' : '' ?>">
            
            <div class="card-body d-flex flex-column position-relative">
                <?php if($isFree): ?>
                    <span class="badge bg-success position-absolute top-0 end-0 m-3">Free</span>
                <?php endif; ?>

                <?php if($isCurrent): ?>
                    <span class="badge bg-primary position-absolute top-0 start-0 m-3">Current Plan</span>
                <?php endif; ?>
                
                <div class="pricing-header">
                    <h4 class="card-title mb-0"><?= htmlspecialchars($plan['name']) ?></h4>
                </div>
                <p class="price mb-1">
                    <?= $isFree ? 'Free' : '₱' . number_format($plan['price'], 2) ?>
                </p>
                <p class="duration mb-4">Valid for <?= htmlspecialchars($plan['duration_days']) ?> days</p>
                <div class="mt-auto">
                    <form method="POST" action="checkout.php">
                        <input type="hidden" name="type" value="subscription">
                        <input type="hidden" name="plan_id" value="<?= $plan['plan_id'] ?>">
                        <button type="submit" class="btn btn-primary btn-choose w-100"
                            <?= $isCurrent ? 'disabled' : '' ?>>
                            <?= $isCurrent ? 'Active Plan' : ($isFree ? 'Get Free Plan' : 'Choose ' . htmlspecialchars($plan['name'])) ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
    </div>
</div>
  </body>
  </html>
