<?php
session_start();
include('includes/header.php');
include('config/dbcon.php');

if (!isset($_SESSION['auth_user']['account_id'])) {
    echo "<script>alert('You need to login first'); window.location.href='index.php';</script>";
    exit();
}

$account_id = $_SESSION['auth_user']['account_id'];

$stmt = $con->prepare("
    SELECT a.account_id, a.name, a.email, a.phone, ui.address, a.image
    FROM accounts a
    LEFT JOIN user_information ui ON a.account_id = ui.account_id
    WHERE a.account_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc(); 
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container py-5 text-dark">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Profile Card -->
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">My Profile</h3>

                    <form id="profileForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row mb-3">
                                <div class="col-md-4 text-center">
                                    <div class="position-relative d-inline-block">
                                    <img id="profileImagePreview" 
                                        src="<?= !empty($user['image']) ? htmlspecialchars($user['image']) : 'uploads/profiles/2.jpg' ?>" 
                                        class="rounded-3 img-thumbnail shadow-sm mb-3" 
                                        width="450"
                                        alt="Profile Image">
                                        <!-- Camera Icon Overlay -->
                                        <label for="imageInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow"
                                            style="cursor: pointer; transform: translate(30%, -30%);">
                                            <i class="bi bi-camera-fill"></i>
                                        </label>
                                    </div>
                                    <!-- Hidden File Input -->
                                    <input type="file" name="image" class="d-none" id="imageInput" accept="image/*">
                                </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Phone</label>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Address</label>
                                    <textarea name="address" class="form-control" rows="2" required><?= htmlspecialchars($user['address']) ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>


<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    function showAlert(msg, success, debug=null, newImage=null){
        Swal.fire({
            icon: success ? 'success' : 'error',
            title: success ? 'Success' : 'Error',
            html: debug ? msg + '<br><pre style="text-align:left">'+debug+'</pre>' : msg,
            timer: 4000,
            showConfirmButton: true
        });
        if(newImage) document.getElementById("profileImagePreview").src = newImage;
    }

    // Profile Update
    const profileForm = document.getElementById("profileForm");
    if(profileForm){
        profileForm.addEventListener("submit", function(e){
            e.preventDefault();
            const formData = new FormData(this);
            formData.append("update_profile","1");
            fetch("functions/profile_actions.php", {method: "POST", body: formData})
            .then(res => res.json())
            .then(data => showAlert(data.message, data.success, data.debug ?? null, data.image ?? null))
            .catch(err => showAlert('An error occurred. Check console for details.', false, err));
        });
    }

    // Profile Image Preview
    const imageInput = document.getElementById("imageInput");
    if(imageInput){
        imageInput.addEventListener("change", function(){
            const file = this.files[0];
            if(file){
                const reader = new FileReader();
                reader.onload = e => document.getElementById("profileImagePreview").src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

});
</script>

<?php include('includes/footer.php'); ?>
