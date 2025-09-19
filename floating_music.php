<?php
// floating_music.php - unified floating player
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$account_id = $_SESSION['auth_user']['account_id'] ??''; // fallback for testing
?>

<!-- Floating Music Player -->
<div id="music-player" class="music-player">
  <div class="player-left">
    <img id="player-cover" src="uploads/covers/default.jpg" alt="Cover" class="cover-img">
    <div>
      <h6 id="player-title">Song Title</h6>
      <p id="player-artist">Artist Name</p>
    </div>
  </div>

  <div class="player-center">
    <div class="controls">
      <button id="shuffle-btn"><i class="fa fa-random"></i></button>
      <button id="prev-btn"><i class="fa fa-step-backward"></i></button>
      <button id="play-btn"><i id="play-icon" class="fa fa-play"></i></button>
      <button id="next-btn"><i class="fa fa-step-forward"></i></button>
      <button id="repeat-btn"><i class="fa fa-repeat"></i></button>
      <button id="volume-btn"><i id="vol-icon" class="fa fa-volume-up"></i></button>
      <input type="range" id="volume-slider" min="0" max="1" step="0.01" value="1">
    </div>
    <div class="progress">
      <span id="current-time">0:00</span>
      <input type="range" id="progress-bar" value="0" min="0" step="0.1">
      <span id="total-time">0:00</span>
    </div>
  </div>
  <div class="player-right">
    <button id="playlist-btn"><i class="fa fa-music"></i></button>
    <button id="queue-btn"><i class="fa fa-list"></i></button>
  </div>
</div>
<audio id="audio-player"></audio>
<!-- Playlist Sidebar -->
<div id="playlist-sidebar" class="playlist-sidebar">
  <button id="sidebar-close"><i class="fa fa-times"></i></button>
  <h6>Your Playlist</h6>
  <div id="playlist-container"></div>
</div>

<!-- Queue Sidebar -->
<div id="queue-sidebar" class="playlist-sidebar">
  <button id="queue-close"><i class="fa fa-times"></i></button>
  <h6>Up Next</h6>
  <div id="queue-container"></div>
</div>


      




<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<style>
.music-player {
  position: fixed;
  bottom:0; left:0; width:100%;
  background:#111; color:#fff;
  display:flex; align-items:center; justify-content:space-between;
  padding:8px 12px; box-shadow:0 -2px 10px rgba(0,0,0,0.5);
  z-index:9999; font-family:sans-serif;
}

.player-left { display:flex; align-items:center; gap:8px; }
.cover-img { width:40px; height:40px; border-radius:4px; object-fit:cover; }
.player-center { flex:1; display:flex; flex-direction:column; align-items:center; gap:6px; }
.controls, .player-right { display:flex; align-items:center; gap:8px; }
.progress { display:flex; align-items:center; gap:6px; width:100%; max-width:400px; color: #fff; }
#progress-bar { flex:1; }
button { background:none; border:none; color:#fff; font-size:16px; cursor:pointer; }
button:hover { color:#1db954; }

/* Playlist Sidebar */
.playlist-sidebar {
  position: fixed;
  top: 60px;
  right: 0;
  width: 300px;
  height: calc(100% - 60px);
  background: #222;
  color: #fff;
  padding: 10px;
  box-shadow: -2px 0 10px rgba(0,0,0,0.5);
  z-index: 9998;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 6px;
  transform: translateX(100%);
  transition: transform 0.3s ease;
}

.playlist-sidebar.show { transform: translateX(0); }

.playlist-sidebar h6 { margin-bottom: 10px; }
.playlist-sidebar .playlist-item {
  display:flex; align-items:center; justify-content:space-between;
  padding:6px; border-bottom:1px solid #333; cursor:pointer;
}
.playlist-sidebar .mini-cover { width:30px; height:30px; border-radius:3px; object-fit:cover; margin-right:6px; }
#sidebar-close { align-self:flex-end; font-size:16px; cursor:pointer; background:none; border:none; color:#fff; margin-bottom:6px; }
.playlist-item.active { background:#1db954; color:#fff; }
  #queue-sidebar.show { transform: translateX(0); }
</style>

<script src="assets/js/fm.js"></script>

