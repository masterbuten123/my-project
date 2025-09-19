<?php
include('include/header.php');
include('../config/dbcon.php');
include('function/myfunctions.php');  // <-- include before calling function

$statuses = ['pending' => 'Pending', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'completed' => 'Completed'];
?>

<div class="container py-5">
    <h2>All Bookings</h2>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="bookingTabs" role="tablist">
        <?php
        $active = 'active';
        foreach ($statuses as $key => $label) {
            echo '<li class="nav-item" role="presentation">
                    <button class="nav-link '. $active .'" id="'. $key .'-tab" data-bs-toggle="tab" data-bs-target="#'. $key .'" type="button" role="tab" aria-controls="'. $key .'" aria-selected="'. ($active === 'active' ? 'true' : 'false') .'">'. $label .'</button>
                  </li>';
            $active = '';
        }
        ?>
    </ul>

    <div class="tab-content" id="bookingTabsContent">
        <?php
        $active = 'show active';
        foreach ($statuses as $key => $label) {
            echo '<div class="tab-pane fade '. $active .'" id="'. $key .'" role="tabpanel" aria-labelledby="'. $key .'-tab">';
            renderBookingsTable($con, $key);  // render bookings by status
            echo '</div>';
            $active = '';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
