<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = isset($_POST['listing_id']) && is_numeric($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;

    if ($listingId > 0) {
        try {
            // Check if already saved
            $checkSql = <<<SQL
            SELECT SavedItemID
            FROM Saved_Item
            WHERE UserID = :user_id AND ListingID = :listing_id
            LIMIT 1;
            SQL;

            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $checkStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $checkStmt->execute();

            if (!$checkStmt->fetch()) {
                // Insert into Saved_Item
                $insertSql = <<<SQL
                INSERT INTO Saved_Item (UserID, ListingID)
                VALUES (:user_id, :listing_id);
                SQL;

                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $insertStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
                $insertStmt->execute();

                // Standardized success feedback
                $_SESSION['success_message'] = 'Item saved to your wishlist.';
            } else {
                $_SESSION['info_message'] = 'Item is already in your wishlist.';
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Failed to save item: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    } else {
        $_SESSION['error_message'] = 'Invalid listing ID.';
    }
}

// Redirect back to listing detail
$redirectId = $listingId ?? 0;
header('Location: /campus-marketplace/pages/listing-detail.php?id=' . $redirectId);
exit;

