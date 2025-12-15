<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$savedItems = [];
$errorMessage = '';

try {
    // Fetch saved items with listing details
    $savedSql = <<<SQL
    SELECT 
        SI.SavedItemID,
        SI.SavedDate,
        PL.ListingID,
        PL.Title,
        PL.Price,
        PL.Status,
        C.CategoryName,
        U.Name AS SellerName
    FROM Saved_Item SI
    INNER JOIN Product_Listing PL ON PL.ListingID = SI.ListingID
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    INNER JOIN User U ON U.UserID = PL.SellerID
    WHERE SI.UserID = :user_id
    ORDER BY SI.SavedDate DESC;
    SQL;

    $savedStmt = $pdo->prepare($savedSql);
    $savedStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $savedStmt->execute();
    $savedItems = $savedStmt->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load saved items: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <h2 class="mb-4">My Saved Items</h2>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if (empty($savedItems)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">❤️</div>
            <h4>No Saved Items</h4>
            <p>You haven't saved any items to your wishlist yet. Start exploring and save your favorites!</p>
            <a href="/pages/listings.php" class="btn btn-primary">Browse Listings</a>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($savedItems as $item): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm listing-card">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($item['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <span class="badge bg-<?php echo $item['Status'] === 'Active' ? 'success' : 'warning'; ?>">
                                    <?php echo htmlspecialchars($item['Status'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($item['Title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h5>
                            <p class="text-muted small mb-2">
                                by <?php echo htmlspecialchars($item['SellerName'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="h5 mb-0 text-primary">
                                        ₺<?php echo number_format((float)$item['Price'], 2); ?>
                                    </span>
                                    <a href="/pages/listing-detail.php?id=<?php echo $item['ListingID']; ?>" 
                                       class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                                <form method="post" action="/pages/remove-saved-item.php">
                                    <input type="hidden" name="listing_id" value="<?php echo $item['ListingID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                        Remove from Wishlist
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

