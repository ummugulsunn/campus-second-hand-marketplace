<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$myBids = [];
$errorMessage = '';

try {
    // Fetch user's bids with listing details
    $bidsSql = <<<SQL
    SELECT 
        B.BidID,
        B.BidAmount,
        B.BidDate,
        PL.ListingID,
        PL.Title,
        PL.Price AS StartingPrice,
        PL.Status AS ListingStatus,
        U.Name AS SellerName,
        (SELECT MAX(B2.BidAmount) 
         FROM Bid B2 
         WHERE B2.ListingID = PL.ListingID) AS HighestBid
    FROM Bid B
    INNER JOIN Product_Listing PL ON PL.ListingID = B.ListingID
    INNER JOIN User U ON U.UserID = PL.SellerID
    WHERE B.BuyerID = :user_id
    ORDER BY B.BidDate DESC;
    SQL;

    $bidsStmt = $pdo->prepare($bidsSql);
    $bidsStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $bidsStmt->execute();
    $myBids = $bidsStmt->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load bids: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <h2 class="mb-4">My Bids</h2>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if (empty($myBids)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">💰</div>
            <h4>No Bids Yet</h4>
            <p>You haven't placed any bids yet. Browse listings and make your first offer!</p>
            <a href="/campus-marketplace/pages/listings.php" class="btn btn-primary">Browse Listings</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Listing</th>
                        <th>My Bid</th>
                        <th>Highest Bid</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myBids as $bid): ?>
                        <?php 
                        $isWinning = (float)$bid['BidAmount'] >= (float)$bid['HighestBid'];
                        $badgeClass = match($bid['ListingStatus']) {
                            'Active' => 'bg-success',
                            'Sold' => 'bg-secondary',
                            'Pending' => 'bg-warning',
                            'Removed' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($bid['Title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <br>
                                <small class="text-muted">by <?php echo htmlspecialchars($bid['SellerName'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td class="<?php echo $isWinning ? 'text-success fw-bold' : ''; ?>">
                                ₺<?php echo number_format((float)$bid['BidAmount'], 2); ?>
                                <?php if ($isWinning && $bid['ListingStatus'] === 'Active'): ?>
                                    <span class="badge bg-success">Winning</span>
                                <?php endif; ?>
                            </td>
                            <td>₺<?php echo number_format((float)$bid['HighestBid'], 2); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo htmlspecialchars($bid['ListingStatus'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($bid['BidDate'])); ?></td>
                            <td>
                                <a href="/campus-marketplace/pages/listing-detail.php?id=<?php echo $bid['ListingID']; ?>" 
                                   class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

