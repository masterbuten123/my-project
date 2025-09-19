<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once('../config/dbcon.php');
require_once('functions/myfunctions.php');

$artistId = $_SESSION['auth_user']['account_id'];
$notifData = getArtistNotifications($con, $artistId);
$notifications = $notifData['notifications'];
$unreadCount = $notifData['unread_count'];
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Museo | Artist</a>
        <div class="d-flex align-items-center">
            <div class="dropdown me-3">
                <a class="btn btn-outline-light btn-sm position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications" style="cursor:pointer;">
                    <i class="bi bi-bell"></i>
                    <?php if($unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $unreadCount ?>
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 250px;">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <?php if(!empty($notifications)): ?>
                        <?php foreach($notifications as $notif): ?>
                            <li>
                                <a class="dropdown-item <?= $notif['is_read'] == '0' ? 'fw-bold' : '' ?>" href="#">
                                    <?= $notif['message'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><span class="dropdown-item text-muted">No notifications</span></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="all_notifications.php">See all</a></li>
                </ul>
            </div>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
