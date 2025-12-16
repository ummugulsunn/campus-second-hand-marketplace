<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$listingId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$listing = null;
$categories = [];
$errors = [];
$successMessage = '';

if ($listingId <= 0) {
    header('Location: /campus-marketplace/pages/profile.php');
    exit;
}

// Fetch listing and verify ownership
try {
    $listingSql = <<<SQL
    SELECT 
        ListingID, 
        Title, 
        Description, 
        Price, 
        Status, 
        SellerID, 
        CategoryID
    FROM Product_Listing
    WHERE ListingID = :listing_id
    LIMIT 1;
    SQL;

    $listingStmt = $pdo->prepare($listingSql);
    $listingStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
    $listingStmt->execute();
    $listing = $listingStmt->fetch();

    if (!$listing) {
        header('Location: /campus-marketplace/pages/profile.php');
        exit;
    }

    // Verify ownership
    if ((int)$listing['SellerID'] !== (int)$_SESSION['user_id']) {
        header('Location: /campus-marketplace/pages/profile.php');
        exit;
    }

    // Fetch categories
    $categorySql = <<<SQL
    SELECT CategoryID, CategoryName
    FROM Category
    ORDER BY CategoryName ASC;
    SQL;
    $categoryStmt = $pdo->query($categorySql);
    $categories = $categoryStmt->fetchAll();

} catch (PDOException $e) {
    $errors[] = 'Failed to load listing: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $title       = cleanInput($_POST['title'] ?? '');
    $description = cleanInput($_POST['description'] ?? '');
    $price       = cleanInput($_POST['price'] ?? '');
    $categoryId  = cleanInput($_POST['category_id'] ?? '');
    $status      = cleanInput($_POST['status'] ?? '');

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
    if (!in_array($status, ['Active', 'Sold', 'Pending', 'Removed'])) {
        $errors[] = 'Invalid status.';
    }

    if (empty($errors)) {
        try {
            $updateSql = <<<SQL
            UPDATE Product_Listing
            SET Title = :title,
                Description = :description,
                Price = :price,
                CategoryID = :category_id,
                Status = :status
            WHERE ListingID = :listing_id AND SellerID = :seller_id;
            SQL;

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindValue(':title', $title, PDO::PARAM_STR);
            $updateStmt->bindValue(':description', $description !== '' ? $description : null, $description !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $updateStmt->bindValue(':price', (float)$price, PDO::PARAM_STR);
            $updateStmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
            $updateStmt->bindValue(':status', $status, PDO::PARAM_STR);
            $updateStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
            $updateStmt->bindValue(':seller_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $updateStmt->execute();

            $_SESSION['success_message'] = 'Listing updated successfully!';
            header('Location: /campus-marketplace/pages/profile.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to update listing: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
                    <h3 class="card-title mb-4">Edit Listing</h3>

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

                    <?php if ($listing && empty($successMessage)): ?>
                        <form method="post" action="<?= base_url('/pages/edit-listing.php?id=' . $listingId) ?>" novalidate>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       value="<?php echo isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($description) ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : htmlspecialchars($listing['Description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price (TL) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" required
                                           value="<?php echo isset($price) ? htmlspecialchars($price, ENT_QUOTES, 'UTF-8') : htmlspecialchars($listing['Price'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['CategoryID']; ?>"
                                                <?php echo (isset($categoryId) ? $categoryId == $cat['CategoryID'] : $listing['CategoryID'] == $cat['CategoryID']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <?php 
                                    $statuses = ['Active', 'Pending', 'Sold', 'Removed'];
                                    foreach ($statuses as $s): 
                                    ?>
                                        <option value="<?php echo $s; ?>"
                                            <?php echo (isset($status) ? $status === $s : $listing['Status'] === $s) ? 'selected' : ''; ?>>
                                            <?php echo $s; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/campus-marketplace/pages/profile.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Listing</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

