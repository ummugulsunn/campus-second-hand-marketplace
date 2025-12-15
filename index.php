<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

startSession();

$dbStatusMessage = '';
$featuredListings = [];
$platformStats = [];

try {
    $healthCheckSql = <<<SQL
    SELECT 1 AS db_health_check;
    SQL;

    $stmt = $pdo->prepare($healthCheckSql);
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result && (int) $result['db_health_check'] === 1) {
        $dbStatusMessage = 'Database Connected Successfully';
    } else {
        $dbStatusMessage = 'Database connection check returned an unexpected result.';
    }
    
    // Fetch featured listings (latest 6 active listings)
    $featuredSql = <<<SQL
    SELECT 
        PL.ListingID,
        PL.Title,
        PL.Price,
        C.CategoryName
    FROM Product_Listing PL
    INNER JOIN Category C ON C.CategoryID = PL.CategoryID
    WHERE PL.Status = 'Active'
    ORDER BY PL.ListingID DESC
    LIMIT 6;
    SQL;
    
    $featuredStmt = $pdo->query($featuredSql);
    $featuredListings = $featuredStmt->fetchAll();
    
    // Platform statistics
    $platformStats['total_users'] = $pdo->query("SELECT COUNT(*) as total FROM User")->fetch()['total'];
    $platformStats['total_listings'] = $pdo->query("SELECT COUNT(*) as total FROM Product_Listing WHERE Status = 'Active'")->fetch()['total'];
    $platformStats['total_categories'] = $pdo->query("SELECT COUNT(*) as total FROM Category")->fetch()['total'];
    
} catch (PDOException $e) {
    $dbStatusMessage = 'Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/includes/header.php';
?>

<main class="container py-5">
    <section class="hero mb-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <p class="text-uppercase fw-semibold text-warning mb-2">Campus Second-Hand Marketplace</p>
                <h1 class="display-5 fw-bold mb-3">Buy, Sell, and Trade within your University</h1>
                <p class="lead mb-4">
                    Discover textbooks, electronics, dorm essentials, and more. Built with a secure,
                    student-focused experience on top of the approved campus_marketplace schema.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a class="btn btn-light btn-lg" href="/pages/register.php">Start Selling</a>
                    <a class="btn btn-outline-light btn-lg" href="/pages/listings.php">Browse Listings</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-end">
                <div class="card p-4 bg-light text-start">
                    <h5 class="fw-bold mb-3 section-heading">Why this marketplace?</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">• Campus-only community for trusted exchanges</li>
                        <li class="mb-2">• Fair listings with bids, messages, and reviews</li>
                        <li class="mb-2">• Verified roles: Student, Moderator, Admin</li>
                        <li class="mb-2">• Secure transactions with prepared statements</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Platform Stats -->
    <section class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h2 class="text-primary mb-0"><?php echo $platformStats['total_users'] ?? 0; ?>+</h2>
                    <p class="text-muted mb-0">Active Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h2 class="text-success mb-0"><?php echo $platformStats['total_listings'] ?? 0; ?>+</h2>
                    <p class="text-muted mb-0">Active Listings</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h2 class="text-warning mb-0"><?php echo $platformStats['total_categories'] ?? 0; ?></h2>
                    <p class="text-muted mb-0">Categories</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Listings -->
    <?php if (!empty($featuredListings)): ?>
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-heading mb-0">🔥 Latest Listings</h3>
                <a href="/pages/listings.php" class="btn btn-outline-primary btn-sm">View All →</a>
            </div>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-6 g-3">
                <?php foreach ($featuredListings as $listing): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm listing-card">
                            <div class="card-body p-3">
                                <span class="badge bg-secondary mb-2 small">
                                    <?php echo htmlspecialchars($listing['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <h6 class="card-title mb-2">
                                    <?php echo htmlspecialchars(strlen($listing['Title']) > 30 ? substr($listing['Title'], 0, 30) . '...' : $listing['Title'], ENT_QUOTES, 'UTF-8'); ?>
                                </h6>
                                <p class="h6 text-primary mb-2">₺<?php echo number_format((float)$listing['Price'], 2); ?></p>
                                <a href="/pages/listing-detail.php?id=<?php echo $listing['ListingID']; ?>" 
                                   class="btn btn-sm btn-outline-primary w-100">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- How It Works -->
    <section class="mb-5">
        <h3 class="section-heading text-center mb-4">How It Works</h3>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="display-4 mb-3">📝</div>
                        <h5 class="fw-bold">1. Create a Listing</h5>
                        <p class="text-muted">Post your item with title, price, and description</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="display-4 mb-3">💰</div>
                        <h5 class="fw-bold">2. Receive Bids</h5>
                        <p class="text-muted">Students place bids and you choose the best offer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="display-4 mb-3">🤝</div>
                        <h5 class="fw-bold">3. Complete Sale</h5>
                        <p class="text-muted">Meet on campus and complete the transaction safely</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="categories" class="row g-4">
        <div class="col-md-3">
            <div class="card h-100 p-3">
                <h5 class="fw-bold section-heading">Books</h5>
                <p class="mb-0 text-muted">Find textbooks, notes, and study materials.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 p-3">
                <h5 class="fw-bold section-heading">Electronics</h5>
                <p class="mb-0 text-muted">Laptops, phones, accessories, and more.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 p-3">
                <h5 class="fw-bold section-heading">Furniture</h5>
                <p class="mb-0 text-muted">Desks, chairs, shelves for dorm and home.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 p-3">
                <h5 class="fw-bold section-heading">Dorm Equipment</h5>
                <p class="mb-0 text-muted">Fridges, lamps, and essentials to settle in.</p>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


