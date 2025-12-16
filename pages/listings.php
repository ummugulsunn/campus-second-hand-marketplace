<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

$listings = [];
$categories = [];
$totalListings = 0;
$successMessage = '';

// Check for success message from session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Pagination
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Filters
$searchTerm = cleanInput($_GET['search'] ?? '');
$categoryFilter = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : 0;
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sortBy = cleanInput($_GET['sort'] ?? 'newest');

try {
    // Fetch all categories for filter dropdown
    $categorySql = <<<SQL
    SELECT CategoryID, CategoryName
    FROM Category
    ORDER BY CategoryName ASC;
    SQL;
    $categoryStmt = $pdo->query($categorySql);
    $categories = $categoryStmt->fetchAll();

    // Build WHERE clause dynamically
    $whereConditions = ["PL.Status = 'Active'"];
    $params = [];

    if ($searchTerm !== '') {
        $whereConditions[] = "PL.Title LIKE :search";
        $params[':search'] = '%' . $searchTerm . '%';
    }

    if ($categoryFilter > 0) {
        $whereConditions[] = "PL.CategoryID = :category_id";
        $params[':category_id'] = $categoryFilter;
    }
    
    if ($minPrice > 0) {
        $whereConditions[] = "PL.Price >= :min_price";
        $params[':min_price'] = $minPrice;
    }
    
    if ($maxPrice > 0) {
        $whereConditions[] = "PL.Price <= :max_price";
        $params[':max_price'] = $maxPrice;
    }

    $whereClause = implode(' AND ', $whereConditions);
    
    // Determine ORDER BY clause
    $orderClause = match($sortBy) {
        'price_low' => 'PL.Price ASC',
        'price_high' => 'PL.Price DESC',
        'oldest' => 'PL.ListingID ASC',
        'newest' => 'PL.ListingID DESC',
        default => 'PL.ListingID DESC'
    };

    // Count total listings (for pagination)
    $countSql = "SELECT COUNT(*) as total FROM Product_Listing PL WHERE $whereClause;";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalListings = (int)$countStmt->fetch()['total'];

    // Fetch listings with JOIN
    $listingsSql = <<<SQL
    SELECT 
        PL.ListingID,
        PL.Title,
        PL.Description,
        PL.Price,
        PL.Status,
        C.CategoryName,
        U.Name AS SellerName
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    INNER JOIN User U ON U.UserID = PL.SellerID
    WHERE $whereClause
    ORDER BY $orderClause
    LIMIT :limit OFFSET :offset;
    SQL;

    $listingsStmt = $pdo->prepare($listingsSql);
    foreach ($params as $key => $val) {
        $listingsStmt->bindValue($key, $val);
    }
    $listingsStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $listingsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listingsStmt->execute();
    $listings = $listingsStmt->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load listings: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

$totalPages = ceil($totalListings / $perPage);

// Helper function for pagination URLs
function buildPaginationUrl(int $pageNum, string $search, int $category, float $minPrice, float $maxPrice, string $sort): string {
    $params = ['page' => $pageNum];
    if ($search !== '') $params['search'] = $search;
    if ($category > 0) $params['category'] = $category;
    if ($minPrice > 0) $params['min_price'] = $minPrice;
    if ($maxPrice > 0) $params['max_price'] = $maxPrice;
    if ($sort !== 'newest') $params['sort'] = $sort;
    return '?' . http_build_query($params);
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/campus-marketplace/">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Browse Listings</li>
        </ol>
    </nav>
    
    <?php if ($successMessage !== ''): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(function() {
                showToast('success', '<?php echo addslashes($successMessage); ?>');
            }, 100);
        </script>
    <?php endif; ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Browse Listings</h2>
        <?php if (isLoggedIn() && hasRole('Student')): ?>
            <a href="/campus-marketplace/pages/add-listing.php" class="btn btn-primary">+ Add Listing</a>
        <?php endif; ?>
    </div>

    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?= base_url('/pages/listings.php') ?>" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" class="form-control" name="search" placeholder="Search by title..." 
                           value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                
                <!-- Category -->
                <div class="col-md-3">
                    <label class="form-label small">Category</label>
                    <select class="form-select" name="category">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['CategoryID']; ?>"
                                <?php echo $categoryFilter === (int)$cat['CategoryID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div class="col-md-2">
                    <label class="form-label small">Min Price (₺)</label>
                    <input type="number" step="0.01" class="form-control" name="min_price" placeholder="0" 
                           value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Max Price (₺)</label>
                    <input type="number" step="0.01" class="form-control" name="max_price" placeholder="∞" 
                           value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>">
                </div>
                
                <!-- Sort -->
                <div class="col-md-3">
                    <label class="form-label small">Sort By</label>
                    <select class="form-select" name="sort">
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
                
                <!-- Buttons -->
                <div class="col-md-4">
                    <label class="form-label small d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label small d-block">&nbsp;</label>
                    <a href="/campus-marketplace/pages/listings.php" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <!-- Results Count -->
    <p class="text-muted mb-3">
        Showing <?php echo count($listings); ?> of <?php echo $totalListings; ?> listings
    </p>

    <!-- Listings Grid -->
    <?php if (empty($listings)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h4>No Listings Found</h4>
            <p>We couldn't find any items matching your search criteria. Try adjusting your filters or browse all categories.</p>
            <?php if (isLoggedIn() && hasRole('Student')): ?>
                <a href="/campus-marketplace/pages/add-listing.php" class="btn btn-primary">Create Your First Listing</a>
            <?php else: ?>
                <a href="/campus-marketplace/pages/listings.php" class="btn btn-outline-primary">Clear Filters</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 mb-4">
            <?php foreach ($listings as $listing): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm listing-card">
                        <!-- Image Placeholder -->
                        <div class="listing-image-placeholder" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 4rem; color: white; opacity: 0.8;">
                            <?php 
                            $categoryEmoji = match($listing['CategoryName']) {
                                'Books' => '📚',
                                'Electronics' => '💻',
                                'Furniture' => '🪑',
                                'Dorm Equipment' => '🛏️',
                                default => '📦'
                            };
                            echo $categoryEmoji;
                            ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2 align-self-start">
                                <?php echo htmlspecialchars($listing['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?>
                            </h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php 
                                $desc = $listing['Description'] ?? '';
                                echo htmlspecialchars(
                                    strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc, 
                                    ENT_QUOTES, 
                                    'UTF-8'
                                ); 
                                ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0 text-primary">
                                    ₺<?php echo number_format((float)$listing['Price'], 2); ?>
                                </span>
                                <a href="/campus-marketplace/pages/listing-detail.php?id=<?php echo $listing['ListingID']; ?>" 
                                   class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                            <p class="text-muted small mb-0 mt-2">
                                Seller: <?php echo htmlspecialchars($listing['SellerName'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Listings pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($page - 1, $searchTerm, $categoryFilter, $minPrice, $maxPrice, $sortBy); ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo buildPaginationUrl($i, $searchTerm, $categoryFilter, $minPrice, $maxPrice, $sortBy); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo buildPaginationUrl($page + 1, $searchTerm, $categoryFilter, $minPrice, $maxPrice, $sortBy); ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

