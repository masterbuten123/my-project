<?php
session_start();

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Museo - Login</title>

    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <style>
        body, html {
            height: 100%;
            font-family: 'Roboto', sans-serif;
        }
        .left-panel {
            background: linear-gradient(to bottom right, #747678ff, #7b7b7cff);
            color: white;
        }
        .left-panel-content {
            text-align: center;
            padding: 2rem;
        }
        .left-panel img {
            max-width: 150px;
            margin-bottom: 1.5rem;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="container-fluid min-vh-100">
    <div class="row vh-100">

        <!-- Left Side -->
        <div class="col-md-6 left-panel d-flex align-items-center justify-content-center">
            <div class="left-panel-content">
                <img src="assets/logo/1.png" alt="Logo">
                <h1 class="fw-bold">Welcome Museo</h1>
                <p class="lead">Log in to continue and enjoy all the features we’ve prepared for you.</p>
            </div>
        </div>

        <!-- Right Side (Login Form) -->
        <div class="col-md-6 d-flex justify-content-center align-items-center bg-light">
            <div style="width: 100%; max-width: 400px;">
                <form method="POST" action="authcode.php" class="p-4 border rounded bg-white shadow-sm">
                    <h4 class="mb-4 text-center">Login</h4>
               <div class="mb-3">
                    <label for="loginIdentifier" class="form-label">Email or Username</label>
                    <input type="text" name="identifier" id="loginIdentifier" class="form-control" placeholder="Enter your email or username" required autofocus>
                </div>

                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="d-grid mt-3">
                        <button type="submit" name="login_btn" class="btn btn-primary">Login</button>
                    </div>

                    <div class="text-center mt-3">
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                            <i class="fa fa-key me-2"></i>Forgot Password
                        </button>
                    </div>

                    <p class="text-center mt-3 mb-0">
                        Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal">Register here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Register / Apply Artist Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="registerModalLabel">Join Our Platform</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="registerTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#userRegister" type="button" role="tab">
                            Register as User
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="artist-tab" data-bs-toggle="tab" data-bs-target="#artistRegister" type="button" role="tab">
                            Apply as Artist
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="registerTabContent">
                    
                    <!-- USER REGISTER TAB -->
                    <div class="tab-pane fade show active" id="userRegister" role="tabpanel" aria-labelledby="user-tab">
                        <form action="authcode.php" method="POST">
                            <div class="mb-3 text-center">
                                <label class="form-label fw-bold">Profile Image</label>
                                <div class="mb-2">
                                    <img id="userImagePreview" src="uploads/profiles/2.jpg" alt="Profile Preview" style="width:100px; height:100px; object-fit:cover; border-radius:50%;">
                                </div>
                                <label class="btn btn-outline-secondary">
                                    Choose Image
                                    <input type="file" name="image" accept="image/*" id="userImageInput" hidden>
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <input type="text" name="username" required class="form-control" placeholder="Enter your username" value="<?= $_SESSION['old']['username'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">name</label>
                                <input type="text" name="name" required class="form-control" placeholder="Enter your name" value="<?= $_SESSION['old']['name'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email address</label>
                                <input type="email" name="email" required class="form-control" placeholder="Enter your email" value="<?= $_SESSION['old']['email'] ?? '' ?>">
                            </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Date of Birth</label>
                                   <input type="date" name="date_of_birth" required class="form-control" value="<?= $_SESSION['old']['date_of_birth'] ?? '' ?>">
                                </div>
                                <div class="mb-3">
                                <label class="form-label fw-bold">Address</label>
                                <input type="text" name="address" required class="form-control" placeholder="Enter your address" value="<?= $_SESSION['old']['address'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="text" name="phone" pattern="\d{11}" title="Phone number should be 11 digits long" class="form-control" required placeholder="Enter your phone number" value="<?= $_SESSION['old']['phone'] ?? '' ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" required class="form-control" placeholder="Enter password" minlength="8">
                                <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Confirm Password</label>
                                <input type="password" name="cpassword" required class="form-control" placeholder="Confirm password">
                            </div>
                            <button type="submit" name="register_btn" class="btn btn-danger w-100">
                                <i class="fa fa-registered me-2"></i>Register
                            </button>
                        </form>
                    </div>

                    <!-- ARTIST APPLY TAB -->
                    <div class="tab-pane fade" id="artistRegister" role="tabpanel" aria-labelledby="artist-tab">
                        <form action="authcode.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-3 text-center">
                                <label class="form-label fw-bold">Profile Image</label>
                                <div class="mb-2">
                                    <img id="artistImagePreview" src="uploads/profiles/2.jpg" alt="Profile Preview" style="width:100px; height:100px; object-fit:cover; border-radius:50%;">
                                </div>
                                <label class="btn btn-outline-secondary">
                                    Choose Image
                                    <input type="file" name="image" accept="image/*" id="artistImageInput" hidden>
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Stage Name</label>
                                <input type="text" name="username" required class="form-control" placeholder="Enter your stage/artist name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Name</label>
                                <input type="text" name="name" required class="form-control" placeholder="Enter your stage/artist name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" required class="form-control" placeholder="Enter your email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone</label>
                                <input type="number" name="phone" required class="form-control" placeholder="Enter your phone number">
                            </div>
                              <div class="mb-3">
                                <label class="form-label fw-bold">address</label>
                                <input type="text" name="address" required class="form-control" >    
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Genre</label>
                                <input type="text" name="genre" required class="form-control" placeholder="Enter your genre (e.g. Rock, Jazz, Pop)">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Bio</label>
                                <textarea name="bio" rows="3" required class="form-control" placeholder="Tell us about yourself"></textarea>
                            </div>
                            <div class="mb-3">
                                    <label class="form-label fw-bold">Date of Birth</label>
                                    <input type="date" name="date_of_birth" required class="form-control" value="<?= $_SESSION['old']['date_of_birth'] ?? '' ?>">

                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Password</label>
                                <input type="password" name="password" required class="form-control" placeholder="Create password" minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Confirm Password</label>
                                <input type="password" name="cpassword" required class="form-control" placeholder="Confirm password">
                            </div>
                            <button type="submit" name="apply_artist" class="btn btn-success w-100">
                                <i class="fa fa-music me-2"></i>Apply as Artist
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="authcode.php" method="POST">
                    <input type="email" name="email" required placeholder="Enter your email" class="form-control mb-2">
                    <div class="d-grid mt-3">
                        <button type="submit" name="send_code_btn" class="btn btn-primary">Send Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const userImageInput = document.querySelector('#userImageInput');
const userImagePreview = document.querySelector('#userImagePreview');
const artistImageInput = document.querySelector('#artistImageInput');
const artistImagePreview = document.querySelector('#artistImagePreview');

userImageInput.addEventListener('change', function() {
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = e => userImagePreview.src = e.target.result;
        reader.readAsDataURL(file);
    } else {
        userImagePreview.src = 'assets/logo/default-profile.png';
    }
});

artistImageInput.addEventListener('change', function() {
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = e => artistImagePreview.src = e.target.result;
        reader.readAsDataURL(file);
    } else {
        artistImagePreview.src = 'assets/logo/default-profile.png';
    }
});

document.addEventListener("DOMContentLoaded", function () {
    <?php if (!empty($alert)): ?>
        Swal.fire({
            icon: "<?= $alert['type'] ?>", // success, error, warning, info
            title: "<?= ($alert['type'] === 'success') ? 'Success!' : ucfirst($alert['type']) ?>",
            text: "<?= $alert['message'] ?>",
            confirmButtonText: "OK"
        }).then(() => {
            <?php if ($alert['type'] === 'success'): ?>
                // After success (e.g., registration complete / verified), focus on login email
                document.querySelector('#loginEmail')?.focus();
            <?php endif; ?>
        });
    <?php endif; ?>

    <?php if (!empty($_SESSION['registration_errors'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Registration Failed',
            html: `<ul style="text-align:left;">
                <?php foreach($_SESSION['registration_errors'] as $err): ?>
                    <li><?= $err ?></li>
                <?php endforeach; ?>
            </ul>`
        });
    <?php endif; ?>
});
</script>

<?php unset($_SESSION['alert'], $_SESSION['registration_errors']); ?>

</body>
</html>
