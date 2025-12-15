<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();
requireLogin();

// Only Moderators and Admins can access
if (!hasRole('Moderator') && !hasRole('Admin')) {
    header('Location: /index.php');
    exit;
}

$complaints = [];
$errorMessage = '';
$successMessage = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_id'], $_POST['new_status'])) {
    $complaintId = (int)$_POST['complaint_id'];
    $newStatus = cleanInput($_POST['new_status']);

    if (in_array($newStatus, ['Pending', 'Reviewed', 'Resolved'])) {
        try {
            $updateSql = <<<SQL
            UPDATE Complaint_Report
            SET Status = :status
            WHERE ComplaintID = :complaint_id;
            SQL;

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindValue(':status', $newStatus, PDO::PARAM_STR);
            $updateStmt->bindValue(':complaint_id', $complaintId, PDO::PARAM_INT);
            $updateStmt->execute();

            $successMessage = 'Complaint status updated successfully.';
        } catch (PDOException $e) {
            $errorMessage = 'Failed to update complaint: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

try {
    // Fetch all complaints
    $complaintsSql = <<<SQL
    SELECT 
        CR.ComplaintID,
        CR.Reason,
        CR.Status,
        CR.ComplaintDate,
        U.Name AS ReporterName,
        U.Email AS ReporterEmail
    FROM Complaint_Report CR
    INNER JOIN User U ON U.UserID = CR.ReporterID
    ORDER BY 
        FIELD(CR.Status, 'Pending', 'Reviewed', 'Resolved'),
        CR.ComplaintDate DESC;
    SQL;

    $complaintsStmt = $pdo->query($complaintsSql);
    $complaints = $complaintsStmt->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load complaints: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="container py-5">
    <h2 class="mb-4">Complaint Management</h2>

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

    <?php if (empty($complaints)): ?>
        <div class="alert alert-info">No complaints to review.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Reporter</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                        <tr>
                            <td><?php echo $complaint['ComplaintID']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($complaint['ReporterName'], ENT_QUOTES, 'UTF-8'); ?>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($complaint['ReporterEmail'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($complaint['Reason'], ENT_QUOTES, 'UTF-8')); ?></td>
                            <td><?php echo date('M d, Y', strtotime($complaint['ComplaintDate'])); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($complaint['Status']) {
                                        'Pending' => 'warning',
                                        'Reviewed' => 'info',
                                        'Resolved' => 'success',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo htmlspecialchars($complaint['Status'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['ComplaintID']; ?>">
                                    <select name="new_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                        <option value="">Change Status</option>
                                        <option value="Pending" <?php echo $complaint['Status'] === 'Pending' ? 'disabled' : ''; ?>>Pending</option>
                                        <option value="Reviewed" <?php echo $complaint['Status'] === 'Reviewed' ? 'disabled' : ''; ?>>Reviewed</option>
                                        <option value="Resolved" <?php echo $complaint['Status'] === 'Resolved' ? 'disabled' : ''; ?>>Resolved</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

