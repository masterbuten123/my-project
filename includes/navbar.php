<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = basename($_SERVER['SCRIPT_NAME']); 

$account_id = null;
$userName   = null;
$role       = null;

if (!empty($_SESSION['auth_user']['account_id'])) {
    $account_id = $_SESSION['auth_user']['account_id'];
    $userName   = $_SESSION['auth_user']['username'] ?? 'User';
    $role       = $_SESSION['auth_user']['role'] ?? null;
} elseif (!empty($_SESSION['new_user_id'])) {
    $account_id = $_SESSION['new_user_id'];
    $userName   = $_SESSION['new_user_name'] ?? 'User';
    $role       = $_SESSION['role'] ?? null;
}
?>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* General navbar styles */
    .navbar {
        background-color: #1a1a1a !important;
        font-weight: 500;
        padding: 10px 0;
    }

    .navbar-brand {
        font-size: 1.5rem;
        color: #fff !important;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
    }

    .navbar-brand span {
        color: #ffffffff;

    }

    /* Navlink basic styling */
    .nav-link {
        position: relative;
        color: #fff !important;
        margin-left: 15px;
        transition: all 0.4s ease-in-out;
        text-transform: uppercase;
        font-weight: 600;
    }

    /* Underline effect for nav links */
    .nav-link::after {
        content: "";
        position: absolute;
        width: 0%;
        height: 3px;
        bottom: 0;
        left: 0;
        background-color: #e50914;
        transition: 0.3s;
    }

    /* Hover effect */
    .nav-link:hover::after {
        width: 100%;
    }

    /* Hover animations */
    .nav-link:hover {
        color: #e50914 !important;
        text-shadow: 0 0 10px #e50914, 0 0 20px #e50914;
        transform: scale(1.1);
        letter-spacing: 2px;
        animation: shake 0.5s ease-in-out infinite alternate;
    }

    /* Rocking effect for links */
    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
        100% { transform: translateX(5px); }
    }

    /* Active link effect */
    .nav-link.active {
        color: #e50914 !important;
        font-weight: 700;
        text-shadow: 0 0 10px #e50914, 0 0 20px #e50914;
    }

    /* Toggler and dropdown styles */
    .navbar-toggler {
        border: 2px solid #e50914;
    }

    .navbar-toggler:focus {
        outline: none;
        box-shadow: 0 0 0 2px #e50914;
    }

    /* Dropdown styles */
    .dropdown-menu {
        background-color: #333;
        border-radius: 8px;
        border: 2px solid #e50914;
    }

    .dropdown-item {
        color: #fff;
        font-size: 1.1rem;
        transition: all 0.3s;
    }

    .dropdown-item:hover {
        background-color: #e50914;
        color: #fff;
        font-weight: bold;
    }

    .dropdown-divider {
        border-color: #e50914;
    }
</style>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow">
    <div class="container">
        <img src="/assets/logo/1.png" alt="Logo" height="30" width="30">
        <a class="navbar-brand ms-2" href="/index1.php"><span>Museo</span></a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" 
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">


                <li class="nav-item">
                    <a class="nav-link <?= $page === "index1.php" ? 'active' : ''; ?>" href="/index1.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $page === "merch.php" ? 'active' : ''; ?>" href="/merch.php" target="_blank">Merchandise</a>
                </li>

                <?php if ($account_id): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($userName); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/my-profile.php" target="_blank">My Profile</a></li>
                            <?php if (in_array($role, ['artist','admin'])): ?>
                                <li>
                                    <a class="dropdown-item" 
                                       href="<?= $role === 'artist' ? '/admin-artist/index.php' : '/admin_Superadmin/index.php'; ?>" 
                                       target="_blank">
                                        Dashboard
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/settings.php">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === "register.php" ? 'active' : ''; ?>" href="/index.php">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === "login.php" ? 'active' : ''; ?>" href="/index.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>


