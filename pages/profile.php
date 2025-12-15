<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$userListings = [];
$userReviews = [];
$averageRating = 0;
$userStats = [];
$usersCanReview = [];
$errorMessage = '';
$successMessage = '';

// Check for delete success message
if (isset($_SESSION['delete_success'])) {
    $successMessage = $_SESSION['delete_success'];
    unset($_SESSION['delete_success']);
}

try {
    // Fetch user's own listings
    $listingsSql = <<<SQL
    SELECT 
        PL.ListingID,
        PL.Title,
        PL.Price,
        PL.Status,
        C.CategoryName,
        COUNT(B.BidID) AS BidCount
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    LEFT JOIN Bid B ON B.ListingID = PL.ListingID
    WHERE PL.SellerID = :user_id AND PL.Status != 'Removed'
    GROUP BY PL.ListingID
    ORDER BY PL.ListingID DESC;
    SQL;

    $listingsStmt = $pdo->prepare($listingsSql);
    $listingsStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $listingsStmt->execute();
    $userListings = $listingsStmt->fetchAll();

    // Fetch reviews received by this user
    $reviewsSql = <<<SQL
    SELECT 
        R.ReviewID,
        R.Rating,
        R.Comment,
        R.ReviewDate,
        U.Name AS ReviewerName
    FROM Review R
    INNER JOIN User U ON U.UserID = R.ReviewerID
    WHERE R.RevieweeID = :user_id
    ORDER BY R.ReviewDate DESC;
    SQL;

    $reviewsStmt = $pdo->prepare($reviewsSql);
    $reviewsStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $reviewsStmt->execute();
    $userReviews = $reviewsStmt->fetchAll();

    // Calculate average rating
    if (!empty($userReviews)) {
        $totalRating = array_sum(array_column($userReviews, 'Rating'));
        $averageRating = round($totalRating / count($userReviews), 1);
    }
    
    // User Stats
    $userStats['total_bids'] = $pdo->prepare("SELECT COUNT(*) as total FROM Bid WHERE BuyerID = :user_id");
    $userStats['total_bids']->execute([':user_id' => $_SESSION['user_id']]);
    $userStats['total_bids'] = (int)$userStats['total_bids']->fetch()['total'];
    
    $userStats['messages_sent'] = $pdo->prepare("SELECT COUNT(*) as total FROM Message WHERE SenderID = :user_id");
    $userStats['messages_sent']->execute([':user_id' => $_SESSION['user_id']]);
    $userStats['messages_sent'] = (int)$userStats['messages_sent']->fetch()['total'];
    
    $userStats['saved_items'] = $pdo->prepare("SELECT COUNT(*) as total FROM Saved_Item WHERE UserID = :user_id");
    $userStats['saved_items']->execute([':user_id' => $_SESSION['user_id']]);
    $userStats['saved_items'] = (int)$userStats['saved_items']->fetch()['total'];
    
    // Users I Can Review (interacted with but not reviewed yet)
    $canReviewSql = <<<SQL
    SELECT DISTINCT U.UserID, U.Name
    FROM User U
    WHERE U.UserID != :current_user_id
    AND U.UserID IN (
        SELECT DISTINCT PL.SellerID
        FROM Bid B
        INNER JOIN Product_Listing PL ON PL.ListingID = B.ListingID
        WHERE B.BuyerID = :current_user_id_bid
        
        UNION
        
        SELECT DISTINCT M.ReceiverID
        FROM Message M
        WHERE M.SenderID = :current_user_id_msg_sender
        
        UNION
        
        SELECT DISTINCT M.SenderID
        FROM Message M
        WHERE M.ReceiverID = :current_user_id_msg_receiver
    )
    AND U.UserID NOT IN (
        SELECT RevieweeID
        FROM Review
        WHERE ReviewerID = :current_user_id_review
    )
    LIMIT 10;
    SQL;
    
    $canReviewStmt = $pdo->prepare($canReviewSql);
    $canReviewStmt->bindValue(':current_user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $canReviewStmt->bindValue(':current_user_id_bid', $_SESSION['user_id'], PDO::PARAM_INT);
    $canReviewStmt->bindValue(':current_user_id_msg_sender', $_SESSION['user_id'], PDO::PARAM_INT);
    $canReviewStmt->bindValue(':current_user_id_msg_receiver', $_SESSION['user_id'], PDO::PARAM_INT);
    $canReviewStmt->bindValue(':current_user_id_review', $_SESSION['user_id'], PDO::PARAM_INT);
    $canReviewStmt->execute();
    $usersCanReview = $canReviewStmt->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load profile: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Profile</li>
        </ol>
    </nav>
    
    <h2 class="mb-4">My Profile</h2>

    <?php if ($successMessage !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <!-- User Info Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?></h4>
                    <p class="text-muted mb-1">
                        <strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['user_role_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="text-muted mb-0">
                        <strong>Total Listings:</strong> <?php echo count($userListings); ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if (!empty($userReviews)): ?>
                        <div class="mb-2">
                            <span class="h3 text-warning">⭐ <?php echo $averageRating; ?></span>
                            <p class="text-muted mb-0"><?php echo count($userReviews); ?> review<?php echo count($userReviews) !== 1 ? 's' : ''; ?></p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No reviews yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="/pages/my-bids.php" class="btn btn-outline-primary w-100">My Bids</a>
        </div>
        <div class="col-md-3">
            <a href="/pages/messages.php" class="btn btn-outline-primary w-100">Messages</a>
        </div>
        <div class="col-md-3">
            <a href="/pages/saved-items.php" class="btn btn-outline-primary w-100">Saved Items</a>
        </div>
        <?php if (hasRole('Student')): ?>
            <div class="col-md-3">
                <a href="/pages/add-listing.php" class="btn btn-primary w-100">+ Add Listing</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0"><?php echo count($userListings); ?></h3>
                    <p class="text-muted small mb-0">My Listings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-warning mb-0"><?php echo $userStats['total_bids'] ?? 0; ?></h3>
                    <p class="text-muted small mb-0">Bids Placed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-info mb-0"><?php echo $userStats['messages_sent'] ?? 0; ?></h3>
                    <p class="text-muted small mb-0">Messages Sent</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h3 class="text-success mb-0"><?php echo $userStats['saved_items'] ?? 0; ?></h3>
                    <p class="text-muted small mb-0">Saved Items</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Users I Can Review -->
    <?php if (!empty($usersCanReview)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">🎯 Users You Can Review</h5>
                <small class="text-muted">You've interacted with these users but haven't reviewed them yet</small>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php foreach ($usersCanReview as $reviewableUser): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($reviewableUser['Name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <a href="/pages/leave-review.php?reviewee_id=<?php echo $reviewableUser['UserID']; ?>" 
                               class="btn btn-sm btn-primary">Leave Review</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- My Listings Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">My Listings</h5>
        </div>
        <div class="card-body">
            <?php if (empty($userListings)): ?>
                <div class="text-center py-4">
                    <div style="font-size: 3rem; opacity: 0.5;">📦</div>
                    <p class="text-muted mb-2">You haven't created any listings yet.</p>
                    <?php if (hasRole('Student')): ?>
                        <a href="/pages/add-listing.php" class="btn btn-sm btn-primary">Create Your First Listing</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Bids</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userListings as $listing): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($listing['CategoryName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>₺<?php echo number_format((float)$listing['Price'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $listing['Status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars($listing['Status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $listing['BidCount']; ?></td>
                                    <td>
                                        <a href="/pages/listing-detail.php?id=<?php echo $listing['ListingID']; ?>" 
                                           class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="/pages/edit-listing.php?id=<?php echo $listing['ListingID']; ?>" 
                                           class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form method="post" action="/pages/delete-listing.php" class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete &quot;<?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?>&quot;?\n\nThis listing will be marked as removed and will no longer appear in your active listings.');">
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['ListingID']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Reviews Received</h5>
        </div>
        <div class="card-body">
            <?php if (empty($userReviews)): ?>
                <div class="text-center py-4">
                    <div style="font-size: 3rem; opacity: 0.5;">⭐</div>
                    <p class="text-muted mb-0">No reviews yet. Complete transactions to receive reviews!</p>
                </div>
            <?php else: ?>
                <?php foreach ($userReviews as $review): ?>
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
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

