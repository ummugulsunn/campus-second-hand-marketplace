-- ========================================
-- TRIGGERS FOR CAMPUS MARKETPLACE
-- ========================================

USE campus_marketplace;

DELIMITER //

-- ========================================
-- TRIGGER 1: Auto-create notification when new bid is placed
-- ========================================
DROP TRIGGER IF EXISTS trg_AfterBidInsert//
CREATE TRIGGER trg_AfterBidInsert
AFTER INSERT ON Bid
FOR EACH ROW
BEGIN
    DECLARE v_seller_id INT;
    DECLARE v_listing_title VARCHAR(200);
    DECLARE v_notification_content TEXT;
    
    -- Get seller ID and listing title
    SELECT SellerID, Title 
    INTO v_seller_id, v_listing_title
    FROM Product_Listing
    WHERE ListingID = NEW.ListingID;
    
    -- Create notification content
    SET v_notification_content = CONCAT(
        'New bid of ₺', 
        FORMAT(NEW.BidAmount, 2), 
        ' placed on your listing: ', 
        v_listing_title
    );
    
    -- Insert notification for seller
    INSERT INTO Notification (UserID, Content, IsRead, CreatedDate)
    VALUES (v_seller_id, v_notification_content, FALSE, CURRENT_TIMESTAMP);
END//

-- ========================================
-- TRIGGER 2: Auto-create notification when new message is received
-- ========================================
DROP TRIGGER IF EXISTS trg_AfterMessageInsert//
CREATE TRIGGER trg_AfterMessageInsert
AFTER INSERT ON Message
FOR EACH ROW
BEGIN
    DECLARE v_sender_name VARCHAR(100);
    DECLARE v_notification_content TEXT;
    
    -- Get sender name
    SELECT Name 
    INTO v_sender_name
    FROM User
    WHERE UserID = NEW.SenderID;
    
    -- Create notification content
    SET v_notification_content = CONCAT(
        'New message from ',
        v_sender_name,
        ': ',
        LEFT(NEW.MessageText, 50),
        CASE 
            WHEN LENGTH(NEW.MessageText) > 50 THEN '...'
            ELSE ''
        END
    );
    
    -- Insert notification for receiver
    INSERT INTO Notification (UserID, Content, IsRead, CreatedDate)
    VALUES (NEW.ReceiverID, v_notification_content, FALSE, CURRENT_TIMESTAMP);
END//

-- ========================================
-- TRIGGER 3: Auto-create notification when listing status changes
-- ========================================
DROP TRIGGER IF EXISTS trg_AfterListingStatusUpdate//
CREATE TRIGGER trg_AfterListingStatusUpdate
AFTER UPDATE ON Product_Listing
FOR EACH ROW
BEGIN
    DECLARE v_notification_content TEXT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_buyer_id INT;
    DECLARE v_seller_id INT;
    
    -- Cursor to get all bidders for this listing
    DECLARE bidder_cursor CURSOR FOR
        SELECT DISTINCT BuyerID
        FROM Bid
        WHERE ListingID = NEW.ListingID;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Get seller ID
    SET v_seller_id = NEW.SellerID;
    
    -- CASE 1: Pending → Active (APPROVED by moderator)
    IF OLD.Status = 'Pending' AND NEW.Status = 'Active' THEN
        SET v_notification_content = CONCAT(
            '✅ Your listing "',
            NEW.Title,
            '" has been approved and is now active!'
        );
        
        INSERT INTO Notification (UserID, Content, IsRead, CreatedDate)
        VALUES (v_seller_id, v_notification_content, FALSE, CURRENT_TIMESTAMP);
    END IF;
    
    -- CASE 2: Pending → Removed (REJECTED by moderator)
    IF OLD.Status = 'Pending' AND NEW.Status = 'Removed' THEN
        SET v_notification_content = CONCAT(
            '❌ Your listing "',
            NEW.Title,
            '" was not approved. Please contact support if you have questions.'
        );
        
        INSERT INTO Notification (UserID, Content, IsRead, CreatedDate)
        VALUES (v_seller_id, v_notification_content, FALSE, CURRENT_TIMESTAMP);
    END IF;
    
    -- CASE 3: Status changed to 'Sold' (notify all bidders)
    IF OLD.Status != 'Sold' AND NEW.Status = 'Sold' THEN
        SET v_notification_content = CONCAT(
            'The listing "',
            NEW.Title,
            '" has been sold.'
        );
        
        -- Open cursor
        OPEN bidder_cursor;
        
        -- Loop through all bidders
        read_loop: LOOP
            FETCH bidder_cursor INTO v_buyer_id;
            IF done THEN
                LEAVE read_loop;
            END IF;
            
            -- Insert notification for each bidder
            INSERT INTO Notification (UserID, Content, IsRead, CreatedDate)
            VALUES (v_buyer_id, v_notification_content, FALSE, CURRENT_TIMESTAMP);
        END LOOP;
        
        -- Close cursor
        CLOSE bidder_cursor;
    END IF;
END//

-- ========================================
-- TRIGGER 4: Prevent bid amount lower than listing price
-- ========================================
DROP TRIGGER IF EXISTS trg_BeforeBidInsert//
CREATE TRIGGER trg_BeforeBidInsert
BEFORE INSERT ON Bid
FOR EACH ROW
BEGIN
    DECLARE v_listing_price DECIMAL(10,2);
    DECLARE v_listing_status VARCHAR(20);
    
    -- Get listing price and status
    SELECT Price, Status
    INTO v_listing_price, v_listing_status
    FROM Product_Listing
    WHERE ListingID = NEW.ListingID;
    
    -- Check if listing is active
    IF v_listing_status != 'Active' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot place bid on inactive listing';
    END IF;
    
    -- Check if bid is at least equal to listing price
    IF NEW.BidAmount < v_listing_price THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Bid amount must be at least equal to listing price';
    END IF;
END//

-- ========================================
-- TRIGGER 5: Update listing status when bid is accepted
-- ========================================
-- Note: This would be called from application logic
-- But we can create a helper trigger for audit purposes

DROP TRIGGER IF EXISTS trg_AfterListingSold//
CREATE TRIGGER trg_AfterListingSold
AFTER UPDATE ON Product_Listing
FOR EACH ROW
BEGIN
    -- Log listing status change
    IF OLD.Status != NEW.Status THEN
        -- You could insert into an audit log table here
        -- For now, we'll just ensure consistency
        
        -- If listing is sold, we could auto-reject pending bids
        IF NEW.Status = 'Sold' THEN
            -- Note: This is just for demonstration
            -- In real application, bid acceptance/rejection would be handled separately
            SET @listing_sold = NEW.ListingID;
        END IF;
    END IF;
END//

DELIMITER ;

-- ========================================
-- VERIFICATION
-- ========================================
SHOW TRIGGERS WHERE `Table` IN ('Bid', 'Message', 'Product_Listing');

-- ========================================
-- TEST DATA FOR TRIGGERS
-- ========================================
/*
-- Test Trigger 1: Place a bid (should create notification)
CALL sp_PlaceBid(2, 1, 100.00, @bid_id);
SELECT * FROM Notification WHERE UserID = (SELECT SellerID FROM Product_Listing WHERE ListingID = 1);

-- Test Trigger 2: Send a message (should create notification)
CALL sp_SendMessage(1, 2, 'Test message', @msg_id);
SELECT * FROM Notification WHERE UserID = 2;

-- Test Trigger 3: Mark listing as sold (should notify all bidders)
UPDATE Product_Listing SET Status = 'Sold' WHERE ListingID = 1;
SELECT * FROM Notification WHERE Content LIKE '%has been sold%';
*/

