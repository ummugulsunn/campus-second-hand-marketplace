<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$bidId = isset($_GET['bid_id']) && is_numeric($_GET['bid_id']) ? (int)$_GET['bid_id'] : 0;
$action = isset($_GET['action']) && in_array($_GET['action'], ['accept', 'reject']) ? cleanInput($_GET['action']) : '';

if ($bidId <= 0 || $action === '') {
    $_SESSION['error_message'] = 'Invalid request.';
    header('Location: /pages/profile.php');
    exit;
}

try {
    // Verify the bid exists and belongs to the current user's listing
    $verifySql = <<<SQL
    SELECT 
        B.BidID,
        B.BidAmount,
        B.BuyerID,
        B.ListingID,
        PL.SellerID,
        PL.Title as ListingTitle,
        U.Name as BuyerName
    FROM Bid B
    INNER JOIN Product_Listing PL ON PL.ListingID = B.ListingID
    INNER JOIN User U ON U.UserID = B.BuyerID
    WHERE B.BidID = :bid_id
    LIMIT 1;
    SQL;
    
    $verifyStmt = $pdo->prepare($verifySql);
    $verifyStmt->bindValue(':bid_id', $bidId, PDO::PARAM_INT);
    $verifyStmt->execute();
    $bid = $verifyStmt->fetch();
    
    if (!$bid) {
        $_SESSION['error_message'] = 'Bid not found.';
        header('Location: /pages/profile.php');
        exit;
    }
    
    if ((int)$bid['SellerID'] !== $_SESSION['user_id']) {
        $_SESSION['error_message'] = 'You are not authorized to manage this bid.';
        header('Location: /pages/profile.php');
        exit;
    }
    
    if ($action === 'accept') {
        // Mark the listing as "Sold"
        $updateListingSql = "UPDATE Product_Listing SET Status = 'Sold' WHERE ListingID = :listing_id;";
        $updateListingStmt = $pdo->prepare($updateListingSql);
        $updateListingStmt->bindValue(':listing_id', $bid['ListingID'], PDO::PARAM_INT);
        $updateListingStmt->execute();
        
        // Create notification for the winner
        $notificationContent = "Congratulations! Your bid of ₺" . number_format((float)$bid['BidAmount'], 2) . 
                               " on '{$bid['ListingTitle']}' has been accepted!";
        $notifSql = <<<SQL
        INSERT INTO Notification (UserID, Content, IsRead, CreatedDate)
        VALUES (:user_id, :content, FALSE, NOW());
        SQL;
        
        $notifStmt = $pdo->prepare($notifSql);
        $notifStmt->bindValue(':user_id', $bid['BuyerID'], PDO::PARAM_INT);
        $notifStmt->bindValue(':content', $notificationContent, PDO::PARAM_STR);
        $notifStmt->execute();
        
        // Notify other bidders that the item is sold
        $otherBiddersSql = <<<SQL
        SELECT DISTINCT BuyerID
        FROM Bid
        WHERE ListingID = :listing_id AND BuyerID != :winner_id;
        SQL;
        
        $otherBiddersStmt = $pdo->prepare($otherBiddersSql);
        $otherBiddersStmt->bindValue(':listing_id', $bid['ListingID'], PDO::PARAM_INT);
        $otherBiddersStmt->bindValue(':winner_id', $bid['BuyerID'], PDO::PARAM_INT);
        $otherBiddersStmt->execute();
        $otherBidders = $otherBiddersStmt->fetchAll();
        
        $otherNotifContent = "The item '{$bid['ListingTitle']}' you bid on has been sold.";
        foreach ($otherBidders as $bidder) {
            $otherNotifStmt = $pdo->prepare($notifSql);
            $otherNotifStmt->bindValue(':user_id', $bidder['BuyerID'], PDO::PARAM_INT);
            $otherNotifStmt->bindValue(':content', $otherNotifContent, PDO::PARAM_STR);
            $otherNotifStmt->execute();
        }
        
        $_SESSION['success_message'] = "Bid accepted! The listing has been marked as 'Sold' and the buyer has been notified.";
        
    } elseif ($action === 'reject') {
        // Delete the bid (or you could add a status column to Bid table)
        $deleteBidSql = "DELETE FROM Bid WHERE BidID = :bid_id;";
        $deleteBidStmt = $pdo->prepare($deleteBidSql);
        $deleteBidStmt->bindValue(':bid_id', $bidId, PDO::PARAM_INT);
        $deleteBidStmt->execute();
        
        // Notify the bidder
        $notificationContent = "Your bid on '{$bid['ListingTitle']}' has been declined by the seller.";
        $notifSql = <<<SQL
        INSERT INTO Notification (UserID, Content, IsRead, CreatedDate)
        VALUES (:user_id, :content, FALSE, NOW());
        SQL;
        
        $notifStmt = $pdo->prepare($notifSql);
        $notifStmt->bindValue(':user_id', $bid['BuyerID'], PDO::PARAM_INT);
        $notifStmt->bindValue(':content', $notificationContent, PDO::PARAM_STR);
        $notifStmt->execute();
        
        $_SESSION['success_message'] = "Bid rejected successfully.";
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Failed to process bid: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

header('Location: /pages/listing-detail.php?id=' . $bid['ListingID']);
exit;

