# SQL QUERY REPORTING

## Critical SQL Queries Used in Campus Second-Hand Marketplace

### 1. User Authentication Query
**Purpose:** Verify user credentials during login
**Type:** SELECT with JOIN
```sql
SELECT 
    U.UserID, U.Name, U.Email, U.Password,
    R.RoleID, R.RoleName
FROM User U
INNER JOIN Role R ON R.RoleID = U.RoleID
WHERE U.Email = :email
LIMIT 1;
```
**Tables Used:** User, Role
**Relationship:** 1:1 (User Has_Role)

---

### 2. Fetch Listings with Category and Seller Info
**Purpose:** Display all active listings with related information
**Type:** Multi-table JOIN
```sql
SELECT 
    PL.ListingID, PL.Title, PL.Description, PL.Price, PL.Status,
    C.CategoryName,
    U.Name AS SellerName, U.UserID AS SellerID,
    COUNT(DISTINCT B.BidID) AS BidCount
FROM Product_Listing PL
INNER JOIN Category C ON C.CategoryID = PL.CategoryID
INNER JOIN User U ON U.UserID = PL.SellerID
LEFT JOIN Bid B ON B.ListingID = PL.ListingID
WHERE PL.Status = 'Active'
GROUP BY PL.ListingID, PL.Title, PL.Description, PL.Price, PL.Status,
         C.CategoryName, U.Name, U.UserID
ORDER BY PL.ListingID DESC;
```
**Tables Used:** Product_Listing, Category, User, Bid
**Aggregate Function:** COUNT() for bid count

---

### 3. User Review Average Calculation
**Purpose:** Calculate average rating for a user
**Type:** Aggregate query
```sql
SELECT 
    AVG(Rating) AS AvgRating,
    COUNT(*) AS ReviewCount
FROM Review
WHERE RevieweeID = :user_id;
```
**Aggregate Functions:** AVG(), COUNT()

---

### 4. Unread Notification Count
**Purpose:** Get count of unread notifications for navbar badge
**Type:** Aggregate with filter
```sql
SELECT COUNT(*) as count 
FROM Notification 
WHERE UserID = :user_id AND IsRead = FALSE;
```

---

### 5. Accept Bid and Update Listing Status
**Purpose:** Mark listing as sold when bid is accepted
**Type:** Transaction with multiple UPDATEs
```sql
-- Transaction Start
BEGIN;

-- Update listing status
UPDATE Product_Listing
SET Status = 'Sold'
WHERE ListingID = :listing_id;

-- Create notification for winning bidder
INSERT INTO Notification (UserID, Content, IsRead, CreatedDate)
VALUES (:buyer_id, :notification_text, FALSE, CURRENT_TIMESTAMP);

-- Commit transaction
COMMIT;
```
**Transaction:** Ensures data consistency

---

### 6. Saved Items (Many-to-Many)
**Purpose:** Fetch user's saved listings via junction table
**Type:** M:N relationship query
```sql
SELECT 
    PL.ListingID, PL.Title, PL.Price, PL.Status,
    C.CategoryName,
    U.Name AS SellerName,
    SI.SavedDate
FROM Saved_Item SI
INNER JOIN Product_Listing PL ON PL.ListingID = SI.ListingID
INNER JOIN Category C ON C.CategoryID = PL.CategoryID
INNER JOIN User U ON U.UserID = PL.SellerID
WHERE SI.UserID = :user_id
ORDER BY SI.SavedDate DESC;
```
**Junction Table:** Saved_Item implements M:N relationship

---

### 7. Admin Dashboard Statistics
**Purpose:** Get platform overview statistics
**Type:** Multiple aggregate queries
```sql
-- Total users
SELECT COUNT(*) FROM User;

-- Total active listings
SELECT COUNT(*) FROM Product_Listing WHERE Status = 'Active';

-- Total bids
SELECT COUNT(*) FROM Bid;

-- Pending complaints
SELECT COUNT(*) FROM Complaint_Report WHERE Status = 'Pending';
```

---

### 8. Role-Based Access Control
**Purpose:** Check if user has specific role
**Type:** JOIN with filter
```sql
SELECT R.RoleName
FROM User U
INNER JOIN Role R ON R.RoleID = U.RoleID
WHERE U.UserID = :user_id
LIMIT 1;
```

---

## Query Optimization Techniques Used

1. **Prepared Statements:** All queries use PDO prepared statements to prevent SQL injection
2. **LIMIT Clauses:** Used where appropriate to limit result sets
3. **Indexes:** Primary keys (UserID, ListingID, etc.) are indexed automatically
4. **JOINs:** INNER JOIN for required relationships, LEFT JOIN for optional
5. **Aggregate Functions:** COUNT(), AVG() used for statistics

---

## Security Measures

1. **Parameterized Queries:** All user inputs are bound as parameters
2. **Type Casting:** Explicit type casting (PDO::PARAM_INT, PDO::PARAM_STR)
3. **htmlspecialchars():** Output escaping to prevent XSS
4. **password_hash():** bcrypt for password storage

