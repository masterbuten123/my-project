<?php 
include('function/myfunctions.php');
include('include/header.php');
?>

<div class="container">
    <div class="row">
       <div class="col-md-12">
                <?php 
                    
                if(isset($_GET['id']))
                {
                    $id = $_GET['id'];
                    $users = "SELECT * FROM users WHERE id='$id'";
                    $user_run = mysqli_query($con, $users);

                    if(mysqli_num_rows($user_run) > 0)
                    {
                        foreach ($user_run as $data) 
                        {
                            # code...

                        ?>
                            <div class="card">
                                <div class="card-header bg-danger">
                                    <h4>View User
                                
                                    </h4>
                                </div>
                                <div class="card-body">
                                <form action="code.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="user_id" value="<?= $data['id'] ?>">
                                        <input type="hidden" name="old_image" value="<?= $data['image'] ?>">

                                        <div class="col-md-6">
                                            <label class="fw-bold text-dark">Name</label>
                                            <input type="text" name="name" value="<?= $data['name'] ?>" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold text-dark">Email</label>
                                            <input type="email" name="email" value="<?= $data['email'] ?>" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold text-dark">Phone</label>
                                            <input type="text" name="phone" value="<?= $data['phone'] ?>" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold text-dark">Role</label>
                                            <select name="role_as" class="form-control">
                                                <option value="0" <?= $data['role_as'] == '0' ? 'selected' : '' ?>>User</option>
                                                <option value="1" <?= $data['role_as'] == '1' ? 'selected' : '' ?>>Admin</option>
                                                <option value="2" <?= $data['role_as'] == '2' ? 'selected' : '' ?>>Artist</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold text-dark">Upload New Image</label>
                                            <input type="file" name="image" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold text-success">Current Image</label><br>
                                            <img src="../userimage/<?= $data['image'] ?>" width="50" height="50" alt="User Image">
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <button type="submit" name="update_user_btn" class="btn btn-dark"><i class="fa fa-refresh me-1"></i>Update</button>
                                        </div>
                                    </form>

                                        <div class="col-md-12 mt-3">
                                            <a href="all-users.php" class="btn btn-light float-end"><i class="fa fa-reply me-1"></i>Back</a>
                                        </div>
                                </div>
                            </div>
                        <?php
                        }

                    }
                    else
                    {
                        echo "No Record Found";
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



    <?php include('include/footer.php'); ?>