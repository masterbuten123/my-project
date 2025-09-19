<?php
include('function/myfunctions.php');
include('include/header.php');
?>

<?php if (isset($_SESSION['status'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['status']; unset($_SESSION['status']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card mt-4">
                <div class="card-header bg-danger">
                    <h4 class="text-white mb-0">Users</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Role</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $info = getAll('users');
                                if (mysqli_num_rows($info) > 0):
                                    while ($items = mysqli_fetch_assoc($info)):
                                        $roleAs = match ($items['role_as']) {
                                            '1' => 'Admin',
                                            '2' => 'Artist',
                                            default => 'User',
                                        };
                                ?>
                                <tr>
                                    <td><?= $roleAs; ?></td>
                                    <td><?= $items['name']; ?></td>
                                    <td><?= $items['email']; ?></td>
                                    <td><?= $items['phone']; ?></td>
                                    <td><?= $items['created_at']; ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewUserModal"
                                                data-id="<?= $items['id'] ?>"
                                                data-name="<?= $items['name'] ?>"
                                                data-email="<?= $items['email'] ?>"
                                                data-phone="<?= $items['phone'] ?>"
                                                data-created_at="<?= $items['created_at'] ?>"
                                                data-role_as="<?= $items['role_as'] ?>"
                                                data-image="<?= $items['image'] ?>">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No Users Found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="index.php" class="btn btn-light float-end mt-3">
                        <i class="fa fa-reply me-1"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Viewing and Editing User -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white" id="viewUserModalLabel">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="userForm" action="code.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" id="userId">
            <input type="hidden" name="old_image" id="oldImage">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="fw-bold text-dark">Name</label>
                <input type="text" name="name" id="userName" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="fw-bold text-dark">Email</label>
                <input type="email" name="email" id="userEmail" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="fw-bold text-dark">Phone</label>
                <input type="text" name="phone" id="userPhone" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="fw-bold text-dark">Date Created</label>
                <input type="text" id="userCreatedAt" class="form-control" disabled>
              </div>
              <div class="col-md-6">
                <label class="fw-bold text-dark">Role</label>
                <select name="role_as" id="userRole" class="form-control" required>
                    <option value="0">User</option>
                    <option value="1">Admin</option>
                    <option value="2">Artist</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="fw-bold text-dark">Upload New Image</label>
                <input type="file" name="image" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="fw-bold text-success">Current Image</label><br>
                <img src="" id="userImage" class="img-thumbnail mt-1" width="60" height="60" alt="User Image">
              </div>
            </div>
            <div class="mt-4 text-end">
                <button type="submit" name="update_user_btn" class="btn btn-dark">
                    <i class="fa fa-refresh me-1"></i>Update
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include('admin-chat-widget.php'); ?>
<?php include('include/footer.php'); ?>

<script>
    const viewUserModal = document.getElementById('viewUserModal');
    viewUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('userId').value = button.getAttribute('data-id');
        document.getElementById('userName').value = button.getAttribute('data-name');
        document.getElementById('userEmail').value = button.getAttribute('data-email');
        document.getElementById('userPhone').value = button.getAttribute('data-phone');
        document.getElementById('userCreatedAt').value = button.getAttribute('data-created_at');
        document.getElementById('userRole').value = button.getAttribute('data-role_as');
        const image = button.getAttribute('data-image');
        document.getElementById('userImage').src = image ? '../userimage/' + image : 'default-image.jpg';
        document.getElementById('oldImage').value = image;
    });
</script>
