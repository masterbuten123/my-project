<?php
session_start();
include('../config/dbcon.php');
include('functions/myfunctions.php');

if (isset($_POST['add_prod_btn'])) {

    // Escape input values
    $name        = mysqli_real_escape_string($con, $_POST['name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $price       = mysqli_real_escape_string($con, $_POST['price']);
    $stock       = mysqli_real_escape_string($con, $_POST['stock']);
    $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
    $status      = mysqli_real_escape_string($con, $_POST['status']);

    // Get account_id from session
    $account_id = $_SESSION['auth_user']['account_id'];

    // Check if product already exists for this artist
    $check_query = "SELECT * FROM products WHERE account_id = ? AND name = ?";
    $stmt_check = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt_check, "is", $account_id, $name);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        $_SESSION['update_message'] = 'This product already exists!';
        redirect("all-products.php", "Duplicate Product");
    } else {

        // Handle image upload
        $image      = $_FILES['image']['name'];
        $tmp_name   = $_FILES['image']['tmp_name'];
        $uploadDir  = '../uploads/products/';
        $new_image  = null;

        if ($image) {
            $image_ext   = pathinfo($image, PATHINFO_EXTENSION);
            $new_image   = time() . '_' . uniqid() . '.' . $image_ext;
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            move_uploaded_file($tmp_name, $uploadDir . $new_image);
        }

        // Insert product
        $insert_query = "INSERT INTO products 
            (account_id, category_id, name, description, price, stock, image, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_insert = mysqli_prepare($con, $insert_query);
        mysqli_stmt_bind_param($stmt_insert, "iissdiss", 
            $account_id, $category_id, $name, $description, $price, $stock, $new_image, $status
        );

        if (mysqli_stmt_execute($stmt_insert)) {
            $_SESSION['update_message'] = 'Product added successfully!';
            redirect("all-products.php", "Add Successful");
        } else {
            $_SESSION['update_message'] = 'Something went wrong, try again.';
            redirect("all-products.php", "Error");
        }

        mysqli_stmt_close($stmt_insert);
    }

    mysqli_stmt_close($stmt_check);
}



else if (isset($_POST['update_prod_btn'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $selling_price = $_POST['price'];  // Updated from 'selling_price' to match the form field name
    $qty = $_POST['stock'];  // Updated to match the form field name

    $image = $_FILES['image']['name'];
    $path = '../uploads/';

    // Get the old image from the form
    $old_image = $_POST['old_image'];

    // If a new image is uploaded, generate a new filename
    if ($image != "") {
        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $update_filename = time() . '.' . $image_ext;
    } else {
        // If no new image, keep the old image
        $update_filename = $old_image;
    }

    // Update product query
    $update_product_query = "UPDATE products SET name='$name', description='$description',
    price='$selling_price', stock='$qty', image='$update_filename' WHERE product_id='$product_id'";

    // Execute the query
    $update_product_query_run = mysqli_query($con, $update_product_query);

    if ($update_product_query_run) {
        // If a new image is uploaded, move the file and delete the old image if exists
        if ($image != "") {
            move_uploaded_file($_FILES['image']['tmp_name'], $path . '/' . $update_filename);
            if (file_exists("../uploads/" . $old_image) && $old_image != "") {
                unlink("../uploads/" . $old_image);  // Delete the old image from the server
            }
        }

        // Success message
        $_SESSION['update_message'] = 'Product updated successfully!';
        // Redirect to the products page
        redirect("all-products.php?id=$product_id", "Update Successful");
    } else {
        // Error message
        $_SESSION['update_message'] = 'Something went wrong, try again.';
        // Redirect to the products page
        redirect("all-products.php?id=$product_id", "Something Went Wrong");
    }
}


else if(isset($_POST['delete_prod_btn']))
{
    $product_id = mysqli_real_escape_string($con, $_POST['product_id']);

    $product_query = "SELECT * FROM products WHERE id='$product_id'";
    $product_query_run = mysqli_query($con, $product_query);
    $product_data = mysqli_fetch_array($product_query_run);
    $image = $product_data['image'];

    $delete_query = "DELETE FROM products WHERE id = '$product_id'";
    $delete_query_run = mysqli_query($con, $delete_query);

    if($delete_query_run)
    {
        if(file_exists("../uploads/".$image))
        {
            unlink("../uploads/".$image);
        }
        //redirect("products.php", " Product Deleted Successfully");
        echo 200;
        
    }
    else
    {
        //redirect("products.php", "Something Went Wrong");
        echo 500;

    }
}
else if(isset($_POST['update_order_btn']))
{
    $track_no = $_POST['tracking_no'];
    $order_status = $_POST['order_status'];

    $updateOrder_query = "UPDATE orders SET status='$order_status' WHERE tracking_no='$track_no' ";
    $updateOrder_query_run = mysqli_query($con, $updateOrder_query);

    redirect("view-order.php?t=$track_no", "Order Status Update Successful");


}

// =================== PROFILE UPDATE ===================
if (isset($_POST['update_profile_btn'])) {
    $account_id = $_POST['account_id'];
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']); 
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $bio = mysqli_real_escape_string($con, $_POST['bio']);
    $genre = mysqli_real_escape_string($con, $_POST['genre']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $age = (int) $_POST['age'];
    $price = (float) $_POST['price'];

    // New fields
    $bank_name     = mysqli_real_escape_string($con, $_POST['bank_name'] ?? '');
    $bank_account  = mysqli_real_escape_string($con, $_POST['bank_account'] ?? '');
    $gcash_number  = mysqli_real_escape_string($con, $_POST['gcash_number'] ?? '');
    $facebook      = mysqli_real_escape_string($con, $_POST['facebook'] ?? '');
    $instagram     = mysqli_real_escape_string($con, $_POST['instagram'] ?? '');
    $youtube       = mysqli_real_escape_string($con, $_POST['youtube'] ?? '');
    $tiktok        = mysqli_real_escape_string($con, $_POST['tiktok'] ?? '');
    $website       = mysqli_real_escape_string($con, $_POST['website'] ?? '');
  

    $upload_dir = '../uploads/profiles/';
    $cover_upload_dir = '../uploads/covers/';

    // ---------------- PROFILE IMAGE (accounts.image) ----------------
    $old_image = $_POST['old_image'];
    $new_image = $_FILES['image']['name'];
    $update_image = $old_image;

    if (!empty($new_image)) {
        $ext = pathinfo($new_image, PATHINFO_EXTENSION);
        $allowed_img = ['jpg','jpeg','png','gif'];

        if (in_array(strtolower($ext), $allowed_img)) {
            $update_image = time() . '.' . $ext;
            $upload_path = $upload_dir . $update_image;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                if ($old_image && $old_image !== 'default.png' && file_exists($upload_dir . $old_image)) {
                    unlink($upload_dir . $old_image);
                }
            } else {
                $_SESSION['alert'] = ['type'=>'error', 'message'=>'Profile image upload failed.'];
                header('Location: my-profile.php'); exit();
            }
        } else {
            $_SESSION['alert'] = ['type'=>'error', 'message'=>'Invalid profile image file type.'];
            header('Location: my-profile.php'); exit();
        }
    }

    // ---------------- COVER IMAGE (user_information.cover_image) ----------------
    $old_cover_image = $_POST['old_cover_image'] ?? '';
    $new_cover_image = $_FILES['cover_image']['name'] ?? '';
    $update_cover_image = $old_cover_image;

    if (!empty($new_cover_image)) {
        $cover_ext = pathinfo($new_cover_image, PATHINFO_EXTENSION);
        $allowed_cover = ['jpg','jpeg','png','gif'];

        if (in_array(strtolower($cover_ext), $allowed_cover)) {
            $update_cover_image = time() . '_cover.' . $cover_ext;
            $upload_cover_path = $cover_upload_dir . $update_cover_image;

            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_cover_path)) {
                if ($old_cover_image && file_exists($cover_upload_dir . $old_cover_image)) {
                    unlink($cover_upload_dir . $old_cover_image);
                }
            } else {
                $_SESSION['alert'] = ['type'=>'error', 'message'=>'Cover image upload failed.'];
                header('Location: my-profile.php'); exit();
            }
        } else {
            $_SESSION['alert'] = ['type'=>'error', 'message'=>'Invalid cover image file type.'];
            header('Location: my-profile.php'); exit();
        }
    }

    // ---------------- RESUME FILE (user_information.resume) ----------------
    $resume_upload_dir = '../uploads/resumes/';
    if (!is_dir($resume_upload_dir)) {
        mkdir($resume_upload_dir, 0777, true);
    }

    $old_resume = $_POST['old_resume'] ?? '';
    $new_resume = $_FILES['resume']['name'] ?? '';
    $update_resume = $old_resume;

    if (!empty($new_resume)) {
        $resume_ext = pathinfo($new_resume, PATHINFO_EXTENSION);
        $allowed_resume = ['pdf','doc','docx'];

        if (in_array(strtolower($resume_ext), $allowed_resume)) {
            $update_resume = time() . '.' . $resume_ext;
            $upload_resume_path = $resume_upload_dir . $update_resume;

            if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_resume_path)) {
                if ($old_resume && file_exists($resume_upload_dir . $old_resume)) {
                    unlink($resume_upload_dir . $old_resume);
                }
            } else {
                $_SESSION['alert'] = ['type'=>'error', 'message'=>'Resume upload failed.'];
                header('Location: my-profile.php'); exit();
            }
        } else {
            $_SESSION['alert'] = ['type'=>'error', 'message'=>'Invalid resume file type.'];
            header('Location: my-profile.php'); exit();
        }
    }

    // ---------------- UPDATE accounts ----------------
    $sql1 = "UPDATE accounts SET
        name = '$name',
        email = '$email',
        phone = '$phone',
        image = '$update_image'
        WHERE account_id = '$account_id'";

    // ---------------- UPDATE or INSERT user_information ----------------
    $check = mysqli_query($con, "SELECT * FROM user_information WHERE account_id='$account_id'");
    if (mysqli_num_rows($check) > 0) {
        $sql2 = "UPDATE user_information SET
            bio = '$bio',
            genre = '$genre',
            gender = '$gender',
            age = $age,
            price_per_hour = $price,
            resume = '$update_resume',
            bank_name = '$bank_name',
            bank_account = '$bank_account',
            gcash_number = '$gcash_number',
            facebook = '$facebook',
            address = '$address',
            instagram = '$instagram',
            youtube = '$youtube',
            tiktok = '$tiktok',
            website = '$website',
            cover_image = '$update_cover_image'
            WHERE account_id = '$account_id'";
    } else {
        $sql2 = "INSERT INTO user_information 
            (account_id, bio, genre, gender, age, price_per_hour, resume, bank_name, bank_account, gcash_number, facebook, instagram, youtube, tiktok, website, cover_image)
            VALUES 
            ('$account_id','$bio','$genre','$gender',$age,$price,'$update_resume','$bank_name','$bank_account','$gcash_number','$facebook','$instagram','$youtube','$tiktok','$website','$update_cover_image')";
    }

    // ---------------- EXECUTE BOTH ----------------
    $ok1 = mysqli_query($con, $sql1);
    $ok2 = mysqli_query($con, $sql2);

    if ($ok1 && $ok2) {
        $_SESSION['alert'] = ['type'=>'success', 'message'=>'Profile updated successfully!'];
    } else {
        $_SESSION['alert'] = ['type'=>'error', 'message'=>'Failed to update profile: ' . mysqli_error($con)];
    }

    header('Location: my-profile.php');
    exit();
}

if(isset($_POST['upload_recording_btn'])){
    $artist_id = $_POST['artist_id'];
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $file = $_FILES['recording'];

    // ====== AUDIO UPLOAD ======
    $allowed = ['mp3','wav','ogg'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if(in_array($ext, $allowed) && $file['error'] === 0){
        $safeName = preg_replace("/[^A-Za-z0-9_-]/","_",pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = time().'_'.$safeName.'.'.$ext;
        $destination = "../uploads/audio/".$filename;

        if(move_uploaded_file($file['tmp_name'], $destination)){
            
            // ====== COVER UPLOAD (optional) ======
            $coverName = NULL;
            if(isset($_FILES['cover']) && $_FILES['cover']['error'] === 0){
                $coverExtAllowed = ['jpg','jpeg','png','gif'];
                $coverExt = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));

                if(in_array($coverExt, $coverExtAllowed)){
                    $safeCover = preg_replace("/[^A-Za-z0-9_-]/","_",pathinfo($_FILES['cover']['name'], PATHINFO_FILENAME));
                    $coverName = time().'_'.$safeCover.'.'.$coverExt;
                    $coverDest = "../uploads/covers/".$coverName;
                    move_uploaded_file($_FILES['cover']['tmp_name'], $coverDest);
                }
            }

            // ====== SAVE TO DATABASE ======
            $coverValue = $coverName ? "'$coverName'" : "NULL";
            $query = "INSERT INTO artist_recordings (account_id,title,recording_path,cover) 
                      VALUES ('$artist_id','$title','$filename',$coverValue)";
            mysqli_query($con, $query);

            $_SESSION['alert'] = ['type'=>'success','message'=>'Recording uploaded!'];

        } else {
            $_SESSION['alert'] = ['type'=>'error','message'=>'Failed to move audio file.'];
        }
    } else {
        $_SESSION['alert'] = ['type'=>'error','message'=>'Invalid audio file type.'];
    }

    header('Location: recordings.php');
    exit();
}



// Delete Recording
// ===================== DELETE RECORDING =====================
if(isset($_POST['delete_recording_btn'])){
    $recording_id = $_POST['recording_id'];
    
    $query = "UPDATE artist_recordings SET status='inactive' WHERE id='$recording_id'";
    if(mysqli_query($con, $query)){
        $_SESSION['alert'] = ['type'=>'success','message'=>'Recording deleted successfully.'];
    } else {
        $_SESSION['alert'] = ['type'=>'error','message'=>'Failed to delete recording.'];
    }
    header('Location: recordings.php');
    exit();
}



else if(isset($_POST['change_pass_btn']))
{
    $userId = $_SESSION['auth_user']['user_id'];
    $password = $_POST['password'];
    $npassword = $_POST['npassword'];
    $cnpassword = $_POST['cnpassword'];

    // Get the user data
    $check_password = "SELECT * FROM artists WHERE artist_id='$userId'";
    $check_password_run = mysqli_query($con, $check_password);
    $row = mysqli_fetch_assoc($check_password_run);

    if(mysqli_num_rows($check_password_run) > 0)
    {
        // Directly compare plain text password
        if($password === $row['password'])
        {
            if($npassword != $cnpassword)
            {
                redirect("changepass.php", "New Password Does Not Match");
            }
            else
            {
                // Directly update password without hashing
                $update_password = "UPDATE artists SET password='$npassword' WHERE artist_id='$userId'";
                mysqli_query($con, $update_password);
                redirect("changepass.php", "Changed Password Successfully");
            }
        }
        else
        {
            redirect("changepass.php", "Incorrect Password");
        }
    }
    else
    {
        redirect("changepass.php", "Incorrect Password");
    }
}


else if(isset($_POST['update_user_btn']))
{
    $id = $_POST['id'];
    $role_as = $_POST['role_as'];

    $query = "UPDATE users SET role_as='$role_as' WHERE id='$id'";
    $query_run = mysqli_query($con, $query);

    if($query_run)
    {
        redirect("all-users.php", "Updated Successfully");
        exit(0);

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

if(isset($_POST['delete_category_btn'])){
    $category_id = $_POST['category_id'];
    
    $query = "UPDATE categories SET is_deleted=1 WHERE category_id='$category_id'";
    $query_run = mysqli_query($con, $query);

    if($query_run){
        $_SESSION['message'] = "Category deleted successfully (soft delete).";
    } else {
        $_SESSION['message'] = "Delete failed!";
    }

    header("Location: all-products.php");
    exit();
}




?>