<?php
session_start();
include('../config/dbcon.php');
include('function/myfunctions.php');


if (isset($_POST['delete_wallpaper_btn']) && isset($_POST['wallpaper_id'])) {
    $wallpaper_id = $_POST['wallpaper_id']; // Get wallpaper ID from POST

    // Call the softDeleteWallpaper function
    if (softDeleteWallpaper($wallpaper_id)) {
        $_SESSION['message'] = "Wallpaper has been successfully deleted.";
        header('Location: wallpaper_change.php');
        exit();
    } else {
        $_SESSION['message'] = "Failed to delete wallpaper.";
        header('Location: wallpaper_change.php');
        exit();
    }
}

if (isset($_POST['add_wallpaper_btn'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);

    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = "../uploads/wallpapers/" . $image;

    // Move uploaded file
    move_uploaded_file($image_tmp, $image_path);

    $query = "INSERT INTO wallpapers (name, description, image) VALUES ('$name', '$description', '$image')";
    $result = mysqli_query($con, $query);

    if ($result) {
        $_SESSION['message'] = "Wallpaper added successfully!";
    } else {
        $_SESSION['message'] = "Something went wrong.";
    }

    header("Location: wallpaper_change.php");
    exit();
}

// Toggle the wallpaper status between active and inactive
if (isset($_POST['toggle_status_btn'])) {
    $wallpaper_id = $_POST['wallpaper_id'];

    // Fetch current status of the wallpaper
    $result = mysqli_query($con, "SELECT status FROM wallpapers WHERE id = '$wallpaper_id'");
    $wallpaper = mysqli_fetch_assoc($result);

    if ($wallpaper) {
        // Toggle status: 'active' -> 'inactive', 'inactive' -> 'active'
        $new_status = ($wallpaper['status'] === 'active') ? 'inactive' : 'active';

        // Update the status in the database
        $query = "UPDATE wallpapers SET status = '$new_status' WHERE id = '$wallpaper_id'";
        if (mysqli_query($con, $query)) {
            // Redirect to the wallpaper management page after updating
            header('Location: wallpaper_change.php');
            exit;
        } else {
            // Error handling if the query fails
            echo "Error: " . mysqli_error($con);
        }
    } else {
        echo "Wallpaper not found.";
    }
}
else if (isset($_POST['update_prod_btn'])) {
    // Capture form data
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $status = $_POST['status']; // Now it's a select dropdown with 'active' or 'inactive'

    // Check if a new image is uploaded
    if ($_FILES['image']['name']) {
        // New image is uploaded
        $image = $_FILES['image']['name'];
        $path = '../uploads/';
        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $filename = time() . '.' . $image_ext;

        // Delete the old image file if it exists
        $old_image = $_POST['old_image'];
        if (file_exists($path . '/' . $old_image)) {
            unlink($path . '/' . $old_image);
        }

        // Move the new image to the upload directory
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $path . '/' . $filename)) {
            $_SESSION['message'] = "Failed to upload the image.";
            header("Location: all-products.php");
            exit();
        }
    } else {
        // No new image, keep the old image
        $filename = $_POST['old_image'];
    }

    // Prepare the query to update product in the database
    $product_query = "UPDATE products SET name = '$name', description = '$description', price = '$price', stock = '$stock', status = '$status', image = '$filename' WHERE product_id = '$product_id'";

    // Run the query
    $product_query_run = mysqli_query($con, $product_query);

    if ($product_query_run) {
        $_SESSION['message'] = "Product updated successfully!";
        header("Location: all-products.php");
        exit();
    } else {
        $_SESSION['message'] = "Something went wrong. Please try again.";
        header("Location: all-products.php");
        exit();
    }
}


else if (isset($_POST['add_prod_btn'])) {
    $artist_id = $_SESSION['auth_user']['user_id']; // Assuming user ID is stored here

    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $selling_price = $_POST['price'];
    $qty = $_POST['stock'];
    $status = isset($_POST['status']) ? '1' : '0';

    $image = $_FILES['image']['name'];
    $path = '../uploads';
    $image_ext = pathinfo($image, PATHINFO_EXTENSION);
    $filename = time() . '.' . $image_ext;

    if (!empty($name)) {
        $product_query = "INSERT INTO products (artist_id, category_id, name, description, price, stock, status, image) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $product_query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iisssiss", $artist_id, $category_id, $name, $description, $selling_price, $qty, $status, $filename);
            $product_query_run = mysqli_stmt_execute($stmt);

            if ($product_query_run) {
                move_uploaded_file($_FILES['image']['tmp_name'], $path . '/' . $filename);
                redirect("all-products.php?id=$category_id", "Product Added Successfully");
            } else {
                echo "MySQL Error: " . mysqli_stmt_error($stmt);
                redirect("all-products.php?id=$category_id", "Something went wrong");
            }
        } else {
            echo "Preparation failed: " . mysqli_error($con);
        }
    } else {
        redirect("all-products.php?id=$category_id", "Product name is required");
    }
}



if (isset($_POST['update_prod_btn'])) {
    $product_id = mysqli_real_escape_string($con, $_POST['product_id']);
    $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $price = mysqli_real_escape_string($con, $_POST['price']);
    $stock = mysqli_real_escape_string($con, $_POST['stock']);
    $status = isset($_POST['status']) ? '0' : '1'; // checkbox is checked = show (0), else hidden (1)
    $old_image = $_POST['old_image'];

    $new_image = $_FILES['image']['name'];
    $image_path = "../uploads/";

    if ($new_image != "") {
        // Rename image with timestamp to prevent duplicate names
        $image_ext = pathinfo($new_image, PATHINFO_EXTENSION);
        $filename = time() . '.' . $image_ext;

        // Upload image
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path . $filename);

        // Delete old image if it exists
        if (file_exists($image_path . $old_image)) {
            unlink($image_path . $old_image);
        }
    } else {
        $filename = $old_image; // No new image uploaded
    }

    $query = "UPDATE products SET 
                category_id='$category_id',
                name='$name',
                description='$description',
                price='$price',
                stock='$stock',
                status='$status',
                image='$filename'
              WHERE product_id='$product_id'";

    $result = mysqli_query($con, $query);

    if ($result) {
        $_SESSION['message'] = "Product updated successfully!";
        header("Location: all-products.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Something went wrong while updating.";
        header("Location: all-products.php");
        exit(0);
    }
}

else if (isset($_POST['add_artist_btn'])) {
    // Capture form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $genre = $_POST['genre'];
    $bio = $_POST['bio'];
    $price = $_POST['price']; // Capture the price
    $status = isset($_POST['status']) ? 'active' : 'inactive';

    // Handle image upload
    $image = $_FILES['image']['name'];
    $path = '../uploads'; // Update this to match your upload path

    // Check required fields
    if (empty($name) || empty($email) || empty($image) || empty($price)) {
        $_SESSION['message'] = "Name, email, image, and price are required.";
        header("Location: all-artist.php");
        exit();
    }

    // Email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format.";
        header("Location: all-artist.php");
        exit();
    }

    // Validate price
    if (!is_numeric($price) || $price <= 0) {
        $_SESSION['message'] = "Price must be a valid number greater than 0.";
        header("Location: all-artist.php");
        exit();
    }

    // Check for duplicate email
    $check_email_query = "SELECT * FROM artists WHERE email = '$email' LIMIT 1";
    $check_email_result = mysqli_query($con, $check_email_query);

    if (mysqli_num_rows($check_email_result) > 0) {
        $_SESSION['message'] = "An artist with this email already exists.";
        header("Location: all-artist.php");
        exit();
    }

    // Validate image
    $image_ext = pathinfo($image, PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $image_ext;

    // Prepare insert query
    $artist_query = "INSERT INTO artists (name, email, phone, genre, bio, price, image, status) 
                     VALUES ('$name', '$email', '$phone', '$genre', '$bio', '$price', '$filename', '$status')";

    $artist_query_run = mysqli_query($con, $artist_query);

    if ($artist_query_run) {
        // Move the uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $path . '/' . $filename)) {
            $_SESSION['message'] = "Artist added successfully!";
            header("Location: all-artist.php");
            exit();
        } else {
            $_SESSION['message'] = "Artist added, but image upload failed.";
            header("Location: all-artist.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Something went wrong. Please try again.";
        header("Location: all-artist.php");
        exit();
    }
}



if (isset($_POST['update_user_btn'])) {
    // Capture form data
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role_as = $_POST['role_as'];  // New role selected from the dropdown
    $old_image = $_POST['old_image'];
    
    // Handle image upload if a new image is selected
    if ($_FILES['image']['name']) {
        $image = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_path = '../uploads/adminfolder' . $image;

        // Move the uploaded image to the desired folder
        move_uploaded_file($image_tmp_name, $image_path);
    } else {
        $image = $old_image;  // Keep the old image if no new image is uploaded
    }

    // Update the user details in the database
    $query = "UPDATE users SET 
                name = '$name', 
                email = '$email', 
                phone = '$phone', 
                role_as = '$role_as', 
                image = '$image'
              WHERE id = '$user_id'";

    $query_run = mysqli_query($con, $query);

    // Check if the update was successful
    if ($query_run) {
        // Redirect or show a success message
        $_SESSION['status'] = "User updated successfully!";
        header('Location: all-users.php');
    } else {
        $_SESSION['status'] = "Error updating user!";
        header('Location: all-users.php');
    }
}

if (isset($_POST['update_profile_btn'])) {
    // Capture form data
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $old_image = $_POST['old_image'];
    
    // Handle image upload if a new image is selected
    if ($_FILES['image']['name']) {
        $image = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_path = '/forartist/uploads/profile_images' . $image;

        // Move the uploaded image to the desired f older
        move_uploaded_file($image_tmp_name, $image_path);
    } else {
        $image = $old_image;  // Keep the old image if no new image is uploaded
    }

    // Update the user details in the database
    $query = "UPDATE users SET 
                name = '$name', 
                email = '$email', 
                phone = '$phone', 
                role_as = '$role_as', 
                image = '$image'
              WHERE id = '$user_id'";

    $query_run = mysqli_query($con, $query);

    // Check if the update was successful
    if ($query_run) {
        // Redirect or show a success message
        $_SESSION['status'] = "User updated successfully!";
        header('Location: my-profile.php');
    } else {
        $_SESSION['status'] = "Error updating user!";
        header('Location: my-profile.php');
    }
}


if (isset($_POST['update_artist_btn'])) {
    // Fetch form data
    $artist_id = $_POST['artist_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $genre = $_POST['genre'];
    $bio = $_POST['bio'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $old_image = $_POST['old_image'];
    $old_attachment = $_POST['old_attachment']; // Old resume file

    // Upload directories
    $image_dir = __DIR__ . "/../uploads/profile_images/";
    $resume_dir = __DIR__ . "/../uploads/resumes/";

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);
        $new_image_name = "artist_" . time() . "." . $image_extension;
        $image_path = $image_dir . $new_image_name;

        if (move_uploaded_file($image_tmp, $image_path)) {
            $image_to_save = $new_image_name;
        } else {
            $image_to_save = $old_image;
        }
    } else {
        $image_to_save = $old_image;
    }

    // Handle resume upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $attachment_name = $_FILES['attachment']['name'];
        $attachment_tmp = $_FILES['attachment']['tmp_name'];
        $attachment_extension = pathinfo($attachment_name, PATHINFO_EXTENSION);
        $allowed_extensions = ['pdf', 'doc', 'docx', 'txt'];

        if (in_array(strtolower($attachment_extension), $allowed_extensions)) {
            $new_attachment_name = "resume_" . time() . "." . $attachment_extension;
            $attachment_path = $resume_dir . $new_attachment_name;

            if (move_uploaded_file($attachment_tmp, $attachment_path)) {
                $resume_to_save = $new_attachment_name;
            } else {
                $resume_to_save = $old_attachment;
            }
        } else {
            $resume_to_save = $old_attachment;
        }
    } else {
        $resume_to_save = $old_attachment;
    }

    // Update artist data in the database
    $update_query = "UPDATE artists SET 
                        name = ?, 
                        email = ?, 
                        phone = ?, 
                        gender = ?, 
                        genre = ?, 
                        bio = ?, 
                        price = ?, 
                        image = ?, 
                        resume = ?, 
                        status = ? 
                    WHERE artist_id = ?";

    if ($stmt = mysqli_prepare($con, $update_query)) {
        mysqli_stmt_bind_param(
            $stmt, 
            'ssssssssssi', 
            $name, 
            $email, 
            $phone, 
            $gender, 
            $genre, 
            $bio, 
            $price, 
            $image_to_save, 
            $resume_to_save, 
            $status, 
            $artist_id
        );

        if (mysqli_stmt_execute($stmt)) {
            header('Location: all-artist.php?status=success');
            exit();
        } else {
            echo "Error updating artist: " . mysqli_error($con);
        }

        mysqli_stmt_close($stmt);
    }
}



if (isset($_POST['change_password_btn'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $new_password = mysqli_real_escape_string($con, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($con, $_POST['cnew_password']); 

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => "All fields are required."];
        header("Location: changepass.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => "Passwords do not match."];
        header("Location: changepass.php");
        exit();
    }

    $check_user = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $check_user_run = mysqli_query($con, $check_user);

    if (mysqli_num_rows($check_user_run) == 1) {
        $user = mysqli_fetch_assoc($check_user_run);
        $user_id = $user['id'];

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
        $update_run = mysqli_query($con, $update_query);

        if ($update_run) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => "Password reset successful. Please log in."];
            header("Location: changepass.php");
            exit();
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Failed to reset password. Please try again."];
            header("Location: changepass.php");
            exit();
        }
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => "Email address not found."];
        header("Location: changepass.php");
        exit();
    }
}

if (isset($_POST['add_category_btn'])) {
    $name        = mysqli_real_escape_string($con, $_POST['name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);

    $query = "INSERT INTO categories (name, description, status) VALUES ('$name', '$description', 'active')";
    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        $_SESSION['message'] = "Category Added Successfully";
        header("Location: all-products.php"); 
        exit(0);
    } else {
        $_SESSION['message'] = "Something went wrong!";
        header("Location: all-products.php");
        exit(0);
    }
}

if(isset($_POST['update_category_btn'])) {
    $category_id = $_POST['category_id'];
    $name        = mysqli_real_escape_string($con, $_POST['name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $status      = mysqli_real_escape_string($con, $_POST['status']);

    $query = "UPDATE categories 
              SET name='$name', description='$description', status='$status' 
              WHERE category_id='$category_id'";
    $query_run = mysqli_query($con, $query);

    if($query_run){
        $_SESSION['message'] = "Category updated successfully";
    } else {
        $_SESSION['message'] = "Category update failed";
    }

    header("Location: all-products.php");
    exit();
}

    

?>
