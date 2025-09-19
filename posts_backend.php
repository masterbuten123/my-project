<?php
session_start();
include('config/dbcon.php');

// For testing without login, fallback to account_id = 1
$account_id = isset($_SESSION['auth_user']['account_id']) ? intval($_SESSION['auth_user']['account_id']) : 1;

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Directory to store uploaded post images
define('UPLOAD_DIR', 'uploads/posts/');

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['post_content'] ?? '');
    $image_name = null;

    if ($content === '') {
        echo json_encode(['status'=>'error','message'=>'Content cannot be empty.']); exit;
    }

    // Handle image upload
    if(isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0){
        $ext = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
        $image_name = time().'_'.rand(1000,9999).'.'.$ext;
        move_uploaded_file($_FILES['post_image']['tmp_name'], UPLOAD_DIR.$image_name);
    }
    $stmt = $con->prepare("INSERT INTO posts (account_id, content, image, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $account_id, $content, $image_name);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['status'=>$ok?'success':'error','message'=>$ok?'Post added.':'Failed to add.']);
    exit;

} elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = intval($_POST['post_id'] ?? 0);
    $content = trim($_POST['post_content'] ?? '');
    $image_name = null;

    if ($post_id <= 0) { echo json_encode(['status'=>'error','message'=>'Invalid post.']); exit; }
    if ($content === '') { echo json_encode(['status'=>'error','message'=>'Content cannot be empty.']); exit; }

    // Handle new image upload
    if(isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0){
        $ext = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
        $image_name = time().'_'.rand(1000,9999).'.'.$ext;
        move_uploaded_file($_FILES['post_image']['tmp_name'], UPLOAD_DIR.$image_name);
    }

    if($image_name){
        $stmt = $con->prepare("UPDATE posts SET content=?, image=?, updated_at=NOW() WHERE post_id=? AND account_id=?");
        $stmt->bind_param("ssii", $content, $image_name, $post_id, $account_id);
    } else {
        $stmt = $con->prepare("UPDATE posts SET content=?, updated_at=NOW() WHERE post_id=? AND account_id=?");
        $stmt->bind_param("sii", $content, $post_id, $account_id);
    }

    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['status'=>$ok?'success':'error','message'=>$ok?'Post updated.':'Failed to update.']);
    exit;

} elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = intval($_POST['post_id'] ?? 0);
    if ($post_id <= 0) { echo json_encode(['status'=>'error','message'=>'Invalid post.']); exit; }

    // Delete post image if exists
    $res = $con->query("SELECT image FROM posts WHERE post_id=$post_id AND account_id=$account_id LIMIT 1");
    if($res && $row = $res->fetch_assoc()){
        if(!empty($row['image']) && file_exists(UPLOAD_DIR.$row['image'])){
            unlink(UPLOAD_DIR.$row['image']);
        }
    }

    $stmt = $con->prepare("DELETE FROM posts WHERE post_id=? AND account_id=?");
    $stmt->bind_param("ii", $post_id, $account_id);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['status'=>$ok?'success':'error','message'=>$ok?'Post deleted.':'Failed to delete.']);
    exit;

} elseif ($action === 'list') {
    // Return rendered HTML
    header('Content-Type: text/html; charset=utf-8');

    $sql = "
    SELECT p.post_id, p.account_id, p.content, p.image, p.created_at, p.updated_at, a.name AS account_name
    FROM posts p
    JOIN accounts a ON p.account_id = a.account_id
    ORDER BY p.created_at DESC
    ";
    $res = $con->query($sql);

    while ($row = $res->fetch_assoc()) {
        $isOwner = intval($row['account_id']) === $account_id;
        $name = esc($row['account_name']);
        $content = nl2br(esc($row['content']));
        $created = esc($row['created_at']);
        $updated = esc($row['updated_at']);
        $image = $row['image'];
        $postId = intval($row['post_id']);

        echo '<div class="main-box mb-3" data-post-id="'.$postId.'">';
        echo '  <div class="d-flex justify-content-between align-items-start">';
        echo '      <div>';
        echo '          <strong>'.$name.'</strong>';
        echo '          <div class="text-muted small">'.($updated ? "Updated: $updated" : "Posted: $created").'</div>';
        echo '      </div>';
        if ($isOwner) {
            echo '      <div class="ms-2">';
            // Escape content for HTML attribute to avoid breaking quotes
            $safeContent = htmlspecialchars($row['content'], ENT_QUOTES);
            echo '          <button class="btn btn-sm btn-outline-secondary me-1 btn-edit-post" data-post-id="'.$postId.'" data-post-content="'.$safeContent.'">Edit</button>';
            echo '          <button class="btn btn-sm btn-outline-danger btn-delete-post" data-post-id="'.$postId.'">Delete</button>';
            echo '      </div>';
        }
        echo '  </div>';
        echo '  <p class="mt-2">'.$content.'</p>';
        if(!empty($image)){
            echo '<img src="'.UPLOAD_DIR.$image.'" class="img-fluid rounded mt-2">';
        }
        echo '</div>';
    }
    exit;
}else {
    echo json_encode(['status'=>'error','message'=>'Invalid action.']);
    exit;
}
