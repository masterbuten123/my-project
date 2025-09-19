<?php
include('function/myfunctions.php');
include('include/header.php');

// Get the sorting option from the GET request or default to 'name'
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Determine the SQL order by clause based on the selected sorting criteria
$sort_column = 'name'; // Default sorting by name
if ($sort_by == 'price') {
    $sort_column = 'price';
} elseif ($sort_by == 'quantity') {
    $sort_column = 'stock';
}

// Set the sort order (ASC or DESC)
$query = "SELECT * FROM products ORDER BY $sort_column $sort_order";
$result = mysqli_query($con, $query);
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger">
                    <h4 class="text-white">All Products</h4>
                </div>
                <div class="card-body" id="product_table">

                    <!-- Sorting Dropdown -->
                    <form method="GET" class="mb-3">
                        <label for="sort_by" class="me-2">Sort by:</label>
                        <select name="sort_by" id="sort_by" class="form-select d-inline-block w-auto">
                            <option value="name" <?= $sort_by == 'name' ? 'selected' : '' ?>>Name</option>
                            <option value="price" <?= $sort_by == 'price' ? 'selected' : '' ?>>Price</option>
                            <option value="quantity" <?= $sort_by == 'quantity' ? 'selected' : '' ?>>Quantity</option>
                        </select>
                        <label for="sort_order" class="ms-3 me-2">Order:</label>
                        <select name="sort_order" id="sort_order" class="form-select d-inline-block w-auto">
                            <option value="ASC" <?= $sort_order == 'ASC' ? 'selected' : '' ?>>Ascending</option>
                            <option value="DESC" <?= $sort_order == 'DESC' ? 'selected' : '' ?>>Descending</option>
                        </select>

                        <button type="submit" class="btn btn-secondary ms-2">Sort</button>
                    </form>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-dark fw-bold">Name</th>
                                <th class="text-dark fw-bold">Description</th>
                                <th class="text-dark fw-bold">Price</th>
                                <th class="text-dark fw-bold">Quantity</th>
                                <th class="text-dark fw-bold">Image</th>
                                <th class="text-dark fw-bold">Status</th>
                                <th class="text-dark fw-bold">Created At</th>
                                <th class="text-dark fw-bold">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch all products
                            $products = get_products();
                            if (mysqli_num_rows($products) > 0) {
                                while ($item = mysqli_fetch_assoc($products)) {
                            ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['name']); ?></td>
                                        <td><?= htmlspecialchars($item['description']); ?></td>
                                        <td><?= number_format($item['price'], 2); ?></td>
                                        <td><?= htmlspecialchars($item['stock']); ?></td>
                                        <td><img src="../uploads/products/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover;"></td>
                                        <td><?= htmlspecialchars($item['status']); ?></td>
                                        <td><?= date('d-m-Y', strtotime($item['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal"
                                                data-id="<?= $item['product_id']; ?>"
                                                data-name="<?= htmlspecialchars($item['name']); ?>"
                                                data-description="<?= htmlspecialchars($item['description']); ?>"
                                                data-price="<?= $item['price']; ?>"
                                                data-quantity="<?= $item['stock']; ?>"
                                                data-image="<?= $item['image']; ?>"
                                                data-status="<?= $item['status']; ?>"
                                                data-created_at="<?= $item['created_at']; ?>"
                                                data-category_id="<?= $item['category_id']; ?>">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="8">No Products Found</td></tr>';
                            }
                            ?>
                        </tbody>
                        
                    </table>
            <!-- Add Product Button -->
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="fa fa-plus"></i> Add Category</button>
                </div>
            </div>


        </div>
    </div>
</div>
<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="code.php" method="POST">
        <div class="modal-header bg-primary">
          <h5 class="modal-title text-white">Add New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
          </div>
          <button type="submit" name="add_category_btn" class="btn btn-success mb-3">Save</button>

          <!-- Categories Table -->
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $categories = mysqli_query($con, "SELECT * FROM categories WHERE is_deleted=0");
              while($cat = mysqli_fetch_assoc($categories)){
              ?>
                <tr>
                  <td><?= htmlspecialchars($cat['name']); ?></td>
                  <td><?= htmlspecialchars($cat['description']); ?></td>
                  <td><?= htmlspecialchars($cat['status']); ?></td>
                  <td>
                    <button type="button" class="btn btn-sm btn-warning editCategoryBtn"
                      data-id="<?= $cat['category_id']; ?>"
                      data-name="<?= htmlspecialchars($cat['name']); ?>"
                      data-description="<?= htmlspecialchars($cat['description']); ?>"
                      data-status="<?= $cat['status']; ?>"
                      data-bs-toggle="modal" data-bs-target="#editCategoryModal">Edit</button>
                    <form action="code.php" method="POST" class="d-inline">
                      <input type="hidden" name="category_id" value="<?= $cat['category_id']; ?>">
                      <button type="submit" name="delete_category_btn" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="code.php" method="POST">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="category_id" id="edit_category_id">
          <div class="mb-3">
            <label>Category Name</label>
            <input type="text" name="name" id="edit_category_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Description</label>
            <textarea name="description" id="edit_category_description" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select name="status" id="edit_category_status" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_category_btn" class="btn btn-success">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Fill Edit Category Modal
  document.querySelectorAll('.editCategoryBtn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.getElementById('edit_category_id').value = this.dataset.id;
      document.getElementById('edit_category_name').value = this.dataset.name;
      document.getElementById('edit_category_description').value = this.dataset.description;
      document.getElementById('edit_category_status').value = this.dataset.status;
    });
  });
</script>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog ">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white" id="addProductModalLabel">Add Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="code.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
                <label class="fw-bold text-dark">Category</label>
                <select name="category_id" class="form-select" required>
                  <?php
                  $categories = mysqli_query($con, "SELECT * FROM categories WHERE status='active'");
                  while ($cat = mysqli_fetch_assoc($categories)) {
                      echo "<option value='{$cat['category_id']}'>{$cat['name']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Name</label>
              <input type="text" required name="name" placeholder="Enter product name" class="form-control mb-2">
            </div>
            <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Description</label>
              <textarea rows="3" required name="description" placeholder="Enter description" class="form-control mb-2"></textarea>
            </div>
            <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Price</label>
              <input type="number" required name="price" placeholder="Enter price" class="form-control mb-2">
            </div>
            <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Upload Image</label>
              <input type="file" required name="image" class="form-control mb-2">
            </div>
            <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Quantity</label>
              <input type="number" required name="stock" placeholder="Enter quantity" class="form-control mb-2">
            </div>
             <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Tags</label>
              <input type="text" name="tags" placeholder="Enter tags" class="form-control mb-2">
            </div>
            <div class="col-md-12">
              <br>
              <div class="col-md-6 mt-2">
              <label class="fw-bold text-dark">Status</label>
              <select name="status" id="editStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            </div>
          </div>
        </div>
          <div class="modal-footer">
          <button type="submit" class="btn btn-dark" name="add_prod_btn"><i class="fa fa-save me-1"></i>Save</button>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fa fa-times me-1"></i>Cancel</button>
        </div>
       </div>
      </form>
    </div>
  </div>
</div>
<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title text-white" id="editProductModalLabel">Edit Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="code.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">
            <input type="hidden" name="product_id">
            <input type="hidden" name="old_image">

            <!-- Category Dropdown -->
            <div class="col-md-12">
              <label class="fw-bold text-dark">Category</label>
              <select name="category_id" id="edit_category_id" class="form-select" required>
                <?php
                $categories = mysqli_query($con, "SELECT * FROM categories WHERE status='active'");
                while ($cat = mysqli_fetch_assoc($categories)) {
                    echo "<option value='{$cat['category_id']}'>{$cat['name']}</option>";
                }
                ?>
              </select>
            </div>

            <div class="col-md-12 mt-2">
              <label class="mb-0 text-dark fw-bold">Name</label>
              <input type="text" name="name" id="edit_product_name" class="form-control">
            </div>
            <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Description</label>
              <textarea name="description" id="edit_product_description" class="form-control"></textarea>
            </div>
            <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Price</label>
              <input type="number" name="price" id="edit_product_price" class="form-control">
            </div>
            <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Upload Image</label>
              <input type="file" name="image" class="form-control mb-2">
              <label class="mb-0 text-dark fw-bold">Current Image</label><br>
              <img id="current_image" src="" alt="Product image" width="50" height="50" style="display: none;">
            </div>
            <div class="col-md-12">
              <label class="mb-0 text-dark fw-bold">Quantity</label>
              <input type="number" name="stock" id="edit_product_quantity" class="form-control">
            </div>
            <div class="col-md-6 mt-2">
              <label class="fw-bold text-dark">Status</label>
              <select name="status" id="editStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success" name="update_prod_btn">
            <i class="fa fa-refresh me-1"></i>Update
          </button>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            <i class="fa fa-times me-1"></i>Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    // Edit Product Modal - Event Listener
    var editProductModal = document.getElementById('editProductModal');
    editProductModal.addEventListener('show.bs.modal', function (event) { 
        var button = event.relatedTarget;  // The button that triggered the modal
       
        // Retrieve data attributes from the button
        var productId = button.getAttribute('data-id');
        var productName = button.getAttribute('data-name');
        var productDescription = button.getAttribute('data-description');
        var productPrice = button.getAttribute('data-price');
        var productQuantity = button.getAttribute('data-quantity');
        var productImage = button.getAttribute('data-image');
        var productStatus = button.getAttribute('data-status');
        var productCreatedAt = button.getAttribute('data-created_at');

        // Populate the modal with the product data
        document.getElementById('editProductModal').querySelector('input[name="product_id"]').value = productId;
        document.getElementById('editProductModal').querySelector('input[name="old_image"]').value = productImage;
        document.getElementById('editProductModal').querySelector('input[name="name"]').value = productName;
        document.getElementById('editProductModal').querySelector('textarea[name="description"]').value = productDescription;
        document.getElementById('editProductModal').querySelector('input[name="price"]').value = productPrice;
        document.getElementById('editProductModal').querySelector('input[name="stock"]').value = productQuantity;
        document.getElementById('editProductModal').querySelector('select[name="status"]').value = productStatus;

        // Set the current product image preview
        var imagePreview = document.getElementById('current_image');
        imagePreview.style.display = 'block';  // Show the image preview
        imagePreview.src = "../uploads/" + productImage;  // Set image source
    });

    // Handle image input (optional: you can show preview before saving)
    document.querySelector('input[name="image"]').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (event) {
                var imagePreview = document.getElementById('current_image');
                imagePreview.src = event.target.result;  // Set preview to selected image
                imagePreview.style.display = 'block';  // Display the preview
            };
            reader.readAsDataURL(file);
        }
    });
</script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>

<?php include('admin-chat-widget.php'); ?>

<?php
if (isset($_SESSION['update_message'])) {
    $msg = $_SESSION['update_message'];
    unset($_SESSION['update_message']);
    echo "<script>
    Swal.fire({
        icon: '".(strpos($msg, 'success')!==false ? "success" : (strpos($msg, 'exists')!==false ? "warning" : "error"))."',
        title: 'Notification',
        text: '$msg',
        confirmButtonColor: '#d33'
    });
    </script>";
}
?>