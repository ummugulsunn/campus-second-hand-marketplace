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

$users = [];
$roles = [];
$errorMessage = '';
$successMessage = '';

// Standardized success feedback from session (e.g., role updates)
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_role_id'])) {
    $userId = (int)$_POST['user_id'];
    $newRoleId = (int)$_POST['new_role_id'];

    // Prevent admin from changing their own role
    if ($userId === (int)$_SESSION['user_id']) {
        $errorMessage = 'You cannot change your own role.';
    } else {
        try {
            $updateRoleSql = <<<SQL
            UPDATE User
            SET RoleID = :role_id
            WHERE UserID = :user_id;
            SQL;

            $updateRoleStmt = $pdo->prepare($updateRoleSql);
            $updateRoleStmt->bindValue(':role_id', $newRoleId, PDO::PARAM_INT);
            $updateRoleStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $updateRoleStmt->execute();

            $_SESSION['success_message'] = 'User role updated successfully.';
            header('Location: /pages/admin/users.php');
            exit;
        } catch (PDOException $e) {
            $errorMessage = 'Failed to update role: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

try {
    // Fetch all roles
    $rolesSql = "SELECT RoleID, RoleName FROM Role ORDER BY RoleID;";
    $roles = $pdo->query($rolesSql)->fetchAll();

    // Fetch all users with role info
    $usersSql = <<<SQL
    SELECT 
        U.UserID,
        U.Name,
        U.Email,
        U.Phone,
        R.RoleID,
        R.RoleName
    FROM User U
    INNER JOIN Role R ON R.RoleID = U.RoleID
    ORDER BY R.RoleID, U.Name;
    SQL;

    $users = $pdo->query($usersSql)->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load users: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Management</h2>
        <a href="/pages/admin/dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
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

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['UserID']; ?></td>
                        <td>
                            <a href="/pages/admin/user-detail.php?id=<?php echo $user['UserID']; ?>">
                                <?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo $user['Phone'] ? htmlspecialchars($user['Phone'], ENT_QUOTES, 'UTF-8') : '<em>N/A</em>'; ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo match($user['RoleName']) {
                                    'Admin' => 'danger',
                                    'Moderator' => 'warning',
                                    'Student' => 'primary',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo htmlspecialchars($user['RoleName'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ((int)$user['UserID'] === (int)$_SESSION['user_id']): ?>
                                <span class="text-muted">You</span>
                            <?php else: ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                    <select name="new_role_id" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                        <option value="">Change Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['RoleID']; ?>"
                                                <?php echo (int)$role['RoleID'] === (int)$user['RoleID'] ? 'disabled' : ''; ?>>
                                                <?php echo htmlspecialchars($role['RoleName'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

