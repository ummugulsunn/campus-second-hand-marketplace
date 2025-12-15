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
            }
        } catch (PDOException $e) {
            // Silent fail or log error
        }
    }
}

// Redirect back to listing detail
$redirectId = $listingId ?? 0;
header('Location: /pages/listing-detail.php?id=' . $redirectId);
exit;

