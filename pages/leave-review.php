<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
requireLogin();

$revieweeId = isset($_GET['for']) && is_numeric($_GET['for']) ? (int)$_GET['for'] : 0;
$reviewee = null;
$errors = [];
$successMessage = '';

if ($revieweeId <= 0 || $revieweeId === (int)$_SESSION['user_id']) {
    header('Location: /pages/listings.php');
    exit;
}

try {
    // Fetch reviewee info
    $userSql = <<<SQL
    SELECT UserID, Name, Email
    FROM User
    WHERE UserID = :user_id
    LIMIT 1;
    SQL;

    $userStmt = $pdo->prepare($userSql);
    $userStmt->bindValue(':user_id', $revieweeId, PDO::PARAM_INT);
    $userStmt->execute();
    $reviewee = $userStmt->fetch();

    if (!$reviewee) {
        header('Location: /pages/listings.php');
        exit;
    }

    // Check if user already reviewed this person
    $existingReviewSql = <<<SQL
    SELECT ReviewID
    FROM Review
    WHERE ReviewerID = :reviewer_id AND RevieweeID = :reviewee_id
    LIMIT 1;
    SQL;

    $existingReviewStmt = $pdo->prepare($existingReviewSql);
    $existingReviewStmt->bindValue(':reviewer_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $existingReviewStmt->bindValue(':reviewee_id', $revieweeId, PDO::PARAM_INT);
    $existingReviewStmt->execute();

    if ($existingReviewStmt->fetch()) {
        $errors[] = 'You have already reviewed this user.';
    }

    // Check if user has interacted with reviewee (placed bid on their listing OR exchanged messages)
    $hasInteraction = false;
    
    // Check for bids on seller's listings
    $bidCheckSql = <<<SQL
    SELECT 1 FROM Bid B
    INNER JOIN Product_Listing PL ON PL.ListingID = B.ListingID
    WHERE B.BuyerID = :buyer_id AND PL.SellerID = :seller_id
    LIMIT 1;
    SQL;
    
    $bidCheckStmt = $pdo->prepare($bidCheckSql);
    $bidCheckStmt->bindValue(':buyer_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $bidCheckStmt->bindValue(':seller_id', $revieweeId, PDO::PARAM_INT);
    $bidCheckStmt->execute();
    
    if ($bidCheckStmt->fetch()) {
        $hasInteraction = true;
    }
    
    // If no bid interaction, check for messages
    if (!$hasInteraction) {
        $messageCheckSql = <<<SQL
        SELECT 1 FROM Message
        WHERE (SenderID = :user1 AND ReceiverID = :user2)
           OR (SenderID = :user2 AND ReceiverID = :user1)
        LIMIT 1;
        SQL;
        
        $messageCheckStmt = $pdo->prepare($messageCheckSql);
        $messageCheckStmt->bindValue(':user1', $_SESSION['user_id'], PDO::PARAM_INT);
        $messageCheckStmt->bindValue(':user2', $revieweeId, PDO::PARAM_INT);
        $messageCheckStmt->execute();
        
        if ($messageCheckStmt->fetch()) {
            $hasInteraction = true;
        }
    }
    
    if (!$hasInteraction) {
        $errors[] = 'You can only review users you have interacted with (placed bids on their listings or exchanged messages).';
    }

} catch (PDOException $e) {
    $errors[] = 'Failed to load user: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $rating = isset($_POST['rating']) && is_numeric($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = cleanInput($_POST['comment'] ?? '');

    // Validation
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Please select a rating between 1 and 5.';
    }

    if (empty($errors)) {
        try {
            $insertReviewSql = <<<SQL
            INSERT INTO Review (Rating, Comment, ReviewerID, RevieweeID)
            VALUES (:rating, :comment, :reviewer_id, :reviewee_id);
            SQL;

            $insertReviewStmt = $pdo->prepare($insertReviewSql);
            $insertReviewStmt->bindValue(':rating', $rating, PDO::PARAM_INT);
            $insertReviewStmt->bindValue(':comment', $comment !== '' ? $comment : null, $comment !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $insertReviewStmt->bindValue(':reviewer_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $insertReviewStmt->bindValue(':reviewee_id', $revieweeId, PDO::PARAM_INT);
            $insertReviewStmt->execute();

            $successMessage = 'Review submitted successfully! Redirecting...';
            header('Refresh: 2; URL=/pages/listings.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to submit review: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
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
                    <h3 class="card-title mb-4">Leave a Review</h3>

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

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-info">
                            <h5 class="alert-heading">📝 How to Review a User</h5>
                            <p>You can only review users you have interacted with. To be able to leave a review, you need to:</p>
                            <ul class="mb-3">
                                <li><strong>Place a bid</strong> on one of their listings, OR</li>
                                <li><strong>Exchange messages</strong> with them</li>
                            </ul>
                            <hr>
                            <p class="mb-0">
                                <small class="text-muted">
                                    This helps ensure reviews are based on actual transactions and keeps our marketplace trustworthy.
                                </small>
                            </p>
                        </div>
                        <div class="d-grid">
                            <a href="/pages/listings.php" class="btn btn-primary">Browse Listings</a>
                        </div>
                    <?php elseif ($reviewee && empty($successMessage)): ?>
                        <div class="mb-4">
                            <p><strong>Reviewing:</strong> <?php echo htmlspecialchars($reviewee['Name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>

                        <form method="post" action="/pages/leave-review.php?for=<?php echo $revieweeId; ?>" class="needs-validation" novalidate>
                            <input type="hidden" name="rating" id="ratingValue" value="<?php echo isset($rating) ? $rating : ''; ?>" required>
                            
                            <div class="mb-4">
                                <label class="form-label d-block">Rating <span class="text-danger">*</span></label>
                                <div class="star-rating" style="font-size: 3rem; cursor: pointer;">
                                    <span class="star" data-rating="1">☆</span>
                                    <span class="star" data-rating="2">☆</span>
                                    <span class="star" data-rating="3">☆</span>
                                    <span class="star" data-rating="4">☆</span>
                                    <span class="star" data-rating="5">☆</span>
                                </div>
                                <div class="invalid-feedback d-block" id="ratingError" style="display: none !important;">
                                    Please select a rating.
                                </div>
                                <small class="form-text text-muted d-block mt-2" id="ratingText">Click to rate</small>
                            </div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const stars = document.querySelectorAll('.star');
                                    const ratingValue = document.getElementById('ratingValue');
                                    const ratingText = document.getElementById('ratingText');
                                    const ratingTexts = {
                                        1: '1 Star - Poor',
                                        2: '2 Stars - Fair',
                                        3: '3 Stars - Good',
                                        4: '4 Stars - Very Good',
                                        5: '5 Stars - Excellent'
                                    };
                                    
                                    // Set initial state if rating exists
                                    const initialRating = ratingValue.value;
                                    if (initialRating) {
                                        updateStars(parseInt(initialRating));
                                    }
                                    
                                    stars.forEach(star => {
                                        // Hover effect
                                        star.addEventListener('mouseenter', function() {
                                            const rating = parseInt(this.getAttribute('data-rating'));
                                            highlightStars(rating);
                                        });
                                        
                                        // Click to select
                                        star.addEventListener('click', function() {
                                            const rating = parseInt(this.getAttribute('data-rating'));
                                            ratingValue.value = rating;
                                            updateStars(rating);
                                            ratingText.textContent = ratingTexts[rating];
                                            ratingText.style.fontWeight = 'bold';
                                            ratingText.style.color = '#f59e0b';
                                        });
                                    });
                                    
                                    // Reset on mouse leave
                                    document.querySelector('.star-rating').addEventListener('mouseleave', function() {
                                        const currentRating = parseInt(ratingValue.value);
                                        if (currentRating) {
                                            updateStars(currentRating);
                                        } else {
                                            resetStars();
                                        }
                                    });
                                    
                                    function highlightStars(rating) {
                                        stars.forEach(star => {
                                            const starRating = parseInt(star.getAttribute('data-rating'));
                                            if (starRating <= rating) {
                                                star.textContent = '★';
                                                star.style.color = '#f59e0b';
                                            } else {
                                                star.textContent = '☆';
                                                star.style.color = '#d1d5db';
                                            }
                                        });
                                    }
                                    
                                    function updateStars(rating) {
                                        highlightStars(rating);
                                    }
                                    
                                    function resetStars() {
                                        stars.forEach(star => {
                                            star.textContent = '☆';
                                            star.style.color = '#d1d5db';
                                        });
                                    }
                                });
                            </script>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Comment (optional)</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4"
                                          placeholder="Share your experience with this user..."><?php echo isset($comment) ? htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/pages/listings.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

