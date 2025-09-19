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
                    $category = getByID("categories", $id);

                    if(mysqli_num_rows($category) > 0)
                    {
                        $data = mysqli_fetch_array($category);

                        ?>
                            <div class="card">
                                <div class="card-header bg-success">
                                    <h4>Edit Category
                                    <a href="category.php" class = "btn btn-warning float-end"><i class="fa fa-reply me-1"></i>Back</a>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <form action="code.php" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="hidden" name="category_id" value = "<?= $data['id']?>">
                                            <label for="" class ="mb-0 text-success fw-bold">Name</label>
                                            <input type="text" name = "name" value = "<?= $data['name']?>" placeholder = "Enter categoroy name" class="form-control">
                                        </div>
                                        <div class="col-md-12">
                                            <label for="" class ="mb-0 text-success fw-bold">Description</label>
                                            <textarea row = "3" name = "description" placeholder = "Enter description" class="form-control"><?= $data['description']?></textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="" class ="mb-0 text-success fw-bold">Upload Image</label>
                                            <input type="file" name = "image" class="form-control">
                                            <label for="" class ="mb-0 text-success fw-bold">Current Image</label>
                                            <input type="hidden" name="old_image" value = "<?= $data['image']?>">
                                            <img src="../uploads/<?= $data['image']?>" width="50px" height="50px" alt="">
                                            
                                        </div>
                                        <div class="col-md-12">  
                                            <label for="" class ="mb-0 text-success fw-bold">Tags#</label>
                                            <textarea row = "3" name = "Tags#" placeholder = "Tags#" class="form-control"><?= $data['tags']?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="" class ="mb-0 text-success fw-bold">Status</label>
                                            <input type="checkbox" <?= $data['status'] ? "checked":""?> name = "status">
                                        </div>
                                        <div class="col-md-12">
                                            <button type = "submit" class = "btn btn-success" name = "update_cate_btn"><i class="fa fa-refresh me-1"></i>Update</button>
                                        </div>
                                        </form>
                                </div>
                            </div>
                        <?php
                    }
                    else
                    {
                        echo "Category not Found";
                    }
                }
                else
                {
                    echo "ID Missing From URL";
                }
                  ?>
         </div>
     </div>
 </div>



    <?php include('includes/footer.php'); ?>