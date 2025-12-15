<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$redirectUrl = '/pages/saved-items.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = isset($_POST['listing_id']) && is_numeric($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;

    if ($listingId > 0) {
        try {
            // Delete from Saved_Item
            $deleteSql = <<<SQL
            DELETE FROM Saved_Item
            WHERE UserID = :user_id AND ListingID = :listing_id;
            SQL;

            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $deleteStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $deleteStmt->execute();

            // Check if redirect should go back to listing detail
            if (isset($_POST['redirect']) && $_POST['redirect'] === 'detail') {
                $redirectUrl = '/pages/listing-detail.php?id=' . $listingId;
            }
        } catch (PDOException $e) {
            // Silent fail or log error
        }
    }
}

header('Location: ' . $redirectUrl);
exit;

