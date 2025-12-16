-- ========================================
-- STORED PROCEDURES FOR CAMPUS MARKETPLACE
-- ========================================

USE campus_marketplace;

DELIMITER //

-- ========================================
-- 1. Get User with Role (3 tables: User, Role)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetUserWithRole//
CREATE PROCEDURE sp_GetUserWithRole(IN p_email VARCHAR(100))
BEGIN
    SELECT 
        U.UserID, U.Name, U.Email, U.Password, U.Phone,
        R.RoleID, R.RoleName
    FROM User U
    INNER JOIN Role R ON R.RoleID = U.RoleID
    WHERE U.Email = p_email
    LIMIT 1;
END//

-- ========================================
-- 2. Get Active Listings with Details (3+ tables)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetActiveListingsWithDetails//
CREATE PROCEDURE sp_GetActiveListingsWithDetails(
    IN p_category_id INT,
    IN p_limit INT
)
BEGIN
    SELECT 
        PL.ListingID, PL.Title, PL.Description, PL.Price, PL.Status,
        C.CategoryID, C.CategoryName,
        U.UserID AS SellerID, U.Name AS SellerName,
        COUNT(DISTINCT B.BidID) AS BidCount,
        MAX(B.BidAmount) AS HighestBid
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    INNER JOIN User U ON U.UserID = PL.SellerID
    LEFT JOIN Bid B ON B.ListingID = PL.ListingID
    WHERE PL.Status = 'Active'
        AND (p_category_id IS NULL OR PL.CategoryID = p_category_id)
    GROUP BY PL.ListingID, PL.Title, PL.Description, PL.Price, PL.Status,
             C.CategoryID, C.CategoryName, U.UserID, U.Name
    ORDER BY PL.ListingID DESC
    LIMIT p_limit;
END//

-- ========================================
-- 3. Get Listing Detail with All Info (4+ tables)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetListingDetail//
CREATE PROCEDURE sp_GetListingDetail(IN p_listing_id INT)
BEGIN
    SELECT 
        PL.ListingID, PL.Title, PL.Description, PL.Price, PL.Status,
        C.CategoryID, C.CategoryName,
        U.UserID AS SellerID, U.Name AS SellerName, U.Email AS SellerEmail,
        COUNT(DISTINCT B.BidID) AS TotalBids,
        MAX(B.BidAmount) AS HighestBid,
        AVG(R.Rating) AS SellerAvgRating,
        COUNT(DISTINCT R.ReviewID) AS SellerReviewCount
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    INNER JOIN User U ON U.UserID = PL.SellerID
    LEFT JOIN Bid B ON B.ListingID = PL.ListingID
    LEFT JOIN Review R ON R.RevieweeID = U.UserID
    WHERE PL.ListingID = p_listing_id
    GROUP BY PL.ListingID, PL.Title, PL.Description, PL.Price, PL.Status,
             C.CategoryID, C.CategoryName, U.UserID, U.Name, U.Email;
END//

-- ========================================
-- 4. Get User's Saved Items (4 tables)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetUserSavedItems//
CREATE PROCEDURE sp_GetUserSavedItems(IN p_user_id INT)
BEGIN
    SELECT 
        PL.ListingID, PL.Title, PL.Price, PL.Status,
        C.CategoryName,
        U.Name AS SellerName,
        SI.SavedDate,
        COUNT(DISTINCT B.BidID) AS BidCount
    FROM Saved_Item SI
    INNER JOIN Product_Listing PL ON PL.ListingID = SI.ListingID
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    INNER JOIN User U ON U.UserID = PL.SellerID
    LEFT JOIN Bid B ON B.ListingID = PL.ListingID
    WHERE SI.UserID = p_user_id
    GROUP BY PL.ListingID, PL.Title, PL.Price, PL.Status,
             C.CategoryName, U.Name, SI.SavedDate
    ORDER BY SI.SavedDate DESC;
END//

-- ========================================
-- 5. Get User's Bids with Listing Info (3 tables)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetUserBids//
CREATE PROCEDURE sp_GetUserBids(IN p_user_id INT)
BEGIN
    SELECT 
        B.BidID, B.BidAmount, B.BidDate,
        PL.ListingID, PL.Title, PL.Price, PL.Status,
        U.Name AS SellerName,
        C.CategoryName
    FROM Bid B
    INNER JOIN Product_Listing PL ON PL.ListingID = B.ListingID
    INNER JOIN User U ON U.UserID = PL.SellerID
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    WHERE B.BuyerID = p_user_id
    ORDER BY B.BidDate DESC;
END//

-- ========================================
-- 6. Get User Messages with Sender/Receiver Info (3 tables)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetUserMessages//
CREATE PROCEDURE sp_GetUserMessages(IN p_user_id INT)
BEGIN
    SELECT 
        M.MessageID, M.MessageText, M.SentDate,
        Sender.UserID AS SenderID, Sender.Name AS SenderName,
        Receiver.UserID AS ReceiverID, Receiver.Name AS ReceiverName
    FROM Message M
    INNER JOIN User Sender ON Sender.UserID = M.SenderID
    INNER JOIN User Receiver ON Receiver.UserID = M.ReceiverID
    WHERE M.SenderID = p_user_id OR M.ReceiverID = p_user_id
    ORDER BY M.SentDate DESC;
END//

-- ========================================
-- 7. Get User Reviews with Reviewer Info (3 tables)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetUserReviews//
CREATE PROCEDURE sp_GetUserReviews(IN p_user_id INT)
BEGIN
    SELECT 
        R.ReviewID, R.Rating, R.Comment, R.ReviewDate,
        Reviewer.UserID AS ReviewerID, Reviewer.Name AS ReviewerName,
        Reviewee.UserID AS RevieweeID, Reviewee.Name AS RevieweeName
    FROM Review R
    INNER JOIN User Reviewer ON Reviewer.UserID = R.ReviewerID
    INNER JOIN User Reviewee ON Reviewee.UserID = R.RevieweeID
    WHERE R.RevieweeID = p_user_id OR R.ReviewerID = p_user_id
    ORDER BY R.ReviewDate DESC;
END//

-- ========================================
-- 8. Get Platform Statistics (Multiple tables)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetPlatformStats//
CREATE PROCEDURE sp_GetPlatformStats()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM User) AS TotalUsers,
        (SELECT COUNT(*) FROM Product_Listing WHERE Status = 'Active') AS ActiveListings,
        (SELECT COUNT(*) FROM Product_Listing WHERE Status = 'Sold') AS SoldListings,
        (SELECT COUNT(*) FROM Bid) AS TotalBids,
        (SELECT COUNT(*) FROM Message) AS TotalMessages,
        (SELECT COUNT(*) FROM Review) AS TotalReviews,
        (SELECT COUNT(*) FROM Complaint_Report WHERE Status = 'Pending') AS PendingComplaints;
END//

-- ========================================
-- 9. Search Listings (3+ tables with full-text search simulation)
-- ========================================
DROP PROCEDURE IF EXISTS sp_SearchListings//
CREATE PROCEDURE sp_SearchListings(
    IN p_search_term VARCHAR(200),
    IN p_category_id INT,
    IN p_min_price DECIMAL(10,2),
    IN p_max_price DECIMAL(10,2)
)
BEGIN
    SELECT 
        PL.ListingID, PL.Title, PL.Description, PL.Price, PL.Status,
        C.CategoryName,
        U.Name AS SellerName,
        COUNT(DISTINCT B.BidID) AS BidCount
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    INNER JOIN User U ON U.UserID = PL.SellerID
    LEFT JOIN Bid B ON B.ListingID = PL.ListingID
    WHERE PL.Status = 'Active'
        AND (p_search_term IS NULL OR PL.Title LIKE CONCAT('%', p_search_term, '%') 
             OR PL.Description LIKE CONCAT('%', p_search_term, '%'))
        AND (p_category_id IS NULL OR PL.CategoryID = p_category_id)
        AND (p_min_price IS NULL OR PL.Price >= p_min_price)
        AND (p_max_price IS NULL OR PL.Price <= p_max_price)
    GROUP BY PL.ListingID, PL.Title, PL.Description, PL.Price, PL.Status,
             C.CategoryName, U.Name
    ORDER BY PL.ListingID DESC
    LIMIT 50;
END//

-- ========================================
-- 10. Get Unread Notifications Count
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetUnreadNotificationCount//
CREATE PROCEDURE sp_GetUnreadNotificationCount(IN p_user_id INT)
BEGIN
    SELECT COUNT(*) AS UnreadCount
    FROM Notification
    WHERE UserID = p_user_id AND IsRead = FALSE;
END//

-- ========================================
-- 11. Create New Listing
-- ========================================
DROP PROCEDURE IF EXISTS sp_CreateListing//
CREATE PROCEDURE sp_CreateListing(
    IN p_title VARCHAR(200),
    IN p_description TEXT,
    IN p_price DECIMAL(10,2),
    IN p_seller_id INT,
    IN p_category_id INT,
    OUT p_listing_id INT
)
BEGIN
    -- New listings start as 'Pending' and require moderator approval
    INSERT INTO Product_Listing (Title, Description, Price, Status, SellerID, CategoryID)
    VALUES (p_title, p_description, p_price, 'Pending', p_seller_id, p_category_id);
    
    SET p_listing_id = LAST_INSERT_ID();
END//

-- ========================================
-- 12. Place Bid
-- ========================================
DROP PROCEDURE IF EXISTS sp_PlaceBid//
CREATE PROCEDURE sp_PlaceBid(
    IN p_buyer_id INT,
    IN p_listing_id INT,
    IN p_bid_amount DECIMAL(10,2),
    OUT p_bid_id INT
)
BEGIN
    INSERT INTO Bid (BidAmount, BidDate, BuyerID, ListingID)
    VALUES (p_bid_amount, CURRENT_TIMESTAMP, p_buyer_id, p_listing_id);
    
    SET p_bid_id = LAST_INSERT_ID();
END//

-- ========================================
-- 13. Send Message
-- ========================================
DROP PROCEDURE IF EXISTS sp_SendMessage//
CREATE PROCEDURE sp_SendMessage(
    IN p_sender_id INT,
    IN p_receiver_id INT,
    IN p_message_text TEXT,
    OUT p_message_id INT
)
BEGIN
    INSERT INTO Message (MessageText, SentDate, SenderID, ReceiverID)
    VALUES (p_message_text, CURRENT_TIMESTAMP, p_sender_id, p_receiver_id);
    
    SET p_message_id = LAST_INSERT_ID();
END//

-- ========================================
-- 14. Mark Notification as Read
-- ========================================
DROP PROCEDURE IF EXISTS sp_MarkNotificationRead//
CREATE PROCEDURE sp_MarkNotificationRead(IN p_notification_id INT)
BEGIN
    UPDATE Notification
    SET IsRead = TRUE
    WHERE NotificationID = p_notification_id;
END//

-- ========================================
-- 15. Get Listings by Seller (3 tables)
-- ========================================
DROP PROCEDURE IF EXISTS sp_GetListingsBySeller//
CREATE PROCEDURE sp_GetListingsBySeller(IN p_seller_id INT)
BEGIN
    SELECT 
        PL.ListingID, PL.Title, PL.Price, PL.Status,
        C.CategoryName,
        COUNT(DISTINCT B.BidID) AS BidCount,
        MAX(B.BidAmount) AS HighestBid
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    LEFT JOIN Bid B ON B.ListingID = PL.ListingID
    WHERE PL.SellerID = p_seller_id
    GROUP BY PL.ListingID, PL.Title, PL.Price, PL.Status, C.CategoryName
    ORDER BY PL.ListingID DESC;
END//

DELIMITER ;

-- ========================================
-- VERIFICATION
-- ========================================
SHOW PROCEDURE STATUS WHERE Db = 'campus_marketplace';

