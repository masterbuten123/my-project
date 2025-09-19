<?php
include('../functions/myfunctions.php');
include('includes/header.php');


?>

<div class="container">
    <div class="row">
       <div class="col-md-12">
           <?php 
            if(isset($_GET['id']))
            {
                $id = $_GET['id'];

                $product = getByID("products", $id);

                if(mysqli_num_rows($product) > 0)
                {
                    $data = mysqli_fetch_array($product);
                    ?>
                        <div class="card">
                            <div class="card-header bg-success">
                                <h4>Edit Product
                                    <a href="products.php" class = "btn btn-warning float-end"><i class="fa fa-reply me-1"></i>Back</a>
                                </h4>
                            </div>
                            <div class="card-body">
                                <form action="code.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                        <div class="col-md-12">
                                            <label class ="mb-0 text-success fw-bold" for="">Select category</label>
                                            <select name = "category_id" class="form-select mb-2">
                                                <option selected>Select category</option>                                                       
                                                <?php
                                                    $categories = getAll("categories");

                                                    if(mysqli_num_rows($categories) > 0)
                                                    {
                                                        foreach ($categories as $item) {
                                                            ?>
                                                                <option value="<?= $item['id']; ?>" <?= $data['category_id'] == $item['id']?'selected':'' ?> ><?= $item['name']; ?></option>
                                                            <?php
                                                        }    
                                                    }   
                                                    else
                                                    {
                                                        echo "No category available";
                                                    }
                                                    
                                                ?>
                                            </select>
                                    </div>
                                    <input type="hidden" name="product_id" value = "<?= $data['id'];?>">
                                    <div class="col-md-6">
                                            <label class ="mb-0 text-success fw-bold" for="">Name</label>
                                            <input type="text" required name = "name" value = "<?= $data['name'];?>" placeholder = "Enter category name" class="form-control mb-2">
                                    </div>
                                    <div class="col-md-12">
                                        <label class ="mb-0 text-success fw-bold" for="">Description</label>
                                        <textarea row = "3" required name = "description" placeholder = "Enter description" class="form-control mb-2"><?= $data['description'];?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                            <label class ="mb-0 text-success fw-bold" for="">Original Price</label>
                                            <input type="text" required name = "original_price" value = "<?= $data['original_price'];?>" placeholder = "Enter original price" class="form-control mb-2">
                                    </div>
                                    <div class="col-md-6">
                                            <label class ="mb-0 text-success fw-bold" for="">Selling Price</label>
                                            <input type="text" required name = "selling_price" value = "<?= $data['selling_price'];?>"placeholder = "Enter selling price" class="form-control mb-2">
                                    </div>
                                    <div class="col-md-12">
                                        <label class ="mb-0 text-success fw-bold" for="">Upload Image</label>
                                        <input type="hidden" name="old_image" value = "<?= $data['image'];?>">
                                        <input type="file" name = "image" class="form-control mb-2"></textarea>
                                        <label class ="mb-0 text-success fw-bold" for="">Current Image</label>
                                        <img src="../uploads/<?= $data['image'];?>" alt="Product image" height="50px" width="50px">
                                    </div>
                                    <div class="row">
                                            <div class="col-md-6">
                                                    <label class ="mb-0 text-success fw-bold" for="">Quantity</label>
                                                    <input type="number" required name = "qty" value = "<?= $data['qty'];?>"placeholder = "Enter quantity" class="form-control mb-2">
                                            </div>
                                            <div class="col-md-3">
                                                <br>
                                                    <label class ="mb-0 text-success fw-bold" for="">Status</label>
                                                    <input type="checkbox" name = "status" <?= $data['status'] == '0'?'':'checked' ?>>
                                            </div>
                                            <div class="col-md-3">
                                                <br>
                                                    <label class ="mb-0 text-success fw-bold" for="">Trending</label>
                                                    <input type="checkbox" name = "trending" <?= $data['trending'] == '0'?'':'checked' ?>>
                                            </div>
                                    </div>
                                    <div class="col-md-12">  
                                        <label for="" class ="mb-0 text-success fw-bold">Meta Keywords</label>
                                        <textarea row = "3" required name = "meta_keywords" placeholder = "Enter meta keywords" class="form-control mb-2"><?= $data['meta_keywords'];?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <button type = "submit" class = "btn btn-success" name = "update_prod_btn"><i class="fa fa-refresh me-1"></i>Update</button>
                                    </div>
                                    </form>
                            </div>
                        </div>
                    <?php 
                }
                else
                {
                   echo "Product not found";
                }
                
           
            }
            else
            {
               echo "Id missing from url";
            }
           ?>
       </div>
    </div>
</div>



    <?php include('includes/footer.php'); ?>