<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/dbcon.php');
include('includes/navbar.php');
include('functions/myfunctions.php');

// TEMP: allow page even when not logged in
$account_id = isset($_SESSION['auth_user']['account_id']) ? $_SESSION['auth_user']['account_id'] : 1;

// Fetch current user info
$user_query = mysqli_query($con, "SELECT * FROM accounts WHERE account_id='$account_id' LIMIT 1");
$current_user = mysqli_fetch_assoc($user_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Network</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f3f2ef; }
        .sidebar-box, .main-box, .right-box { background: #fff; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .post-box textarea { width: 100%; border: none; resize: none; padding: 10px; border-radius: 5px; background-color: #f3f2ef; }
        .connection-card, .suggestion-card, .chat-user-card { display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; }
        .connection-card img, .suggestion-card img, .chat-user-card img { border-radius: 50%; width: 45px; height: 45px; margin-right: 10px; }
        .chat-box { position: fixed; bottom: 20px; right: 20px; width: 300px; max-height: 400px; border-radius: 8px; overflow: hidden; display: none; flex-direction: column; box-shadow: 0 0 10px rgba(0,0,0,0.2); background: #fff; }
        .chat-box-header { background: #0d6efd; color: #fff; padding: 10px; display: flex; justify-content: space-between; align-items: center; cursor: move; }
        .chat-box-body { flex: 1; padding: 10px; overflow-y: auto; background: #f8f9fa; }
        .chat-box-footer { display: flex; border-top: 1px solid #ddd; }
        .chat-box-footer input { flex: 1; border: none; padding: 5px 10px; }
        .chat-box-footer button { border: none; background: #0d6efd; color: #fff; padding: 5px 10px; }
        .container-fixed { height: calc(100vh - 70px); }
        .sidebar-box, .right-box, .music-player-box { position: sticky; top: 70px; }
        .posts-scroll {max-height: calc(100vh - 70px);overflow-y: auto;padding-right: 10px;scrollbar-width: none;-ms-overflow-style: none;}
        .posts-scroll::-webkit-scrollbar {display: none;}
        .nav-link i {margin-right: 6px;color: #e50914;}
        .post-image {width: 100%;max-width: 100%;max-height: 400px;object-fit: cover;border-radius: 0.25rem;}
        .sidebar-box {position: sticky;top: 70px; /* same as navbar height */overflow-y: auto;padding: 15px;background: #fff;border-radius: 8px;}
        .posts-scroll {max-height: calc(100vh - 70px);overflow-y: auto;padding-right: 10px;scrollbar-width: none; /* Firefox */-ms-overflow-style: none; /* IE 10+ */}
        .posts-scroll::-webkit-scrollbar {display: none;}
.profile-box {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.profile-box img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #0d6efd;
    margin-bottom: 12px;
    transition: transform 0.3s ease;
}


.profile-box h6 {
    font-weight: 600;
    margin-bottom: 4px;
    font-size: 1rem;
    color: #212529;
}

.profile-box p {
    color: #6c757d;
    margin-bottom: 12px;
    font-size: 0.9rem;
}

.profile-box .edit-profile-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 6px 14px;
    font-size: 0.85rem;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.profile-box .edit-profile-btn:hover {
    background-color: #0d6efd;
    color: #fff;
    box-shadow: 0 3px 8px rgba(13,110,253,0.3);
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    
    <!-- BRAND -->
    <a class="navbar-brand" href="#">My Network</a>
    
    <!-- TOGGLER (Mobile Menu) -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <!-- SEARCH FORM -->
      <form class="d-flex me-auto position-relative" id="searchForm" onsubmit="return false;">
        <input 
          class="form-control me-2" 
          type="search" 
          placeholder="Search friends..." 
          id="searchInput"
          autocomplete="off"
        >
        <button class="btn btn-light" type="submit">Search</button>
        <!-- DROPDOWN RESULTS -->
        <div id="searchResults" class="list-group position-absolute w-100 mt-5 shadow" 
          style="z-index: 1050; display: none; max-height: 300px; overflow-y: auto;">
        </div>
      </form>

      <!-- NAV ICONS -->
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        
        <!-- FRIEND LIST -->
        <li class="nav-item dropdown mx-2">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="connectionsDropdown" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-people fs-5"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="connectionsDropdown" style="min-width: 300px; max-height: 400px; overflow-y:auto;">
            <li id="connectionsContainer"><em class="text-muted">Loading...</em></li>
          </ul>
        </li>
        <!-- FRIEND REQUESTS + SUGGESTIONS (merged) -->
        <li class="nav-item dropdown mx-2">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="friendsDropdown" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-plus fs-5"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="friendsDropdown" style="min-width: 300px; max-height: 400px; overflow-y:auto;">
            <li id="invitationsContainer"><em class="text-muted">Loading...</em></li>
          </ul>
        </li>
        <!-- NOTIFICATIONS / SUGGESTED -->
        <li class="nav-item dropdown mx-2">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-lightbulb fs-5"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="notificationsDropdown" style="min-width: 300px; max-height: 400px; overflow-y:auto;">
            <li id="suggestedContainer"><em class="text-muted">Loading...</em></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>



<div class="container-fluid container-fixed mt-3">
  <div class="row">
    <!-- LEFT SIDEBAR -->
    <div class="col-md-3">
      <div class="profile-box mb-3">
        <img src="<?= !empty($current_user['image']) ? '/'.$current_user['image'] : 'uploads/profiles/2.jpg'; ?>" alt="Profile Picture">
        <h6><?= htmlspecialchars($current_user['name']); ?></h6>
        <p>@<?= htmlspecialchars($current_user['username']); ?></p>
        <a href="my-profile.php" class="btn btn-outline-primary btn-sm edit-profile-btn">my Profile</a>
      </div>
        
    <div class="sidebar-box mb-3 text-center p-3" style="background: #f8f9fa; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <h6 class="mb-2">🌟 Upgrade to Premium</h6>
    <p class="text-muted" style="font-size: 0.9rem;">Get unlimited access to exclusive content and ad-free experience.</p>
    <a href="plan_selection.php" class="btn btn-primary btn-sm " target="_blank">Subscribe Now</a>
  </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="col-md-6 posts-scroll">
  <div class="main-box post-box mb-3 p-3" id="createPostBox">
    <div class="d-flex align-items-center">
      <img src="<?= !empty($current_user['image']) ? '/'.$current_user['image'] : 'uploads/profiles/2.jpg'; ?>" class="rounded-circle me-2" width="45" height="45">
      <textarea id="postTextarea" class="form-control flex-grow-1" rows="1" placeholder="What's on your mind, <?= htmlspecialchars($current_user['name']); ?>?" ></textarea>
    </div>

    </div>

      <div class="main-box mb-3 border p-3 text-center ad-box">
        <h6 class="text-muted">Sponsored</h6>
        <img src="uploads/ads/ad1.jpg" class="img-fluid rounded mb-2">
        <p>Check out this amazing product!</p>
      </div>

      <div id="postsContainer"></div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="col-md-3">
      <div class="right-box p-3 mb-3">
        <h6>Suggestions</h6>
        <p class="text-muted">Friends, groups, ads, etc.</p>
      </div>
      <!-- Music Sidebar -->
      <div class="profile-box mb-3">
        <h6 class="mb-3">Library</h6>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <a href="library.php" class="text-decoration-none">Library</a>
          </li>
          <li class="list-group-item">
            <a href="playlists.php" class="text-decoration-none">Playlists</a>
          </li>
          <li class="list-group-item">
            <a href="songs.php" class="text-decoration-none">Songs</a>
          </li>
          <li class="list-group-item">
            <a href="made_for_you.php" class="text-decoration-none">Made for You</a>
          </li>
          <li class="list-group-item">
            <a href="artist.php" class="text-decoration-none">Artists</a>
          </li>
          <li class="list-group-item">
            <a href="albums.php" class="text-decoration-none">Albums</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php include('floating_music.php') ?>
<!-- ADD POST MODAL -->
<div class="modal fade" id="addPostModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addPostForm" class="modal-content" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="add_post" value="1">
      <div class="modal-header">
        <h5 class="modal-title">Add Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <textarea class="form-control mb-3" name="post_content" id="addPostContent" rows="5" placeholder="What's on your mind?" required></textarea>
        <div class="mb-3 d-flex align-items-center">
            <input type="file" class="d-none" id="postImage" name="post_image" accept="image/*">
              <button type="button" class="btn btn-outline-secondary" id="uploadBtn">
                <i class="bi bi-camera"></i>
              </button>
              <span id="fileName" class="ms-2 text-muted"></span>
        </div>
        <div class="text-center">
            <img id="previewImage" src="" alt="Image Preview" class="img-fluid rounded mt-2 d-none" style="max-height: 200px;">
        </div>
      </div>
      <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Post</button>
      </div>
    </form>
  </div>
</div>
<!-- Edit Post Modal -->
<div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editPostForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="editPostModalLabel">Edit Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- FIX: change name to 'content' -->
          <textarea class="form-control mb-3" name="content" id="editPostContent" rows="5" placeholder="What's on your mind?" required></textarea>
          
          <!-- hidden post id -->
          <input type="hidden" name="post_id" id="editPostId">

          <!-- optional image upload -->
          <div class="mb-3 d-flex align-items-center">
              <input type="file" class="d-none" id="editPostImage" name="post_image" accept="image/*">
              <button type="button" class="btn btn-outline-secondary" id="editUploadBtn">
                <i class="bi bi-camera"></i>
              </button>
              <span id="editFileName" class="ms-2 text-muted"></span>
          </div>

          <div class="text-center">
              <img id="editPreviewImage" src="" alt="Image Preview" class="img-fluid rounded mt-2 d-none" style="max-height: 200px;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Report Post Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Report Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="reportForm">
          <input type="hidden" id="reportPostId" name="post_id">

          <div class="mb-3">
            <label class="form-label">Reason</label>
            <select class="form-select" id="reportReason" name="reason" required>
              <option value="">-- Select a reason --</option>
              <option value="Spam">Spam</option>
              <option value="Fake Account">Fake Account</option>
              <option value="Inappropriate Content">Inappropriate Content</option>
              <option value="Harassment">Harassment</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="mb-3 d-none" id="otherReasonBox">
            <label class="form-label">Other reason</label>
            <textarea class="form-control" id="otherReason" rows="3" placeholder="Tell us a bit more..."></textarea>
          </div>

          <button type="submit" class="btn btn-danger w-100">Submit Report</button>
        </form>
      </div>
    </div>
  </div>
</div>
</div>

<script src="assets/js/socials.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">



<?php if (isset($_SESSION['status']) && $_SESSION['status'] != ''): ?>
<script>
Swal.fire({
  title: '<?= $_SESSION['status']; ?>',
  icon: '<?= $_SESSION['status_code']; ?>',
  confirmButtonText: 'OK'
});
</script>
<?php unset($_SESSION['status']); unset($_SESSION['status_code']); ?>
<?php endif; ?>
</body>
</html>