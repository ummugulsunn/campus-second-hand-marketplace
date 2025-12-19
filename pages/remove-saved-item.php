<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: ' . base_url('/pages/saved-items.php'));
    exit;
}

$redirectUrl = base_url('/pages/saved-items.php');
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

        // Check if any row was actually deleted
        if ($deleteStmt->rowCount() > 0) {
            // Standardized success feedback
            $_SESSION['success_message'] = 'Item removed from your wishlist.';
        } else {
            $_SESSION['error_message'] = 'Item not found in your wishlist.';
        }

        // Check if redirect should go back to listing detail
        if (isset($_POST['redirect']) && $_POST['redirect'] === 'detail') {
            $redirectUrl = base_url('/pages/listing-detail.php?id=' . $listingId);
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Failed to remove item: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
} else {
    $_SESSION['error_message'] = 'Invalid listing ID.';
}

header('Location: ' . $redirectUrl);
exit;

