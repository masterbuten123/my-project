<?php
include('../function/myfunctions.php');
include('../includes/header.php');


?>

<div class="container">
    <div class="row">
        <div class="colmd-12">
           <div class="card">
               <div class="card-header bg-danger">
                   <h4 class="text-white">Confirmed Orders
                        
                   </h4>
               </div>
               <div class="card-body" id="">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-dark fw-bold">ID</th>
                                <th class="text-dark fw-bold">Users</th>
                                <th class="text-dark fw-bold">Tracking No.</th>
                                <th class="text-dark fw-bold">Price</th>
                                <th class="text-dark fw-bold">Date Ordered</th>
                                <th class="text-dark fw-bold">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $orders = getConfirmedOrders();

                                if(mysqli_num_rows($orders) > 0)
                                {
                                    foreach ($orders as $items) {
                                        ?>
                                            <tr>
                                                <td><?= $items['id']; ?></td>
                                                <td><?= $items['name']; ?></td>
                                                <td><?= $items['tracking_no']; ?></td>
                                                <td>₱ <?= $items['total_price']; ?>.00</td>
                                                <td><?= $items['created_at']; ?></td>
                                                <td>
                                                    <a href="view-order.php?t=<?= $items['tracking_no']; ?>" class="btn btn-outline-success">View Details</a>
                                                </td>
                                            </tr>
                                        <?php
                                    }
                                }
                                else
                                {
                                    ?>
                                        <tr>
                                            <td colspan="5">No Orders Yet</td>
                                        </tr>
                                    <?php   
                                }
                            ?>
                           
                        </tbody>
                    </table>
                    <a href="orders.php" class="btn btn-light float-end"><i class="fa fa-reply me-1"></i>Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>