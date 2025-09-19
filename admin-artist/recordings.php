<?php
session_start();
include('../config/dbcon.php');
include('includes/header.php');

$artist_id = $_SESSION['auth_user']['account_id'];

// Fetch recordings
$result = mysqli_query($con, "SELECT * FROM artist_recordings WHERE account_id='$artist_id' AND status='active' ORDER BY created_at DESC");
?>

<div class="container py-5">
    <h2 class="fw-bold text-danger mb-4">🎵 My Recordings</h2>

    <!-- Alert Messages -->
    <?php if(isset($_SESSION['alert'])): ?>
        <div class="alert alert-<?= $_SESSION['alert']['type'] === 'success' ? 'success':'danger' ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['alert']['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <!-- Recordings List -->
    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="row">
            <?php while($rec = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow rounded-3 h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($rec['title']) ?></h5>
                            <p class="text-muted small mb-2">Uploaded: <?= date('M d, Y', strtotime($rec['created_at'])) ?></p>

                            <!-- Cover Image -->
                            <?php if(!empty($rec['cover'])): ?>
                                <img src="../uploads/covers/<?= htmlspecialchars($rec['cover']) ?>" 
                                     alt="Cover Image" class="img-fluid mb-3" 
                                     style="max-height:200px; object-fit:cover; border-radius:8px;">
                            <?php endif; ?>

                            <!-- Audio Player -->
                            <audio controls class="w-100 mb-3 mt-auto">
                                <source src="../uploads/audio/<?= htmlspecialchars($rec['recording_path']) ?>" type="audio/mpeg">
                            </audio>

                            <!-- Soft Delete -->
                            <form method="POST" action="code.php" class="mt-auto">
                                <input type="hidden" name="recording_id" value="<?= $rec['id'] ?>">
                                <button type="submit" name="delete_recording_btn" 
                                        class="btn btn-danger btn-sm w-100" 
                                        onclick="return confirm('Delete this recording?');">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No recordings yet.</div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="card shadow rounded-3 mb-4 p-3">
        <h5 class="fw-bold text-success">Upload New Recording</h5>
        <form id="uploadForm" method="POST" enctype="multipart/form-data" action="code.php">
            <input type="hidden" name="artist_id" value="<?= $artist_id ?>">
            <div class="mb-3">
    <label class="form-label fw-bold">Cover Photo</label>
    <input type="file" name="cover" id="cover" class="form-control" accept="image/*">
            </div>

            <!-- Preview -->
            <img id="coverPreview" src="" alt="Cover preview" 
                style="display:none; max-width:100%; height:auto; border-radius:8px; margin-bottom:10px;">


            <div class="mb-3">
                <label class="form-label fw-bold">Title</label>
                <input type="text" name="title" class="form-control" placeholder="Song title" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Recording</label>
                <input type="file" name="recording" id="recording" class="form-control" accept="audio/*" required>
            </div>

            <!-- Preview -->
            <audio id="preview" controls style="display:none;" class="w-100 mb-3"></audio>

            <!-- Progress Bar -->
            <progress id="progressBar" value="0" max="100" style="width:100%; display:none;"></progress>

            <button type="submit" name="upload_recording_btn" class="btn btn-success">Upload</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('cover').addEventListener('change', function(){
    const file = this.files[0];
    if(file){
        const preview = document.getElementById('coverPreview');
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    }
});
// Preview before upload
document.getElementById('recording').addEventListener('change', function(){
    const file = this.files[0];
    if(file){
        const audio = document.getElementById('preview');
        audio.src = URL.createObjectURL(file);
        audio.style.display = 'block';
    }
});
</script>

<?php include('includes/footer.php'); ?>
