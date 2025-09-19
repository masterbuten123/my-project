<?php
session_start();
include('config/dbcon.php'); // MySQL version of dbcon.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function sendVerificationEmail($email, $name, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 1; // 
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mikovargas5@gmail.com';
        $mail->Password   = 'dydwouieefblfyrf'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('mikovargas5@gmail.com', 'Museo Email Verification');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = "Email Verification Code";
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;'>
                <h2 style='color: #0d6efd;'>Museo Email Verification</h2>
                <p>Hi there,</p>
                <p>Your new verification code is:</p>
                <p style='font-size: 24px; font-weight: bold; color: #198754;'>$otp</p>
                <p>Enter this code on the verification page to activate your account.</p>
                <hr>
                <small>If you didn’t request this, you can ignore this email.</small>
            </div>
        ";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

// Helper function to escape strings
function escape($con, $str) {
    return mysqli_real_escape_string($con, $str);
}



if (isset($_POST[''])) {
    // Collect form data safely
    $name     = $_POST['name'] ?? null;
    $email    = $_POST['email'] ?? null;
    $phone    = $_POST['phone'] ?? null;
    $password = $_POST['password'] ?? null;
    $bio      = $_POST['bio'] ?? null;
    $genre    = $_POST['genre'] ?? null;
    $gender   = $_POST['gender'] ?? null;
    $location = $_POST['location'] ?? null;
    $price    = $_POST['price_per_hour'] ?? null;

    echo "<pre>DEBUG: Incoming data:
    name = $name
    email = $email
    phone = $phone
    password = $password
    bio = $bio
    genre = $genre
    gender = $gender
    location = $location
    price = $price
    </pre>";

    // Step 1: Insert into accounts
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $role = "artist";

    $query1 = "INSERT INTO accounts (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
    $stmt1 = $con->prepare($query1);

    if (!$stmt1) {
        die("Prepare failed for accounts insert: " . $con->error);
    }

    $stmt1->bind_param("sssss", $name, $email, $phone, $hashedPassword, $role);

    if (!$stmt1->execute()) {
        die("Account insert failed: " . $stmt1->error);
    }

    $account_id = $stmt1->insert_id;
    echo "<p>DEBUG: Inserted into accounts with account_id = $account_id</p>";

    // Step 2: Insert into user_information
    $query2 = "INSERT INTO user_information (account_id, bio, genre, gender, location, price_per_hour) 
               VALUES (?, ?, ?, ?, ?, ?)";
    $stmt2 = $con->prepare($query2);

    if (!$stmt2) {
        die("Prepare failed for user_information insert: " . $con->error);
    }

    $stmt2->bind_param("issssd", $account_id, $bio, $genre, $gender, $location, $price);

    if (!$stmt2->execute()) {
        die("User_information insert failed: " . $stmt2->error);
    }

    echo "<p>DEBUG: Inserted into user_information successfully</p>";
    echo "<p>Artist registration completed!</p>";
}


if (isset($_POST['apply_artist'])) {
    $username      = mysqli_real_escape_string($con, $_POST['username']);
    $name      = mysqli_real_escape_string($con, $_POST['name']);
    $email     = mysqli_real_escape_string($con, $_POST['email']);
    $phone     = mysqli_real_escape_string($con, $_POST['phone']);
    $password  = mysqli_real_escape_string($con, $_POST['password']);
    $bio       = mysqli_real_escape_string($con, $_POST['bio']);
    $genre     = mysqli_real_escape_string($con, $_POST['genre']);
    $address   = mysqli_real_escape_string($con, $_POST['address'] ?? '');
    $gender    = mysqli_real_escape_string($con, $_POST['gender'] ?? '');
    $date_of_birth = mysqli_real_escape_string($con, $_POST['date_of_birth'] ?? null);
    $price     = mysqli_real_escape_string($con, $_POST['price_per_hour'] ?? 0);

    $image = '';
    $default_image = 'uploads/profiles/2.jpg';
if (!empty($_FILES['image']['name'])) {
    $target_dir = "uploads/artists/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $image = time() . "_" . basename($_FILES['image']['name']);
    $target_file = $target_dir . $image;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $_SESSION['alert'] = ['type'=>'error','message'=>'Failed to upload image!'];
        header("Location: index.php");
        exit();
    }
} else {
    $image = $default_image; // Assign default image if none uploaded
}

    // Check if email already exists
    $check_email = mysqli_query($con, "SELECT * FROM accounts WHERE email='$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['alert'] = ['type'=>'error','message'=>'Email already exists!'];
        header("Location: index.php");
        exit();
    }

    // Hash password and generate OTP
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $otp = rand(100000, 999999);
    $otp_created_at = date('Y-m-d H:i:s');

    mysqli_begin_transaction($con);

    try {
        // Insert into accounts
        $account_sql = "INSERT INTO accounts (name, email, phone, password, image, role, otp, otp_created_at, is_verified) 
                        VALUES ('$name', '$email', '$phone', '$hashed_password', '$image', 'artist', '$otp', '$otp_created_at', 0)";
        if (!mysqli_query($con, $account_sql)) {
            throw new Exception("Account insert failed: " . mysqli_error($con));
        }

        $account_id = mysqli_insert_id($con);

        // Insert into user_information
        $info_sql = "INSERT INTO user_information 
                    (account_id, bio, genre, address, gender, date_of_birth, price_per_hour) 
                     VALUES ('$account_id', '$bio', '$genre', '$address', '$gender', 
                             " . ($date_of_birth ? "'$date_of_birth'" : "NULL") . ", '$price')";
        if (!mysqli_query($con, $info_sql)) {
            throw new Exception("User information insert failed: " . mysqli_error($con));
        }

        mysqli_commit($con);

        $freePlanResult = $con->query("SELECT plan_id, duration_days FROM subscription_plans WHERE price = 0 LIMIT 1");
        $freePlan = $freePlanResult->fetch_assoc();

        if($freePlan){
            $startDate = date("Y-m-d");
            $endDate   = date("Y-m-d", strtotime("+{$freePlan['duration_days']} days"));

            $stmt_sub = mysqli_prepare($con, "INSERT INTO subscriptions 
                (account_id, plan_id, start_date, end_date, status, type, payment_status) 
                VALUES (?, ?, ?, ?, 'active', 'user', 'paid')");
            mysqli_stmt_bind_param($stmt_sub, "iiss", $account_id, $freePlan['plan_id'], $startDate, $endDate);
            mysqli_stmt_execute($stmt_sub);
}

        // Send verification email
        $result = sendVerificationEmail($email, $name, $otp); // make sure this function exists
        if ($result !== true) {
            $_SESSION['alert'] = ['type'=>'warning','message'=>"Artist account created, but verification email could not be sent: $result"];
        } else {
            $_SESSION['alert'] = ['type'=>'success','message'=>'Artist account created successfully! Check your email for OTP verification.'];
        }

        // Redirect to OTP verification page
        $_SESSION['registrationComplete'] = true;
        header("Location: emailverification.php?email=" . urlencode($email));
        exit();

    } catch (Exception $e) {
        mysqli_rollback($con);
        $_SESSION['alert'] = ['type'=>'error','message'=>'Something went wrong: ' . $e->getMessage()];
        header("Location: index.php");
        exit();
    }
}



// ================= REGISTRATION =================
if (isset($_POST['register_btn'])) {
    $username      = trim($_POST['username']);
    $name          = trim($_POST['name']);
    $email         = trim($_POST['email']);
    $address       = trim($_POST['address']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $phone         = trim($_POST['phone']);
    $password      = $_POST['password'];
    $cpassword     = $_POST['cpassword'];

    // Save old input for repopulation
    $_SESSION['old'] = compact('username', 'name', 'email', 'address', 'date_of_birth', 'phone');

    $errors = [];

    // Password validation
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if ($password !== $cpassword) $errors[] = "Passwords do not match.";

    // Check email uniqueness
    $stmt_check = mysqli_prepare($con, "SELECT email FROM accounts WHERE email = ?");
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    if (mysqli_stmt_num_rows($stmt_check) > 0) $errors[] = "Email is already registered.";

    // Check username uniqueness
    $stmt_check_user = mysqli_prepare($con, "SELECT username FROM accounts WHERE username = ?");
    mysqli_stmt_bind_param($stmt_check_user, "s", $username);
    mysqli_stmt_execute($stmt_check_user);
    mysqli_stmt_store_result($stmt_check_user);
    if (mysqli_stmt_num_rows($stmt_check_user) > 0) $errors[] = "Username is already taken.";

    // Handle profile image
    $default_image = '2.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName    = $_FILES['image']['name'];
        $fileSize    = $_FILES['image']['size'];
        $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExt, $allowedExts)) {
            $errors[] = "Invalid image type. Allowed types: " . implode(', ', $allowedExts);
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errors[] = "Image size should not exceed 2MB.";
        } else {
            $newFileName = uniqid('profile_', true) . '.' . $fileExt;
            $uploadDir = __DIR__ . '/../uploads/profiles/';
            if (!move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                $errors[] = "Failed to save uploaded image.";
            } else {
                $default_image = $newFileName;
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        header('Location: index.php');
        exit();
    }

    $otp = rand(100000, 999999);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    mysqli_begin_transaction($con);

    try {
        // Insert into accounts
        $stmt = mysqli_prepare($con, "INSERT INTO accounts 
            (username, name, email, phone, password, role, otp, otp_created_at, is_verified, image, status, plan) 
            VALUES (?, ?, ?, ?, ?, 'user', ?, NOW(), 0, ?, 'pending', 'basic')");
        mysqli_stmt_bind_param($stmt, "sssssss", $username, $name, $email, $phone, $hashed_password, $otp, $default_image);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to insert account: " . mysqli_error($con));
        }
        $account_id = mysqli_insert_id($con);

        // Insert into user_information
        $stmt_info = mysqli_prepare($con, "INSERT INTO user_information (account_id, address, date_of_birth) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_info, "iss", $account_id, $address, $date_of_birth);
        if (!mysqli_stmt_execute($stmt_info)) {
            throw new Exception("Failed to insert user information: " . mysqli_error($con));
        }

        mysqli_commit($con);

        // Auto-assign Free plan if user has no active subscription
        $freePlanResult = $con->query("SELECT plan_id, duration_days FROM subscription_plans WHERE price = 0 LIMIT 1");
        $freePlan = $freePlanResult->fetch_assoc();

        if($freePlan){
            $startDate = date("Y-m-d");
            $endDate   = date("Y-m-d", strtotime("+{$freePlan['duration_days']} days"));

            $stmt_sub = mysqli_prepare($con, "INSERT INTO subscriptions 
                (account_id, plan_id, start_date, end_date, status, type, payment_status) 
                VALUES (?, ?, ?, ?, 'active', 'user', 'paid')");
            mysqli_stmt_bind_param($stmt_sub, "iiss", $account_id, $freePlan['plan_id'], $startDate, $endDate);
            mysqli_stmt_execute($stmt_sub);
}

        // Send verification email
        $result = sendVerificationEmail($email, $username, $otp);
        if ($result === true) {
            unset($_SESSION['old']);
            $_SESSION['alert'] = ['type' => 'success', 'message' => "Registered successfully. Check your email for verification code."];
        } else {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => "Account created, but verification email could not be sent: $result"];
        }

        $_SESSION['registrationComplete'] = true;
        header("Location: emailverification.php?email=" . urlencode($email));
        exit();

    } catch (Exception $e) {
        mysqli_rollback($con);
        $_SESSION['registration_errors'] = ["Registration failed: " . $e->getMessage()];
        header('Location: index.php');
        exit();
    }
}

// ================= LOGIN =================
if (isset($_POST['login_btn'])) {
    $identifier = escape($con, $_POST['identifier']); // can be email OR username
    $password   = escape($con, $_POST['password']);

    $user_query = "SELECT * FROM accounts 
                   WHERE email = '$identifier' OR username = '$identifier' 
                   LIMIT 1";
    $user_query_run = mysqli_query($con, $user_query);

    if (!$user_query_run || mysqli_num_rows($user_query_run) === 0) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => "Account not found."];
        header('Location: ../index.php');
        exit();
    }

    $user = mysqli_fetch_assoc($user_query_run);

    if (!password_verify($password, $user['password'])) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => "Incorrect password."];
        header('Location: ../index.php');
        exit();
    }

    if ((int)$user['is_verified'] === 0) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => "Please verify your email first."];
        header('Location: ../emailverification.php?email=' . urlencode($user['email']));
        exit();
    }

    // Save session data
    $_SESSION['auth'] = true;
    $_SESSION['auth_user'] = [
        'account_id' => $user['account_id'],
        'name'       => $user['name'],
        'username'   => $user['username'],
        'email'      => $user['email'],
        'role'       => $user['role']
    ];
    $_SESSION['role'] = $user['role'];

    // Redirect
    if ($user['role'] === 'admin') {
        $redirect = '../admin_Superadmin/index.php';
    } elseif ($user['role'] === 'artist') {
        $redirect = '../admin-artist/index.php';
    } else {
        $redirect = 'index1.php';
    }

    $_SESSION['alert'] = ['type' => 'success', 'message' => "Logged in successfully"];
    header("Location: $redirect");
    exit();
}


// ================= SEND OTP =================
if (isset($_POST['send_code_btn'])) {
    
    $email = mysqli_real_escape_string($con, $_POST['email']);

    // Check if email exists
    $checkEmail = mysqli_query($con, "SELECT * FROM accounts WHERE email='$email' LIMIT 1");

    if (mysqli_num_rows($checkEmail) > 0) {
        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // Save OTP to database (INT column, no quotes)
        $updateOtp = mysqli_query($con, "UPDATE accounts SET otp=$otp WHERE email='$email'");

        if (!$updateOtp) {
            die("Database Error: " . mysqli_error($con));
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'mikovargas5@gmail.com'; // replace with your Gmail
            $mail->Password   = 'dydwouieefblfyrf';  // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('yourgmail@gmail.com', 'Your Website');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body    = "<h3>Your OTP is: <b>$otp</b></h3>";

            $mail->send();
            $_SESSION['alert'] = "OTP has been sent to your email.";
            header("Location: reset_password.php");
            exit();
        } catch (Exception $e) {
            die("Mailer Error: {$mail->ErrorInfo}");
        }
    } else {
        $_SESSION['alert'] = "No account found with this email.";
        header("Location: index.php");
        exit();
    }
}
// VERIFY OTP
if (isset($_POST['verify_otp_btn'])) {
    $email = trim($_POST['email'] ?? '');
    $otpIn = preg_replace('/\D/', '', $_POST['otp'] ?? ''); // only digits

    // Basic validation
    if ($email === '' || $otpIn === '' || strlen($otpIn) !== 6) {
        $_SESSION['otp_alert'] = ['type' => 'error', 'message' => 'Please enter a valid 6-digit OTP.'];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }

    // Fetch account info
    $stmt = $con->prepare("SELECT account_id, otp, otp_created_at, is_verified FROM accounts WHERE email = ? LIMIT 1");
    if (!$stmt) {
        $_SESSION['otp_alert'] = ['type' => 'error', 'message' => 'Database error: ' . $con->error];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $_SESSION['otp_alert'] = ['type' => 'error', 'message' => 'Account not found.'];
        header("Location: index.php");
        exit;
    }

    if ((int)$row['is_verified'] === 1) {
        $_SESSION['otp_alert'] = ['type' => 'info', 'message' => 'Account already verified. Please log in.'];
        header("Location: index.php");
        exit;
    }

    // Check OTP expiry (10 minutes)
    $otpTime = $row['otp_created_at'] ? strtotime($row['otp_created_at']) : 0;
    if (!$otpTime || (time() - $otpTime) > 600) {
        $_SESSION['otp_alert'] = ['type' => 'error', 'message' => 'OTP has expired. Please resend a new one.'];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }

    // Check OTP match
    if ((int)$otpIn !== (int)$row['otp']) {
        $_SESSION['otp_alert'] = ['type' => 'error', 'message' => 'Invalid OTP. Please try again.'];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }

    // OTP correct — mark account as verified
    $upd = $con->prepare("UPDATE accounts SET is_verified = 1, otp = NULL, otp_created_at = NULL WHERE email = ?");
    if (!$upd) {
        $_SESSION['otp_alert'] = ['type' => 'error', 'message' => 'Database error (update): ' . $con->error];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }
    $upd->bind_param("s", $email);
    if (!$upd->execute()) {
        $_SESSION['otp_alert'] = ['type' => 'error', 'message' => 'Failed to mark account as verified: ' . $upd->error];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }
    $upd->close();

    // Set session for new user for plan selection
    $_SESSION['new_user_id'] = $row['account_id'];
    $_SESSION['alert'] = [
        'type' => 'success',
        'message' => 'Your account has been successfully verified. you can now login.'
    ];

    // Redirect to plan selection
    header("Location: index.php");
    exit();
}


if (isset($_POST['resend_otp_btn'])) {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        $_SESSION['otp_alert'] = ['type'=>'error', 'message'=>'Email not provided.'];
        header("Location: emailverification.php");
        exit;
    }

    // Check account
    $stmt = $con->prepare("SELECT is_verified, otp_created_at FROM accounts WHERE email = ? LIMIT 1");
    if (!$stmt) {
        $_SESSION['otp_alert'] = ['type'=>'error', 'message'=>'DB Error: '.$con->error];
        header("Location: emailverification.php");
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        $_SESSION['otp_alert'] = ['type'=>'error','message'=>'Account not found.'];
        header("Location: index.php");
        exit;
    }

    if ((int)$row['is_verified'] === 1) {
        $_SESSION['otp_alert'] = ['type'=>'info','message'=>'Account is already verified.'];
        header("Location: index.php");
        exit;
    }

    // Anti-spam check
    if ($row['otp_created_at'] && (time() - strtotime($row['otp_created_at'])) < 30) {
        $_SESSION['otp_alert'] = ['type'=>'warning','message'=>'Please wait before requesting again.'];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }

    // Generate new OTP
    $otp = strval(rand(100000, 999999));

    // Update DB
    $stmt = $con->prepare("UPDATE accounts SET otp = ?, otp_created_at = NOW() WHERE email = ?");
    if (!$stmt) {
        $_SESSION['otp_alert'] = ['type'=>'error','message'=>'Prepare failed: '.$con->error];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }
    $stmt->bind_param("ss", $otp, $email);
    $exec = $stmt->execute();

    if (!$exec) {
        $_SESSION['otp_alert'] = ['type'=>'error','message'=>'Update failed: '.$stmt->error];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }

    if ($stmt->affected_rows === 0) {
        $_SESSION['otp_alert'] = ['type'=>'error','message'=>'OTP not updated (maybe same value or no match).'];
        header("Location: emailverification.php?email=" . urlencode($email));
        exit;
    }

    // Send email
    $result = sendVerificationEmail($email, 'User', $otp);

    if ($result === true) {
        $_SESSION['otp_alert'] = ['type'=>'success','message'=>'OTP has been resent to your email.'];
    } else {
        $_SESSION['otp_alert'] = ['type'=>'error','message'=>'Mailer error: ' . $result];
    }

    header("Location: emailverification.php?email=" . urlencode($email));
    exit;
}

?>
