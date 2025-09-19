<?php
session_start();
include('config/dbcon.php');

// TEMP: allow page even when not logged in
$account_id = isset($_SESSION['auth_user']['account_id']) ? $_SESSION['auth_user']['account_id'] : 1;


/* =================== ADD POST =================== */
if (isset($_POST['add_post'])) {
    $post_content = mysqli_real_escape_string($con, $_POST['post_content']);
    $post_image = null;

    // Handle image upload if exists
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0) {
        $allowed_ext = ['jpg','jpeg','png','gif','webp'];
        $file_name = $_FILES['post_image']['name'];
        $file_tmp = $_FILES['post_image']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_ext)) {
            $new_name = 'post_'.time().'_'.rand(1000,9999).'.'.$ext;
            $upload_path = 'uploads/posts/'.$new_name;
            if (!is_dir('uploads/posts')) mkdir('uploads/posts', 0777, true);
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $post_image = $new_name;
            }
        }
    }

    // Insert post
    $insert = mysqli_query($con, "INSERT INTO posts (account_id, content, image, created_at) 
        VALUES ('$account_id', '$post_content', ".($post_image ? "'$post_image'" : "NULL").", NOW())");

    if ($insert) {
        // Generate HTML for the new post
        $post_html = "<div class='main-box mb-3'>
            <p>".htmlspecialchars($post_content)."</p>";
        if ($post_image) {
            $post_html .= "<img src='uploads/posts/$post_image' class='img-fluid rounded mt-2'>";
        }
        $post_html .= "<small class='text-muted'>Just now</small></div>";

        echo json_encode([
            'status'=>'success',
            'message'=>'Post added successfully.',
            'post_html'=>$post_html
        ]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to add post.']);
    }
    exit();
}



if (isset($_POST['search_user'])) {
    $keyword = "%{$_POST['keyword']}%";

    $stmt = $con->prepare("
        SELECT a.account_id, a.name, a.email, a.image
        FROM accounts a
        LEFT JOIN friendships f 
            ON ( (f.user1_id = a.account_id AND f.user2_id = ?) 
              OR (f.user2_id = a.account_id AND f.user1_id = ?) )
        LEFT JOIN friend_requests fr 
            ON ( (fr.sender_id = a.account_id AND fr.receiver_id = ? AND fr.status='pending') 
              OR (fr.receiver_id = a.account_id AND fr.sender_id = ? AND fr.status='pending') )
        WHERE a.name LIKE ?
          AND a.account_id != ?
          AND f.user1_id IS NULL 
          AND f.user2_id IS NULL
          AND fr.id IS NULL
        LIMIT 10
    ");
    $stmt->bind_param("iiiisi", $account_id, $account_id, $account_id, $account_id, $keyword, $account_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode(['status'=>'success','data'=>$users]);
    exit();
}



if(isset($_POST['edit_post'])){
    $post_id = $_POST['post_id'];
    $content = mysqli_real_escape_string($con, $_POST['content']);
    $image_sql = '';

    if(isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK){
        $tmp = $_FILES['post_image']['tmp_name'];
        $name = time() . '_' . basename($_FILES['post_image']['name']);
        $dst = __DIR__ . '/uploads/posts/' . $name;
        if(move_uploaded_file($tmp, $dst)){
            $old = mysqli_fetch_assoc(mysqli_query($con, "SELECT image FROM posts WHERE post_id='$post_id'"));
            if(!empty($old['image']) && file_exists(__DIR__.'/uploads/posts/'.$old['image'])){
                unlink(__DIR__.'/uploads/posts/'.$old['image']);
            }
            $image_sql = ", image='".mysqli_real_escape_string($con, $name)."'";
        } else {
            echo json_encode(['status'=>'error','message'=>'Failed to save image.']);
            exit;
        }
    }

    $update = mysqli_query($con, "UPDATE posts SET content='$content' $image_sql WHERE post_id='$post_id' AND account_id='$account_id'");

    if($update){
        echo json_encode(['status'=>'success','message'=>'Post updated', 'image'=> isset($name)?$name:null]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to update']);
    }
    exit;
}






// ====================== DELETE POST ======================
if(isset($_POST['delete_post'])){
    $post_id = $_POST['post_id'];

    $delete = mysqli_query($con, "
        DELETE FROM posts WHERE post_id='$post_id' AND account_id='$account_id'
    ");

    if($delete){
        echo json_encode(['status'=>'success','message'=>'Post deleted']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to delete']);
    }
    exit();
}



// ====================== LOAD_POSTS ======================
if (isset($_POST['load_posts'])) {
    $query = mysqli_query($con, "
        SELECT p.*, a.name, a.account_id
        FROM posts p
        JOIN accounts a ON p.account_id = a.account_id
        ORDER BY p.created_at DESC
        LIMIT 20
    ");

    $html = '';
    $count = 0; // counter to insert ad after every 3 posts

    if(mysqli_num_rows($query) > 0) {
        while($row = mysqli_fetch_assoc($query)) {
            $html .= "<div class='main-box mb-3 position-relative' data-postid='{$row['post_id']}'>
                        <div class='d-flex justify-content-between align-items-start'>
                            <strong>{$row['name']}</strong>";

            // Settings cog / report
            if(isset($_SESSION['auth_user']['account_id']) && $row['account_id'] == $_SESSION['auth_user']['account_id']) {
                $html .= "
                <div class='dropdown'>
                    <button class='btn btn-light btn-sm dropdown-toggle' type='button' data-bs-toggle='dropdown'>
                        <i class='bi bi-gear-fill'></i>
                    </button>
                    <ul class='dropdown-menu dropdown-menu-end'>
                        <li><button class='dropdown-item edit-post-btn' data-postid='{$row['post_id']}'>Edit</button></li>
                        <li><button class='dropdown-item delete-post-btn' data-postid='{$row['post_id']}'>Delete</button></li>
                    </ul>
                </div>";
            } else {
                $html .= "
                <button class='btn btn-light btn-sm report-post-btn' data-postid='{$row['post_id']}'>
                    <i class='bi bi-flag-fill'></i>
                </button>";
            }

            $html .= "</div>";

            // Post content + image
            $html .= "<p>".htmlspecialchars($row['content'])."</p>";
            if($row['image']) {
                $html .= "<img src='uploads/posts/{$row['image']}' class='post-image mt-2'>";
            }

            // Like & Share buttons
            $html .= "
            <div class='d-flex mt-2 gap-2'>
                <button class='btn btn-sm btn-outline-primary like-post-btn' data-postid='{$row['post_id']}'>
                    <i class='bi bi-hand-thumbs-up'></i> Like
                </button>
                <button class='btn btn-sm btn-outline-secondary share-post-btn' data-postid='{$row['post_id']}'>
                    <i class='bi bi-share'></i> Share
                </button>
            </div>";

            // Timestamp
            $html .= "<small class='text-muted'>".date('M d, Y H:i', strtotime($row['created_at']))."</small>
                    </div>";

            // Insert ad after every 3 posts
            $count++;
            if($count % 3 === 0) {
                $html .= "<div class='main-box mb-3 text-center'>
                            <p>Advertisement</p>
                            <img src='uploads/ads/ad_placeholder.jpg' class='img-fluid rounded' style='max-height:200px; object-fit:cover;'>
                          </div>";
            }
        }
    } else {
        $html = "<p>No posts yet.</p>";
    }

    echo $html;
    exit();
}


// ====================== LOAD CONNECTIONS ======================
if (isset($_POST['load_connections'])) {
    $query = mysqli_query($con, "
        SELECT a.account_id, a.name
        FROM accounts a
        JOIN friendships f ON 
            (f.user1_id = a.account_id OR f.user2_id = a.account_id)
        WHERE (f.user1_id='$account_id' OR f.user2_id='$account_id')
          AND a.account_id != '$account_id'
    ");

    $html = '';
    if(mysqli_num_rows($query) > 0){
        while($row = mysqli_fetch_assoc($query)){
            $html .= "<div class='connection-card'>
                        <span>{$row['name']}</span>
                        <button class='btn btn-danger removeFriendBtn' data-id='{$row['account_id']}'>Remove</button>
                      </div>";
        }
    } else {
        $html = "<p>No friends yet.</p>";
    }

    echo $html;
    exit;
}

// ====================== LOAD FRIEND REQUESTS + SUGGESTED ======================
if (isset($_POST['load_friends_dropdown'])) {

    $account_id_safe = mysqli_real_escape_string($con, $account_id);
    $html = '';

    // --- Pending Friend Requests ---
    $requests_query = mysqli_query($con, "
        SELECT fr.request_id, a.account_id, a.name
        FROM friend_requests fr
        JOIN accounts a ON a.account_id = fr.sender_id
        WHERE fr.receiver_id='$account_id_safe' AND fr.status='pending'
        ORDER BY fr.request_id DESC
    ");

    if(mysqli_num_rows($requests_query) > 0){
        $html .= "<h6 class='dropdown-header'>Friend Requests</h6>";
        while($row = mysqli_fetch_assoc($requests_query)){
            $html .= "<div class='connection-card d-flex justify-content-between align-items-center mb-1 p-2 border rounded'>
                        <span>{$row['name']}</span>
                        <div>
                          <button class='btn btn-success btn-sm acceptBtn' data-id='{$row['request_id']}'>Accept</button>
                          <button class='btn btn-secondary btn-sm rejectBtn' data-id='{$row['request_id']}'>Reject</button>
                        </div>
                      </div>";
        }
    }

    // --- Suggested Friends ---
    $suggested_query = mysqli_query($con, "
        SELECT * FROM accounts 
        WHERE account_id != '$account_id_safe'
          AND account_id NOT IN (
            -- Exclude friends
            SELECT CASE 
                     WHEN user1_id = '$account_id_safe' THEN user2_id 
                     ELSE user1_id 
                   END
            FROM friendships
            WHERE user1_id = '$account_id_safe' OR user2_id = '$account_id_safe'
          )
          AND account_id NOT IN (
            -- Exclude pending requests (sent or received)
            SELECT sender_id FROM friend_requests WHERE receiver_id = '$account_id_safe' AND status='pending'
            UNION
            SELECT receiver_id FROM friend_requests WHERE sender_id = '$account_id_safe' AND status='pending'
          )
        ORDER BY RAND()
        LIMIT 5
    ");

    if(mysqli_num_rows($suggested_query) > 0){
        $html .= "<h6 class='dropdown-header mt-2'>Suggested Friends</h6>";
        while($row = mysqli_fetch_assoc($suggested_query)){
            $profile = !empty($row['profile_image']) 
                ? "uploads/profiles/{$row['profile_image']}" 
                : "uploads/profiles/2.jpg";

            $html .= "
            <div class='suggestion-card d-flex align-items-center mb-1 p-2 border rounded'>
                <img src='$profile' class='rounded-circle me-2' width='40' height='40' alt='Profile'>
                <span class='flex-grow-1'>{$row['name']}</span>
                <button class='btn btn-sm btn-primary addFriendBtn' data-id='{$row['account_id']}'>
                    <i class='bi bi-person-plus'></i> Add
                </button>
            </div>";
        }
    }

    // --- If nothing found ---
    if(empty($html)){
        $html = "<p class='text-muted text-center mb-0'>No requests or suggestions.</p>";
    }

    echo $html;
    exit();
}

// ====================== LOAD SUGGESTED USERS ======================

if (isset($_POST['send_request'])) {
    $target_id = (int)$_POST['target_id'];

    // 🚫 Cannot friend yourself
    if ($target_id == $account_id) {
        echo json_encode(['status'=>'error','message'=>'You cannot add yourself.']);
        exit;
    }

    // ✅ Check if a request already exists in friend_requests or friendship
    $exists = mysqli_query($con, "
        SELECT 1 FROM friend_requests
        WHERE (sender_id='$account_id' AND receiver_id='$target_id') 
           OR (sender_id='$target_id' AND receiver_id='$account_id')
    ");
    $alreadyFriends = mysqli_query($con, "
        SELECT 1 FROM friendships
        WHERE (user1_id='$account_id' AND user2_id='$target_id') 
           OR (user1_id='$target_id' AND user2_id='$account_id')
    ");

    if(mysqli_num_rows($exists) > 0 || mysqli_num_rows($alreadyFriends) > 0){
        echo json_encode(['status'=>'error','message'=>'Request already exists or you are already friends.']);
        exit;
    }

    // Insert pending request
    $ins = mysqli_query($con, "
        INSERT INTO friend_requests (sender_id, receiver_id, status)
        VALUES ('$account_id','$target_id','pending')
    ");

    echo json_encode($ins 
        ? ['status'=>'success','message'=>'Friend request sent.'] 
        : ['status'=>'error','message'=>'Failed to send request.']);
    exit;
}

if (isset($_POST['accept_request'])) {
    $request_id = (int)$_POST['request_id'];

    // 1. Update request status
    $update = mysqli_query($con, "
        UPDATE friend_requests 
        SET status='accepted' 
        WHERE request_id='$request_id' 
          AND receiver_id='$account_id'
    ");

    if ($update) {
        // 2. Get sender_id to insert into friendships
        $row = mysqli_fetch_assoc(mysqli_query($con, "
            SELECT sender_id, receiver_id FROM friend_requests WHERE request_id='$request_id'
        "));
        $u1 = min($row['sender_id'], $row['receiver_id']);
        $u2 = max($row['sender_id'], $row['receiver_id']);

        // Insert into friendships table
        mysqli_query($con, "
            INSERT INTO friendships (user1_id, user2_id, status) 
            VALUES ('$u1','$u2','accepted')
        ");

        echo json_encode(['status'=>'success','message'=>'Friend request accepted.']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to accept request.']);
    }
    exit;
}

if (isset($_POST['reject_request'])) {
    $request_id = (int)$_POST['request_id'];
    $delete = mysqli_query($con, "
        DELETE FROM friend_requests 
        WHERE request_id='$request_id' AND receiver_id='$account_id'
    ");

    echo json_encode($delete 
        ? ['status'=>'success','message'=>'Friend request rejected.'] 
        : ['status'=>'error','message'=>'Failed to reject request.']);
    exit;
}

if (isset($_POST['remove_friend'])) {
    $friend_id = (int)$_POST['friend_id'];

    $delete = mysqli_query($con, "
        DELETE FROM friendships
        WHERE (user1_id='$account_id' AND user2_id='$friend_id') 
           OR (user1_id='$friend_id' AND user2_id='$account_id')
    ");

    echo json_encode($delete 
        ? ['status'=>'success','message'=>'Friend removed.'] 
        : ['status'=>'error','message'=>'Failed to remove friend.']);
    exit;
}

// ============== REPORT POST ==============
if (isset($_POST['report_post'])) {
    header('Content-Type: application/json');

    $post_id = (int)($_POST['post_id'] ?? 0);
    // Honor your TEMP login fallback:
    $reported_by = isset($_SESSION['auth_user']['account_id'])
        ? (int)$_SESSION['auth_user']['account_id']
        : 1;

    $reason = trim($_POST['reason'] ?? '');
    if ($post_id <= 0 || $reason === '') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid report data.']);
        exit;
    }

    $reasonEsc = mysqli_real_escape_string($con, $reason);

    // prevent duplicate reports by same user for same post
    $check = mysqli_query($con, "SELECT 1 FROM reports WHERE post_id={$post_id} AND reported_by={$reported_by} LIMIT 1");
    if ($check && mysqli_num_rows($check) > 0) {
        echo json_encode(['status' => 'success', 'message' => 'You already reported this post.']);
        exit;
    }

    $ins = mysqli_query(
        $con,
        "INSERT INTO reports (post_id, reported_by, reason, created_at)
         VALUES ({$post_id}, {$reported_by}, '{$reasonEsc}', NOW())"
    );

    if ($ins) {
        echo json_encode(['status' => 'success', 'message' => 'Thanks! Your report has been submitted.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not save your report.']);
    }
    exit;
}



if(isset($_POST['like_post'])){
    $post_id = $_POST['post_id'];
    $check = mysqli_query($con,"SELECT * FROM post_likes WHERE post_id='$post_id' AND account_id='$account_id'");
    if(mysqli_num_rows($check) > 0){
        // Already liked: remove
        mysqli_query($con,"DELETE FROM post_likes WHERE post_id='$post_id' AND account_id='$account_id'");
    } else {
        // Add like
        mysqli_query($con,"INSERT INTO post_likes (post_id, account_id) VALUES ('$post_id','$account_id')");
    }
    $count = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) as cnt FROM post_likes WHERE post_id='$post_id'"))['cnt'];
    echo json_encode(['status'=>'success','likes_count'=>$count]);
    exit();
}
// --- LOAD MESSAGES BETWEEN FRIENDS ---
if(isset($_POST['load_messages'])){
    $chatWith = $_POST['chat_with'];

    // Validate friendship
    $checkFriend = mysqli_query($con, "
        SELECT * FROM friendships
        WHERE ((user1_id='$account_id' AND user2_id='$chatWith') 
            OR (user1_id='$chatWith' AND user2_id='$account_id'))
            AND status='accepted'
    ");
    if(mysqli_num_rows($checkFriend) == 0){
        echo json_encode(['status'=>'error','message'=>'Cannot load messages.']);
        exit;
    }

    // Fetch messages
    $messagesQuery = mysqli_query($con, "
        SELECT * FROM messages
        WHERE (sender_id='$account_id' AND receiver_id='$chatWith')
           OR (sender_id='$chatWith' AND receiver_id='$account_id')
        ORDER BY timestamp ASC
    ");

    $messages = [];
    while($msg = mysqli_fetch_assoc($messagesQuery)){
        $messages[] = $msg;
    }

    // Mark as read (only messages sent TO me that are still unread)
    mysqli_query($con, "
        UPDATE messages 
        SET status='read' 
        WHERE receiver_id='$account_id' 
          AND sender_id='$chatWith' 
          AND status='unread'
    ");

    echo json_encode(['status'=>'success','messages'=>$messages]);
    exit();
}


// --- FETCH FRIENDS FOR CHAT ---
if(isset($_POST['load_chat_friends'])){
    $friendsQuery = "
        SELECT a.account_id, a.name, a.image
        FROM accounts a
        JOIN friendships f ON 
            ((f.user1_id = '$account_id' AND f.user2_id = a.account_id) 
            OR (f.user2_id = '$account_id' AND f.user1_id = a.account_id))
        WHERE f.status = 'accepted'
    ";
    $result = mysqli_query($con, $friendsQuery);
    $friends = [];
    while($row = mysqli_fetch_assoc($result)){
        $friends[] = $row;
    }
    echo json_encode(['status'=>'success','data'=>$friends]);
    exit();
}
// --- SEND MESSAGE ---
if(isset($_POST['send_message'])){
    $receiver_id = $_POST['receiver_id'];
    $message = mysqli_real_escape_string($con, $_POST['message']);

    // Check if they are friends
    $checkFriend = mysqli_query($con, "
        SELECT * FROM friendships
        WHERE ((user1_id='$account_id' AND user2_id='$receiver_id') 
            OR (user1_id='$receiver_id' AND user2_id='$account_id'))
            AND status='accepted'
    ");

    if(mysqli_num_rows($checkFriend) == 0){
        echo json_encode(['status'=>'error','message'=>'You can only message your friends.']);
        exit;
    }

    // 1. Check if a conversation already exists
    $checkConv = mysqli_query($con, "
        SELECT conversation_id FROM conversations 
        WHERE (user1_id='$account_id' AND user2_id='$receiver_id') 
           OR (user1_id='$receiver_id' AND user2_id='$account_id')
        LIMIT 1
    ");

    if(mysqli_num_rows($checkConv) > 0){
        $conv = mysqli_fetch_assoc($checkConv);
        $conversation_id = $conv['conversation_id'];  // ✅ Fixed
    } else {
        // 2. If no conversation exists, create one
        mysqli_query($con, "
            INSERT INTO conversations (user1_id, user2_id, created_at) 
            VALUES ('$account_id', '$receiver_id', NOW())
        ");
        $conversation_id = mysqli_insert_id($con);
    }

    // 3. Insert the new message under that conversation
    mysqli_query($con, "
        INSERT INTO messages (conversation_id, sender_id, receiver_id, message, timestamp, status)
        VALUES ('$conversation_id', '$account_id', '$receiver_id', '$message', NOW(), 'unread')
    ");

    echo json_encode(['status'=>'success','message'=>'Message sent']);
    exit();
}
// ===== LOAD NOTIFICATIONS =====
if (isset($_POST['load_notifications'])) {
    $account_id_safe = mysqli_real_escape_string($con, $account_id);
    $html = '';

    $notif_query = mysqli_query($con, "
        SELECT id, message, is_read, created_at
        FROM notifications
        WHERE artist_id = '$account_id_safe'
        ORDER BY created_at DESC
        LIMIT 10
    ");

    if (mysqli_num_rows($notif_query) > 0) {
        while ($row = mysqli_fetch_assoc($notif_query)) {
            $time = date("M j, g:i a", strtotime($row['created_at']));
            $readClass = ($row['is_read'] === '0') ? "fw-bold" : "text-muted";

            $html .= "
            <div class='notification-item p-2 border-bottom $readClass' data-id='{$row['id']}'>
                <small class='d-block text-muted'>$time</small>
                <span>{$row['message']}</span>
            </div>";
        }
    } else {
        $html = "<p class='text-muted text-center mb-0'>No notifications.</p>";
    }

    echo $html;
    exit();
}

if (isset($_POST['count_notifications'])) {
    $account_id_safe = mysqli_real_escape_string($con, $account_id);
    $count = mysqli_fetch_assoc(mysqli_query($con,
        "SELECT COUNT(*) as cnt FROM notifications WHERE artist_id='$account_id_safe' AND is_read='0'"
    ))['cnt'];
    echo json_encode(['count' => $count]);
    exit();
}

?>