<?php
include('function/myfunctions.php');
include('include/header.php');
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
           <div class="card">
               <div class="card-header bg-danger">
                   <h4 class="text-white">Orders
                        <a href="order_history.php" class="btn btn-light float-end">
                            <i class="fa fa-history me-1"></i>Order History
                        </a>
                   </h4>
               </div>
               <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-dark fw-bold">Customer</th>
                                <th class="text-dark fw-bold">Tracking No.</th>
                                <th class="text-dark fw-bold">Price</th>
                                <th class="text-dark fw-bold">Date Ordered</th>
                                <th class="text-dark fw-bold">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $orders = getAllOrders();

                                if(count($orders) > 0) {
                                    foreach ($orders as $items) {
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($items['account_name']); ?></td>
                                                <td><?= htmlspecialchars($items['tracking_no']); ?></td>
                                                <td>₱ <?= number_format($items['total_amount'], 2); ?></td>
                                                <td><?= htmlspecialchars($items['created_at']); ?></td>
                                                <td>
                                                    <a href="view-order.php?t=<?= htmlspecialchars($items['tracking_no']); ?>" class="btn btn-success">View Details</a>
                                                </td>
                                            </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                        <tr>
                                            <td colspan="5">No Orders Yet</td>
                                        </tr>
                                    <?php   
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('admin-chat-widget.php'); ?>
<?php include('include/footer.php'); ?>
