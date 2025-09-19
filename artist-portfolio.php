<?php
session_start();
include('functions/myfunctions.php');
include('includes/header.php');
include('config/dbcon.php');

$audioDir = 'uploads/audio';
$defaultProfile = 'uploads/profiles/2.jpg';

// Get artist
$artist_id = $_GET['artist_id'] ?? null;
if (!$artist_id) die("<div class='text-center mt-5'>Artist not found.</div>");

$artist = getArtistByIdd($artist_id);
if (!$artist) die("<div class='text-center mt-5'>Artist not found.</div>");

$recordings = getRecordingsByArtist($artist_id);
$isOwner = isset($_SESSION['auth_user']) && $_SESSION['auth_user']['account_id'] == $artist_id;
$profileImage = !empty($artist['image']) ? 'uploads/profiles/'.$artist['image'] : $defaultProfile;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
/* ---------- General ---------- */
body { background-color: #121212; color: #e0e0e0; font-family: 'Inter', sans-serif; }
a { color: #e50914; text-decoration: none; }

/* ---------- Artist Profile ---------- */
.artist-card {
    background-color: #1e1e1e;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.artist-card:hover { transform: scale(1.02); box-shadow: 0 10px 25px rgba(255,0,0,0.4); }
.artist-card img { width: 100%; object-fit: cover; height: 300px; }

/* ---------- Recording List ---------- */
.recording-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: #1e1e1e;
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer;
}
.recording-card:hover { transform: scale(1.01); box-shadow: 0 5px 15px rgba(255,0,0,0.3); }
.recording-card img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; margin-right: 10px; }
.play-btn { background-color: #e50914; border: none; color: #fff; }

/* ---------- Responsive ---------- */
@media (max-width: 768px) {
    .row { flex-direction: column; }
    .recording-card { flex-direction: column; align-items: flex-start; }
}
</style>

<div class="container py-5">
  <div class="row">
    <!-- Artist Profile -->
    <div class="col-md-4 mb-4">
      <div class="artist-card text-center">
        <img src="<?= htmlspecialchars($profileImage); ?>" alt="<?= htmlspecialchars($artist['name']); ?>" 
             onerror="this.src='uploads/profiles/2.jpg'">
        <div class="p-3">
          <h2><?= htmlspecialchars($artist['name']); ?> <?= $isOwner ? "(You)" : ""; ?></h2>
          <p><strong>Genre:</strong> <?= htmlspecialchars($artist['genre'] ?? 'N/A'); ?></p>
          <p><strong>Location:</strong> <?= htmlspecialchars($artist['location'] ?? 'N/A'); ?></p>
          <p><strong>Price:</strong> 
<?php 
    if (!empty($artist['price_per_hour']) && $artist['price_per_hour'] > 0) {
        echo '₱' . number_format($artist['price_per_hour'], 2);
    } else {
        echo 'Call for Fee';
    }
?></p>
        </div>
<a class="btn btn-danger ms-3" 
   href="entertainment-request.php?account_id=<?= $artist_id ?>" 
   role="button" aria-label="Get Started">
   <i class="fas fa-music me-1"></i> book now 
</a>
      </div>
      
    </div>
    

    <!-- Recording List -->
    <div class="col-md-8">
      <h4 class="mb-3">Tracks</h4>
      <?php if (!empty($recordings)): ?>
        <?php foreach ($recordings as $rec): 
            $recPath = filter_var($rec['recording_path'], FILTER_VALIDATE_URL) ? $rec['recording_path'] : $audioDir.'/'.$rec['recording_path'];
            $coverPath = !empty($rec['cover']) ? 'uploads/covers/'.$rec['cover'] : 'uploads/covers/default.jpg';
        ?>
        <div class="recording-card" 
            data-src="<?= htmlspecialchars($recPath); ?>" 
            data-title="<?= htmlspecialchars($rec['title']); ?>" 
            data-artist="<?= htmlspecialchars($artist['name']); ?>" 
            data-cover="<?= htmlspecialchars($coverPath); ?>" 
            data-id="<?= $rec['id']; ?>">

          <div class="d-flex align-items-center">
            <img src="<?= htmlspecialchars($coverPath); ?>" alt="cover">
            <span><?= htmlspecialchars($rec['title'] ?? 'Untitled'); ?></span>
          </div>

          <div>
            <button class="btn btn-sm play-btn"><i class="fas fa-play"></i></button>
            <button class="btn btn-sm btn-primary add-to-playlist-btn" data-id="<?= $rec['id']; ?>">Add to Playlist</button>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-muted">No recordings available.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include("floating_music.php") ?>
<script>
/* Artist profile -> use the global player's API */
document.querySelectorAll('.add-to-playlist-btn').forEach(btn => {
  btn.addEventListener('click', (e) => {
    const recordingId = btn.dataset.id;
    // Optional: call server to add to DB playlist (your existing endpoint)
    fetch('playlist.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: `add=1&recording_id=${recordingId}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success' && data.song) {
        // add to floating player's playlist (no autoplay)
        window.addToPlayerPlaylist({
          title: data.song.title,
          src: data.song.recording_path || data.song.recordingPath || data.song.recording_path,
          artist: data.song.artist || 'Artist',
          cover: data.song.cover
        }, false);
        // small confirmation
        Swal.fire({icon:'success',title:'Added',text:'Added to floating playlist',timer:900,showConfirmButton:false});
      } else if (data.status === 'info') {
        Swal.fire({icon:'info',title:data.message, timer:900, showConfirmButton:false});
      } else {
        Swal.fire({icon:'error', title:'Error', text:data.message || 'Could not add to playlist'});
      }
    }).catch(err=>{
      console.error(err);
      Swal.fire({icon:'error', title:'Network Error', text:'Could not add to playlist'});
    });
  });
});

// Play button on recording card: add to player and play immediately
document.querySelectorAll('.recording-card .play-btn').forEach((btn, idx) => {
  btn.addEventListener('click', (e) => {
    const card = btn.closest('.recording-card');
    const src = card.dataset.src;
    const title = card.dataset.title;
    const artist = card.dataset.artist;
    const cover = card.dataset.cover;
    // Add and autoplay
    window.addToPlayerPlaylist({ title, src, artist, cover }, true);
  });
});
</script>

<?php include('includes/footer.php'); ?>
