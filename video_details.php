<?php
session_start();
require_once "config/db.php";
require_once "includes/helpers.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$video = null;

if ($id) {
    $stmt = $conn->prepare("
        SELECT v.*, a.artist_name, al.album_name, g.genre_name, l.language_name
        FROM videos v
        LEFT JOIN artists a ON v.artist_id = a.id
        LEFT JOIN albums al ON v.album_id = al.id
        LEFT JOIN genres g ON v.genre_id = g.id
        LEFT JOIN languages l ON v.language_id = l.id
        WHERE v.id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $video = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$feedback = "";
$feedbackType = "info";

if ($video && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_SESSION['role'] ?? '') === 'user') {
    $userId = (int)$_SESSION['user_id'];
    $action = $_POST['action'] ?? '';

    if ($action === 'rate') {
        $ratingVal = (int)($_POST['rating'] ?? 0);
        if ($ratingVal >= 1 && $ratingVal <= 5) {
            $rStmt = $conn->prepare(
                "INSERT INTO video_ratings (user_id, video_id, rating)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating)"
            );
            $rStmt->bind_param("iii", $userId, $id, $ratingVal);
            if ($rStmt->execute()) {
                $feedback = "Rating saved!";
                $feedbackType = "success";
            } else {
                $feedback = "Could not save rating.";
                $feedbackType = "danger";
            }
            $rStmt->close();
        } else {
            $feedback = "Pick a rating from 1 to 5.";
            $feedbackType = "danger";
        }
    } elseif ($action === 'review') {
        $reviewText = trim($_POST['review'] ?? '');
        if ($reviewText !== '' && strlen($reviewText) <= 2000) {
            $revStmt = $conn->prepare(
                "INSERT INTO video_reviews (user_id, video_id, review)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE review = VALUES(review)"
            );
            $revStmt->bind_param("iis", $userId, $id, $reviewText);
            if ($revStmt->execute()) {
                $feedback = "Review saved!";
                $feedbackType = "success";
            } else {
                $feedback = "Could not save review.";
                $feedbackType = "danger";
            }
            $revStmt->close();
        } else {
            $feedback = "Review must be between 1 and 2000 characters.";
            $feedbackType = "danger";
        }
    }
}

$pageTitle = $video ? ($video['title'] . " - SOUND") : "Not Found - SOUND";
$basePath = "";
if (!$video) {
    http_response_code(404);
}
include "includes/header.php";

if (!$video):
?>
    <div class="card p-5 text-center my-4">
        <h3 class="mb-2">Video Not Found</h3>
        <p class="text-muted mb-4">This video may have been removed.</p>
        <div><a href="videos.php" class="btn btn-primary">Back to Videos</a></div>
    </div>
<?php
    include "includes/footer.php";
    exit;
endif;

$ratingStmt = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM video_ratings WHERE video_id = ?");
$ratingStmt->bind_param("i", $id);
$ratingStmt->execute();
$rating = $ratingStmt->get_result()->fetch_assoc();
$ratingStmt->close();

$reviewStmt = $conn->prepare("
    SELECT r.review, r.created_at, u.first_name, u.last_name
    FROM video_reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.video_id = ?
    ORDER BY r.id DESC
    LIMIT 20
");
$reviewStmt->bind_param("i", $id);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result();

$userRating = null;
$userReview = "";
if (($_SESSION['role'] ?? '') === 'user') {
    $uid = (int)$_SESSION['user_id'];
    $ur = $conn->prepare("SELECT rating FROM video_ratings WHERE user_id = ? AND video_id = ?");
    $ur->bind_param("ii", $uid, $id);
    $ur->execute();
    if ($row = $ur->get_result()->fetch_assoc()) {
        $userRating = (int)$row['rating'];
    }
    $ur->close();

    $uv = $conn->prepare("SELECT review FROM video_reviews WHERE user_id = ? AND video_id = ?");
    $uv->bind_param("ii", $uid, $id);
    $uv->execute();
    if ($row = $uv->get_result()->fetch_assoc()) {
        $userReview = $row['review'];
    }
    $uv->close();
}

$poster = !empty($video['thumbnail']) ? $video['thumbnail'] : 'assets/images/video-placeholder.svg';
?>

<div class="mb-4 d-flex gap-2">
    <a href="videos.php" class="btn btn-outline-light btn-sm">&larr; Back to Videos</a>
    <a href="search.php?type=video" class="btn btn-outline-accent btn-sm">Search Videos</a>
</div>

<?php if ($feedback !== ''): ?>
    <div class="alert alert-<?= e($feedbackType) ?>"><?= e($feedback) ?></div>
<?php endif; ?>

<div class="card p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <h1 class="fw-bold mb-0"><?= e($video['title']) ?></h1>
        <?php if (!empty($video['is_new'])): ?>
            <span class="tag-new">NEW</span>
        <?php endif; ?>
    </div>

    <p class="text-muted mb-3">
        Artist:
        <?php if (!empty($video['artist_id']) && !empty($video['artist_name'])): ?>
            <a href="artist_details.php?id=<?= (int)$video['artist_id'] ?>" class="link-accent"><?= e($video['artist_name']) ?></a>
        <?php else: ?>
            <?= e($video['artist_name'] ?? 'Unknown') ?>
        <?php endif; ?>
        &middot;
        Album:
        <?php if (!empty($video['album_id']) && !empty($video['album_name'])): ?>
            <a href="album_details.php?id=<?= (int)$video['album_id'] ?>" class="link-accent"><?= e($video['album_name']) ?></a>
        <?php else: ?>
            <?= e($video['album_name'] ?? 'Single') ?>
        <?php endif; ?>
    </p>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php if (!empty($video['release_year'])): ?>
            <span class="badge bg-dark border border-secondary">Year: <?= e($video['release_year']) ?></span>
        <?php endif; ?>
        <?php if (!empty($video['genre_name'])): ?>
            <span class="badge bg-dark border border-secondary"><?= e($video['genre_name']) ?></span>
        <?php endif; ?>
        <?php if (!empty($video['language_name'])): ?>
            <span class="badge bg-dark border border-secondary"><?= e($video['language_name']) ?></span>
        <?php endif; ?>
        <span class="badge bg-warning text-dark">
            &#9733; <?= number_format((float)($rating['avg_rating'] ?? 0), 1) ?> / 5 (<?= (int)($rating['total'] ?? 0) ?>)
        </span>
    </div>

    <?php if (!empty($video['video_file'])): ?>
        <video controls class="w-100 mb-3" style="max-height:480px;background:#000" poster="<?= e($poster) ?>">
            <source src="<?= e($video['video_file']) ?>">
        </video>
    <?php endif; ?>

    <?php if (!empty($video['description'])): ?>
        <p class="mb-0"><?= nl2br(e($video['description'])) ?></p>
    <?php endif; ?>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4 h-100">
            <h4 class="mb-3">Rate &amp; Review</h4>
            <?php if (($_SESSION['role'] ?? '') === 'user'): ?>
                <form method="POST" class="mb-4">
                    <input type="hidden" name="action" value="rate">
                    <label class="form-label">Your Rating</label>
                    <div class="d-flex gap-2">
                        <select name="rating" class="form-select" required>
                            <option value="5" <?= $userRating === 5 ? 'selected' : '' ?>>5 - Excellent</option>
                            <option value="4" <?= $userRating === 4 ? 'selected' : '' ?>>4 - Very Good</option>
                            <option value="3" <?= $userRating === 3 ? 'selected' : '' ?>>3 - Good</option>
                            <option value="2" <?= $userRating === 2 ? 'selected' : '' ?>>2 - Fair</option>
                            <option value="1" <?= $userRating === 1 ? 'selected' : '' ?>>1 - Poor</option>
                        </select>
                        <button type="submit" class="btn btn-primary text-nowrap">Save</button>
                    </div>
                </form>

                <form method="POST">
                    <input type="hidden" name="action" value="review">
                    <label class="form-label">Your Review</label>
                    <textarea name="review" class="form-control mb-3" rows="4" maxlength="2000"
                              placeholder="Write what you think..." required><?= e($userReview) ?></textarea>
                    <button type="submit" class="btn btn-warning w-100">
                        <?= $userReview !== '' ? 'Update Review' : 'Post Review' ?>
                    </button>
                </form>
            <?php elseif (($_SESSION['role'] ?? '') === 'admin'): ?>
                <p class="text-muted mb-0">Signed in as admin. Use a listener account to submit ratings or reviews.</p>
            <?php else: ?>
                <p class="text-muted">Login to rate or review this video.</p>
                <div class="d-flex gap-2">
                    <a href="login.php" class="btn btn-primary">Login</a>
                    <a href="register.php" class="btn btn-outline-light">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card p-4 h-100">
            <h4 class="mb-3">Reviews</h4>
            <?php if ($reviews && $reviews->num_rows > 0): ?>
                <div class="d-flex flex-column gap-3">
                    <?php while ($review = $reviews->fetch_assoc()): ?>
                        <div class="review-box">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="link-accent"><?= e($review['first_name'] . ' ' . $review['last_name']) ?></strong>
                                <?php if (!empty($review['created_at'])): ?>
                                    <small class="text-muted"><?= e($review['created_at']) ?></small>
                                <?php endif; ?>
                            </div>
                            <p class="mb-0 small"><?= nl2br(e($review['review'])) ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No reviews yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$reviewStmt->close();
include "includes/footer.php";
?>
