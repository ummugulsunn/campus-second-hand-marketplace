<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();
requireLogin();

// Only Admins can access
if (!hasRole('Admin')) {
    header('Location: /index.php');
    exit;
}

$categories = [];
$errorMessage = '';
$successMessage = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $categoryName = cleanInput($_POST['category_name'] ?? '');
    
    $validCategories = ['Books', 'Electronics', 'Furniture', 'Dorm Equipment'];
    
    if ($categoryName === '') {
        $errorMessage = 'Category name is required.';
    } elseif (!in_array($categoryName, $validCategories)) {
        $errorMessage = 'Invalid category. Allowed: Books, Electronics, Furniture, Dorm Equipment.';
    } else {
        try {
            // Check if category already exists
            $checkSql = "SELECT CategoryID FROM Category WHERE CategoryName = :name LIMIT 1;";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindValue(':name', $categoryName, PDO::PARAM_STR);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                $errorMessage = 'Category already exists.';
            } else {
                $insertSql = "INSERT INTO Category (CategoryName) VALUES (:name);";
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->bindValue(':name', $categoryName, PDO::PARAM_STR);
                $insertStmt->execute();
                
                $successMessage = 'Category added successfully!';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Failed to add category: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $categoryId = (int)$_POST['category_id'];
    $newName = cleanInput($_POST['new_name'] ?? '');
    
    $validCategories = ['Books', 'Electronics', 'Furniture', 'Dorm Equipment'];
    
    if ($newName === '') {
        $errorMessage = 'Category name is required.';
    } elseif (!in_array($newName, $validCategories)) {
        $errorMessage = 'Invalid category. Allowed: Books, Electronics, Furniture, Dorm Equipment.';
    } else {
        try {
            $updateSql = "UPDATE Category SET CategoryName = :name WHERE CategoryID = :id;";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindValue(':name', $newName, PDO::PARAM_STR);
            $updateStmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
            $updateStmt->execute();
            
            $successMessage = 'Category updated successfully!';
        } catch (PDOException $e) {
            $errorMessage = 'Failed to update category: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $categoryId = (int)$_POST['category_id'];
    
    try {
        // Check if category has listings
        $checkListingsSql = "SELECT COUNT(*) as count FROM Product_Listing WHERE CategoryID = :id;";
        $checkListingsStmt = $pdo->prepare($checkListingsSql);
        $checkListingsStmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
        $checkListingsStmt->execute();
        $listingCount = (int)$checkListingsStmt->fetch()['count'];
        
        if ($listingCount > 0) {
            $errorMessage = "Cannot delete category. It has {$listingCount} listing(s) associated with it.";
        } else {
            $deleteSql = "DELETE FROM Category WHERE CategoryID = :id;";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
            $deleteStmt->execute();
            
            $successMessage = 'Category deleted successfully!';
        }
    } catch (PDOException $e) {
        $errorMessage = 'Failed to delete category: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// Fetch all categories with listing count
try {
    $categoriesSql = <<<SQL
    SELECT 
        C.CategoryID,
        C.CategoryName,
        COUNT(PL.ListingID) as ListingCount
    FROM Category C
    LEFT JOIN Product_Listing PL ON PL.CategoryID = C.CategoryID
    GROUP BY C.CategoryID, C.CategoryName
    ORDER BY C.CategoryName ASC;
    SQL;
    
    $categories = $pdo->query($categoriesSql)->fetchAll();
} catch (PDOException $e) {
    $errorMessage = 'Failed to load categories: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Category Management</h2>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                + Add Category
            </button>
            <a href="/pages/admin/dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
        </div>
    </div>

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

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(function() {
                showToast('error', '<?php echo addslashes($errorMessage); ?>');
            }, 100);
        </script>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($categories)): ?>
            <div class="col-12">
                <div class="alert alert-info">No categories found. Add your first category!</div>
            </div>
        <?php else: ?>
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($category['CategoryName'], ENT_QUOTES, 'UTF-8'); ?></h5>
                            <p class="card-text text-muted">
                                <strong><?php echo $category['ListingCount']; ?></strong> listing(s)
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button class="btn btn-sm btn-outline-secondary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editCategoryModal"
                                    data-id="<?php echo $category['CategoryID']; ?>"
                                    data-name="<?php echo htmlspecialchars($category['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>">
                                Edit
                            </button>
                            
                            <?php if ($category['ListingCount'] == 0): ?>
                                <button class="btn btn-sm btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteCategoryModal"
                                        data-id="<?php echo $category['CategoryID']; ?>"
                                        data-name="<?php echo htmlspecialchars($category['CategoryName'], ENT_QUOTES, 'UTF-8'); ?>">
                                    Delete
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete category with listings">
                                    Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name</label>
                        <select class="form-select" id="category_name" name="category_name" required>
                            <option value="">-- Select Category --</option>
                            <option value="Books">Books</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Dorm Equipment">Dorm Equipment</option>
                        </select>
                        <small class="text-muted">Only predefined categories are allowed per project requirements.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="mb-3">
                        <label for="new_name" class="form-label">Category Name</label>
                        <select class="form-select" id="new_name" name="new_name" required>
                            <option value="">-- Select Category --</option>
                            <option value="Books">Books</option>
                            <option value="Electronics">Electronics</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Dorm Equipment">Dorm Equipment</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="category_id" id="delete_category_id">
                    <p>Are you sure you want to delete the category <strong id="delete_category_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit Modal - populate data
document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    
    document.getElementById('edit_category_id').value = id;
    document.getElementById('new_name').value = name;
});

// Delete Modal - populate data
document.getElementById('deleteCategoryModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    
    document.getElementById('delete_category_id').value = id;
    document.getElementById('delete_category_name').textContent = name;
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

