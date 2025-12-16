<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();
requireLogin();

// Only Admins can access
if (!hasRole('Admin')) {
    header('Location: /campus-marketplace/index.php');
    exit;
}

$userId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;
$userStats = [];
$recentActivity = [];
$errorMessage = '';

if ($userId <= 0) {
    header('Location: /campus-marketplace/pages/admin/users.php');
    exit;
}

try {
    // Fetch user details with role
    $userSql = <<<SQL
    SELECT 
        U.UserID,
        U.Name,
        U.Email,
        U.Phone,
        R.RoleID,
        R.RoleName
    FROM User U
    INNER JOIN Role R ON R.RoleID = U.RoleID
    WHERE U.UserID = :user_id
    LIMIT 1;
    SQL;
    
    $userStmt = $pdo->prepare($userSql);
    $userStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $userStmt->execute();
    $user = $userStmt->fetch();
    
    if (!$user) {
        header('Location: /campus-marketplace/pages/admin/users.php');
        exit;
    }
    
    // Count user's listings
    $listingCountSql = "SELECT COUNT(*) as total FROM Product_Listing WHERE SellerID = :user_id;";
    $listingCountStmt = $pdo->prepare($listingCountSql);
    $listingCountStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $listingCountStmt->execute();
    $userStats['total_listings'] = (int)$listingCountStmt->fetch()['total'];
    
    // Count user's bids
    $bidCountSql = "SELECT COUNT(*) as total FROM Bid WHERE BuyerID = :user_id;";
    $bidCountStmt = $pdo->prepare($bidCountSql);
    $bidCountStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $bidCountStmt->execute();
    $userStats['total_bids'] = (int)$bidCountStmt->fetch()['total'];
    
    // Count messages sent
    $messageSentSql = "SELECT COUNT(*) as total FROM Message WHERE SenderID = :user_id;";
    $messageSentStmt = $pdo->prepare($messageSentSql);
    $messageSentStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $messageSentStmt->execute();
    $userStats['messages_sent'] = (int)$messageSentStmt->fetch()['total'];
    
    // Count messages received
    $messageReceivedSql = "SELECT COUNT(*) as total FROM Message WHERE ReceiverID = :user_id;";
    $messageReceivedStmt = $pdo->prepare($messageReceivedSql);
    $messageReceivedStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $messageReceivedStmt->execute();
    $userStats['messages_received'] = (int)$messageReceivedStmt->fetch()['total'];
    
    // Count reviews received and average rating
    $reviewStatsSql = <<<SQL
    SELECT 
        COUNT(*) as total,
        AVG(Rating) as avg_rating
    FROM Review
    WHERE RevieweeID = :user_id;
    SQL;
    $reviewStatsStmt = $pdo->prepare($reviewStatsSql);
    $reviewStatsStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $reviewStatsStmt->execute();
    $reviewData = $reviewStatsStmt->fetch();
    $userStats['total_reviews'] = (int)$reviewData['total'];
    $userStats['avg_rating'] = $reviewData['avg_rating'] ? round((float)$reviewData['avg_rating'], 1) : 0;
    
    // Count complaints filed
    $complaintCountSql = "SELECT COUNT(*) as total FROM Complaint_Report WHERE ReporterID = :user_id;";
    $complaintCountStmt = $pdo->prepare($complaintCountSql);
    $complaintCountStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $complaintCountStmt->execute();
    $userStats['complaints_filed'] = (int)$complaintCountStmt->fetch()['total'];
    
    // Count saved items
    $savedCountSql = "SELECT COUNT(*) as total FROM Saved_Item WHERE UserID = :user_id;";
    $savedCountStmt = $pdo->prepare($savedCountSql);
    $savedCountStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $savedCountStmt->execute();
    $userStats['saved_items'] = (int)$savedCountStmt->fetch()['total'];
    
    // Recent listings
    $recentListingsSql = <<<SQL
    SELECT 
        ListingID,
        Title,
        Price,
        Status
    FROM Product_Listing
    WHERE SellerID = :user_id
    ORDER BY ListingID DESC
    LIMIT 5;
    SQL;
    $recentListingsStmt = $pdo->prepare($recentListingsSql);
    $recentListingsStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $recentListingsStmt->execute();
    $userStats['recent_listings'] = $recentListingsStmt->fetchAll();
    
    // Recent bids
    $recentBidsSql = <<<SQL
    SELECT 
        B.BidID,
        B.BidAmount,
        B.BidDate,
        PL.Title as ListingTitle
    FROM Bid B
    INNER JOIN Product_Listing PL ON PL.ListingID = B.ListingID
    WHERE B.BuyerID = :user_id
    ORDER BY B.BidDate DESC
    LIMIT 5;
    SQL;
    $recentBidsStmt = $pdo->prepare($recentBidsSql);
    $recentBidsStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $recentBidsStmt->execute();
    $userStats['recent_bids'] = $recentBidsStmt->fetchAll();
    
    // Recent reviews received
    $recentReviewsSql = <<<SQL
    SELECT 
        R.Rating,
        R.Comment,
        R.ReviewDate,
        U.Name as ReviewerName
    FROM Review R
    INNER JOIN User U ON U.UserID = R.ReviewerID
    WHERE R.RevieweeID = :user_id
    ORDER BY R.ReviewDate DESC
    LIMIT 5;
    SQL;
    $recentReviewsStmt = $pdo->prepare($recentReviewsSql);
    $recentReviewsStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $recentReviewsStmt->execute();
    $userStats['recent_reviews'] = $recentReviewsStmt->fetchAll();
    
} catch (PDOException $e) {
    $errorMessage = 'Failed to load user details: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Details</h2>
        <a href="/campus-marketplace/pages/admin/users.php" class="btn btn-outline-secondary">← Back to Users</a>
    </div>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if ($user): ?>
        <!-- User Info Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h3><?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-muted mb-1">
                            <strong>Email:</strong> <?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p class="text-muted mb-1">
                            <strong>Phone:</strong> <?php echo $user['Phone'] ? htmlspecialchars($user['Phone'], ENT_QUOTES, 'UTF-8') : 'N/A'; ?>
                        </p>
                        <p class="text-muted mb-0">
                            <strong>User ID:</strong> <?php echo $user['UserID']; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge bg-<?php 
                            echo match($user['RoleName']) {
                                'Admin' => 'danger',
                                'Moderator' => 'warning',
                                'Student' => 'primary',
                                default => 'secondary'
                            };
                        ?> fs-5 px-3 py-2">
                            <?php echo htmlspecialchars($user['RoleName'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        
                        <?php if ($userStats['avg_rating'] > 0): ?>
                            <div class="mt-3">
                                <h4 class="text-warning mb-0">⭐ <?php echo $userStats['avg_rating']; ?></h4>
                                <small class="text-muted"><?php echo $userStats['total_reviews']; ?> review(s)</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-0"><?php echo $userStats['total_listings']; ?></h3>
                        <p class="text-muted small mb-0">Listings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-0"><?php echo $userStats['total_bids']; ?></h3>
                        <p class="text-muted small mb-0">Bids Placed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="text-info mb-0"><?php echo $userStats['messages_sent'] + $userStats['messages_received']; ?></h3>
                        <p class="text-muted small mb-0">Messages</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h3 class="text-danger mb-0"><?php echo $userStats['complaints_filed']; ?></h3>
                        <p class="text-muted small mb-0">Complaints</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Info Tabs -->
        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#listings">
                            Listings (<?php echo $userStats['total_listings']; ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#bids">
                            Bids (<?php echo $userStats['total_bids']; ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#reviews">
                            Reviews (<?php echo $userStats['total_reviews']; ?>)
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Listings Tab -->
                    <div class="tab-pane fade show active" id="listings">
                        <?php if (empty($userStats['recent_listings'])): ?>
                            <p class="text-muted">No listings created yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($userStats['recent_listings'] as $listing): ?>
                                            <tr>
                                                <td><?php echo $listing['ListingID']; ?></td>
                                                <td><?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>₺<?php echo number_format((float)$listing['Price'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $listing['Status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $listing['Status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="/campus-marketplace/pages/listing-detail.php?id=<?php echo $listing['ListingID']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($userStats['total_listings'] > 5): ?>
                                <p class="text-muted small">Showing recent 5 of <?php echo $userStats['total_listings']; ?> listings</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Bids Tab -->
                    <div class="tab-pane fade" id="bids">
                        <?php if (empty($userStats['recent_bids'])): ?>
                            <p class="text-muted">No bids placed yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bid ID</th>
                                            <th>Listing</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($userStats['recent_bids'] as $bid): ?>
                                            <tr>
                                                <td><?php echo $bid['BidID']; ?></td>
                                                <td><?php echo htmlspecialchars($bid['ListingTitle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>₺<?php echo number_format((float)$bid['BidAmount'], 2); ?></td>
                                                <td><?php echo date('M d, Y H:i', strtotime($bid['BidDate'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($userStats['total_bids'] > 5): ?>
                                <p class="text-muted small">Showing recent 5 of <?php echo $userStats['total_bids']; ?> bids</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="reviews">
                        <?php if (empty($userStats['recent_reviews'])): ?>
                            <p class="text-muted">No reviews received yet.</p>
                        <?php else: ?>
                            <?php foreach ($userStats['recent_reviews'] as $review): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo htmlspecialchars($review['ReviewerName'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <span class="text-warning ms-2">
                                                <?php echo str_repeat('⭐', (int)$review['Rating']); ?>
                                            </span>
                                        </div>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($review['ReviewDate'])); ?></small>
                                    </div>
                                    <?php if ($review['Comment']): ?>
                                        <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($review['Comment'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($userStats['total_reviews'] > 5): ?>
                                <p class="text-muted small">Showing recent 5 of <?php echo $userStats['total_reviews']; ?> reviews</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h5 class="card-title">Admin Actions</h5>
                <div class="d-flex gap-2">
                    <a href="/campus-marketplace/pages/admin/users.php" class="btn btn-primary">Change Role</a>
                    <a href="/campus-marketplace/pages/send-message.php?to=<?php echo $userId; ?>" class="btn btn-outline-primary">Send Message</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

