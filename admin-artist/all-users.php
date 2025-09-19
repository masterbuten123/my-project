<?php
include('functions/myfunctions.php'); 
include('includes/header.php');


?>

<div class="container">
    <div class="row">
        <div class="colmd-12">
           <div class="card">
               <div class="card-header bg-danger">
                   <h4 class="text-white">Users
                   </h4>
               </div>
               <div class="card-body" id="">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-dark fw-bold">ID</th>
                                <th class="text-dark fw-bold">Name</th>
                                <th class="text-dark fw-bold">Email</th>
                                <th class="text-dark fw-bold">Phone</th>
                                <th class="text-dark fw-bold">Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $info = getAllInfos('users');

                                if(mysqli_num_rows($info) > 0)
                                {
                                    foreach ($info as $items) {
                                        ?>
                                            <tr>
                                                <td><?= $items['id']; ?></td>
                                                <td><?= $items['name']; ?></td>
                                                <td><?= $items['email']; ?></td>
                                                <td><?= $items['phone']; ?></td>
                                                <td><?= $items['created_at']; ?></td>
                                                <td>
                                                    <a href="view-user.php?id=<?= $items['id']; ?>" class="btn btn-success">View Details</a>
                                                </td>
                                            </tr>
                                        <?php
                                    }
                                }
                                else
                                {
                                    ?>
                                        <tr>
                                            <td colspan="5">No Users Yet</td>
                                        </tr>
                                    <?php   
                                }
                            ?>
                           
                        </tbody>
                    </table>
                    <a href="index.php" class="btn btn-light float-end"><i class="fa fa-reply me-1"></i>Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>