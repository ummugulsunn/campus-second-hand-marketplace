<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['listing_id'])) {
    $listingId = (int)$_POST['listing_id'];

    try {
        // Verify ownership before deletion
        $verifySql = <<<SQL
        SELECT SellerID
        FROM Product_Listing
        WHERE ListingID = :listing_id
        LIMIT 1;
        SQL;

        $verifyStmt = $pdo->prepare($verifySql);
        $verifyStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $verifyStmt->execute();
        $listing = $verifyStmt->fetch();

        if ($listing && (int)$listing['SellerID'] === (int)$_SESSION['user_id']) {
            // Option 1: Soft delete (change status to 'Removed')
            $deleteSql = <<<SQL
            UPDATE Product_Listing
            SET Status = 'Removed'
            WHERE ListingID = :listing_id AND SellerID = :seller_id;
            SQL;

            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $deleteStmt->bindValue(':seller_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $deleteStmt->execute();

            // Standardized success feedback
            $_SESSION['success_message'] = 'Listing deleted successfully.';

            // Option 2: Hard delete (uncomment if you want to permanently delete)
            // Note: This may fail due to foreign key constraints if there are bids/saved items
            /*
            $deleteSql = <<<SQL
            DELETE FROM Product_Listing
            WHERE ListingID = :listing_id AND SellerID = :seller_id;
            SQL;

            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $deleteStmt->bindValue(':seller_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $deleteStmt->execute();
            */
        }
    } catch (PDOException $e) {
        // Silent fail or log error
    }
}

header('Location: /pages/profile.php');
exit;

