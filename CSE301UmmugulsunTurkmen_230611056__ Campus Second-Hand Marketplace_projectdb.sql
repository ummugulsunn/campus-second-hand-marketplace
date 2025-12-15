

-- ============================================================================
-- CAMPUS SECOND-HAND MARKETPLACE DATABASE
-- Database Management Project - Report 1
-- Group Members: AYŞENUR OTARAN, BÜŞRA DEMİREL, ŞEYMA AKIN, ÜMMÜGÜLSÜN TÜRKMEN
-- ============================================================================

-- Drop database if exists and create new one
DROP DATABASE IF EXISTS campus_marketplace;
CREATE DATABASE campus_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_marketplace;

-- ============================================================================
-- TABLE CREATION
-- ============================================================================

-- 1. Role Table
CREATE TABLE Role (
    RoleID INT AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(50) NOT NULL,
    CONSTRAINT chk_role_name CHECK (RoleName IN ('Student', 'Moderator', 'Admin'))
);

-- 2. User Table
CREATE TABLE User (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Phone VARCHAR(20) NULL,
    RoleID INT NOT NULL,
    CONSTRAINT fk_user_role FOREIGN KEY (RoleID) REFERENCES Role(RoleID)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 3. Category Table
CREATE TABLE Category (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(50) NOT NULL,
    CONSTRAINT chk_category_name CHECK (CategoryName IN ('Books', 'Electronics', 'Furniture', 'Dorm Equipment'))
);

-- 4. Product_Listing Table
CREATE TABLE Product_Listing (
    ListingID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(200) NOT NULL,
    Description TEXT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'Active',
    SellerID INT NOT NULL,
    CategoryID INT NOT NULL,
    CONSTRAINT chk_listing_status CHECK (Status IN ('Active', 'Sold', 'Pending', 'Removed')),
    CONSTRAINT fk_listing_seller FOREIGN KEY (SellerID) REFERENCES User(UserID)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_listing_category FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 5. Bid Table
CREATE TABLE Bid (
    BidID INT AUTO_INCREMENT PRIMARY KEY,
    BidAmount DECIMAL(10,2) NOT NULL,
    BidDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    BuyerID INT NOT NULL,
    ListingID INT NOT NULL,
    CONSTRAINT fk_bid_buyer FOREIGN KEY (BuyerID) REFERENCES User(UserID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_bid_listing FOREIGN KEY (ListingID) REFERENCES Product_Listing(ListingID)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 6. Message Table
CREATE TABLE Message (
    MessageID INT AUTO_INCREMENT PRIMARY KEY,
    MessageText TEXT NOT NULL,
    SentDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    SenderID INT NOT NULL,
    ReceiverID INT NOT NULL,
    CONSTRAINT fk_message_sender FOREIGN KEY (SenderID) REFERENCES User(UserID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_message_receiver FOREIGN KEY (ReceiverID) REFERENCES User(UserID)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 7. Review Table
CREATE TABLE Review (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    Rating INT NOT NULL,
    Comment TEXT NULL,
    ReviewDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ReviewerID INT NOT NULL,
    RevieweeID INT NOT NULL,
    CONSTRAINT chk_review_rating CHECK (Rating BETWEEN 1 AND 5),
    CONSTRAINT fk_review_reviewer FOREIGN KEY (ReviewerID) REFERENCES User(UserID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_review_reviewee FOREIGN KEY (RevieweeID) REFERENCES User(UserID)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 8. Complaint_Report Table
CREATE TABLE Complaint_Report (
    ComplaintID INT AUTO_INCREMENT PRIMARY KEY,
    Reason TEXT NOT NULL,
    Status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    ComplaintDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ReporterID INT NOT NULL,
    CONSTRAINT chk_complaint_status CHECK (Status IN ('Pending', 'Reviewed', 'Resolved')),
    CONSTRAINT fk_complaint_reporter FOREIGN KEY (ReporterID) REFERENCES User(UserID)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 9. Notification Table
CREATE TABLE Notification (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    Content TEXT NOT NULL,
    IsRead BOOLEAN NOT NULL DEFAULT FALSE,
    CreatedDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UserID INT NOT NULL,
    CONSTRAINT fk_notification_user FOREIGN KEY (UserID) REFERENCES User(UserID)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- 10. Saved_Item Table (Junction table for M:N relationship)
CREATE TABLE Saved_Item (
    SavedItemID INT AUTO_INCREMENT PRIMARY KEY,
    SavedDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UserID INT NOT NULL,
    ListingID INT NOT NULL,
    CONSTRAINT fk_saved_user FOREIGN KEY (UserID) REFERENCES User(UserID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_saved_listing FOREIGN KEY (ListingID) REFERENCES Product_Listing(ListingID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uq_user_listing UNIQUE (UserID, ListingID)
);

-- ============================================================================
-- DATA INSERTION
-- ============================================================================

-- Insert Roles (3 roles)
INSERT INTO Role (RoleName) VALUES 
('Student'),
('Moderator'),
('Admin');

-- Insert Users (15 users: 10 students, 3 moderators, 2 admins)
INSERT INTO User (Name, Email, Password, Phone, RoleID) VALUES
('Ahmet Yılmaz', 'ahmet.yilmaz@istun.edu.tr', '$2y$10$abcdefghijk', '05321234567', 1),
('Ayşe Kara', 'ayse.kara@istun.edu.tr', '$2y$10$lmnopqrstuv', '05339876543', 1),
('Mehmet Demir', 'mehmet.demir@istun.edu.tr', '$2y$10$wxyzabcdefg', '05345678901', 1),
('Fatma Çelik', 'fatma.celik@istun.edu.tr', '$2y$10$hijklmnopqr', '05356789012', 1),
('Ali Şahin', 'ali.sahin@istun.edu.tr', '$2y$10$stuvwxyzabc', '05367890123', 1),
('Zeynep Öztürk', 'zeynep.ozturk@istun.edu.tr', '$2y$10$defghijklmn', '05378901234', 1),
('Can Arslan', 'can.arslan@istun.edu.tr', '$2y$10$opqrstuvwxy', '05389012345', 1),
('Elif Yıldız', 'elif.yildiz@istun.edu.tr', '$2y$10$zabcdefghij', '05390123456', 1),
('Burak Koç', 'burak.koc@istun.edu.tr', '$2y$10$klmnopqrstu', '05301234567', 1),
('Selin Aydın', 'selin.aydin@istun.edu.tr', '$2y$10$vwxyzabcdef', '05312345678', 1),
('Deniz Moderator', 'deniz.mod@istun.edu.tr', '$2y$10$ghijklmnopq', '05323456789', 2),
('Ece Moderator', 'ece.mod@istun.edu.tr', '$2y$10$rstuvwxyzab', '05334567890', 2),
('Mert Moderator', 'mert.mod@istun.edu.tr', '$2y$10$cdefghijklm', '05345678901', 2),
('Admin User', 'admin@istun.edu.tr', '$2y$10$nopqrstuvwx', '05356789012', 3),
('System Admin', 'sysadmin@istun.edu.tr', '$2y$10$yzabcdefghi', '05367890123', 3);

-- Insert Categories (4 categories)
INSERT INTO Category (CategoryName) VALUES
('Books'),
('Electronics'),
('Furniture'),
('Dorm Equipment');

-- Insert Product_Listings (20 listings)
INSERT INTO Product_Listing (Title, Description, Price, Status, SellerID, CategoryID) VALUES
('Database Management Systems Book', 'Ramakrishnan & Gehrke, 3rd Edition. Excellent condition.', 150.00, 'Active', 1, 1),
('Data Structures and Algorithms', 'Cormen book, slightly used with highlights.', 200.00, 'Active', 2, 1),
('Calculus Textbook', 'Thomas Calculus 14th Edition. Like new.', 180.00, 'Active', 3, 1),
('Physics for Scientists', 'Serway & Jewett. Good condition.', 120.00, 'Sold', 4, 1),
('Linear Algebra Notes', 'Complete semester notes, organized and clear.', 50.00, 'Active', 5, 1),
('Laptop Dell XPS 13', 'i5 processor, 8GB RAM, 256GB SSD. 2 years old.', 8500.00, 'Active', 1, 2),
('iPhone 12', '64GB, Black color. Battery health 85%.', 12000.00, 'Active', 6, 2),
('Wireless Mouse Logitech', 'Barely used, includes USB receiver.', 150.00, 'Active', 7, 2),
('Mechanical Keyboard', 'RGB backlit, Cherry MX switches.', 800.00, 'Pending', 8, 2),
('iPad Air 2020', '256GB, Space Gray. Perfect condition with case.', 9000.00, 'Active', 9, 2),
('Study Desk', 'Wooden desk, 120x60cm. Very sturdy.', 600.00, 'Active', 2, 3),
('Office Chair', 'Ergonomic chair with lumbar support.', 400.00, 'Active', 3, 3),
('Bookshelf', '5-tier bookshelf, white color. Easy to assemble.', 350.00, 'Active', 4, 3),
('Small Sofa', 'Two-seater sofa, blue fabric. Clean and comfortable.', 1500.00, 'Active', 5, 3),
('Coffee Table', 'Glass top coffee table, modern design.', 300.00, 'Removed', 6, 3),
('Mini Fridge', '50L capacity, energy efficient. Perfect for dorm.', 1200.00, 'Active', 7, 4),
('Desk Lamp', 'LED desk lamp with adjustable brightness.', 100.00, 'Active', 8, 4),
('Laundry Basket', 'Large capacity, foldable design.', 80.00, 'Active', 9, 4),
('Electric Kettle', '1.7L kettle, stainless steel.', 150.00, 'Active', 10, 4),
('Full Length Mirror', 'Standing mirror with wooden frame.', 250.00, 'Active', 1, 4);

-- Insert Bids (25 bids)
INSERT INTO Bid (BidAmount, BuyerID, ListingID) VALUES
(140.00, 3, 1),
(145.00, 4, 1),
(150.00, 5, 1),
(190.00, 1, 2),
(195.00, 6, 2),
(170.00, 7, 3),
(175.00, 8, 3),
(110.00, 2, 4),
(115.00, 9, 4),
(120.00, 10, 4),
(8000.00, 2, 6),
(8300.00, 3, 6),
(11500.00, 4, 7),
(11800.00, 5, 7),
(140.00, 6, 8),
(750.00, 7, 9),
(8800.00, 8, 10),
(550.00, 9, 11),
(380.00, 10, 12),
(340.00, 1, 13),
(1450.00, 2, 14),
(1150.00, 3, 16),
(95.00, 4, 17),
(145.00, 5, 19),
(240.00, 6, 20);

-- Insert Messages (20 messages)
INSERT INTO Message (MessageText, SenderID, ReceiverID) VALUES
('Hi, is the Database book still available?', 3, 1),
('Yes, it is! Would you like to meet on campus?', 1, 3),
('Great! I can meet tomorrow at the library.', 3, 1),
('Is your laptop negotiable on price?', 2, 1),
('I can do 8200 TL if you can pick it up today.', 1, 2),
('Deal! Let me know your location.', 2, 1),
('Can I see more photos of the iPhone?', 4, 6),
('Sure, I will send them via email.', 6, 4),
('Is the desk still available?', 9, 2),
('Yes, when can you come see it?', 2, 9),
('Does the chair have any damage?', 10, 3),
('No damage, it is in perfect condition.', 3, 10),
('I am interested in the mini fridge.', 3, 7),
('It works perfectly, used for only 6 months.', 7, 3),
('Can you deliver the bookshelf?', 1, 4),
('Sorry, pickup only from campus.', 4, 1),
('Is the kettle still under warranty?', 8, 10),
('No warranty, but works perfectly.', 10, 8),
('Can I get a discount on the lamp?', 5, 8),
('I can do 90 TL final price.', 8, 5);

-- Insert Reviews (15 reviews)
INSERT INTO Review (Rating, Comment, ReviewerID, RevieweeID) VALUES
(5, 'Excellent seller! Item exactly as described.', 3, 1),
(5, 'Very professional and quick response.', 1, 3),
(4, 'Good transaction, item was good but slight delay.', 2, 1),
(5, 'Perfect condition laptop, highly recommend!', 3, 1),
(5, 'Honest seller, iPhone works great.', 4, 6),
(4, 'Good deal, desk is solid. Pickup was easy.', 9, 2),
(5, 'Chair is comfortable, great value!', 10, 3),
(5, 'Very helpful and friendly seller.', 1, 4),
(3, 'Item was okay but description could be better.', 5, 2),
(5, 'Fast communication and smooth transaction.', 6, 7),
(4, 'Good buyer, paid on time.', 7, 3),
(5, 'Excellent buyer, very professional.', 8, 5),
(5, 'Smooth transaction, would sell again.', 10, 8),
(4, 'Good buyer but negotiated a lot.', 1, 2),
(5, 'Quick and easy transaction!', 2, 9);

-- Insert Complaint_Reports (12 complaints)
INSERT INTO Complaint_Report (Reason, Status, ReporterID) VALUES
('Seller not responding to messages.', 'Pending', 3),
('Item description does not match actual product.', 'Reviewed', 4),
('Suspected fraudulent listing.', 'Resolved', 5),
('Inappropriate language in messages.', 'Reviewed', 6),
('Seller cancelled after agreement.', 'Pending', 7),
('Price increased after initial agreement.', 'Resolved', 8),
('Item was broken upon delivery.', 'Reviewed', 9),
('Seller blocked me without reason.', 'Pending', 10),
('Duplicate listing spam.', 'Resolved', 1),
('Misleading photos in listing.', 'Reviewed', 2),
('Buyer did not show up for meeting.', 'Pending', 3),
('Harassment in private messages.', 'Resolved', 4);

-- Insert Notifications (30 notifications)
INSERT INTO Notification (Content, IsRead, UserID) VALUES
('New bid placed on your Database book listing.', FALSE, 1),
('Your bid was accepted for Data Structures book!', TRUE, 1),
('New message from Ahmet Yılmaz.', FALSE, 3),
('Your listing has been approved by moderator.', TRUE, 1),
('Price alert: Similar items posted cheaper.', FALSE, 2),
('New bid placed on your Laptop listing.', TRUE, 1),
('Your bid was outbid on iPhone 12.', FALSE, 4),
('New message from Ayşe Kara.', TRUE, 1),
('Your review has been posted.', TRUE, 3),
('Someone saved your listing to their wishlist.', FALSE, 2),
('New bid placed on your Study Desk.', TRUE, 2),
('Your complaint has been reviewed.', TRUE, 4),
('New message from Can Arslan.', FALSE, 2),
('Your listing status changed to Sold.', TRUE, 4),
('New review received from Mehmet Demir.', FALSE, 1),
('Your bid of 8300 TL is currently highest.', TRUE, 3),
('New message from Fatma Çelik.', FALSE, 6),
('Your listing Chair has 2 new watchers.', FALSE, 3),
('Reminder: Respond to pending messages.', TRUE, 1),
('New bid placed on your iPhone listing.', TRUE, 6),
('Your account has been verified.', TRUE, 1),
('New message from Burak Koç.', FALSE, 4),
('Your listing has been removed by moderator.', TRUE, 6),
('New bid placed on your Mini Fridge.', FALSE, 7),
('Price drop alert on items you are watching.', TRUE, 8),
('Your complaint has been resolved.', TRUE, 5),
('New message from Selin Aydın.', FALSE, 10),
('Your bid was accepted for Coffee Table!', TRUE, 2),
('New review received from Elif Yıldız.', FALSE, 3),
('System maintenance scheduled for tomorrow.', FALSE, 1);

-- Insert Saved_Items (25 saved items)
INSERT INTO Saved_Item (UserID, ListingID) VALUES
(1, 2),
(1, 7),
(1, 11),
(2, 1),
(2, 6),
(2, 16),
(3, 3),
(3, 8),
(3, 12),
(4, 4),
(4, 9),
(4, 13),
(5, 5),
(5, 10),
(5, 14),
(6, 1),
(6, 11),
(7, 2),
(7, 12),
(8, 3),
(8, 13),
(9, 6),
(9, 17),
(10, 7),
(10, 18);

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Show table row counts
SELECT 'Role' AS TableName, COUNT(*) AS RowCount FROM Role
UNION ALL
SELECT 'User', COUNT(*) FROM User
UNION ALL
SELECT 'Category', COUNT(*) FROM Category
UNION ALL
SELECT 'Product_Listing', COUNT(*) FROM Product_Listing
UNION ALL
SELECT 'Bid', COUNT(*) FROM Bid
UNION ALL
SELECT 'Message', COUNT(*) FROM Message
UNION ALL
SELECT 'Review', COUNT(*) FROM Review
UNION ALL
SELECT 'Complaint_Report', COUNT(*) FROM Complaint_Report
UNION ALL
SELECT 'Notification', COUNT(*) FROM Notification
UNION ALL
SELECT 'Saved_Item', COUNT(*) FROM Saved_Item;

-- ============================================================================
-- END OF SQL FILE
-- ============================================================================