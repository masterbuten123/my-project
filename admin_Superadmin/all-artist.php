<?php
session_start();
include('include/header.php');
include('../config/dbcon.php');
include('function/myfunctions.php');

// ✅ Redirect if not logged in
if (!isset($_SESSION['auth_user']['account_id'])) {
    echo "<script>alert('You need to login first'); window.location.href='index.php';</script>";
    exit();
}

// Get sorting option
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// Fetch artists using the function
$artists = getAllArtists($sort);
?>
<div class="container my-5">
  <h2 class="text-center mb-4">🎤 Artists List</h2>

  <!-- Sort Dropdown -->
  <div class="d-flex justify-content-end mb-3">
    <form method="get">
      <select name="sort" onchange="this.form.submit()" class="form-select w-auto">
        <option value="date_desc" <?= $sort == 'date_desc' ? 'selected' : '' ?>>Newest</option>
        <option value="date_asc" <?= $sort == 'date_asc' ? 'selected' : '' ?>>Oldest</option>
        <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Name (A–Z)</option>
        <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>Name (Z–A)</option>
      </select>
    </form>
  </div>

  <!-- Artist Table -->
  <div class="table-responsive shadow rounded">
    <table class="table table-hover align-middle text-center mb-0">
      <thead class="table-dark">
        <tr>
          <th>Profile</th>
          <th>Details</th>
          <th>Subscription</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($artists as $row): ?>
          <tr>
            <!-- Profile Picture -->
            <td>
              <img src="<?= $row['image'] ? $row['image'] : 'https://via.placeholder.com/80?text=Artist'; ?>"
     alt="Profile"
     class="rounded-circle shadow-sm"
     width="80" height="80">
            </td>

            <!-- Artist Details -->
            <td class="text-start">
              <strong><?= htmlspecialchars($row['name']); ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars($row['email']); ?></small><br>
              <small><?= htmlspecialchars($row['phone'] ?? 'N/A'); ?></small><br>
              <span class="fw-bold text-success">₱<?= number_format($row['price_per_hour'] ?? 0, 2); ?>/hr</span>
            </td>

            <!-- Subscription Info -->
            <td>
              <?php if (!empty($row['latest_subscription'])): ?>
                <span class="fw-bold"><?= htmlspecialchars($row['latest_subscription']['plan_name']); ?></span><br>
                <small>(₱<?= number_format($row['latest_subscription']['plan_price'], 2); ?>)</small><br>
                <span class="badge <?= $row['latest_subscription']['subscription_status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                  <?= ucfirst($row['latest_subscription']['subscription_status']); ?>
                </span>
              <?php else: ?>
                <span class="text-muted">No Plan</span><br>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </td>

            <!-- Account Status -->
            <td>
              <span class="badge <?= $row['account_status'] == 'active' ? 'bg-success' : ($row['account_status'] == 'banned' ? 'bg-danger' : 'bg-secondary'); ?>">
                <?= ucfirst($row['account_status']); ?>
              </span>
            </td>

            <!-- Actions -->
            <td>
              <a href="#"
                 class="btn btn-sm btn-info artist-view"
                 data-bs-toggle="modal"
                 data-bs-target="#artistModal"
                 data-name="<?= htmlspecialchars($row['name']); ?>"
                 data-email="<?= htmlspecialchars($row['email']); ?>"
                 data-phone="<?= htmlspecialchars($row['phone']); ?>"
                 data-gender="<?= htmlspecialchars($row['gender']); ?>"
                 data-genre="<?= htmlspecialchars($row['genre']); ?>"
                 data-price="<?= htmlspecialchars($row['price_per_hour']); ?>"
                 data-image="<?= htmlspecialchars($row['image']); ?>"
                 data-bio="<?= htmlspecialchars($row['bio']); ?>"
                 data-plan="<?= !empty($row['latest_subscription']) ? htmlspecialchars($row['latest_subscription']['plan_name']) : 'No Plan'; ?>"
                 data-planprice="<?= !empty($row['latest_subscription']) ? htmlspecialchars($row['latest_subscription']['plan_price']) : '0.00'; ?>"
                 data-substatus="<?= !empty($row['latest_subscription']) ? htmlspecialchars($row['latest_subscription']['subscription_status']) : 'inactive'; ?>"
                 data-status="<?= htmlspecialchars($row['account_status']); ?>">
                <i class="bi bi-eye"></i> View
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Artist Modal -->
<div class="modal fade" id="artistModal" tabindex="-1" aria-labelledby="artistModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="artistModalLabel">🎶 Artist Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row align-items-center">
          <!-- Profile Image -->
          <div class="col-md-4 text-center">
            <img id="artistImage"
                 src="https://via.placeholder.com/200?text=Artist"
                 class="rounded-circle shadow mb-3"
                 width="200" height="200">
          </div>

          <!-- Details -->
          <div class="col-md-8">
            <h4 id="artistName" class="fw-bold"></h4>
            <p><strong>Email:</strong> <span id="artistEmail"></span></p>
            <p><strong>Phone:</strong> <span id="artistPhone"></span></p>
            <p><strong>Gender:</strong> <span id="artistGender"></span></p>
            <p><strong>Genre:</strong> <span id="artistGenre"></span></p>
            <p><strong>Price/hr:</strong> ₱<span id="artistPrice"></span></p>
            <p><strong>Plan:</strong> <span id="artistPlan"></span> (₱<span id="artistPlanPrice"></span>)</p>
            <p><strong>Subscription Status:</strong> <span id="artistSubStatus"></span></p>
            <p><strong>Account Status:</strong> <span id="artistStatus" class="badge"></span></p>
            <p><strong>Bio:</strong> <span id="artistBio"></span></p>
          </div>
        </div>

        <hr>

        <!-- Reports Section -->
        <div class="mt-3">
          <h5>🚩 Reports</h5>
          <div id="artistReports" class="d-flex flex-column gap-2">
            <span class="text-muted">No reports found</span>
          </div>
        </div>
        <hr>
        <!-- Resume Section -->
        <div class="mt-3">
          <h5>📄 Resume</h5>
          <p id="artistResume">
            <a href="#" target="_blank" class="btn btn-outline-primary btn-sm d-none" id="resumeLink">
              <i class="bi bi-file-earmark-text"></i> View Resume
            </a>
            <span class="text-muted" id="resumePlaceholder">No resume uploaded</span>
          </p>
        </div>

        <!-- Sample Tracks Section -->
        <div class="mt-3">
          <h5>🎵 Sample Tracks</h5>
          <div id="artistTracks" class="d-flex flex-column gap-2">
            <span class="text-muted">No sample tracks uploaded</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
// ✅ Modal Data Binding
document.addEventListener('DOMContentLoaded', function () {
    const artistLinks = document.querySelectorAll('.artist-view');

    artistLinks.forEach(link => {
        link.addEventListener('click', function () {
        document.getElementById('artistImage').src = this.dataset.image 
            ? '../uploads/profiles' + this.dataset.image 
            : 'https://via.placeholder.com/200';

            document.getElementById('artistName').textContent = this.dataset.name;
            document.getElementById('artistEmail').textContent = this.dataset.email;
            document.getElementById('artistPhone').textContent = this.dataset.phone || 'N/A';
            document.getElementById('artistGender').textContent = this.dataset.gender || 'N/A';
            document.getElementById('artistGenre').textContent = this.dataset.genre || 'N/A';
            document.getElementById('artistPrice').textContent = this.dataset.price || '0.00';
            document.getElementById('artistPlan').textContent = this.dataset.plan || 'No Plan';
            document.getElementById('artistPlanPrice').textContent = this.dataset.planprice || '0.00';
            document.getElementById('artistSubStatus').textContent = this.dataset.substatus || 'N/A';
            document.getElementById('artistBio').textContent = this.dataset.bio || 'No bio available';

            const statusBadge = document.getElementById('artistStatus');
            statusBadge.textContent = this.dataset.status;
            statusBadge.className = 'badge ' + 
                (this.dataset.status === 'active' ? 'bg-success' : 
                 this.dataset.status === 'banned' ? 'bg-danger' : 
                 'bg-secondary');
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
  // Get artistId from PHP (example: profile.php?artist_id=1)
  const artistId = "<?php echo $artist_id; ?>";

  fetch(`myfunction.php?action=get_reports&artist_id=${artistId}`)
    .then(res => res.json())
    .then(data => {
      const reportsContainer = document.getElementById('artistReports');
      reportsContainer.innerHTML = "";

      if (data.length === 0) {
        reportsContainer.innerHTML = `<span class="text-muted">No reports found</span>`;
      } else {
        data.forEach(rep => {
          const div = document.createElement('div');
          div.classList.add('p-2', 'border', 'rounded', 'bg-light');
          div.innerHTML = `<strong>${rep.reporter || 'Anonymous'}</strong> 
                           <small class="text-muted">(${rep.created_at})</small><br>
                           ${rep.reason}`;
          reportsContainer.appendChild(div);
        });
      }
    })
    .catch(err => {
      console.error("Error loading reports:", err);
    });
});
</script>

<?php include('include/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>