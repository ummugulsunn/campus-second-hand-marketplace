<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();
requireLogin();

// Only Admins can access
if (!hasRole('Admin')) {
    header('Location: /index.php');
    exit;
}

$stats = [];
$errorMessage = '';

try {
    // Count total users
    $userCountSql = "SELECT COUNT(*) as total FROM User;";
    $stats['total_users'] = $pdo->query($userCountSql)->fetch()['total'];

    // Count users by role
    $roleCountSql = <<<SQL
    SELECT R.RoleName, COUNT(U.UserID) as count
    FROM Role R
    LEFT JOIN User U ON U.RoleID = R.RoleID
    GROUP BY R.RoleID, R.RoleName
    ORDER BY R.RoleID;
    SQL;
    $stats['users_by_role'] = $pdo->query($roleCountSql)->fetchAll();

    // Count total listings
    $listingCountSql = "SELECT COUNT(*) as total FROM Product_Listing;";
    $stats['total_listings'] = $pdo->query($listingCountSql)->fetch()['total'];

    // Count listings by status
    $listingStatusSql = <<<SQL
    SELECT Status, COUNT(*) as count
    FROM Product_Listing
    GROUP BY Status
    ORDER BY FIELD(Status, 'Active', 'Pending', 'Sold', 'Removed');
    SQL;
    $stats['listings_by_status'] = $pdo->query($listingStatusSql)->fetchAll();

    // Count total bids
    $bidCountSql = "SELECT COUNT(*) as total FROM Bid;";
    $stats['total_bids'] = $pdo->query($bidCountSql)->fetch()['total'];

    // Count total messages
    $messageCountSql = "SELECT COUNT(*) as total FROM Message;";
    $stats['total_messages'] = $pdo->query($messageCountSql)->fetch()['total'];

    // Count total reviews
    $reviewCountSql = "SELECT COUNT(*) as total FROM Review;";
    $stats['total_reviews'] = $pdo->query($reviewCountSql)->fetch()['total'];

    // Average rating
    $avgRatingSql = "SELECT AVG(Rating) as avg_rating FROM Review;";
    $stats['avg_rating'] = round((float)$pdo->query($avgRatingSql)->fetch()['avg_rating'], 2);

    // Count complaints by status
    $complaintCountSql = <<<SQL
    SELECT Status, COUNT(*) as count
    FROM Complaint_Report
    GROUP BY Status
    ORDER BY FIELD(Status, 'Pending', 'Reviewed', 'Resolved');
    SQL;
    $stats['complaints_by_status'] = $pdo->query($complaintCountSql)->fetchAll();

    // Top categories by listing count
    $topCategoriesSql = <<<SQL
    SELECT C.CategoryName, COUNT(PL.ListingID) as count
    FROM Category C
    LEFT JOIN Product_Listing PL ON PL.CategoryID = C.CategoryID
    GROUP BY C.CategoryID, C.CategoryName
    ORDER BY count DESC;
    SQL;
    $stats['top_categories'] = $pdo->query($topCategoriesSql)->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load statistics: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="container py-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
        </ol>
    </nav>
    
    <h2 class="mb-4">Admin Dashboard</h2>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?php echo $stats['total_users'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo $stats['total_listings'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Total Listings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo $stats['total_bids'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Total Bids</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo $stats['total_messages'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Total Messages</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Users by Role -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Users by Role</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach ($stats['users_by_role'] ?? [] as $role): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($role['RoleName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-end"><strong><?php echo $role['count']; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Listings by Status -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Listings by Status</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach ($stats['listings_by_status'] ?? [] as $status): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($status['Status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-end"><strong><?php echo $status['count']; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Categories -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Listings by Category</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach ($stats['top_categories'] ?? [] as $cat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cat['CategoryName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-end"><strong><?php echo $cat['count']; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Complaints & Reviews -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Complaints by Status</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php if (!empty($stats['complaints_by_status'])): ?>
                                <?php foreach ($stats['complaints_by_status'] as $complaint): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($complaint['Status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end"><strong><?php echo $complaint['count']; ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-muted">No complaints</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-warning">⭐ <?php echo $stats['avg_rating'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Average Rating (<?php echo $stats['total_reviews'] ?? 0; ?> reviews)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="mt-4">
        <h4>Quick Actions</h4>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/pages/admin/users.php" class="btn btn-primary">Manage Users</a>
            <a href="/pages/admin/categories.php" class="btn btn-success">Manage Categories</a>
            <a href="/pages/moderator/complaints.php" class="btn btn-warning">View Complaints</a>
            <a href="/pages/moderator/manage-listings.php" class="btn btn-info">Manage Listings</a>
            <a href="/pages/listings.php" class="btn btn-outline-primary">View All Listings</a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

