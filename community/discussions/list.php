<?php
require_once __DIR__ . '/../../database.php';

try {
    // Modified SQL query without votes table reference
    $sql = "
    SELECT d.*, u.username, u.profile_image, r.title AS recipe_title, 
       COUNT(DISTINCT c.comment_id) AS comment_count,
       d.media_path, d.user_id  -- Add user_id here
    FROM discussions d
    LEFT JOIN users u ON d.user_id = u.user_id
    LEFT JOIN recipes r ON d.recipe_id = r.recipe_id
    LEFT JOIN comments c ON d.discussion_id = c.discussion_id
    GROUP BY d.discussion_id
    ORDER BY d.created_at DESC
";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $discussions = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Helper function to format time in Reddit style
function time_elapsed_string($datetime)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . 'y ago';
    } elseif ($diff->m > 0) {
        return $diff->m . 'mo ago';
    } elseif ($diff->d > 0) {
        return $diff->d . 'd ago';
    } elseif ($diff->h > 0) {
        return $diff->h . 'h ago';
    } elseif ($diff->i > 0) {
        return $diff->i . 'm ago';
    } else {
        return 'just now';
    }
}

// Helper function to detect media type
function get_media_type($media_path)
{
    if (empty($media_path)) return 'none';

    $extension = strtolower(pathinfo($media_path, PATHINFO_EXTENSION));

    $video_extensions = ['mp4', 'webm', 'ogg', 'mov'];
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($extension, $video_extensions)) {
        return 'video';
    } elseif (in_array($extension, $image_extensions)) {
        return 'image';
    } else {
        return 'other';
    }
}

include __DIR__ . '/../../header.php';
?>

<link rel="stylesheet" href="/recipe culinary/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .rd-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 10px;
    }

    .rd-post-feed {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .rd-post-card {
        display: flex;
        background: white;
        border-radius: 4px;
        border: 1px solid #ccc;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
        position: relative;
    }

    .rd-post-card:hover {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .rd-post-link {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .rd-vote-column {
        width: 40px;
        background-color: #f8f8f8;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 8px 0;
        position: relative;
        z-index: 2;
    }

    .rd-vote-button {
        background: none;
        border: none;
        cursor: pointer;
        color: #878a8c;
        font-size: 1.2rem;
        position: relative;
        z-index: 3;
    }

    .rd-vote-score {
        font-weight: bold;
        margin: 5px 0;
    }

    .rd-post-content {
        flex: 1;
        padding: 10px 15px;
        display: flex;
        flex-direction: column;
    }

    .rd-post-header {
        margin-bottom: 8px;
    }

    .rd-post-title {
        font-size: 1.2rem;
        margin: 0 0 5px 0;
        color: #1c1c1c;
    }

    .rd-post-metadata {
        font-size: 0.8rem;
        color: #787c7e;
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
    }

    .rd-user-avatar {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        object-fit: cover;
    }

    .rd-post-body {
        display: flex;
        gap: 15px;
    }

    .rd-post-text {
        flex: 1;
    }

    .rd-post-media {
        flex: 1;
        max-width: 50%;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
        z-index: 2;
    }

    .rd-post-media img {
        width: 100%;
        height: auto;
        object-fit: cover;
    }

    .rd-post-media video {
        width: 100%;
        max-height: 400px;
        object-fit: contain;
    }

    .rd-post-actions {
        display: flex;
        padding-top: 10px;
        border-top: 1px solid #edeff1;
        margin-top: 10px;
        gap: 15px;
        position: relative;
        z-index: 2;
    }

    .rd-post-action {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #878a8c;
        font-size: 0.9rem;
        text-decoration: none;
        position: relative;
        z-index: 3;
    }

    .rd-post-action:hover {
        color: #1484D6;
    }

    .rd-new-post-button {
        display: inline-block;
        background-color: #0079d3;
        color: white;
        padding: 8px 16px;
        border-radius: 9999px;
        font-weight: bold;
        text-decoration: none;
        margin-bottom: 20px;
        transition: background-color 0.2s;
    }

    .rd-new-post-button:hover {
        background-color: #0061a9;
    }

    .rd-community-heading {
        margin-bottom: 20px;
    }

    .rd-recipe-related {
        font-size: 0.8rem;
        margin-top: 5px;
        padding: 3px 6px;
        background-color: #f2f8fc;
        border-radius: 3px;
        display: inline-block;
    }

    @media (max-width: 768px) {
        .rd-post-body {
            flex-direction: column;
        }

        .rd-post-media {
            max-width: 100%;
        }
    }

    body {
        background: rgb(31, 31, 31);
        color: rgb(207, 208, 209);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .rd-container {
        flex: 1;
        margin-bottom: 100px;
        /* Match footer height + extra spacing */
    }

    /* Footer styling */
    footer {
        position: relative;
        z-index: 100;
        background: white;
        margin-top: auto;
        /* Pushes footer to bottom */
    }

    /* Prevent media overflow */
    .rd-media-image,
    .rd-media-video {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 15px 0;
    }

    /* Card overflow protection */
    .rd-post-card {
        overflow: hidden;
    }
</style>

<body class="list-page">
    <div class="rd-container">
        <div class="rd-community-heading">
            <h1>Culinary Community Discussions</h1>
            <a href="<?= $base_path ?>/community/discussions/create.php" class="rd-new-post-button">
                <i class="fas fa-plus"></i> New Discussion
            </a>
        </div>

        <?php if (count($discussions) > 0): ?>
            <div class="rd-post-feed">
                <?php foreach ($discussions as $discussion): ?>
                    <?php
                    $mediaType = get_media_type($discussion['media_path']);
                    $hasMedia = $mediaType !== 'none';
                    $media_url = $hasMedia ? $base_path . '/' . $discussion['media_path'] : '';
                    ?>
                    <div class="rd-post-card">
                        <!-- Main clickable area that covers the whole card -->
                        <a href="view.php?id=<?= $discussion['discussion_id'] ?>" class="rd-post-link"></a>

                        <div class="rd-vote-column">
                            <button class="rd-vote-button" data-discussion-id="<?= $discussion['discussion_id'] ?>" data-vote="up">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <div class="rd-vote-score" id="score-<?= $discussion['discussion_id'] ?>">0</div>
                            <button class="rd-vote-button" data-discussion-id="<?= $discussion['discussion_id'] ?>" data-vote="down">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                        </div>

                        <div class="rd-post-content">
                            <div class="rd-post-header">
                                <h3 class="rd-post-title"><?= htmlspecialchars($discussion['title']) ?></h3>
                                <div class="rd-post-metadata">
                                    <?php if (!empty($discussion['profile_image'])): ?>
                                        <img src="<?= htmlspecialchars($discussion['profile_image']) ?>" alt="" class="rd-user-avatar">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                    <span>Posted by <?= htmlspecialchars($discussion['username'] ?? 'Anonymous') ?></span>
                                    <span>·</span>
                                    <span><?= time_elapsed_string($discussion['created_at']) ?></span>

                                    <?php if ($discussion['recipe_title']): ?>
                                        <span>·</span>
                                        <span class="rd-recipe-related">
                                            <i class="fas fa-utensils"></i>
                                            <?= htmlspecialchars($discussion['recipe_title']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="rd-post-body">
                                <div class="rd-post-text">
                                    <?= nl2br(htmlspecialchars(substr($discussion['content'], 0, 300))) ?>
                                    <?php if (strlen($discussion['content']) > 300): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($hasMedia): ?>
                                    <div class="rd-post-media">
                                        <?php if ($mediaType === 'image'): ?>
                                            <img src="<?= $media_url ?>" alt="Discussion image">
                                        <?php elseif ($mediaType === 'video'): ?>
                                            <video controls>
                                                <source src="<?= $media_url ?>"
                                                    type="video/<?= pathinfo($discussion['media_path'], PATHINFO_EXTENSION) ?>">
                                            </video>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="rd-post-actions">
                                <a href="view.php?id=<?= $discussion['discussion_id'] ?>" class="rd-post-action">
                                    <i class="fas fa-comment-alt"></i>
                                    <span><?= $discussion['comment_count'] ?> Comments</span>
                                </a>



                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No discussions found yet. Be the first to start one!
            </div>
        <?php endif; ?>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle voting buttons
        const voteButtons = document.querySelectorAll('.rd-vote-button');

        voteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent the click from bubbling up to the card link
                e.preventDefault();
                e.stopPropagation();

                const discussionId = this.getAttribute('data-discussion-id');
                const voteType = this.getAttribute('data-vote');

                // This would be replaced with an actual AJAX call to a vote handling script
                console.log(`Vote ${voteType} for discussion ${discussionId}`);

                // For demonstration purposes only - in production this would update based on server response
                const scoreElement = document.getElementById(`score-${discussionId}`);
                let currentScore = parseInt(scoreElement.textContent);

                if (voteType === 'up') {
                    scoreElement.textContent = currentScore + 1;
                } else {
                    scoreElement.textContent = currentScore - 1;
                }
            });
        });

        // Handle share and bookmark buttons
        const actionButtons = document.querySelectorAll('.share-button, .bookmark-button');

        actionButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent the click from bubbling up to the card link
                e.preventDefault();
                e.stopPropagation();

                const discussionId = this.getAttribute('data-id');
                const actionType = this.classList.contains('share-button') ? 'share' : 'bookmark';

                console.log(`${actionType} discussion ${discussionId}`);

                // Add your actual share/bookmark logic here
            });
        });

        // Make media clicks not navigate to the post detail page
        const mediaElements = document.querySelectorAll('.rd-post-media video, .rd-post-media img');

        mediaElements.forEach(media => {
            media.addEventListener('click', function(e) {
                // Only prevent navigation for videos that need to be played
                if (this.tagName.toLowerCase() === 'video') {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    });
</script>

<?php include __DIR__ . '/../../footer.php'; ?>
