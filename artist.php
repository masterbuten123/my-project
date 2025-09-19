<?php
session_start();
include('functions/myfunctions.php');
include('includes/header.php');

$audioDir = 'uploads/audio';
$defaultProfile = 'uploads/profiles/2.jpg';

$artists = getAllArtists();
$allRecordings = getAllRecordings();
?>

<div class="container py-5">
    <h2 class="mb-4 text-danger fw-bold">Our Artists</h2>
    <div class="row row-cols-2 row-cols-md-4 g-3">
        <?php if(!empty($artists)): ?>
            <?php foreach($artists as $artist):
                $recordings = $allRecordings[$artist['account_id']] ?? [];
                $preview = !empty($recordings) ? $recordings[0]['recording_path'] : '';
                $recordingPath = $preview ? (filter_var($preview, FILTER_VALIDATE_URL) ? $preview : $audioDir.'/'.$preview) : '';
                $profile = !empty($artist['image']) ? '/'.$artist['image'] : $defaultProfile;
            ?>
            <div class="col">
                <div class="artist-card p-2 bg-dark text-white rounded position-relative">
                    <img src="<?= htmlspecialchars($profile); ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($artist['name']); ?>">
                    
                    <?php if($recordingPath): ?>
                    <button class="play-btn btn btn-danger btn-sm" 
                        onclick="event.stopPropagation(); playPreview('<?= $recordingPath ?>', this, 'progress-<?= $artist['account_id'] ?>')">
                        <i class="fas fa-play"></i>
                    </button>
                    <div class="progress mt-2" style="height:4px;">
                        <div class="progress-bar bg-danger" id="progress-<?= $artist['account_id'] ?>" style="width:0%;"></div>
                    </div>
                    <?php endif; ?>
                    
                    <a href="artist-portfolio.php?artist_id=<?= $artist['account_id'] ?>" 
                        class="stretched-link text-decoration-none text-white"></a>
                    <div class="mt-2 text-center fw-semibold"><?= htmlspecialchars($artist['name']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted">No artists available.</div>
        <?php endif; ?>
    </div>
</div>
<?php include('floating_music.php') ?>

<script>
let currentAudio = null, currentButton = null, currentProgress = null, progressInterval = null;

function playPreview(src, btn, progressId){
    const progressBar = document.getElementById(progressId);
    
    if(currentAudio && currentAudio.src === src){
        if(currentAudio.paused){
            currentAudio.play(); btn.innerHTML = '<i class="fas fa-pause"></i>'; startProgress();
        } else {
            currentAudio.pause(); btn.innerHTML = '<i class="fas fa-play"></i>'; stopProgress();
        }
        return;
    }

    if(currentAudio){
        currentAudio.pause(); currentAudio.currentTime = 0;
        if(currentProgress) currentProgress.style.width='0%';
        if(currentButton) currentButton.innerHTML = '<i class="fas fa-play"></i>';
        stopProgress();
    }

    currentAudio = new Audio(src);
    currentButton = btn;
    currentProgress = progressBar;
    currentAudio.play(); btn.innerHTML = '<i class="fas fa-pause"></i>';
    startProgress();

    currentAudio.onended = () => {
        btn.innerHTML = '<i class="fas fa-play"></i>';
        if(currentProgress) currentProgress.style.width='0%';
        stopProgress(); currentAudio = currentButton = currentProgress = null;
    };
}

function startProgress(){
    stopProgress();
    progressInterval = setInterval(() => {
        if(currentAudio && currentProgress){
            currentProgress.style.width = (currentAudio.currentTime / currentAudio.duration * 100) + '%';
        }
    }, 200);
}

function stopProgress(){ clearInterval(progressInterval); }
</script>

<style>
.artist-card{
    cursor:pointer;
    transition: transform .2s ease, box-shadow .2s ease;
    overflow:hidden;
}
.artist-card:hover{ transform: scale(1.05); box-shadow:0 10px 20px rgba(255,0,0,0.3); }
.play-btn{
    position:absolute; bottom:10px; right:10px; border-radius:50%;
    padding:6px 8px; z-index:5; opacity:0; transition:opacity .2s;
}
.artist-card:hover .play-btn{ opacity:1; }
.progress{border-radius:2px;}
</style>

<?php include('includes/footer.php'); ?>
