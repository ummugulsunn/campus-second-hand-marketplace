<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();
requireLogin();

// Only Moderators and Admins can access
if (!hasRole('Moderator') && !hasRole('Admin')) {
    header('Location: /index.php');
    exit;
}

$listings = [];
$errorMessage = '';
$successMessage = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listing_id'], $_POST['new_status'])) {
    $listingId = (int)$_POST['listing_id'];
    $newStatus = cleanInput($_POST['new_status']);

    if (in_array($newStatus, ['Active', 'Sold', 'Pending', 'Removed'])) {
        try {
            $updateSql = <<<SQL
            UPDATE Product_Listing
            SET Status = :status
            WHERE ListingID = :listing_id;
            SQL;

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindValue(':status', $newStatus, PDO::PARAM_STR);
            $updateStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $updateStmt->execute();

            $successMessage = 'Listing status updated successfully.';
        } catch (PDOException $e) {
            $errorMessage = 'Failed to update listing: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

try {
    // Fetch all listings with user and category info
    $listingsSql = <<<SQL
    SELECT 
        PL.ListingID,
        PL.Title,
        PL.Price,
        PL.Status,
        C.CategoryName,
        U.Name AS SellerName,
        U.Email AS SellerEmail,
        COUNT(B.BidID) AS BidCount
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    INNER JOIN User U ON U.UserID = PL.SellerID
    LEFT JOIN Bid B ON B.ListingID = PL.ListingID
    GROUP BY PL.ListingID
    ORDER BY 
        FIELD(PL.Status, 'Active', 'Pending', 'Sold', 'Removed'),
        PL.ListingID DESC;
    SQL;

    $listingsStmt = $pdo->query($listingsSql);
    $listings = $listingsStmt->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load listings: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="container py-5">
    <h2 class="mb-4">Manage Listings</h2>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if ($successMessage !== ''): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <?php if (empty($listings)): ?>
        <div class="alert alert-info">No listings found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Bids</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing): ?>
                        <tr>
                            <td><?php echo $listing['ListingID']; ?></td>
                            <td>
                                <a href="/pages/listing-detail.php?id=<?php echo $listing['ListingID']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($listing['SellerName'], ENT_QUOTES, 'UTF-8'); ?>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($listing['SellerEmail'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($listing['CategoryName'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>₺<?php echo number_format((float)$listing['Price'], 2); ?></td>
                            <td><?php echo $listing['BidCount']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($listing['Status']) {
                                        'Active' => 'success',
                                        'Pending' => 'warning',
                                        'Sold' => 'secondary',
                                        'Removed' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo htmlspecialchars($listing['Status'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="listing_id" value="<?php echo $listing['ListingID']; ?>">
                                    <select name="new_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                        <option value="">Change Status</option>
                                        <option value="Active" <?php echo $listing['Status'] === 'Active' ? 'disabled' : ''; ?>>Active</option>
                                        <option value="Pending" <?php echo $listing['Status'] === 'Pending' ? 'disabled' : ''; ?>>Pending</option>
                                        <option value="Sold" <?php echo $listing['Status'] === 'Sold' ? 'disabled' : ''; ?>>Sold</option>
                                        <option value="Removed" <?php echo $listing['Status'] === 'Removed' ? 'disabled' : ''; ?>>Removed</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

