<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errorMessage = 'Email and Password are required.';
    } else {
        try {
            $userSql = <<<SQL
            SELECT U.UserID, U.Name, U.Password, U.RoleID, R.RoleName
            FROM User U
            INNER JOIN Role R ON R.RoleID = U.RoleID
            WHERE U.Email = :email
            LIMIT 1;
            SQL;
            $userStmt = $pdo->prepare($userSql);
            $userStmt->bindValue(':email', $email, PDO::PARAM_STR);
            $userStmt->execute();
            $user = $userStmt->fetch();

            if ($user && password_verify($password, $user['Password'])) {
                loginUser(
                    (int) $user['UserID'],
                    (string) $user['Name'],
                    (int) $user['RoleID'],
                    (string) $user['RoleName']
                );
                header('Location: /index.php');
                exit;
            } else {
                $errorMessage = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Login failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4 text-center">Welcome back</h3>

                    <?php if ($errorMessage !== ''): ?>
                        <div class="alert alert-danger">
                            <?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/pages/login.php" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus
                                   value="<?php echo isset($email) ? cleanInput($email) : ''; ?>" 
                                   placeholder="your.email@university.edu">
                            <div class="invalid-feedback">
                                Please enter your email address.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required 
                                   placeholder="Enter your password">
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <strong>Sign In</strong>
                            </button>
                        </div>
                    </form>

                    <p class="mt-3 text-center">
                        New here? <a href="/pages/register.php">Create an account</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


