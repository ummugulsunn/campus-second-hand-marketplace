<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = cleanInput($_POST['name'] ?? '');
    $email    = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone    = cleanInput($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Name, Email, and Password are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        try {
            $roleSql = <<<SQL
            SELECT RoleID
            FROM Role
            WHERE RoleName = :roleName
            LIMIT 1;
            SQL;
            $roleStmt = $pdo->prepare($roleSql);
            $roleStmt->bindValue(':roleName', 'Student', PDO::PARAM_STR);
            $roleStmt->execute();
            $roleRow = $roleStmt->fetch();

            if (!$roleRow) {
                $errors[] = 'Student role is not configured.';
            } else {
                $studentRoleId = (int) $roleRow['RoleID'];

                $emailCheckSql = <<<SQL
                SELECT UserID
                FROM User
                WHERE Email = :email
                LIMIT 1;
                SQL;
                $emailCheckStmt = $pdo->prepare($emailCheckSql);
                $emailCheckStmt->bindValue(':email', $email, PDO::PARAM_STR);
                $emailCheckStmt->execute();

                if ($emailCheckStmt->fetch()) {
                    $errors[] = 'An account with this email already exists.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $insertSql = <<<SQL
                    INSERT INTO User (Name, Email, Password, Phone, RoleID)
                    VALUES (:name, :email, :password, :phone, :role_id);
                    SQL;
                    $insertStmt = $pdo->prepare($insertSql);
                    $insertStmt->bindValue(':name', $name, PDO::PARAM_STR);
                    $insertStmt->bindValue(':email', $email, PDO::PARAM_STR);
                    $insertStmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
                    $insertStmt->bindValue(':phone', $phone !== '' ? $phone : null, $phone !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $insertStmt->bindValue(':role_id', $studentRoleId, PDO::PARAM_INT);
                    $insertStmt->execute();

                    $successMessage = 'Registration successful! Redirecting to login...';
                    header('Refresh: 2; URL=/pages/login.php');
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Registration failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title mb-4 text-center">Create your account</h3>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success">
                            <?php echo $successMessage; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/pages/register.php" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required minlength="3" maxlength="100" 
                                   value="<?php echo isset($name) ? cleanInput($name) : ''; ?>" 
                                   placeholder="Enter your full name">
                            <div class="invalid-feedback">
                                Please enter your full name (at least 3 characters).
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo isset($email) ? cleanInput($email) : ''; ?>" 
                                   placeholder="your.email@university.edu">
                            <div class="invalid-feedback">
                                Please enter a valid university email address.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6" 
                                   placeholder="At least 6 characters">
                            <div class="invalid-feedback">
                                Password must be at least 6 characters long.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-muted">(optional)</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" pattern="[0-9]{10,15}" 
                                   value="<?php echo isset($phone) ? cleanInput($phone) : ''; ?>" 
                                   placeholder="5551234567">
                            <small class="form-text text-muted">Enter 10-15 digits without spaces or dashes.</small>
                            <div class="invalid-feedback">
                                Please enter a valid phone number (10-15 digits).
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <strong>Create Account</strong>
                            </button>
                        </div>
                    </form>

                    <p class="mt-3 text-center">
                        Already have an account? <a href="/pages/login.php">Login here</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


