<?php
include('functions/myfunctions.php');
include('includes/header.php');

if(isset($_GET['t']))
{
    $tracking_no = $_GET['t'];

    $orderData = checkTrackingNoValid($tracking_no);
    if(mysqli_num_rows($orderData) == 0) // Fix the validation here
    {
        ?>
            <h4>Something Went Wrong. Order not found!</h4>
        <?php
        die();
    }
}
else
{
    ?>
        <h4>Something Went Wrong. No tracking number provided!</h4>
    <?php
    die();
}

$data = mysqli_fetch_array($orderData);
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger">
                    <span class="fs-4 fw-bold text-white">View Order</span>                          
                    <a href="orders.php" class="btn btn-light float-end"><i class="fa fa-reply me-1"></i>Back</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Delivery Details</h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label class="fw-bold">Name</label>
                                    <div class="border p-1">
                                        <?= htmlspecialchars($data['name']); ?>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="fw-bold">E-mail</label>
                                    <div class="border p-1">
                                        <?= htmlspecialchars($data['email']); ?>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="fw-bold">Phone</label>
                                    <div class="border p-1">
                                        <?= htmlspecialchars($data['phone']); ?>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="fw-bold">Tracking No.</label>
                                    <div class="border p-1">
                                        <?= htmlspecialchars($data['tracking_no']); ?>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="fw-bold">Address</label>
                                    <div class="border p-1">
                                        <?= htmlspecialchars($data['shipping_address']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>Order Details</h4>
                            <hr>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>                                      
                                        <?php
                                                $order_query = "SELECT o.order_id as oid, o.tracking_no, o.user_id, oi.*, oi.quantity as orderqty, p.* 
                                                        FROM orders o 
                                                        JOIN order_items oi ON oi.order_id = o.order_id 
                                                        JOIN products p ON p.product_id = oi.product_id 
                                                        WHERE o.tracking_no = '$tracking_no'";
                                                $order_query_run = mysqli_query($con, $order_query);

                                                if(mysqli_num_rows($order_query_run) > 0)
                                                {
                                                    foreach ($order_query_run as $item) {
                                                        ?>
                                                            <tr>
                                                                <td class="align-middle">
                                                                    <img src="../uploads/<?= htmlspecialchars($item['image']); ?>" width="50px" height="50px" alt="<?= htmlspecialchars($item['name']); ?>">
                                                                    <?= htmlspecialchars($item['name']); ?>
                                                                </td>
                                                                <td class="align-middle">₱ 
                                                                    <?= htmlspecialchars($item['price']); ?>.00
                                                                </td>    
                                                                <td class="align-middle">
                                                                    <?= htmlspecialchars($item['orderqty']); ?>x
                                                                </td>
                                                            </tr>   
                                                        <?php
                                                    }
                                                }
                                        ?>
                                    </tbody>
                            </table>
                            <hr>
                            <h5>Total Price: <span class="float-end fw-bold">₱<?= htmlspecialchars($data['total_price']); ?>.00</span></h5>
                            
                            <hr>
                            <label class="fw-bold">Payment Method</label>
                            <div class="border p-1 mb-3">
                                <?= htmlspecialchars($data['payment_method']); // Fixed the column name ?>
                            </div>
                            <label class="fw-bold">Status</label>
                            <div class="mb-3">
                                <form action="code.php" method="POST">
                                    <input type="hidden" name="tracking_no" value="<?= htmlspecialchars($data['tracking_no']); ?>">
                                    <select name="order_status" class="form-select">
                                        <option value="0" <?= $data['status'] == 0 ? "selected" : "" ?>>Pending</option>
                                        <option value="1" <?= $data['status'] == 1 ? "selected" : "" ?>>Confirmed</option>
                                        <option value="2" <?= $data['status'] == 2 ? "selected" : "" ?>>Completed</option>
                                        <option value="3" <?= $data['status'] == 3 ? "selected" : "" ?>>Canceled</option>
                                    </select>
                                    <button type="submit" name="update_order_btn" class="btn btn-dark mt-2"><i class="fa fa-refresh me-1"></i>Update Status</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
