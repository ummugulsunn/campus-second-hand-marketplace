<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

$listingId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = null;
$highestBid = null;
$totalBids = 0;
$isSaved = false;
$allBids = [];
$errorMessage = '';
$successMessage = '';
$infoMessage = '';

// Standardized feedback from previous actions
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['info_message'])) {
    $infoMessage = $_SESSION['info_message'];
    unset($_SESSION['info_message']);
}

if ($listingId <= 0) {
    header('Location: /pages/listings.php');
    exit;
}

try {
    // Fetch listing details with JOIN
    $listingSql = <<<SQL
    SELECT 
        PL.ListingID,
        PL.Title,
        PL.Description,
        PL.Price,
        PL.Status,
        PL.SellerID,
        C.CategoryName,
        U.Name AS SellerName,
        U.Email AS SellerEmail,
        U.Phone AS SellerPhone
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    INNER JOIN User U ON U.UserID = PL.SellerID
    WHERE PL.ListingID = :listing_id
    LIMIT 1;
    SQL;

    $listingStmt = $pdo->prepare($listingSql);
    $listingStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
    $listingStmt->execute();
    $listing = $listingStmt->fetch();

    if (!$listing) {
        header('Location: /pages/listings.php');
        exit;
    }

    // Fetch highest bid and total bid count
    $bidSql = <<<SQL
    SELECT 
        MAX(BidAmount) as HighestBid,
        COUNT(*) as TotalBids
    FROM Bid
    WHERE ListingID = :listing_id;
    SQL;

    $bidStmt = $pdo->prepare($bidSql);
    $bidStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
    $bidStmt->execute();
    $bidData = $bidStmt->fetch();
    $highestBid = $bidData['HighestBid'] ? (float)$bidData['HighestBid'] : null;
    $totalBids = (int)$bidData['TotalBids'];

    // Check if current user has saved this listing
    if (isLoggedIn()) {
        $savedSql = <<<SQL
        SELECT SavedItemID
        FROM Saved_Item
        WHERE UserID = :user_id AND ListingID = :listing_id
        LIMIT 1;
        SQL;

        $savedStmt = $pdo->prepare($savedSql);
        $savedStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $savedStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $savedStmt->execute();
        $isSaved = (bool)$savedStmt->fetch();
        
        // If current user is the seller, fetch all bids for management
        if (isset($_SESSION['user_id']) && (int)$listing['SellerID'] === $_SESSION['user_id']) {
            $allBidsSql = <<<SQL
            SELECT 
                B.BidID,
                B.BidAmount,
                B.BidDate,
                U.UserID as BuyerID,
                U.Name as BuyerName
            FROM Bid B
            INNER JOIN User U ON U.UserID = B.BuyerID
            WHERE B.ListingID = :listing_id
            ORDER BY B.BidAmount DESC, B.BidDate ASC;
            SQL;
            
            $allBidsStmt = $pdo->prepare($allBidsSql);
            $allBidsStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $allBidsStmt->execute();
            $allBids = $allBidsStmt->fetchAll();
        }
    }

} catch (PDOException $e) {
    $errorMessage = 'Failed to load listing: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

$isOwnListing = isLoggedIn() && isset($_SESSION['user_id']) && $_SESSION['user_id'] === (int)$listing['SellerID'];

// Check if user can review seller (has interacted with them)
$canReview = false;
if (isLoggedIn() && !$isOwnListing && $listing) {
    try {
        // Check for bids on seller's listings
        $bidCheckSql = <<<SQL
        SELECT 1 FROM Bid B
        INNER JOIN Product_Listing PL ON PL.ListingID = B.ListingID
        WHERE B.BuyerID = :buyer_id AND PL.SellerID = :seller_id
        LIMIT 1;
        SQL;
        
        $bidCheckStmt = $pdo->prepare($bidCheckSql);
        $bidCheckStmt->bindValue(':buyer_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $bidCheckStmt->bindValue(':seller_id', $listing['SellerID'], PDO::PARAM_INT);
        $bidCheckStmt->execute();
        
        if ($bidCheckStmt->fetch()) {
            $canReview = true;
        }
        
        // If no bid interaction, check for messages
        if (!$canReview) {
            $messageCheckSql = <<<SQL
            SELECT 1 FROM Message
            WHERE (SenderID = :user1 AND ReceiverID = :user2)
               OR (SenderID = :user2 AND ReceiverID = :user1)
            LIMIT 1;
            SQL;
            
            $messageCheckStmt = $pdo->prepare($messageCheckSql);
            $messageCheckStmt->bindValue(':user1', $_SESSION['user_id'], PDO::PARAM_INT);
            $messageCheckStmt->bindValue(':user2', $listing['SellerID'], PDO::PARAM_INT);
            $messageCheckStmt->execute();
            
            if ($messageCheckStmt->fetch()) {
                $canReview = true;
            }
        }
    } catch (PDOException $e) {
        // Silent fail
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <?php if ($successMessage !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(function() {
                showToast('success', '<?php echo addslashes($successMessage); ?>');
            }, 100);
        </script>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(function() {
                showToast('error', '<?php echo addslashes($errorMessage); ?>');
            }, 100);
        </script>
    <?php endif; ?>

    <?php if ($infoMessage !== ''): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?php echo $infoMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(function() {
                showToast('success', '<?php echo addslashes($infoMessage); ?>');
            }, 100);
        </script>
    <?php endif; ?>

    <?php if ($listing): ?>
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/pages/listings.php">Listings</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <!-- Image Placeholder -->
                    <div class="listing-image-placeholder" style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 8rem; color: white; opacity: 0.9;">
                        <?php 
                        $categoryEmoji = match($listing['CategoryName']) {
                            'Books' => '📚',
                            'Electronics' => '💻',
                            'Furniture' => '🪑',
                            'Dorm Equipment' => '🛏️',
                            default => '📦'
                        };
                        echo $categoryEmoji;
                        ?>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($listing['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="badge bg-<?php echo $listing['Status'] === 'Active' ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars($listing['Status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <h2 class="mb-3"><?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?></h2>

                        <div class="mb-4">
                            <h3 class="text-primary">₺<?php echo number_format((float)$listing['Price'], 2); ?></h3>
                            <?php if ($highestBid !== null): ?>
                                <p class="text-muted">
                                    Highest Bid: <strong>₺<?php echo number_format((float)$highestBid, 2); ?></strong>
                                    (<?php echo $totalBids; ?> bid<?php echo $totalBids !== 1 ? 's' : ''; ?>)
                                </p>
                            <?php else: ?>
                                <p class="text-muted">No bids yet</p>
                            <?php endif; ?>
                        </div>

                        <h5>Description</h5>
                        <p class="text-muted">
                            <?php 
                            echo $listing['Description'] 
                                ? nl2br(htmlspecialchars($listing['Description'], ENT_QUOTES, 'UTF-8')) 
                                : '<em>No description provided.</em>'; 
                            ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Seller Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Seller Information</h5>
                        <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($listing['SellerName'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($listing['SellerEmail'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if ($listing['SellerPhone']): ?>
                            <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($listing['SellerPhone'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <?php if (isLoggedIn() && !$isOwnListing): ?>
                            <hr>
                            <?php if ($canReview): ?>
                                <a href="/pages/leave-review.php?for=<?php echo $listing['SellerID']; ?>" class="btn btn-sm btn-outline-warning w-100">
                                    ⭐ Leave Review for Seller
                                </a>
                                <small class="text-muted d-block mt-1 text-center">
                                    You can review this seller
                                </small>
                            <?php else: ?>
                                <div class="alert alert-info small mb-0">
                                    <strong>📝 Want to review?</strong><br>
                                    Place a bid or send a message first!
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <?php if (isLoggedIn() && !$isOwnListing && $listing['Status'] === 'Active'): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="/pages/place-bid.php?listing_id=<?php echo $listingId; ?>" class="btn btn-primary">
                                    Place Bid
                                </a>
                                <a href="/pages/send-message.php?to=<?php echo $listing['SellerID']; ?>&listing_id=<?php echo $listingId; ?>" class="btn btn-outline-primary">
                                    Contact Seller
                                </a>
                                <?php if ($isSaved): ?>
                                    <form method="post" action="/pages/remove-saved-item.php">
                                        <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
                                        <input type="hidden" name="redirect" value="detail">
                                        <button type="submit" class="btn btn-outline-secondary w-100">
                                            ❤️ Saved
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="/pages/save-item.php">
                                        <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
                                        <button type="submit" class="btn btn-outline-secondary w-100">
                                            🤍 Save to Wishlist
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php elseif ($isOwnListing): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Manage Listing</h5>
                            <div class="alert alert-info">This is your listing.</div>
                            <div class="d-grid gap-2">
                                <a href="/pages/my-bids.php?listing_id=<?php echo $listingId; ?>" class="btn btn-outline-primary">
                                    View Bids (<?php echo $totalBids; ?>)
                                </a>
                            </div>
                        </div>
                    </div>
                <?php elseif (!isLoggedIn()): ?>
                    <div class="alert alert-warning">
                        <a href="/pages/login.php">Login</a> to place bids or contact the seller.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Bid Management (Only for Seller) -->
    <?php if ($isOwnListing && !empty($allBids)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">📊 Manage Bids (<?php echo count($allBids); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bidder</th>
                                        <th>Bid Amount</th>
                                        <th>Bid Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allBids as $bid): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($bid['BuyerName'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <strong class="text-primary">₺<?php echo number_format((float)$bid['BidAmount'], 2); ?></strong>
                                                <?php if ((float)$bid['BidAmount'] == $highestBid): ?>
                                                    <span class="badge bg-success ms-2">Highest</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($bid['BidDate'])); ?></td>
                                            <td>
                                                <?php if ($listing['Status'] === 'Active'): ?>
                                                    <a href="/pages/accept-bid.php?bid_id=<?php echo $bid['BidID']; ?>&action=accept" 
                                                       class="btn btn-sm btn-success"
                                                       onclick="return confirm('Accept this bid and mark the listing as Sold?');">
                                                        ✓ Accept
                                                    </a>
                                                    <a href="/pages/accept-bid.php?bid_id=<?php echo $bid['BidID']; ?>&action=reject" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Reject this bid?');">
                                                        ✗ Reject
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Listing <?php echo $listing['Status']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mb-0 mt-3">
                            <strong>💡 Tip:</strong> When you accept a bid, the listing will be marked as "Sold" and the buyer will be notified. Other bidders will also be informed that the item is sold.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

