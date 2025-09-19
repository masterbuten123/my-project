<?php
include('functions/myfunctions.php');
include('includes/header.php');
include('functions/authenticate.php');
?>


<div class="py-5">
    <div class="container">
        <div class="card card-body shadow">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <span class="text-white fs-4">My Profile</span>
                            <a href="my-profile.php" class="btn btn-light float-end"><i class="fa fa-reply"></i>Back</a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php
                                $info = getAllInfo('aritsts');

                                if (mysqli_num_rows($info) > 0) {
                                    foreach ($info as $data) {
                                ?>
                                        <form action="code.php" method="POST" enctype="multipart/form-data">
                                            <h4>Edit User Details</h4>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                                    <label class="fw-bold">Name</label>
                                                    <input type="text" name="name" value="<?= $data['name'] ?>" placeholder="Enter name" class="form-control">
                                                    <label class="fw-bold">E-mail</label>
                                                    <input type="email" name="email" value="<?= $data['email'] ?>" placeholder="Enter email" class="form-control">
                                                    <label class="fw-bold">Phone</label>
                                                    <input type="number" name="phone" value="<?= $data['phone'] ?>" placeholder="Enter phone" class="form-control">
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="card shadow text-center">
                                                        <div class="card-body">
                                                            <input type="hidden" name="old_image" value="<?= $data['image'] ?>">
                                                            <img src="../userimage/<?= $data['image']; ?>" width="250px" height="250px" alt="Profile Image" class="rounded-circle" id="previewImage">
                                                            <input type="file" name="image" class="form-control mt-2" accept="image/*" onchange="previewSelectedImage(this)">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-2 mt-2">
                                                <button type="submit" class="btn btn-dark" name="update_profile_btn"><i class="fa fa-refresh me-1"></i>Update</button>
                                            </div>
                                        </form>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function previewSelectedImage(input) {
    const preview = document.getElementById('previewImage');
    const file = input.files[0];

    if (file) {
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
        };

        reader.readAsDataURL(file);
    }
}
</script>
<?php include('includes/footer.php'); ?>
