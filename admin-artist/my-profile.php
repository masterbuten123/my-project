<?php
session_start();
include('functions/myfunctions.php');
include('includes/header.php');

if (!isset($_SESSION['auth_user']['account_id'])) {
    $_SESSION['alert'] = [
        'type' => 'error',
        'message' => 'You need to be logged in to view this page.'
    ];
    header('Location: index.php');
    exit();
}

$account_id = $_SESSION['auth_user']['account_id'];

$query = "
    SELECT a.account_id,
           a.name,
           a.email,
           a.phone,
           a.image,
           ui.age,
           ui.address,
           ui.gender,
           ui.location,
           ui.price_per_hour AS price,
           ui.bank_name,
           ui.bank_account,
           ui.gcash_number,
           ui.cover_image,
           ui.website,
           ui.facebook,
           ui.instagram,
           ui.youtube,
           ui.tiktok,
           ui.bio,
           ui.genre,
           ui.resume,
           a.created_at,
           a.updated_at
    FROM accounts a
    LEFT JOIN user_information ui ON a.account_id = ui.account_id
    WHERE a.account_id = '$account_id'
    LIMIT 1
";

$result = mysqli_query($con, $query);
$data   = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;
?>

<div class="container py-5">
    <div class="card shadow-sm p-4">
        <h3 class="mb-4">My Information</h3>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-<?= $_SESSION['alert']['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['alert']['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>

        <?php if ($data): ?>
            <form id="profileForm" action="code.php" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="account_id" value="<?= htmlspecialchars($data['account_id']) ?>">

                <div class="row">
                    <!-- Left Column: Form Fields -->
                    <div class="col-lg-8">

                        <div class="mb-3">
                            <label for="name" class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control"
                                   value="<?= htmlspecialchars($data['name']) ?>" placeholder="Your full name" required>
                            <div class="invalid-feedback">Please enter your name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($data['email']) ?>" placeholder="you@example.com" required>
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label fw-bold">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   value="<?= htmlspecialchars($data['phone']) ?>" placeholder="+63 912 345 6789">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">Address</label>
                            <input type="text" id="address" name="address" class="form-control"
                                   value="<?= htmlspecialchars($data['address']) ?>" placeholder="Your address">
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label fw-bold">Bio</label>
                            <textarea id="bio" name="bio" class="form-control" rows="3"
                                      placeholder="Tell us about yourself"><?= htmlspecialchars($data['bio']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="genre" class="form-label fw-bold">Genre</label>
                                <input type="text" id="genre" name="genre" class="form-control"
                                       value="<?= htmlspecialchars($data['genre']) ?>" placeholder="Music genre">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label fw-bold">Gender</label>
                                <select id="gender" name="gender" class="form-select">
                                    <option value="" <?= $data['gender'] == '' ? 'selected' : '' ?>>Select Gender</option>
                                    <option value="Male"   <?= $data['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $data['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other"  <?= $data['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="age" class="form-label fw-bold">Age</label>
                                <input type="number" id="age" name="age" class="form-control"
                                       value="<?= htmlspecialchars($data['age']) ?>" min="0" placeholder="Your age">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label fw-bold">Price per Hour</label>
                                <input type="number" id="price" name="price" class="form-control"
                                       value="<?= htmlspecialchars($data['price']) ?>" step="0.01" min="0" placeholder="₱">
                            </div>
                        </div>
                        <div class="row">
                            <!-- BANK INFO -->
                            <div class="col-md-6 mb-3">
                                <label for="bank_name" class="form-label fw-bold">Bank Name</label>
                                <input type="text" id="bank_name" name="bank_name" class="form-control"
                                    value="<?= htmlspecialchars($data['bank_name'] ?? '') ?>" placeholder="e.g. BDO, BPI">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bank_account" class="form-label fw-bold">Bank Account</label>
                                <input type="text" id="bank_account" name="bank_account" class="form-control"
                                    value="<?= htmlspecialchars($data['bank_account'] ?? '') ?>" placeholder="Account Number">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gcash_number" class="form-label fw-bold">GCash Number</label>
                                <input type="text" id="gcash_number" name="gcash_number" class="form-control"
                                    value="<?= htmlspecialchars($data['gcash_number'] ?? '') ?>" placeholder="09XXXXXXXXX">
                            </div>
                        </div>

                        <!-- SOCIAL MEDIA LINKS -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="facebook" class="form-label fw-bold">Facebook</label>
                                <input type="text" id="facebook" name="facebook" class="form-control"
                                    value="<?= htmlspecialchars($data['facebook'] ?? '') ?>" placeholder="Facebook profile link">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="instagram" class="form-label fw-bold">Instagram</label>
                                <input type="text" id="instagram" name="instagram" class="form-control"
                                    value="<?= htmlspecialchars($data['instagram'] ?? '') ?>" placeholder="@username">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="youtube" class="form-label fw-bold">YouTube</label>
                                <input type="text" id="youtube" name="youtube" class="form-control"
                                    value="<?= htmlspecialchars($data['youtube'] ?? '') ?>" placeholder="Channel link">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tiktok" class="form-label fw-bold">TikTok</label>
                                <input type="text" id="tiktok" name="tiktok" class="form-control"
                                    value="<?= htmlspecialchars($data['tiktok'] ?? '') ?>" placeholder="@username">
                            </div>
                        </div>

                        <!-- WEBSITE -->
                        <div class="mb-3">
                            <label for="website" class="form-label fw-bold">Website</label>
                            <input type="text" id="website" name="website" class="form-control"
                                value="<?= htmlspecialchars($data['website'] ?? '') ?>" placeholder="https://example.com">
                        </div>
                        <!-- RESUME UPLOAD -->  
                        <div class="mb-3">
                            <label for="resume" class="form-label fw-bold">Resume (PDF/DOC)</label><br>
                            <?php if (!empty($data['resume'])): ?>
                                <a href="../uploads/resumes/<?= htmlspecialchars($data['resume']) ?>" target="_blank">
                                    View Current Resume
                                </a><br>
                            <?php else: ?>
                                <small class="text-muted">No resume uploaded.</small><br>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx">
                            <input type="hidden" name="old_resume" value="<?= htmlspecialchars($data['resume']) ?>">
                        </div>

                            <!-- Recording Upload Form -->


                        <!-- Submit button -->
                        <button type="submit" name="update_profile_btn" class="btn btn-primary">Save Profile</button>
                    </div>

<div class="col-lg-4 d-flex flex-column align-items-center">
    <!-- Profile Image -->
    <label for="image" class="form-label fw-bold">Profile Image</label>
    <img id="previewProfile" 
         src="/<?= htmlspecialchars($data['image'] ?: '2.png') ?>" 
         alt="Profile Image"
         class="rounded-circle mb-3"
         style="width: 180px; height: 180px; object-fit: cover;">
    <div class="mb-3">
        <input type="file" class="form-control" id="image" name="image" accept="image/*">
        <input type="hidden" name="old_image" value="<?= htmlspecialchars($data['image'] ?? '') ?>">
    </div>

    <!-- Cover Image -->
    <label for="cover_image" class="form-label fw-bold">Cover Image</label>
    <?php if (!empty($data['cover_image'])): ?>
    <?php endif; ?>
<img id="previewCover" 
     src="../uploads/covers/<?= htmlspecialchars($data['cover_image'] ?: '../uploads/covers/2.jpg') ?>" 
     alt="Cover Image"
     class="mb-3"
     style="width: 300px; height: 120px; object-fit: cover;">
    <div class="mb-3">
        <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
        <input type="hidden" name="old_cover_image" value="<?= htmlspecialchars($data['cover_image'] ?? '') ?>">
    </div>
</div>

                </div>
            </form>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <!-- ...modal content here... -->
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>



<script>
// Live preview for profile image
document.getElementById('image').addEventListener('change', function(event) {
    const preview = document.getElementById('previewProfile');
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => preview.src = e.target.result;
        reader.readAsDataURL(file);
    }
});

// Live preview for cover image
document.getElementById('cover_image').addEventListener('change', function(event) {
    const preview = document.getElementById('previewCover');
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => preview.src = e.target.result;
        reader.readAsDataURL(file);
    }
});
</script>