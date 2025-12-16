-- MySQL dump 10.13  Distrib 9.5.0, for macos26.0 (arm64)
--
-- Host: localhost    Database: campus_marketplace
-- ------------------------------------------------------
-- Server version	9.5.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Bid`
--

DROP TABLE IF EXISTS `Bid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Bid` (
  `BidID` int NOT NULL AUTO_INCREMENT,
  `BidAmount` decimal(10,2) NOT NULL,
  `BidDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `BuyerID` int NOT NULL,
  `ListingID` int NOT NULL,
  PRIMARY KEY (`BidID`),
  KEY `fk_bid_buyer` (`BuyerID`),
  KEY `fk_bid_listing` (`ListingID`),
  CONSTRAINT `fk_bid_buyer` FOREIGN KEY (`BuyerID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bid_listing` FOREIGN KEY (`ListingID`) REFERENCES `Product_Listing` (`ListingID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Bid`
--

LOCK TABLES `Bid` WRITE;
/*!40000 ALTER TABLE `Bid` DISABLE KEYS */;
INSERT INTO `Bid` VALUES (1,140.00,'2025-12-15 23:15:12',3,1),(2,145.00,'2025-12-15 23:15:12',4,1),(3,150.00,'2025-12-15 23:15:12',5,1),(4,190.00,'2025-12-15 23:15:12',1,2),(5,195.00,'2025-12-15 23:15:12',6,2),(6,170.00,'2025-12-15 23:15:12',7,3),(7,175.00,'2025-12-15 23:15:12',8,3),(8,110.00,'2025-12-15 23:15:12',2,4),(9,115.00,'2025-12-15 23:15:12',9,4),(10,120.00,'2025-12-15 23:15:12',10,4),(12,8300.00,'2025-12-15 23:15:12',3,6),(13,11500.00,'2025-12-15 23:15:12',4,7),(14,11800.00,'2025-12-15 23:15:12',5,7),(15,140.00,'2025-12-15 23:15:12',6,8),(16,750.00,'2025-12-15 23:15:12',7,9),(17,8800.00,'2025-12-15 23:15:12',8,10),(18,550.00,'2025-12-15 23:15:12',9,11),(19,380.00,'2025-12-15 23:15:12',10,12),(20,340.00,'2025-12-15 23:15:12',1,13),(21,1450.00,'2025-12-15 23:15:12',2,14),(22,1150.00,'2025-12-15 23:15:12',3,16),(23,95.00,'2025-12-15 23:15:12',4,17),(24,145.00,'2025-12-15 23:15:12',5,19),(25,240.00,'2025-12-15 23:15:12',6,20),(26,195.02,'2025-12-16 00:21:27',1,2),(27,8888.00,'2025-12-16 14:43:37',1,17);
/*!40000 ALTER TABLE `Bid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Category`
--

DROP TABLE IF EXISTS `Category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Category` (
  `CategoryID` int NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  PRIMARY KEY (`CategoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Category`
--

LOCK TABLES `Category` WRITE;
/*!40000 ALTER TABLE `Category` DISABLE KEYS */;
INSERT INTO `Category` VALUES (1,'Books'),(2,'Electronics'),(3,'Furniture'),(4,'Dorm Equipment');
/*!40000 ALTER TABLE `Category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Complaint_Report`
--

DROP TABLE IF EXISTS `Complaint_Report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Complaint_Report` (
  `ComplaintID` int NOT NULL AUTO_INCREMENT,
  `Reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `ComplaintDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ReporterID` int NOT NULL,
  PRIMARY KEY (`ComplaintID`),
  KEY `fk_complaint_reporter` (`ReporterID`),
  CONSTRAINT `fk_complaint_reporter` FOREIGN KEY (`ReporterID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_complaint_status` CHECK ((`Status` in (_utf8mb4'Pending',_utf8mb4'Reviewed',_utf8mb4'Resolved')))
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Complaint_Report`
--

LOCK TABLES `Complaint_Report` WRITE;
/*!40000 ALTER TABLE `Complaint_Report` DISABLE KEYS */;
INSERT INTO `Complaint_Report` VALUES (1,'Seller not responding to messages.','Pending','2025-12-15 23:15:23',3),(2,'Item description does not match actual product.','Reviewed','2025-12-15 23:15:23',4),(3,'Suspected fraudulent listing.','Resolved','2025-12-15 23:15:23',5),(4,'Inappropriate language in messages.','Reviewed','2025-12-15 23:15:23',6),(5,'Seller cancelled after agreement.','Pending','2025-12-15 23:15:23',7),(6,'Price increased after initial agreement.','Resolved','2025-12-15 23:15:23',8),(7,'Item was broken upon delivery.','Reviewed','2025-12-15 23:15:23',9),(8,'Seller blocked me without reason.','Pending','2025-12-15 23:15:23',10),(9,'Duplicate listing spam.','Resolved','2025-12-15 23:15:23',1),(10,'Misleading photos in listing.','Reviewed','2025-12-15 23:15:23',2),(11,'Buyer did not show up for meeting.','Pending','2025-12-15 23:15:23',3),(12,'Harassment in private messages.','Resolved','2025-12-15 23:15:23',4);
/*!40000 ALTER TABLE `Complaint_Report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Message`
--

DROP TABLE IF EXISTS `Message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Message` (
  `MessageID` int NOT NULL AUTO_INCREMENT,
  `MessageText` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `SentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SenderID` int NOT NULL,
  `ReceiverID` int NOT NULL,
  PRIMARY KEY (`MessageID`),
  KEY `fk_message_sender` (`SenderID`),
  KEY `fk_message_receiver` (`ReceiverID`),
  CONSTRAINT `fk_message_receiver` FOREIGN KEY (`ReceiverID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_message_sender` FOREIGN KEY (`SenderID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Message`
--

LOCK TABLES `Message` WRITE;
/*!40000 ALTER TABLE `Message` DISABLE KEYS */;
INSERT INTO `Message` VALUES (1,'merhaba','2025-12-16 00:25:11',1,10),(2,'jjjjjj','2025-12-16 14:43:56',1,8);
/*!40000 ALTER TABLE `Message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Notification`
--

DROP TABLE IF EXISTS `Notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Notification` (
  `NotificationID` int NOT NULL AUTO_INCREMENT,
  `Content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `IsRead` tinyint(1) NOT NULL DEFAULT '0',
  `CreatedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UserID` int NOT NULL,
  PRIMARY KEY (`NotificationID`),
  KEY `fk_notification_user` (`UserID`),
  CONSTRAINT `fk_notification_user` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Notification`
--

LOCK TABLES `Notification` WRITE;
/*!40000 ALTER TABLE `Notification` DISABLE KEYS */;
INSERT INTO `Notification` VALUES (1,'New bid placed on your Database book listing.',0,'2025-12-15 23:15:29',1),(2,'Your bid was accepted for Data Structures book!',1,'2025-12-15 23:15:29',1),(3,'New message from Ahmet Yılmaz.',0,'2025-12-15 23:15:29',3),(4,'Your listing has been approved by moderator.',1,'2025-12-15 23:15:29',1),(5,'Price alert: Similar items posted cheaper.',0,'2025-12-15 23:15:29',2),(6,'New bid placed on your Laptop listing.',1,'2025-12-15 23:15:29',1),(7,'Your bid was outbid on iPhone 12.',0,'2025-12-15 23:15:29',4),(8,'New message from Ayşe Kara.',1,'2025-12-15 23:15:29',1),(9,'Your review has been posted.',1,'2025-12-15 23:15:29',3),(10,'Someone saved your listing to their wishlist.',0,'2025-12-15 23:15:29',2),(11,'New bid placed on your Study Desk.',1,'2025-12-15 23:15:29',2),(12,'Your complaint has been reviewed.',1,'2025-12-15 23:15:29',4),(13,'New message from Can Arslan.',0,'2025-12-15 23:15:29',2),(14,'Your listing status changed to Sold.',1,'2025-12-15 23:15:29',4),(15,'New review received from Mehmet Demir.',0,'2025-12-15 23:15:29',1),(16,'Your bid of 8300 TL is currently highest.',1,'2025-12-15 23:15:29',3),(17,'New message from Fatma Çelik.',0,'2025-12-15 23:15:29',6),(18,'Your listing Chair has 2 new watchers.',0,'2025-12-15 23:15:29',3),(19,'Reminder: Respond to pending messages.',1,'2025-12-15 23:15:29',1),(20,'New bid placed on your iPhone listing.',1,'2025-12-15 23:15:29',6),(21,'Your account has been verified.',1,'2025-12-15 23:15:29',1),(22,'New message from Burak Koç.',0,'2025-12-15 23:15:29',4),(23,'Your listing has been removed by moderator.',1,'2025-12-15 23:15:29',6),(24,'New bid placed on your Mini Fridge.',0,'2025-12-15 23:15:29',7),(25,'Price drop alert on items you are watching.',1,'2025-12-15 23:15:29',8),(26,'Your complaint has been resolved.',1,'2025-12-15 23:15:29',5),(27,'New message from Selin Aydın.',0,'2025-12-15 23:15:29',10),(28,'Your bid was accepted for Coffee Table!',1,'2025-12-15 23:15:29',2),(29,'New review received from Elif Yıldız.',0,'2025-12-15 23:15:29',3),(30,'System maintenance scheduled for tomorrow.',0,'2025-12-15 23:15:29',1),(31,'Your bid on \'Laptop Dell XPS 13\' has been declined by the seller.',0,'2025-12-16 00:22:10',2),(32,'Congratulations! Your bid of ₺8,300.00 on \'Laptop Dell XPS 13\' has been accepted!',0,'2025-12-16 00:22:18',3);
/*!40000 ALTER TABLE `Notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Product_Listing`
--

DROP TABLE IF EXISTS `Product_Listing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Product_Listing` (
  `ListingID` int NOT NULL AUTO_INCREMENT,
  `Title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Description` text COLLATE utf8mb4_unicode_ci,
  `Price` decimal(10,2) NOT NULL,
  `Status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `SellerID` int NOT NULL,
  `CategoryID` int NOT NULL,
  PRIMARY KEY (`ListingID`),
  KEY `fk_listing_seller` (`SellerID`),
  KEY `fk_listing_category` (`CategoryID`),
  CONSTRAINT `fk_listing_category` FOREIGN KEY (`CategoryID`) REFERENCES `Category` (`CategoryID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_listing_seller` FOREIGN KEY (`SellerID`) REFERENCES `User` (`UserID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_listing_status` CHECK ((`Status` in (_utf8mb4'Active',_utf8mb4'Sold',_utf8mb4'Pending',_utf8mb4'Removed')))
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Product_Listing`
--

LOCK TABLES `Product_Listing` WRITE;
/*!40000 ALTER TABLE `Product_Listing` DISABLE KEYS */;
INSERT INTO `Product_Listing` VALUES (1,'Database Management Systems Book','Ramakrishnan & Gehrke, 3rd Edition. Excellent condition.',150.00,'Active',1,1),(2,'Data Structures and Algorithms','Cormen book, slightly used with highlights.',200.00,'Active',2,1),(3,'Calculus Textbook','Thomas Calculus 14th Edition. Like new.',180.00,'Active',3,1),(4,'Physics for Scientists','Serway & Jewett. Good condition.',120.00,'Sold',4,1),(5,'Linear Algebra Notes','Complete semester notes, organized and clear.',50.00,'Active',5,1),(6,'Laptop Dell XPS 13','i5 processor, 8GB RAM, 256GB SSD. 2 years old.',8500.00,'Sold',1,2),(7,'iPhone 12','64GB, Black color. Battery health 85%.',12000.00,'Active',6,2),(8,'Wireless Mouse Logitech','Barely used, includes USB receiver.',150.00,'Active',7,2),(9,'Mechanical Keyboard','RGB backlit, Cherry MX switches.',800.00,'Pending',8,2),(10,'iPad Air 2020','256GB, Space Gray. Perfect condition with case.',9000.00,'Active',9,2),(11,'Study Desk','Wooden desk, 120x60cm. Very sturdy.',600.00,'Active',2,3),(12,'Office Chair','Ergonomic chair with lumbar support.',400.00,'Active',3,3),(13,'Bookshelf','5-tier bookshelf, white color. Easy to assemble.',350.00,'Active',4,3),(14,'Small Sofa','Two-seater sofa, blue fabric. Clean and comfortable.',1500.00,'Active',5,3),(15,'Coffee Table','Glass top coffee table, modern design.',300.00,'Removed',6,3),(16,'Mini Fridge','50L capacity, energy efficient. Perfect for dorm.',1200.00,'Active',7,4),(17,'Desk Lamp','LED desk lamp with adjustable brightness.',100.00,'Active',8,4),(18,'Laundry Basket','Large capacity, foldable design.',80.00,'Active',9,4),(19,'Electric Kettle','1.7L kettle, stainless steel.',150.00,'Active',10,4),(20,'Full Length Mirror','Standing mirror with wooden frame.',250.00,'Active',1,4),(21,'aaa','aaa',111.00,'Active',1,1),(22,'bbbbook','🌹',25.00,'Active',1,1),(23,'bbbbook','🌹',25.01,'Active',1,1);
/*!40000 ALTER TABLE `Product_Listing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Review`
--

DROP TABLE IF EXISTS `Review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Review` (
  `ReviewID` int NOT NULL AUTO_INCREMENT,
  `Rating` int NOT NULL,
  `Comment` text COLLATE utf8mb4_unicode_ci,
  `ReviewDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ReviewerID` int NOT NULL,
  `RevieweeID` int NOT NULL,
  PRIMARY KEY (`ReviewID`),
  KEY `fk_review_reviewer` (`ReviewerID`),
  KEY `fk_review_reviewee` (`RevieweeID`),
  CONSTRAINT `fk_review_reviewee` FOREIGN KEY (`RevieweeID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_review_reviewer` FOREIGN KEY (`ReviewerID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_review_rating` CHECK ((`Rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Review`
--

LOCK TABLES `Review` WRITE;
/*!40000 ALTER TABLE `Review` DISABLE KEYS */;
INSERT INTO `Review` VALUES (1,5,'Excellent seller! Item exactly as described.','2025-12-15 23:15:18',3,1),(2,5,'Very professional and quick response.','2025-12-15 23:15:18',1,3),(3,4,'Good transaction, item was good but slight delay.','2025-12-15 23:15:18',2,1),(4,5,'Perfect condition laptop, highly recommend!','2025-12-15 23:15:18',3,1),(5,5,'Honest seller, iPhone works great.','2025-12-15 23:15:18',4,6),(6,4,'Good deal, desk is solid. Pickup was easy.','2025-12-15 23:15:18',9,2),(7,5,'Chair is comfortable, great value!','2025-12-15 23:15:18',10,3),(8,5,'Very helpful and friendly seller.','2025-12-15 23:15:18',1,4),(9,3,'Item was okay but description could be better.','2025-12-15 23:15:18',5,2),(10,5,'Fast communication and smooth transaction.','2025-12-15 23:15:18',6,7),(11,4,'Good buyer, paid on time.','2025-12-15 23:15:18',7,3),(12,5,'Excellent buyer, very professional.','2025-12-15 23:15:18',8,5),(13,5,'Smooth transaction, would sell again.','2025-12-15 23:15:18',10,8),(14,4,'Good buyer but negotiated a lot.','2025-12-15 23:15:18',1,2),(15,5,'Quick and easy transaction!','2025-12-15 23:15:18',2,9),(16,4,NULL,'2025-12-16 14:43:46',1,8);
/*!40000 ALTER TABLE `Review` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Role`
--

DROP TABLE IF EXISTS `Role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Role` (
  `RoleID` int NOT NULL AUTO_INCREMENT,
  `RoleName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`RoleID`),
  CONSTRAINT `chk_role_name` CHECK ((`RoleName` in (_utf8mb4'Student',_utf8mb4'Moderator',_utf8mb4'Admin')))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Role`
--

LOCK TABLES `Role` WRITE;
/*!40000 ALTER TABLE `Role` DISABLE KEYS */;
INSERT INTO `Role` VALUES (1,'Student'),(2,'Moderator'),(3,'Admin');
/*!40000 ALTER TABLE `Role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Saved_Item`
--

DROP TABLE IF EXISTS `Saved_Item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Saved_Item` (
  `SavedItemID` int NOT NULL AUTO_INCREMENT,
  `SavedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UserID` int NOT NULL,
  `ListingID` int NOT NULL,
  PRIMARY KEY (`SavedItemID`),
  UNIQUE KEY `uq_user_listing` (`UserID`,`ListingID`),
  KEY `fk_saved_listing` (`ListingID`),
  CONSTRAINT `fk_saved_listing` FOREIGN KEY (`ListingID`) REFERENCES `Product_Listing` (`ListingID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_saved_user` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Saved_Item`
--

LOCK TABLES `Saved_Item` WRITE;
/*!40000 ALTER TABLE `Saved_Item` DISABLE KEYS */;
INSERT INTO `Saved_Item` VALUES (4,'2025-12-15 23:15:34',2,1),(5,'2025-12-15 23:15:34',2,6),(6,'2025-12-15 23:15:34',2,16),(7,'2025-12-15 23:15:34',3,3),(8,'2025-12-15 23:15:34',3,8),(9,'2025-12-15 23:15:34',3,12),(10,'2025-12-15 23:15:34',4,4),(11,'2025-12-15 23:15:34',4,9),(12,'2025-12-15 23:15:34',4,13),(13,'2025-12-15 23:15:34',5,5),(14,'2025-12-15 23:15:34',5,10),(15,'2025-12-15 23:15:34',5,14),(16,'2025-12-15 23:15:34',6,1),(17,'2025-12-15 23:15:34',6,11),(18,'2025-12-15 23:15:34',7,2),(19,'2025-12-15 23:15:34',7,12),(20,'2025-12-15 23:15:34',8,3),(21,'2025-12-15 23:15:34',8,13),(22,'2025-12-15 23:15:34',9,6),(23,'2025-12-15 23:15:34',9,17),(24,'2025-12-15 23:15:34',10,7),(25,'2025-12-15 23:15:34',10,18),(27,'2025-12-16 14:43:39',1,17),(28,'2025-12-16 14:44:56',1,18);
/*!40000 ALTER TABLE `Saved_Item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `User` (
  `UserID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `RoleID` int NOT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Email` (`Email`),
  KEY `fk_user_role` (`RoleID`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`RoleID`) REFERENCES `Role` (`RoleID`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `User`
--

LOCK TABLES `User` WRITE;
/*!40000 ALTER TABLE `User` DISABLE KEYS */;
INSERT INTO `User` VALUES (1,'Ahmet Yılmaz','ahmet.yilmaz@istun.edu.tr','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','05321234567',1),(2,'Ayşe Kara','ayse.kara@istun.edu.tr','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','05339876543',2),(3,'Mehmet Demir','mehmet.demir@istun.edu.tr','$2y$10$wxyzabcdefg','05345678901',1),(4,'Fatma Çelik','fatma.celik@istun.edu.tr','$2y$10$hijklmnopqr','05356789012',1),(5,'Ali Şahin','ali.sahin@istun.edu.tr','$2y$10$stuvwxyzabc','05367890123',1),(6,'Zeynep Öztürk','zeynep.ozturk@istun.edu.tr','$2y$10$defghijklmn','05378901234',1),(7,'Can Arslan','can.arslan@istun.edu.tr','$2y$10$opqrstuvwxy','05389012345',1),(8,'Elif Yıldız','elif.yildiz@istun.edu.tr','$2y$10$zabcdefghij','05390123456',1),(9,'Burak Koç','burak.koc@istun.edu.tr','$2y$10$klmnopqrstu','05301234567',1),(10,'Selin Aydın','selin.aydin@istun.edu.tr','$2y$10$vwxyzabcdef','05312345678',1),(11,'Deniz Moderator','deniz.mod@istun.edu.tr','$2y$10$ghijklmnopq','05323456789',2),(12,'Ece Moderator','ece.mod@istun.edu.tr','$2y$10$rstuvwxyzab','05334567890',2),(13,'Mert Moderator','mert.mod@istun.edu.tr','$2y$10$cdefghijklm','05345678901',2),(14,'Admin User','admin@istun.edu.tr','$2y$10$nopqrstuvwx','05356789012',3),(15,'System Admin','sysadmin@istun.edu.tr','$2y$10$yzabcdefghi','05367890123',3),(16,'Site Admin','admin@campus.local','$2y$12$ZT0NiZtvzwvCzP3nDJyTo.XlQZB6Vq7pI8PYLrRXJc2rnGz2.fbpW',NULL,3);
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-16 14:52:57
