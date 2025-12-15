<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$receiverId = isset($_GET['to']) && is_numeric($_GET['to']) ? (int)$_GET['to'] : 0;
$listingId = isset($_GET['listing_id']) && is_numeric($_GET['listing_id']) ? (int)$_GET['listing_id'] : 0;
$receiver = null;
$listing = null;
$errors = [];
$successMessage = '';

// Fetch receiver info if provided
if ($receiverId > 0) {
    try {
        $receiverSql = <<<SQL
        SELECT UserID, Name, Email
        FROM User
        WHERE UserID = :user_id
        LIMIT 1;
        SQL;

        $receiverStmt = $pdo->prepare($receiverSql);
        $receiverStmt->bindValue(':user_id', $receiverId, PDO::PARAM_INT);
        $receiverStmt->execute();
        $receiver = $receiverStmt->fetch();

        if (!$receiver) {
            $errors[] = 'Recipient not found.';
        }

        // Check if user is trying to message themselves
        if ($receiver && (int)$receiver['UserID'] === (int)$_SESSION['user_id']) {
            $errors[] = 'You cannot send a message to yourself.';
        }
    } catch (PDOException $e) {
        $errors[] = 'Failed to load recipient: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

// Fetch listing info if provided (for context)
if ($listingId > 0) {
    try {
        $listingSql = <<<SQL
        SELECT ListingID, Title
        FROM Product_Listing
        WHERE ListingID = :listing_id
        LIMIT 1;
        SQL;

        $listingStmt = $pdo->prepare($listingSql);
        $listingStmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $listingStmt->execute();
        $listing = $listingStmt->fetch();
    } catch (PDOException $e) {
        // Non-critical, just for context
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $messageText = cleanInput($_POST['message_text'] ?? '');
    $toUserId = isset($_POST['to_user_id']) && is_numeric($_POST['to_user_id']) ? (int)$_POST['to_user_id'] : 0;

    if ($messageText === '') {
        $errors[] = 'Message cannot be empty.';
    }

    if ($toUserId <= 0) {
        $errors[] = 'Please select a recipient.';
    }

    if ((int)$toUserId === (int)$_SESSION['user_id']) {
        $errors[] = 'You cannot send a message to yourself.';
    }

    if (empty($errors)) {
        try {
            $insertMessageSql = <<<SQL
            INSERT INTO Message (MessageText, SenderID, ReceiverID)
            VALUES (:message_text, :sender_id, :receiver_id);
            SQL;

            $insertMessageStmt = $pdo->prepare($insertMessageSql);
            $insertMessageStmt->bindValue(':message_text', $messageText, PDO::PARAM_STR);
            $insertMessageStmt->bindValue(':sender_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $insertMessageStmt->bindValue(':receiver_id', $toUserId, PDO::PARAM_INT);
            $insertMessageStmt->execute();

            // Standardized success feedback: store message in session and redirect
            $_SESSION['success_message'] = 'Message sent successfully!';
            header('Location: /pages/messages.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to send message: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
                    <h3 class="card-title mb-4">Send Message</h3>

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

                    <?php if (empty($successMessage)): ?>
                        <?php if ($listing): ?>
                            <div class="alert alert-info">
                                Regarding: <strong><?php echo htmlspecialchars($listing['Title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="/pages/send-message.php" novalidate>
                            <?php if ($receiver): ?>
                                <input type="hidden" name="to_user_id" value="<?php echo $receiver['UserID']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">To:</label>
                                    <input type="text" class="form-control" readonly 
                                           value="<?php echo htmlspecialchars($receiver['Name'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($receiver['Email'], ENT_QUOTES, 'UTF-8'); ?>)">
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    Please specify a recipient by adding ?to=USER_ID to the URL.
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="message_text" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message_text" name="message_text" rows="6" required
                                          placeholder="Type your message here..."><?php echo isset($messageText) ? htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/pages/messages.php" class="btn btn-secondary">Cancel</a>
                                <?php if ($receiver && empty($errors)): ?>
                                    <button type="submit" class="btn btn-primary">Send Message</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

