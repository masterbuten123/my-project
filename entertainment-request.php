<?php
session_start();
include('includes/navbar.php');
include('functions/myfunctions.php');
$swal_success = $_SESSION['success_message'] ?? null;
$swal_errors  = $_SESSION['errors'] ?? null;

// Clear them so they don't persist
unset($_SESSION['success_message'], $_SESSION['errors']);
// Display alerts if any
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success text-center mt-3">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']); // Clear after showing
}

if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
    echo '<div class="alert alert-danger mt-3"><ul>';
    foreach ($_SESSION['errors'] as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul></div>';
    unset($_SESSION['errors']); // Clear after showing
}
$user_id = $_SESSION['user_id'] ?? NULL;
// Get current datetime for min attribute
$now = date('Y-m-d\TH:i');

// Validate artist_id from URL
$artist_id = isset($_GET['account_id']) ? intval($_GET['account_id']) : 0;
if ($artist_id <= 0) {
    die("<div class='alert alert-danger text-center mt-5'>Invalid artist ID. Please go back and select a valid artist.</div>");
}

// Fetch artist details
$artist = getArtistByIdd($artist_id);
if (!$artist) {
    die("<div class='alert alert-danger text-center mt-5'>Artist not found. Please go back and select a valid artist.</div>");
}
$artist_name = $artist['name'];

?>
<style>
    #attendees,#budget::-webkit-outer-spin-button, 
    #attendees,#budget::-webkit-inner-spin-button 
    { -webkit-appearance: none; margin: 0; }
</style>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Entertainment Request</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-danger fw-bold">Entertainment Request Form</h2>
  <div class="row">
    <!-- FORM -->
    <div class="col-lg-8">
       <form id="entRequestForm" action="form_handler.php" method="post" class="needs-validation" novalidate>
        <div class="row g-3">
        <input type="hidden" name="form_type" value="entertainment_request">
        <input type="hidden" name="artist_id" value="<?= $artist_id ?>">
          <!-- Requester Name -->
          <div class="col-md-6">
            <label for="requesterName" class="form-label">Name</label>
            <input type="text" class="form-control" id="requesterName" name="name" required>
            <div class="invalid-feedback">Please provide your name.</div>
          </div>

          <!-- Email -->
          <div class="col-md-6">
            <label for="requesterEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="requesterEmail" name="email" required>
            <div class="invalid-feedback">Please provide a valid email.</div>
          </div>

          <!-- Phone -->
          <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="tel" class="form-control" id="phone" name="phone">
          </div>

          <!-- Event Type -->
          <div class="col-md-6">
            <label for="eventType" class="form-label">Event Type</label>
            <select id="eventType" name="event_type" class="form-select" required>
              <option value="">Choose...</option>
              <option>Concert / Live Music</option>
              <option>Movie Night</option>
              <option>Game Night / Tournament</option>
              <option>Comedy / Open Mic</option>
              <option>Workshop / Talk</option>
              <option value="Other">Other</option>
            </select>
            <div class="invalid-feedback">Please choose an event type.</div>
            <input type="text" id="eventTypeOther" name="event_type_other" class="form-control mt-2 d-none" placeholder="Specify other event type">
          </div>

            <!-- Booking Date & Time -->
            <div class="col-md-6">
            <label for="booking_start" class="form-label">Preferred Date & Time</label>
            <input 
                type="datetime-local" 
                id="booking_start" 
                name="booking_start" 
                class="form-control" 
                min="<?= $now ?>" 
                required
            >
            </div>

            <!-- Duration in hours -->
            <div class="col-md-6">
            <label for="duration_hours" class="form-label">Duration (hours)</label>
            <input 
                type="number" 
                id="duration_hours" 
                name="duration_hours" 
                class="form-control" 
                min="1" 
                step="0.5" 
                required
            >
            </div>

            <!-- Optional: show expected end time -->
            <div class="col-md-6 mt-2">
            <label class="form-label">Expected End Time</label>
            <input type="text" id="booking_end" class="form-control" readonly>
            </div>

          <!-- Venue -->
          <div class="col-md-6">
            <label for="venue" class="form-label"> Location</label>
            <input type="text" id="venue" name="venue" class="form-control">
          </div>

          <!-- Attendees -->
          <div class="col-md-4">
            <label for="attendees" class="form-label">Estimated # of Attendees</label>
            <input type="number" id="attendees" name="attendees" class="form-control" min="1">
          </div>

          <!-- Budget -->
          <div class="col-md-4">
            <label for="budget" class="form-label">Approx. Budget (optional)</label>
            <input type="number" id="budget" name="budget" class="form-control" min="0" step="0.01" placeholder="PHP">
          </div>

          <!-- Additional Notes -->
          <div class="col-12">
            <label for="additional_notes" class="form-label">Additional Notes</label>
            <textarea id="additional_notes" name="additional_notes" rows="4" class="form-control" placeholder="Additional Notes"></textarea>
          </div>

          <!-- Agreement Checkbox -->
           
          <div class="col-12">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="agreeCheck" name="agree" required>
              <label class="form-check-label" for="agreeCheck">
                <label for="terms">I have read and agree to the <a href="terms.html" target="_blank">Terms & Conditions
                </a> and provide legal consent for the Artist's performance.</label>
                I confirm that the information provided is accurate.
              </label>
              <div class="invalid-feedback">You must confirm before submitting.</div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="col-12 d-flex justify-content-end">
            <button type="submit" name="submit" class="btn btn-primary">Submit Request</button>
          </div>

        </div>
      </form>
    </div>

    <!-- Notes / Info Section -->
    <div class="col-lg-4">
      <div class="p-4 bg-light border rounded shadow-sm sticky-top" style="top: 90px; z-index: 1020;">
        <h5 class="fw-bold text-danger mb-3">
          <i class="fas fa-star me-2"></i> NEW TO HIRING TALENT?
        </h5>
        <p class="mb-4">READ THIS FIRST!</p>

        <div class="mb-3">
          <i class="fas fa-info-circle text-primary me-2"></i>
          <strong>OUR SERVICE</strong>
          <p class="small text-muted mb-0">
            Booking Entertainment is a celebrity booking agency for paid events, 
            we do not handle media requests, interviews or give out any contact information.
          </p>
        </div>

        <div>
          <i class="fas fa-exclamation-triangle text-warning me-2"></i>
          <strong>REMEMBER</strong>
          <p class="small text-muted mb-0">
            Celebrities do not donate their time and always will charge a fee to do any event, project, 
            venue or celebrity appearance. Celebrities will always need the detailed information we ask 
            for on this request form.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const bookingInput = document.getElementById('booking_start');
const durationInput = document.getElementById('duration_hours');
const endTimeInput = document.getElementById('booking_end');

function updateEndTime() {
    const startValue = bookingInput.value; // e.g., "2025-09-20T14:30"
    const duration = parseFloat(durationInput.value);

    if (!startValue || isNaN(duration)) {
        endTimeInput.value = '';
        return;
    }

    const startDate = new Date(startValue);
    const endDate = new Date(startDate.getTime() + duration * 60 * 60 * 1000);

    const yyyy = endDate.getFullYear();
    const mm = String(endDate.getMonth() + 1).padStart(2, '0');
    const dd = String(endDate.getDate()).padStart(2, '0');
    const hh = String(endDate.getHours()).padStart(2, '0');
    const min = String(endDate.getMinutes()).padStart(2, '0');

    endTimeInput.value = `${yyyy}-${mm}-${dd} ${hh}:${min}`;
}

// Listen for changes
bookingInput.addEventListener('change', updateEndTime);
durationInput.addEventListener('input', updateEndTime);


  // Bootstrap validation
  (function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
  })()

  // Show/hide "Other" event type input
  document.getElementById('eventType').addEventListener('change', function () {
    var other = document.getElementById('eventTypeOther')
    if (this.value === 'Other') {
      other.classList.remove('d-none')
      other.required = true
    } else {
      other.classList.add('d-none')
      other.required = false
      other.value = ''
    }
  })
</script>



<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php if ($swal_success): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: "<?= addslashes($swal_success) ?>",
        confirmButtonColor: '#0d6efd'
    });
    <?php endif; ?>

    <?php if ($swal_errors && count($swal_errors) > 0): ?>
    Swal.fire({
        icon: 'error',
        title: 'Oops!',
        html: "<?= implode('<br>', array_map('addslashes', $swal_errors)) ?>",
        confirmButtonColor: '#d33'
    });
    <?php endif; ?>
});
</script>
</body>
</html>
<?php include('includes/footer.php') ?>
