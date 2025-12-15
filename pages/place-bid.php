<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$listingId = isset($_GET['listing_id']) && is_numeric($_GET['listing_id']) ? (int)$_GET['listing_id'] : 0;
$listing = null;
$highestBid = null;
$errors = [];
$successMessage = '';

if ($listingId <= 0) {
    header('Location: /pages/listings.php');
    exit;
}

try {
    // Fetch listing details
    $listingSql = <<<SQL
    SELECT 
        PL.ListingID,
        PL.Title,
        PL.Price,
        PL.Status,
        PL.SellerID
    FROM Product_Listing PL
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

    // Check if user is the seller
    if ((int)$listing['SellerID'] === (int)$_SESSION['user_id']) {
        $errors[] = 'You cannot bid on your own listing.';
    }

    // Check listing status
    if ($listing['Status'] !== 'Active') {
        $errors[] = 'This listing is no longer active.';
    }

    // Fetch highest bid
    $bidSql = <<<SQL
    SELECT MAX(BidAmount) as HighestBid
    FROM Bid
    WHERE ListingID = :listing_id;
    SQL;

    $bidStmt = $pdo->prepare($bidSql);
    $bidStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
    $bidStmt->execute();
    $bidData = $bidStmt->fetch();
    $highestBid = $bidData['HighestBid'] ? (float)$bidData['HighestBid'] : null;

} catch (PDOException $e) {
    $errors[] = 'Failed to load listing: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $bidAmount = cleanInput($_POST['bid_amount'] ?? '');

    if ($bidAmount === '' || !is_numeric($bidAmount) || (float)$bidAmount <= 0) {
        $errors[] = 'Please enter a valid bid amount.';
    } else {
        $bidAmountFloat = (float)$bidAmount;
        $minBid = $highestBid !== null ? $highestBid : (float)$listing['Price'];

        if ($bidAmountFloat <= $minBid) {
            $errors[] = 'Your bid must be higher than ' . number_format((float)$minBid, 2) . ' TL.';
        }
    }

    if (empty($errors)) {
        try {
            $insertBidSql = <<<SQL
            INSERT INTO Bid (BidAmount, BuyerID, ListingID)
            VALUES (:bid_amount, :buyer_id, :listing_id);
            SQL;

            $insertBidStmt = $pdo->prepare($insertBidSql);
            $insertBidStmt->bindValue(':bid_amount', $bidAmountFloat, PDO::PARAM_STR);
            $insertBidStmt->bindValue(':buyer_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $insertBidStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $insertBidStmt->execute();

            $_SESSION['success_message'] = 'Bid placed successfully!';
            header('Location: /pages/listing-detail.php?id=' . $listingId);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to place bid: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Place a Bid</h3>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <script>
                            setTimeout(function() {
                                showToast('error', '<?php echo addslashes(implode(' ', $errors)); ?>');
                            }, 100);
                        </script>
                    <?php endif; ?>

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

                    <?php if ($listing && empty($successMessage)): ?>
                        <div class="mb-4">
                            <h5><?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="text-muted mb-1">
                                <strong>Starting Price:</strong> ₺<?php echo number_format((float)$listing['Price'], 2); ?>
                            </p>
                            <?php if ($highestBid !== null): ?>
                                <p class="text-muted mb-0">
                                    <strong>Current Highest Bid:</strong> ₺<?php echo number_format((float)$highestBid, 2); ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted mb-0">No bids yet. Be the first!</p>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($errors) || !in_array('You cannot bid on your own listing.', $errors)): ?>
                            <form method="post" action="/pages/place-bid.php?listing_id=<?php echo $listingId; ?>" novalidate>
                                <div class="mb-3">
                                    <label for="bid_amount" class="form-label">
                                        Your Bid (TL) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" class="form-control" id="bid_amount" 
                                           name="bid_amount" required
                                           placeholder="Enter amount higher than current bid"
                                           value="<?php echo isset($bidAmount) ? htmlspecialchars($bidAmount, ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    <small class="form-text text-muted">
                                        Minimum bid: ₺<?php echo number_format((float)($highestBid !== null ? $highestBid + 0.01 : $listing['Price']), 2); ?>
                                    </small>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="/pages/listing-detail.php?id=<?php echo $listingId; ?>" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Place Bid</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <a href="/pages/listing-detail.php?id=<?php echo $listingId; ?>" class="btn btn-secondary">Back to Listing</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

