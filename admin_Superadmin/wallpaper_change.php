<?php
include('function/myfunctions.php');
include('include/header.php');

$result = getAllWallpapers(); 
?>

<!-- Modal for Add Wallpaper Form -->
<div class="modal fade" id="addWallpaperModal" tabindex="-1" aria-labelledby="addWallpaperModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="addWallpaperModalLabel">Add Wallpaper</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="code.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="mb-2 text-dark fw-bold">Wallpaper Name</label>
                            <input type="text" required name="name" placeholder="Enter wallpaper name" class="form-control mb-2">
                        </div>
                        <div class="col-md-12">
                            <label class="mb-2 text-dark fw-bold">Description</label>
                            <textarea rows="3" name="description" placeholder="Enter description" class="form-control mb-2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="mb-0 text-dark fw-bold">Upload Image</label>
                            <input type="file" name="image" accept="image/*" class="form-control mb-2">
                        </div>
                        <input type="hidden" name="uploaded_by" value="<?= $uploaded_by ?? 'admin' ?>">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-dark mt-2" name="add_wallpaper_btn">
                                <i class="fa fa-save me-1"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Display uploaded wallpapers -->
<div class="col-md-12 mt-4">
    <div class="card">
        <div class="card-header bg-danger">
            <h4 class="text-white">Uploaded Wallpapers</h4>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Wallpaper Name</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($wallpaper = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($wallpaper['name']) ?></td>
                                <td><?= htmlspecialchars($wallpaper['description']) ?></td>
                                <td>
                                    <img src="../uploads/wallpapers/<?= htmlspecialchars($wallpaper['image']) ?>" alt="<?= htmlspecialchars($wallpaper['name']) ?>" style="height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    <span class="badge <?= $wallpaper['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($wallpaper['status']) ?>
                                    </span>
                                </td>
                                <td class="d-flex justify-content-start">
                                    <!-- Toggle Status Button -->
                                    <form action="code.php" method="POST" class="me-2" onsubmit="return confirm('Are you sure you want to change the status?')">
                                        <input type="hidden" name="wallpaper_id" value="<?= $wallpaper['id'] ?>">
                                        <button type="submit" name="toggle_status_btn" class="btn btn-sm <?= $wallpaper['status'] === 'active' ? 'btn-secondary' : 'btn-success' ?>">
                                            <?= $wallpaper['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                    <!-- Optional: Add Edit/Delete buttons if needed -->
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No wallpapers uploaded yet.</p>
            <?php endif; ?>

            <!-- Add Wallpaper Button -->
            <div class="text-start mt-3">
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addWallpaperModal">
                    <i class="fa fa-plus me-1"></i> Add Wallpaper
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('include/footer.php'); ?>
<?php include('admin-chat-widget.php') ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
