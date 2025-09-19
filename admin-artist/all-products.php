<?php
session_start();
include('functions/myfunctions.php');
include('includes/header.php');


// Artist account
$account_id = $_SESSION['auth_user']['account_id'] ?? 0;

// Sorting
$sort_by = $_GET['sort_by'] ?? 'name';
$sort_order = $_GET['sort_order'] ?? 'ASC';

// Fetch products & categories using functions
$products = get_artist_products($con, $account_id, $sort_by, $sort_order);
$categories = get_active_categories($con);
?>
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger">
                    <h4 class="text-white">My Products</h4>
                </div>
                <div class="card-body">

                    <!-- Sorting -->
                    <form method="GET" class="mb-3">
                        <label for="sort_by" class="me-2">Sort by:</label>
                        <select name="sort_by" class="form-select d-inline-block w-auto">
                            <option value="name" <?= $sort_by=='name'?'selected':'' ?>>Name</option>
                            <option value="price" <?= $sort_by=='price'?'selected':'' ?>>Price</option>
                            <option value="quantity" <?= $sort_by=='quantity'?'selected':'' ?>>Quantity</option>
                        </select>
                        <label for="sort_order" class="ms-3 me-2">Order:</label>
                        <select name="sort_order" class="form-select d-inline-block w-auto">
                            <option value="ASC" <?= $sort_order=='ASC'?'selected':'' ?>>Ascending</option>
                            <option value="DESC" <?= $sort_order=='DESC'?'selected':'' ?>>Descending</option>
                        </select>
                        <button class="btn btn-secondary ms-2">Sort</button>
                    </form>

                    <!-- Products Table -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($products): foreach($products as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']); ?></td>
                                <td><?= htmlspecialchars($row['category_name']); ?></td>
                                <td><?= htmlspecialchars($row['description']); ?></td>
                                <td>₱<?= number_format($row['price'],2); ?></td>
                                <td><?= $row['stock']; ?></td>
                                <td>
                                    <?php if(!empty($row['image'])): ?>
                                        <img src="../uploads/products/<?= htmlspecialchars($row['image']); ?>" width="50">
                                    <?php else: ?>
                                        <span class="text-muted">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= ucfirst($row['status']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm editProductBtn"
                                        data-bs-toggle="modal" data-bs-target="#editProductModal"
                                        data-id="<?= $row['product_id']; ?>"
                                        data-name="<?= htmlspecialchars($row['name']); ?>"
                                        data-description="<?= htmlspecialchars($row['description']); ?>"
                                        data-price="<?= $row['price']; ?>"
                                        data-quantity="<?= $row['stock']; ?>"
                                        data-image="<?= htmlspecialchars($row['image']); ?>"
                                        data-category_id="<?= $row['category_id']; ?>"
                                        data-status="<?= $row['status']; ?>">
                                        Edit
                                    </button>
                                    <form method="POST" action="code.php" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
                                        <button type="submit" name="delete_prod_btn" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="8" class="text-center text-muted">No products found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="code.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header bg-danger">
          <h5 class="modal-title text-white">Add Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-12 mb-2">
                <label class="mb-0 fw-bold">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    <?php
                    $categories = mysqli_query($con, "SELECT category_id, name FROM categories WHERE status='active'");
                    while($cat = mysqli_fetch_assoc($categories)) {
                        echo "<option value='{$cat['category_id']}'>{$cat['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label>Price</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="stock" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Image</label>
                <input type="file" name="image" class="form-control" id="addProductImage" required>
                <div id="addImagePreview" class="mt-2"></div>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" name="add_prod_btn" class="btn btn-dark">Save</button>
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="code.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header bg-success">
          <h5 class="modal-title text-white">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="product_id">
            <input type="hidden" name="old_image">

            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Category</label>
                <select name="category_id" class="form-select" required></select>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label>Price</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="stock" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Current Image</label>
                <div class="image-preview mb-2"></div>
                <input type="file" name="image" class="form-control">
                <small class="text-muted">Leave empty to keep current image</small>
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" name="update_prod_btn" class="btn btn-success">Update</button>
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('artist-chat-widget.php'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    // Add image preview
    $('#addProductImage').on('change', function(){
        const file = this.files[0];
        const preview = $('#addImagePreview');
        if(file){
            const reader = new FileReader();
            reader.onload = function(e){ preview.html('<img src="'+e.target.result+'" width="100">'); }
            reader.readAsDataURL(file);
        } else { preview.html(''); }
    });

    // Edit product modal
    $('.editProductBtn').on('click', function(){
        var modal = $('#editProductModal');
        modal.find('input[name="product_id"]').val($(this).data('id'));
        modal.find('input[name="name"]').val($(this).data('name'));
        modal.find('textarea[name="description"]').val($(this).data('description'));
        modal.find('input[name="price"]').val($(this).data('price'));
        modal.find('input[name="stock"]').val($(this).data('quantity'));
        modal.find('input[name="old_image"]').val($(this).data('image'));
        modal.find('.image-preview').html($(this).data('image') ? '<img src="../uploads/products/'+$(this).data('image')+'" width="50">' : 'No image');

        // Populate categories dropdown
        var select = modal.find('select[name="category_id"]');
        select.html('');
        select.append('<option value="">-- Select Category --</option>');
        <?php foreach($categories as $cat): ?>
            select.append('<option value="<?= $cat['category_id']; ?>"><?= addslashes($cat['name']); ?></option>');
        <?php endforeach; ?>
        select.val($(this).data('category_id'));

        // Status
        modal.find('select[name="status"]').val($(this).data('status'));
    });
});
</script>
