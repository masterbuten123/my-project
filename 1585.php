<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Museo Artist Platform</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --accent: #e50914;
      --glass: rgba(255,255,255,0.05);
      --card-bg: rgba(255,255,255,0.04);
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(180deg, #0a0a0a, #141414);
      color: #f2f2f2;
    }

    /* Hero */
    .hero {
      position: relative;
      overflow: hidden;
      border-radius: 18px;
      background: linear-gradient(180deg, rgba(0,0,0,0.6), rgba(0,0,0,0.9)),
                  url("https://images.unsplash.com/photo-1511379938547-c1f69419868d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1400&q=80")
                  center/cover no-repeat;
      animation: zoom-pan 20s ease-in-out infinite alternate;
      padding: 2rem;
    }

  

    .hero::after {
      content: "";
      position: absolute;
      top: 50%; left: 50%;
      width: 400px; height: 400px;
      background: url("495072995_555986543907684_5661959930299558935_n.jpg") center/contain no-repeat;
      opacity: 0.07;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }

    /* Artist Card */
    .artist-card {
      background: var(--card-bg);
      border-radius: 14px;
      padding: 1.5rem;
      text-align: center;
      animation: floaty 6s ease-in-out infinite;
    }

    @keyframes floaty {
      0%,100% { transform: translateY(0); }
      50% { transform: translateY(-8px); }
    }

    .artist-photo {
      width: 180px; height: 180px;
      border-radius: 50%;
      background: linear-gradient(135deg,#222,#111);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; margin: 0 auto 1rem auto;
    }

    /* Cards */
    .card-custom {
      background: var(--card-bg);
      border-radius: 14px;
      padding: 1rem;
      transition: all 0.3s ease;
    }
    .card-custom:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.5);
    }
  </style>
</head>
<body>
  <div class="container py-4">

    <!-- NAV -->
    <header class="d-flex justify-content-between align-items-center mb-5 flex-wrap">
      <div class="d-flex align-items-center gap-3">
        <div class="logo rounded shadow" style="width:44px; height:44px; overflow:hidden;">
          <img src="assets/logo/1.png" alt="Museo Logo" class="img-fluid">
        </div>
        <div>
          <div class="fw-bold fs-5">Museo's Artist</div>
        </div>
      </div>
      <nav class="d-flex gap-2">
        <a class="btn btn-danger fw-bold" href="index.php">Sign in</a>
      </nav>
    </header>

    <!-- HERO -->
    <section class="hero row g-4 align-items-center mb-4">
      <div class="col-md-6 text-md-start text-center hero-left">
        <h1 class="mb-3">Your music. Your story.</h1>
        <p class="text-muted mb-3">Manage your profile, view your listeners, and reach fans.</p>
        <div class="d-flex justify-content-md-start justify-content-center gap-2 mb-3">
          <button class="btn btn-danger fw-bold">Get Started</button>
          <button class="btn btn-outline-light fw-bold">Learn more</button>
        </div>
        <div class="d-flex gap-3 flex-wrap">
          <div class="bg-dark p-2 rounded text-center" style="min-width:90px;">
            <strong>1.2M</strong><br><small class="text-muted">Monthly listeners</small>
          </div>
          <div class="bg-dark p-2 rounded text-center" style="min-width:90px;">
            <strong>24</strong><br><small class="text-muted">Releases</small>
          </div>
        </div>
      </div>

      <aside class="col-md-6 d-flex justify-content-center">
        <div class="artist-card">
          <div class="artist-photo">Artist</div>
          <div class="fw-bold">Artist Name</div>
          <div class="text-muted small">Indie · Electronic</div>
        </div>
      </aside>
    </section>

    <!-- Overview -->
    <section>
      <h2 class="mb-3">Overview</h2>
      <div class="row g-3">
        <div class="col-md-4"><div class="card-custom h-100"><h5>Audience</h5><p>Listener locations, growth and trends at a glance.</p></div></div>
        <div class="col-md-4"><div class="card-custom h-100"><h5>Releases</h5><p>All your tracks and album performance in one place.</p></div></div>
        <div class="col-md-4"><div class="card-custom h-100"><h5>Merch</h5><p>Sell merch directly to fans using third-party integrations.</p></div></div>
        <div class="col-md-4"><div class="card-custom h-100"><h5>Profile</h5><p>Update bio, links, and images for your artist profile.</p></div></div>
        <div class="col-md-4"><div class="card-custom h-100"><h5>Team</h5><p>Invite collaborators and manage permissions.</p></div></div>
        <div class="col-md-4"><div class="card-custom h-100"><h5>Help</h5><p>Get support, guides, and best practices.</p></div></div>
      </div>
    </section>

  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
