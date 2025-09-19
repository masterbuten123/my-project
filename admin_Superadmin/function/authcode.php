<?php
include('../config/dbcon.php');

// REGISTER
if (isset($_POST['register_btn'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    if ($password !== $cpassword) {
        $_SESSION['message'] = "Passwords do not match";
        header('Location: ../register.php');
        exit();
    }

    // Check if email exists
    $check_email_query = "SELECT email FROM users WHERE email = '$email'";
    $check_email_query_run = mysqli_query($con, $check_email_query);

    if (mysqli_num_rows($check_email_query_run) > 0) {
        $_SESSION['message'] = "Email already used";
        header('Location: ../register.php');
        exit();
    }

    // Hash and insert
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_query = "INSERT INTO users (name, email, phone, password, role_as) VALUES ('$name', '$email', '$phone', '$hashed_password', 0)";
    $insert_query_run = mysqli_query($con, $insert_query); 

    if ($insert_query_run) {
        $_SESSION['message'] = "Registered successfully";
        header('Location: ../login.php');
        exit();
    } else {
        $_SESSION['message'] = "Something went wrong";
        header('Location: ../register.php');
        exit();
    }
}

// LOGIN
if (isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $login_query = "SELECT * FROM users WHERE email = '$email'";
    $login_query_run = mysqli_query($con, $login_query);

    // Debugging: Check query result
    error_log("Login query: $login_query");

    if (mysqli_num_rows($login_query_run) > 0) {
        $user = mysqli_fetch_assoc($login_query_run);

        if (password_verify($password, $user['password'])) {
            $_SESSION['auth'] = true;
            $_SESSION['auth_user'] = [
                'user_id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ];

            $_SESSION['role_as'] = $user['role_as'];

            // Debugging: Check session values
            error_log("User authenticated. Redirecting...");

            // Redirect based on role
            switch ($user['role_as']) {
                case 1:
                    $_SESSION['message'] = "Welcome to Superadmin Dashboard";
                    header('Location: ../admin_Superadmin/index.php');
                    exit();
                case 2:
                    $_SESSION['message'] = "Welcome to Artist Dashboard";
                    header('Location: ../admin-artist/index.php');
                    exit(); 
                default:
                    $_SESSION['message'] = "Logged in successfully";
                    header('Location: ../index.php');
                    exit();
            }
        } else {
            error_log("Invalid password.");
            $_SESSION['message'] = "Invalid email or password";
            header('Location: ../login.php');
            exit();
        }
    } else {
        error_log("No user found with that email.");
        $_SESSION['message'] = "Invalid email or password";
        header('Location: ../login.php');
        exit();
    }
}
?>
