<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = cleanInput($_POST['reason'] ?? '');

    // Validation
    if ($reason === '') {
        $errors[] = 'Please provide a reason for your complaint.';
    }

    if (empty($errors)) {
        try {
            $insertComplaintSql = <<<SQL
            INSERT INTO Complaint_Report (Reason, ReporterID)
            VALUES (:reason, :reporter_id);
            SQL;

            $insertComplaintStmt = $pdo->prepare($insertComplaintSql);
            $insertComplaintStmt->bindValue(':reason', $reason, PDO::PARAM_STR);
            $insertComplaintStmt->bindValue(':reporter_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $insertComplaintStmt->execute();

            $successMessage = 'Complaint submitted successfully! Our moderators will review it soon.';
            header('Refresh: 3; URL=/pages/listings.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to submit complaint: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
                    <h3 class="card-title mb-4">Report an Issue</h3>
                    <p class="text-muted mb-4">
                        Use this form to report inappropriate listings, users, or other concerns. 
                        Our moderators will review your complaint and take appropriate action.
                    </p>

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

                    <?php if (empty($successMessage)): ?>
                        <form method="post" action="/pages/create-complaint.php" novalidate>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Complaint <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reason" name="reason" rows="6" required
                                          placeholder="Please describe the issue in detail..."><?php echo isset($reason) ? htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                <small class="form-text text-muted">
                                    Be specific about the listing ID, user name, or nature of the problem.
                                </small>
                            </div>

                            <div class="alert alert-info">
                                <strong>Note:</strong> Complaints are reviewed by moderators typically within 24-48 hours. 
                                False or malicious reports may result in account restrictions.
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/pages/listings.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-danger">Submit Complaint</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

