<?php
session_start();
include('includes/header.php');
include('../config/dbcon.php');


$artistId = $_SESSION['auth_user']['account_id']; 
$bookings = getBookingsByArtist($artistId);

// Handle Accept/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['accept']) || isset($_POST['reject']))) {
    $bookingId = intval($_POST['booking_id']); 
    $newStatus = isset($_POST['accept']) ? 'accepted' : 'rejected';

    if (updateBookingStatus($bookingId, $newStatus, $artistId)) {
        $_SESSION['message'] = "Booking has been $newStatus successfully.";
        header("Location: " . $_SERVER['PHP_SELF']); 
        exit();
    } else {
        $_SESSION['message'] = "Failed to update booking status.";
    }
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Bookings Dashboard</h2>

    <!-- Alerts -->
    <?php if(isset($_SESSION['message'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="bookingTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all">All</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pending">Pending</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#accepted">Accepted</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rejected">Rejected</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#completed">Completed</button></li>
    </ul>

    <div class="table-responsive tab-content">
        <?php
        $tabs = ['all', 'pending', 'accepted', 'rejected', 'completed'];
        foreach ($tabs as $tab):
        ?>
        <div class="tab-pane fade <?= $tab==='all' ? 'show active' : ''; ?>" id="<?= $tab; ?>">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Event Date</th>
                        <th>Event Type</th>
                        <th>Duration</th>
                        <th>Attendees</th>
                        <th>Payment Status</th>
                        <th>Location</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Contact No</th>
                        <th>Optional Extras</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $hasBookings = false;
                mysqli_data_seek($bookings, 0); // Reset pointer for each tab
                while ($row = mysqli_fetch_assoc($bookings)):
                    $status = strtolower(trim($row['status'])); 
                    if($tab !== 'all' && $status !== $tab) continue;
                    $hasBookings = true;

                    $eventDate = date('M d, Y H:i', strtotime($row['booking_start']));
                    $badgeClass = ($status === 'accepted') ? 'success' 
                                : (($status === 'rejected') ? 'danger' 
                                : (($status === 'completed') ? 'info' : 'warning text-dark'));
                    $rowClass = ($status === 'pending') ? 'table-warning' : '';
                    $paymentBadge = (strtolower(trim($row['payment_status'])) === 'paid') ? 'success' : 'warning text-dark';
                ?>
                <tr class="<?= $rowClass; ?>">
                    <td><?= htmlspecialchars($eventDate); ?></td>
                    <td><?= htmlspecialchars($row['event_type']); ?></td>
                    <td><?= htmlspecialchars($row['duration_hours']); ?> hrs</td>
                    <td><?= htmlspecialchars($row['attendees']); ?></td>
                    <td><span class="badge bg-<?= $paymentBadge; ?>"><?= ucfirst($row['payment_status']); ?></span></td>
                    <td><?= htmlspecialchars($row['venue']); ?></td>
                    <td><?= htmlspecialchars($row['additional_notes']); ?></td>
                    <td><span class="badge bg-<?= $badgeClass; ?>"><?= ucfirst($status); ?></span></td>
                    <td><?= htmlspecialchars($row['phone']); ?></td>
                    <td><?= htmlspecialchars($row['extras'] ?? '-'); ?></td>
                    <td>
                        <?php if ($status === 'pending'): ?>
                            <button type="button" class="btn btn-success btn-sm show-terms-btn" data-booking-id="<?= $row['booking_id']; ?>">Accept</button>
                            <form action="" method="POST" class="d-inline">
                                <input type="hidden" name="booking_id" value="<?= $row['booking_id']; ?>">
                                <button type="submit" name="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        <?php elseif ($status === 'accepted'): ?>
                            <button type="button" class="btn btn-primary btn-sm chat-client-btn" data-user-id="<?= $row['user_id']; ?>">Chat</button>
                        <?php else: ?>
                            <em>No actions available</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (!$hasBookings): ?>
                <tr>
                    <td colspan="11" class="text-center">No bookings found.</td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Terms and Conditions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>By accepting this booking, you agree to the following:</p>
        <ul>
          <li>Appear at the agreed date/time.</li>
          <li>Failure may result in penalties.</li>
          <li>Payment & cancellation policies apply.</li>
          <li>Coordinate with the client in advance.</li>
        </ul>
        <p>Do you agree to these terms?</p>
      </div>
      <div class="modal-footer">
        <form id="acceptForm" method="POST">
          <input type="hidden" name="booking_id" id="bookingIdInput">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="accept" class="btn btn-success">I Agree & Accept</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include('artist-chat-widget.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.show-terms-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const bookingId = btn.getAttribute('data-booking-id');
        document.getElementById('bookingIdInput').value = bookingId;
        const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
        termsModal.show();
    });
});

document.querySelectorAll('.chat-client-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const userId = btn.getAttribute('data-user-id');
        openChatWithClient(userId); // Implement this in your chat widget
    });
});
</script>
