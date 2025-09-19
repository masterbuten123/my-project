<?php
include('function/myfunctions.php');
include('include/header.php');

// Kunin ang user_id mula sa session
session_start();
$user_id = $_SESSION['auth_user']['user_id'] ?? null;

if (!$user_id) {
    // Kung walang naka-login, redirect sa login page
    header('Location: index.php');
    exit();
}

// Kunin ang info ng current user lang
$info_query = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
$info_result = mysqli_query($con, $info_query);
$data = mysqli_fetch_assoc($info_result);
?>

<div class="py-5">
    <div class="container">
        <div class="card card-body shadow">
            <div class="row">
                <div class="col-md-12">
                   <div class="card">
                       <div class="card-header bg-danger d-flex justify-content-between align-items-center">
                           <span class="text-white fs-4">My Profile</span>                          
                           <a href="index.php" class="btn btn-light"><i class="fa fa-reply"></i> Back</a>
                       </div>
                       <div class="card-body">
                           <div class="row">
                               <div class="col-md-8">
                                   <h4>User Details</h4>
                                   <hr>
                                   <div class="mb-3">
                                       <label class="fw-bold">Name</label>
                                       <div class="form-control"><?= htmlspecialchars($data['name']) ?></div>
                                   </div>
                                   <div class="mb-3">
                                       <label class="fw-bold">E-mail</label>
                                       <div class="form-control"><?= htmlspecialchars($data['email']) ?></div>
                                   </div>
                                   <div class="mb-3">
                                       <label class="fw-bold">Phone</label>
                                       <div class="form-control"><?= htmlspecialchars($data['phone']) ?></div>
                                   </div>

                                   <!-- Artist-specific fields -->
                                   <?php if ($data['role_as'] === 'artist'): ?>
                                   <h4 class="mt-4">Artist Details</h4>
                                   <hr>
                                   <div class="mb-3">
                                       <label class="fw-bold">Bio</label>
                                       <div class="form-control"><?= nl2br(htmlspecialchars($data['bio'])) ?></div>
                                   </div>
                                   <div class="mb-3">
                                       <label class="fw-bold">Genre</label>
                                       <div class="form-control"><?= htmlspecialchars($data['genre']) ?></div>
                                   </div>
                                   <div class="mb-3">
                                       <label class="fw-bold">Price Per Hour</label>
                                       <div class="form-control">₱<?= number_format($data['price_per_hour'], 2) ?></div>
                                   </div>
                                   <?php endif; ?>

                                   <div class="d-flex gap-2 mt-3">
                                       <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="fa fa-edit"></i> Edit</button>
                                       <a href="changepass.php" class="btn btn-danger"><i class="fa fa-edit"></i> Change Password</a>
                                   </div>
                               </div>

                               <div class="col-md-4 mb-2 text-center">
                                   <div class="card shadow">
                                       <div class="card-body">
                                           <img src="../userimage/<?= htmlspecialchars($data['image']) ?>" width="300" height="250" alt="Profile Image" class="radius img-fluid">
                                       </div>
                                   </div>
                               </div> 
                           </div>
                       </div>
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="code.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
              <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">
              <label class="fw-bold">Name</label>
              <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>" placeholder="Enter name" class="form-control mb-2" required>
              <label class="fw-bold">E-mail</label>
              <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" placeholder="Enter email" class="form-control mb-2" required>
              <label class="fw-bold">Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>" placeholder="Enter phone" class="form-control mb-2" required>

              <div class="card shadow text-center">
                  <div class="card-body">
                      <input type="hidden" name="old_image" value="<?= htmlspecialchars($data['image']) ?>">
                      <img src="../userimage/<?= htmlspecialchars($data['image']) ?>" width="250" height="250" alt="Profile Image" class="rounded-circle img-fluid mb-2">
                      <input type="file" name="image" class="form-control">
                  </div>
              </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-dark" name="update_profile_btn"><i class="fa fa-refresh me-1"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  img.radius {
      border-radius: 50%;
      object-fit: cover;
  }
</style>

<?php include('include/footer.php'); ?>
