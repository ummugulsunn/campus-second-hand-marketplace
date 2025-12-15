<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

// Only Students can create listings
if (!hasRole('Student')) {
    header('Location: /index.php');
    exit;
}

$errors = [];
$successMessage = '';

// Check for success message from session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
$categories = [];

// Fetch categories for the dropdown
try {
    $categorySql = <<<SQL
    SELECT CategoryID, CategoryName
    FROM Category
    ORDER BY CategoryName ASC;
    SQL;
    $categoryStmt = $pdo->query($categorySql);
    $categories = $categoryStmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Failed to load categories: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = cleanInput($_POST['title'] ?? '');
    $description = cleanInput($_POST['description'] ?? '');
    $price       = cleanInput($_POST['price'] ?? '');
    $categoryId  = cleanInput($_POST['category_id'] ?? '');

    // Validation
    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($price === '' || !is_numeric($price) || (float)$price <= 0) {
        $errors[] = 'Please enter a valid price (greater than 0).';
    }
    if ($categoryId === '' || !is_numeric($categoryId)) {
        $errors[] = 'Please select a category.';
    }

    if (empty($errors)) {
        try {
            $sellerId = $_SESSION['user_id'] ?? 0;

            $insertSql = <<<SQL
            INSERT INTO Product_Listing (Title, Description, Price, Status, SellerID, CategoryID)
            VALUES (:title, :description, :price, 'Active', :seller_id, :category_id);
            SQL;

            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->bindValue(':title', $title, PDO::PARAM_STR);
            $insertStmt->bindValue(':description', $description !== '' ? $description : null, $description !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $insertStmt->bindValue(':price', (float)$price, PDO::PARAM_STR);
            $insertStmt->bindValue(':seller_id', $sellerId, PDO::PARAM_INT);
            $insertStmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
            $insertStmt->execute();

            $_SESSION['success_message'] = 'Listing created successfully!';
            header('Location: /pages/listings.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to create listing: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4">Create New Listing</h3>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <script>
                            setTimeout(function() {
                                showToast('error', '<?php echo addslashes(implode(' ', $errors)); ?>');
                            }, 100);
                        </script>
                    <?php endif; ?>

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

                    <form method="post" action="/pages/add-listing.php" id="addListingForm" class="needs-validation" data-autosave novalidate>
                        <div class="mb-3">
                            <label for="title" class="form-label">Listing Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required minlength="5" maxlength="200"
                                   value="<?php echo isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : ''; ?>"
                                   placeholder="e.g., Database Management Systems Book - Like New">
                            <div class="invalid-feedback">
                                Please enter a descriptive title (5-200 characters).
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-muted">(optional but recommended)</span></label>
                            <textarea class="form-control" id="description" name="description" rows="5" maxlength="2000"
                                      placeholder="• What condition is it in?&#10;• Any defects or issues?&#10;• Why are you selling?&#10;• When/where can buyer pick it up?"><?php echo isset($description) ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                            <small class="form-text text-muted">A detailed description helps buyers make informed decisions.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price (₺) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₺</span>
                                    <input type="number" step="0.01" min="0.01" max="999999.99" class="form-control" id="price" name="price" required
                                           value="<?php echo isset($price) ? htmlspecialchars($price, ENT_QUOTES, 'UTF-8') : ''; ?>"
                                           placeholder="0.00">
                                </div>
                                <div class="invalid-feedback">
                                    Please enter a valid price (between ₺0.01 and ₺999,999.99).
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">-- Select a Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['CategoryID']; ?>"
                                            <?php echo (isset($categoryId) && $categoryId == $cat['CategoryID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a category.
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>💡 Tip:</strong> Your draft is automatically saved. Complete listings get more views and bids!
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/pages/listings.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <strong>Create Listing</strong>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

