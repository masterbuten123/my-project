<?php
session_start();
include('includes/header.php');
include('config/dbcon.php');

// Redirect if not logged in
if (!isset($_SESSION['auth_user']['account_id'])) {
    echo "<script>alert('You need to be logged in first.'); window.location.href='index.php';</script>";
    exit();
}

$account_id = $_SESSION['auth_user']['account_id'];

// Fetch account info
$stmt = $con->prepare("SELECT * FROM accounts WHERE account_id=?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
body{
    background-color: #f8f9fa; /* lighter for better contrast */
}
.nav-tabs .nav-link {
    color: black;
}
.nav-tabs .nav-link.active {
    color: black;
    background-color: #e9ecef;
    border-color: #dee2e6 #dee2e6 #fff;
}
</style>
<body>
<div class="container mt-4 text-dark">
    <h3>Account Settings</h3>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="settingsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">Profile</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button">Settings</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="subscription-tab" data-bs-toggle="tab" data-bs-target="#subscription" type="button">Subscription</button>
        </li>
    </ul>

    <div class="tab-content mt-3 text-dark" id="settingsTabContent">
        <!-- Profile Tab -->
        <div class="tab-pane fade show active" id="profile">
            <h5>Profile Info</h5>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade" id="settings">
            <h5>Apply as Artist</h5>
            <button class="btn btn-primary" id="applyArtistBtn" <?= $user['role'] === 'artist' ? 'disabled' : '' ?>>
                <?= $user['role'] === 'artist' ? 'Already an Artist' : 'Apply as Artist' ?>
            </button>
            <span id="artistMessage" class="ms-2 text-success" style="display: none;">You are now an artist!</span>

            <h5 class="mt-4">Change Password</h5>
            <button class="btn btn-warning" id="changePasswordBtn">Change Password</button>
        </div>

        <!-- Subscription Tab -->
        <div class="tab-pane fade" id="subscription">
            <h5>Subscription Details</h5>
            <?php
            $plan_name   = $user['plan_name'] ?? 'Free';
            $plan_status = $user['plan_status'] ?? 'Inactive';
            $plan_expiry = $user['plan_expiry'] ?? '-';
            ?>
            <p><strong>Current Plan:</strong> <?= htmlspecialchars($plan_name) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($plan_status) ?></p>
            <p><strong>Expiry Date:</strong> <?= htmlspecialchars($plan_expiry) ?></p>

            <button class="btn btn-primary mt-2" id="upgradePlanBtn">Upgrade Plan</button>
        </div>
    </div>
</div>

<script>
const applyBtn = document.getElementById('applyArtistBtn');

applyBtn?.addEventListener('click', function() {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to apply as an Artist?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to subscription/plan selection page
            window.location.href = 'plan_selection.php';
        }
    });
});

// Change Password
document.getElementById('changePasswordBtn')?.addEventListener('click', function() {
    Swal.fire({
        title: 'Change Password',
        html:
            '<input type="password" id="oldPass" class="swal2-input" placeholder="Old Password">' +
            '<input type="password" id="newPass" class="swal2-input" placeholder="New Password">',
        showCancelButton: true,
        confirmButtonText: 'Update',
        preConfirm: () => {
            const oldPass = document.getElementById('oldPass').value.trim();
            const newPass = document.getElementById('newPass').value.trim();
            if (!oldPass || !newPass) {
                Swal.showValidationMessage('Please fill in all fields');
            }
            return { oldPass, newPass }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const params = new URLSearchParams({
                action: 'change_password',
                oldPass: result.value.oldPass,
                newPass: result.value.newPass
            });
            fetch('functions/settings_actions.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: params
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Updated!', data.message, 'success');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
});

// Upgrade Plan (placeholder)
document.getElementById('upgradePlanBtn')?.addEventListener('click', function() {
    Swal.fire({
        title: 'Upgrade Plan',
        text: 'Subscription upgrade feature will be available soon.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
