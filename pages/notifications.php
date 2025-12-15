<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$notifications = [];
$errorMessage = '';

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notificationId = (int)$_POST['notification_id'];

    try {
        $markReadSql = <<<SQL
        UPDATE Notification
        SET IsRead = TRUE
        WHERE NotificationID = :notification_id AND UserID = :user_id;
        SQL;

        $markReadStmt = $pdo->prepare($markReadSql);
        $markReadStmt->bindValue(':notification_id', $notificationId, PDO::PARAM_INT);
        $markReadStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $markReadStmt->execute();
    } catch (PDOException $e) {
        // Silent fail
    }
}

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    try {
        $markAllReadSql = <<<SQL
        UPDATE Notification
        SET IsRead = TRUE
        WHERE UserID = :user_id AND IsRead = FALSE;
        SQL;

        $markAllReadStmt = $pdo->prepare($markAllReadSql);
        $markAllReadStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $markAllReadStmt->execute();
    } catch (PDOException $e) {
        // Silent fail
    }
}

try {
    // Fetch notifications for current user
    $notificationsSql = <<<SQL
    SELECT 
        NotificationID,
        Content,
        IsRead,
        CreatedDate
    FROM Notification
    WHERE UserID = :user_id
    ORDER BY CreatedDate DESC;
    SQL;

    $notificationsStmt = $pdo->prepare($notificationsSql);
    $notificationsStmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $notificationsStmt->execute();
    $notifications = $notificationsStmt->fetchAll();

    // Count unread
    $unreadCount = count(array_filter($notifications, fn($n) => !$n['IsRead']));

} catch (PDOException $e) {
    $errorMessage = 'Failed to load notifications: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            Notifications 
            <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                <span class="badge bg-danger"><?php echo $unreadCount; ?> new</span>
            <?php endif; ?>
        </h2>
        <?php if (isset($unreadCount) && $unreadCount > 0): ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="mark_all_read" value="1">
                <button type="submit" class="btn btn-outline-primary btn-sm">Mark All as Read</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if (empty($notifications)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔔</div>
            <h4>No Notifications</h4>
            <p>You're all caught up! We'll notify you about new bids, messages, and listing updates.</p>
            <a href="/pages/listings.php" class="btn btn-primary">Browse Listings</a>
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifications as $notification): ?>
                <div class="list-group-item <?php echo !$notification['IsRead'] ? 'list-group-item-primary' : ''; ?>">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <?php if (!$notification['IsRead']): ?>
                                <span class="badge bg-danger me-2">New</span>
                            <?php endif; ?>
                            <p class="mb-1"><?php echo htmlspecialchars($notification['Content'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <small class="text-muted">
                                <?php echo date('M d, Y H:i', strtotime($notification['CreatedDate'])); ?>
                            </small>
                        </div>
                        <?php if (!$notification['IsRead']): ?>
                            <form method="post" class="ms-3">
                                <input type="hidden" name="notification_id" value="<?php echo $notification['NotificationID']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Mark Read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

