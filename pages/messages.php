<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$messages = [];
$errorMessage = '';
$successMessage = '';

// Standardized success feedback from previous actions (e.g., send-message)
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

try {
    // Fetch all messages (sent and received) for the current user
    $messagesSql = <<<SQL
    SELECT 
        M.MessageID,
        M.MessageText,
        M.SentDate,
        M.SenderID,
        M.ReceiverID,
        Sender.Name AS SenderName,
        Receiver.Name AS ReceiverName
    FROM Message M
    INNER JOIN User Sender ON Sender.UserID = M.SenderID
    INNER JOIN User Receiver ON Receiver.UserID = M.ReceiverID
    WHERE M.SenderID = :user_id_sender OR M.ReceiverID = :user_id_receiver
    ORDER BY M.SentDate DESC;
    SQL;

    $messagesStmt = $pdo->prepare($messagesSql);
    $messagesStmt->bindValue(':user_id_sender', $_SESSION['user_id'], PDO::PARAM_INT);
    $messagesStmt->bindValue(':user_id_receiver', $_SESSION['user_id'], PDO::PARAM_INT);
    $messagesStmt->execute();
    $messages = $messagesStmt->fetchAll();

} catch (PDOException $e) {
    $errorMessage = 'Failed to load messages: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Messages</h2>
        <a href="/pages/send-message.php" class="btn btn-primary">+ New Message</a>
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
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <?php if (empty($messages)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">💬</div>
            <h4>No Messages Yet</h4>
            <p>Start a conversation with sellers to negotiate prices and arrange meetups!</p>
            <a href="/pages/listings.php" class="btn btn-primary">Browse Listings</a>
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($messages as $msg): ?>
                <?php 
                $isSender = (int)$msg['SenderID'] === (int)$_SESSION['user_id'];
                $otherUser = $isSender ? $msg['ReceiverName'] : $msg['SenderName'];
                $otherUserId = $isSender ? $msg['ReceiverID'] : $msg['SenderID'];
                ?>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">
                            <?php if ($isSender): ?>
                                <span class="badge bg-primary me-2">Sent</span>
                                To: <?php echo htmlspecialchars($otherUser, ENT_QUOTES, 'UTF-8'); ?>
                            <?php else: ?>
                                <span class="badge bg-success me-2">Received</span>
                                From: <?php echo htmlspecialchars($otherUser, ENT_QUOTES, 'UTF-8'); ?>
                            <?php endif; ?>
                        </h6>
                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($msg['SentDate'])); ?></small>
                    </div>
                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($msg['MessageText'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php if (!$isSender): ?>
                        <a href="/pages/send-message.php?to=<?php echo $otherUserId; ?>" class="btn btn-sm btn-outline-primary">Reply</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

