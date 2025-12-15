<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/db.php';

startSession();
$isLoggedIn     = isLoggedIn();
$currentUser    = getCurrentUserName();
$isStudent      = hasRole('Student');
$isModerator    = hasRole('Moderator');
$isAdmin        = hasRole('Admin');

// Count unread notifications
$unreadNotifications = 0;
if ($isLoggedIn && isset($_SESSION['user_id'])) {
    try {
        $notifSql = "SELECT COUNT(*) as count FROM Notification WHERE UserID = :user_id AND IsRead = FALSE;";
        $notifStmt = $pdo->prepare($notifSql);
        $notifStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $notifStmt->execute();
        $unreadNotifications = (int)$notifStmt->fetch()['count'];
    } catch (PDOException $e) {
        // Silently fail - notification badge is not critical
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campus Second-Hand Marketplace</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">Campus Market</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#categories">Categories</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <?php if ($isLoggedIn): ?>
                        <a class="btn btn-outline-light btn-sm" href="/pages/listings.php">Browse</a>
                        <?php if ($isStudent): ?>
                            <a class="btn btn-warning btn-sm" href="/pages/add-listing.php">+ Listing</a>
                        <?php endif; ?>
                        <?php if ($isModerator || $isAdmin): ?>
                            <a class="btn btn-info btn-sm" href="/pages/moderator/complaints.php">Complaints</a>
                            <a class="btn btn-info btn-sm" href="/pages/moderator/manage-listings.php">Manage</a>
                        <?php endif; ?>
                        <?php if ($isAdmin): ?>
                            <a class="btn btn-danger btn-sm" href="/pages/admin/dashboard.php">Admin</a>
                        <?php endif; ?>
                        
                        <!-- Notifications -->
                        <div class="position-relative">
                            <a class="btn btn-outline-light btn-sm" href="/pages/notifications.php">
                                🔔
                                <?php if ($unreadNotifications > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        <?php echo $unreadNotifications > 99 ? '99+' : $unreadNotifications; ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo cleanInput($currentUser ?? 'User'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/pages/profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="/pages/my-bids.php">My Bids</a></li>
                                <li><a class="dropdown-item" href="/pages/messages.php">Messages</a></li>
                                <li><a class="dropdown-item" href="/pages/saved-items.php">Saved Items</a></li>
                                <li><a class="dropdown-item" href="/pages/notifications.php">Notifications</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/pages/create-complaint.php">Report Issue</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a class="btn btn-outline-light btn-sm" href="/pages/login.php">Login</a>
                        <a class="btn btn-warning btn-sm" href="/pages/register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

