(() => {
  const accountId = window.fmAccountId || 1;

  // DOM elements
  const audio = document.getElementById('audio-player');
  const playBtn = document.getElementById('play-btn');
  const playIcon = document.getElementById('play-icon');
  const prevBtn = document.getElementById('prev-btn');
  const nextBtn = document.getElementById('next-btn');
  const shuffleBtn = document.getElementById('shuffle-btn');
  const repeatBtn = document.getElementById('repeat-btn');
  const progressBar = document.getElementById('progress-bar');
  const currentTimeEl = document.getElementById('current-time');
  const totalTimeEl = document.getElementById('total-time');
  const volumeSlider = document.getElementById('volume-slider');
  const volIcon = document.getElementById('vol-icon');
  const playerCover = document.getElementById('player-cover');
  const playerTitle = document.getElementById('player-title');
  const playerArtist = document.getElementById('player-artist');

  const playlistBtn = document.getElementById('playlist-btn');
  const queueBtn = document.getElementById('queue-btn');
  const playlistPanel = document.getElementById('playlist-sidebar');
  const queuePanel = document.getElementById('queue-sidebar');
  const playlistContainer = document.getElementById('playlist-container');
  const queueContainer = document.getElementById('queue-container');
  const sidebarCloseBtn = document.getElementById('sidebar-close');
  const queueCloseBtn = document.getElementById('queue-close');

  // State
  let playlist = [];
  let queue = [];
  let currentIndex = 0;
  let repeatMode = false;
  let shuffleMode = false;

  const fmt = s => {
    if (isNaN(s) || s === Infinity) return '0:00';
    const m = Math.floor(s / 60), sec = Math.floor(s % 60).toString().padStart(2, '0');
    return `${m}:${sec}`;
  };

  const saveState = () => {
    localStorage.setItem('musicPlayerState', JSON.stringify({
      playlist, currentIndex, time: audio.currentTime, playing: !audio.paused, repeatMode, shuffleMode
    }));
  };

  const loadState = () => {
    try {
      const st = JSON.parse(localStorage.getItem('musicPlayerState') || 'null');
      if (st && Array.isArray(st.playlist)) {
        playlist = st.playlist;
        currentIndex = Math.min(Math.max(0, st.currentIndex || 0), playlist.length - 1);
        populatePlaylistUI();
        if (playlist.length) loadSong(currentIndex, false);
        if (st.time) audio.currentTime = st.time;
        repeatMode = !!st.repeatMode;
        shuffleMode = !!st.shuffleMode;
      }
    } catch (e) { console.error(e); }
  };

  const loadSong = (index, autoplay = true) => {
    if (!playlist.length) return;
    index = Math.min(Math.max(0, index), playlist.length - 1);
    currentIndex = index;
    const s = playlist[index];
    audio.src = s.src;
    playerCover.src = s.cover || 'uploads/covers/default.jpg';
    playerTitle.innerText = s.title || 'Untitled';
    playerArtist.innerText = s.artist || 'Artist';
    highlightActiveSong();
    if (autoplay) {
      audio.play().then(() => playIcon.className = 'fa fa-pause').catch(() => playIcon.className = 'fa fa-play');
    }
    saveState();
  };

  const playPause = () => {
    if (audio.paused) audio.play().then(() => playIcon.className='fa fa-pause').catch(()=>playIcon.className='fa fa-play');
    else { audio.pause(); playIcon.className='fa fa-play'; }
    saveState();
  };

  const nextSong = () => {
    if (!playlist.length) return;
    if (shuffleMode) loadSong(Math.floor(Math.random() * playlist.length));
    else loadSong((currentIndex + 1) % playlist.length);
    audio.play();
  };

  const prevSong = () => {
    if (!playlist.length) return;
    if (shuffleMode) loadSong(Math.floor(Math.random() * playlist.length));
    else loadSong((currentIndex - 1 + playlist.length) % playlist.length);
    audio.play();
  };

  const toggleShuffle = () => { shuffleMode = !shuffleMode; shuffleBtn.classList.toggle('active', shuffleMode); saveState(); };
  const toggleRepeat = () => { repeatMode = !repeatMode; repeatBtn.classList.toggle('active', repeatMode); saveState(); };

  const populatePlaylistUI = () => {
    playlistContainer.innerHTML = '';
    playlist.forEach((s, i) => {
      const div = document.createElement('div');
      div.className = 'playlist-item' + (i === currentIndex ? ' active' : '');
      div.dataset.index = i;
      div.dataset.playlistId = s.playlist_id || '';
      div.innerHTML = `
        <div style="display:flex;align-items:center;">
          <img src="${s.cover || 'uploads/covers/default.jpg'}" class="mini-cover">
          <div>
            <div>${s.title}</div>
            <small style="color:#aaa">${s.artist || ''}</small>
          </div>
        </div>
        <div>
          <button class="icon-btn play-now" title="Play now"><i class="fa fa-play"></i></button>
          <button class="icon-btn remove-from-playlist" title="Remove"><i class="fa fa-trash"></i></button>
        </div>
      `;
      div.querySelector('.play-now').addEventListener('click', ev => { ev.stopPropagation(); loadSong(i, true); });
      div.querySelector('.remove-from-playlist').addEventListener('click', ev => {
        ev.stopPropagation();
        if(s.playlist_id) removeSongFromServerPlaylist(s.playlist_id);
      });
      div.addEventListener('click', ()=>loadSong(i,true));
      playlistContainer.appendChild(div);
    });
    highlightActiveSong();
    renderQueue();
  };

  const highlightActiveSong = () => {
    document.querySelectorAll('.playlist-item').forEach(el =>
      el.classList.toggle('active', Number(el.dataset.index) === currentIndex)
    );
  };

  const renderQueue = () => {
    if (!queueContainer) return;
    queueContainer.innerHTML = '';
    queue.forEach(s => {
      const div = document.createElement('div');
      div.className = 'playlist-item';
      div.innerHTML = `
        <div style="display:flex;align-items:center;">
          <img src="${s.cover || 'uploads/covers/default.jpg'}" class="mini-cover">
          <div>
            <div>${s.title}</div>
            <small style="color:#aaa">${s.artist || ''}</small>
          </div>
        </div>
      `;
      queueContainer.appendChild(div);
    });
  };

  // ===================== AJAX =====================
  const loadUserPlaylist = async () => {
    try {
      const res = await fetch(`playlist.php?get=1&account_id=${accountId}`);
      const data = await res.json();
      if(data.status==='success'){
        playlist = data.playlist.map(s=>({
          title: s.title,
          src: s.recording_path,
          cover: s.cover,
          artist: s.artist||'',
          playlist_id: s.playlist_id
        }));
        populatePlaylistUI();
        if(playlist.length) loadSong(0,true);
      }
    } catch(e){ console.error(e); }
  };

  window.addSongToServerPlaylist = async (recording_id) => {
    const formData = new FormData();
    formData.append('add', 1);
    formData.append('recording_id', recording_id);
    try {
      const res = await fetch(`playlist.php?account_id=${accountId}`, { method:'POST', body: formData });
      const data = await res.json();
      if(data.status==='success'){
        const song = {
          title:data.song.title,
          src:data.song.recording_path,
          cover:data.song.cover,
          playlist_id:data.song.playlist_id
        };
        playlist.push(song);
        populatePlaylistUI();
        loadSong(playlist.length-1,true);
      } else { console.log(data.message); }
    } catch(e){ console.error(e); }
  };

  const removeSongFromServerPlaylist = async (playlist_id) => {
    const formData = new FormData();
    formData.append('remove',1);
    formData.append('playlist_id',playlist_id);
    try {
      const res = await fetch(`playlist.php?account_id=${accountId}`, { method:'POST', body: formData });
      const data = await res.json();
      if(data.status==='success'){
        playlist = playlist.filter(s=>s.playlist_id!==playlist_id);
        populatePlaylistUI();
      }
    } catch(e){ console.error(e); }
  };

  // ===================== INIT =====================
  loadUserPlaylist();
  loadState();

  window.addEventListener('beforeunload', saveState);

  // ===================== Controls =====================
  playBtn.addEventListener('click', playPause);
  prevBtn.addEventListener('click', prevSong);
  nextBtn.addEventListener('click', nextSong);
  shuffleBtn.addEventListener('click', toggleShuffle);
  repeatBtn.addEventListener('click', toggleRepeat);

  playlistBtn.addEventListener('click', ()=>{ playlistPanel.classList.toggle('show'); queuePanel.classList.remove('show'); });
  queueBtn.addEventListener('click', ()=>{ queuePanel.classList.toggle('show'); playlistPanel.classList.remove('show'); });
  sidebarCloseBtn.addEventListener('click', ()=>playlistPanel.classList.remove('show'));
  queueCloseBtn.addEventListener('click', ()=>queuePanel.classList.remove('show'));
})();
