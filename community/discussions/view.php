<?php
session_start();
session_regenerate_id(true);
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../includes/csrf.php';

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$current_user_id = $logged_in ? $_SESSION['user_id'] : null;

// Check if ID exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid discussion ID");
}

$discussion_id = (int)$_GET['id'];

// Get discussion
$stmt = $conn->prepare("SELECT d.*, 
                        u.username, 
                        r.title AS recipe_title,
                        d.media_path,
                        UNIX_TIMESTAMP(d.created_at) as created_timestamp,
                        (SELECT COUNT(*) FROM comments WHERE discussion_id = d.discussion_id) as comment_count,
                        COALESCE(SUM(dv.vote_value), 0) AS votes,
                        (SELECT vote_value FROM discussions_vote 
                        WHERE user_id = ? AND discussion_id = d.discussion_id) AS user_vote
                       FROM discussions d
                       LEFT JOIN users u ON d.user_id = u.user_id
                       LEFT JOIN recipes r ON d.recipe_id = r.recipe_id
                       LEFT JOIN discussions_vote dv ON d.discussion_id = dv.discussion_id
                       WHERE d.discussion_id = ?
                       GROUP BY d.discussion_id");

$stmt->bind_param("ii", $user_id_param, $discussion_id); // Ensure both params are integers
$stmt->execute();
$result = $stmt->get_result();
$discussion = $result->fetch_assoc();


if (!$discussion) {
    die("Discussion not found!");
}

// Recursive comments function
function getComments($parentId = null)
{
    global $conn, $discussion_id, $current_user_id;

    // Handle null user ID by creating a bindable variable
    $user_id_param = $current_user_id ?? 0;

    // Determine sorting order based on the 'sort' parameter
    $sort = $_GET['sort'] ?? 'best';
    $orderBy = match ($sort) {
        'new' => 'c.created_at DESC',
        'old' => 'c.created_at ASC',
        'best' => 'votes DESC, c.created_at DESC', // Sort by votes first, then by creation time
        default => 'votes DESC, c.created_at DESC', // Default to "best"
    };

    $sql = "SELECT c.*, 
            UNIX_TIMESTAMP(c.created_at) as created_timestamp,
            u.username,
            COALESCE(SUM(v.vote_value), 0) AS votes,
            (SELECT vote_value FROM comments_vote 
            WHERE user_id = ? AND comment_id = c.comment_id) AS user_vote
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.user_id
            LEFT JOIN comments_vote v ON c.comment_id = v.comment_id
            WHERE c.discussion_id = ? AND c.parent_comment_id " .
        ($parentId ? "= ?" : "IS NULL") . "
            GROUP BY c.comment_id
            ORDER BY $orderBy";

    $stmt = $conn->prepare($sql);

    if ($parentId) {
        // Create separate variables for binding
        $params = [$user_id_param, $discussion_id, $parentId];
        $stmt->bind_param("iii", ...$params);
    } else {
        $params = [$user_id_param, $discussion_id];
        $stmt->bind_param("ii", ...$params);
    }

    if (!$stmt->execute()) {
        die("Error fetching comments: " . $conn->error);
    }

    $result = $stmt->get_result();
    $comments = [];

    while ($row = $result->fetch_assoc()) {
        $row['replies'] = getComments($row['comment_id']);
        $comments[] = $row;
    }

    return $comments;
}

$comments = getComments();

// Helper function to format time elapsed
function timeElapsed($timestamp)
{
    $seconds = time() - $timestamp;

    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];

    foreach ($intervals as $seconds_in_interval => $interval) {
        $count = floor($seconds / $seconds_in_interval);
        if ($count >= 1) {
            return $count == 1 ? "1 {$interval} ago" : "{$count} {$interval}s ago";
        }
    }

    return "just now";
}

include __DIR__ . '/../../header.php';

?>

<link rel="stylesheet" href="/recipe%20culinary/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<body class="view-page" style="display: flex; flex-direction: column; min-height: 100vh;">
    <div class="rd-container" style="flex: 1;">
        <div class="rd-discussion-card">
            <div class="rd-discussion-header">
                Posted by u/<?= htmlspecialchars($discussion['username'] ?? 'Unknown') ?> • <?= timeElapsed($discussion['created_timestamp']) ?>
                <?php if ($discussion['recipe_title'] ?? null): ?>
                    <div class="rd-related-recipe">
                        <i class="fas fa-utensils"></i> Related to recipe:
                        <a href="<?= $base_path ?>/recipes/recipe_detail.php?recipe_id=<?= $discussion['recipe_id'] ?>">
                            <?= htmlspecialchars($discussion['recipe_title']) ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($current_user_id && $current_user_id == $discussion['user_id']): ?>
                    <div class="rd-discussion-actions">
                        <a href="/recipe%20culinary/community/discussions/delete.php?id=<?= $discussion_id ?>"
                            onclick="return confirm('Are you sure you want to delete this discussion? This cannot be undone.')"
                            class="rd-delete-btn">
                            <i class="far fa-trash-alt"></i> Delete Discussion
                        </a>
                    </div>
                <?php endif; ?>

            </div>
            <div class="rd-discussion-title">
                <?= htmlspecialchars($discussion['title'] ?? 'Untitled Discussion') ?>
            </div>
            <div class="rd-discussion-content">
                <?= nl2br(htmlspecialchars($discussion['content'] ?? 'No content available')) ?>
            </div>

            <?php if (!empty($discussion['media_path'])): ?>
                <div class="rd-discussion-media">
                    <?php
                    $media_extension = pathinfo($discussion['media_path'], PATHINFO_EXTENSION);
                    $media_url = $base_path . '/' . $discussion['media_path'];
                    ?>
                    <?php if (in_array(strtolower($media_extension), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <img src="<?= $media_url ?>" alt="Discussion media" class="rd-media-image">
                    <?php elseif (in_array(strtolower($media_extension), ['mp4', 'webm', 'ogg'])): ?>
                        <video controls class="rd-media-video">
                            <source src="<?= $media_url ?>" type="video/<?= $media_extension ?>">
                        </video>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="rd-discussion-footer">
                <div class="rd-vote-controls">
                    <!-- Add method="POST" to both forms -->
                    <form class="vote-form" method="POST" data-discussion-id="<?= $discussion_id ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="discussion_id" value="<?= $discussion_id ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="vote_value" value="1">
                        <button type="submit" class="rd-upvote <?= ($discussion['user_vote'] ?? 0) === 1 ? 'active' : '' ?>">↑</button>
                    </form>

                    <div class="rd-vote-count"><?= $discussion['votes'] ?? 0 ?></div>

                    <form class="vote-form" method="POST" data-discussion-id="<?= $discussion_id ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="discussion_id" value="<?= $discussion_id ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="vote_value" value="-1">
                        <button type="submit" class="rd-downvote <?= ($discussion['user_vote'] ?? 0) === -1 ? 'active' : '' ?>">↓</button>
                    </form>
                </div>
            </div>



        </div>


        <div class="rd-comment-container" id="comments">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-alert" style="margin-top: 10px; color: #ff4f4f; font-size: 14px;">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #ff4f4f; cursor: pointer;">×</button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if ($logged_in): ?>
                <div class="rd-comment-form">
                    <form action="<?= $base_path ?>/community/comments/create.php" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="discussion_id" value="<?= $discussion_id ?>">
                        <textarea name="content" placeholder="Comments"><?= htmlspecialchars($_SESSION['form_data']['content'] ?? '') ?></textarea>
                        <button type="submit">Comment</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="rd-comment-form">
                    <p style="color: #d7dadc;">Please <a href="/recipe%20culinary/login.php" style="color: #4fbcff;">log in</a> to leave a comment.</p>
                </div>
            <?php endif; ?>

            <?php
            function renderComments($comments, $depth = 0)
            {
                global $current_user_id, $base_path, $logged_in;
                foreach ($comments as $comment):
            ?>
                    <div class="rd-comment">

                        <div class="rd-vote-controls">

                            <!-- In renderComments() function -->
                            <form class="vote-form" method="POST" data-comment-id="<?= $comment['comment_id'] ?>">
                                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="vote_value" value="1">
                                <button type="submit" class="rd-upvote <?= ($comment['user_vote'] ?? 0) === 1 ? 'active' : '' ?>">
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                            </form>

                            <div class="rd-vote-count"><?= $comment['votes'] ?></div>

                            <form class="vote-form" method="POST" data-comment-id="<?= $comment['comment_id'] ?>">
                                <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="vote_value" value="-1">
                                <button type="submit" class="rd-downvote <?= ($comment['user_vote'] ?? 0) === -1 ? 'active' : '' ?>">
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                            </form>
                        </div>


                        <div class="rd-comment-body">

                            <div class="rd-comment-header">
                                <span class="rd-comment-author"><?= htmlspecialchars($comment['username'] ?? 'Anonymous') ?></span> •
                                <span class="rd-comment-time"><?= timeElapsed($comment['created_timestamp']) ?></span>
                            </div>

                            <div class="rd-comment-text" id="comment-text-<?= $comment['comment_id'] ?>">
                                <?= nl2br(htmlspecialchars($comment['content'] ?? '[Comment deleted]')) ?>
                            </div>

                            <div class="rd-comment-actions">
                                <a href="#reply" onclick="showReplyForm(<?= $comment['comment_id'] ?>)"><i class="far fa-comment"></i> Reply</a>
                                <?php if ($current_user_id && $current_user_id == $comment['user_id']): ?>
                                    <a href="javascript:void(0)" class="edit-comment-btn" data-comment-id="<?= $comment['comment_id'] ?>"><i class="far fa-edit"></i> Edit</a>
                                    <a href="/recipe%20culinary/community/comments/delete.php?id=<?= $comment['comment_id'] ?>" onclick="return confirm('Are you sure you want to delete this comment?')"><i class="far fa-trash-alt"></i> Delete</a>
                                <?php endif; ?>
                            </div>

                            <div id="rd-reply-form-<?= $comment['comment_id'] ?>" class="rd-reply-form" style="display: none;">
                                <form action="/recipe%20culinary/community/comments/create.php" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="discussion_id" value="<?= $comment['discussion_id'] ?>">
                                    <input type="hidden" name="parent_comment_id" value="<?= $comment['comment_id'] ?>">
                                    <textarea name="content" placeholder="What are your thoughts?"></textarea>
                                    <div class="rd-reply-buttons">
                                        <button type="button" class="rd-cancel-btn" onclick="hideReplyForm(<?= $comment['comment_id'] ?>)">Cancel</button>
                                        <button type="submit" class="rd-reply-btn">Reply</button>
                                    </div>
                                </form>
                            </div>

                            <?php if (!empty($comment['replies'])): ?>
                                <div class="rd-comment-replies">
                                    <?php renderComments($comment['replies'], $depth + 1) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
            <?php
                endforeach;
            }

            if (!empty($comments)) {
                renderComments($comments);
            } else {
                echo '<p class="rd-no-comments">No comments yet. Be the first to share your thoughts!</p>';
            }
            ?>
        </div>
    </div>
    
</body>

<!-- Include jQuery library -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function showReplyForm(commentId) {
        document.getElementById('rd-reply-form-' + commentId).style.display = 'block';
    }

    function hideReplyForm(commentId) {
        document.getElementById('rd-reply-form-' + commentId).style.display = 'none';
    }

    // Add AJAX-based editing functionality for comments
    $(document).on('click', '.edit-comment-btn', function() {
        const commentId = $(this).data('comment-id');
        const commentTextElement = $(`#comment-text-${commentId}`);
        const originalContent = commentTextElement.text().trim(); // Trim spaces around the text

        // Replace comment text with a textarea for editing
        commentTextElement.html(
            `<textarea id="edit-textarea-${commentId}" class="edit-textarea" style="width: 90%; height: 80px;">${originalContent}</textarea>` +
            `<div style="margin-top: 10px;">` +
            `<button class="save-edit-btn" data-comment-id="${commentId}" style="margin-right: 5px;">Save</button>` +
            `<button class="cancel-edit-btn" data-comment-id="${commentId}">Cancel</button>` +
            `</div>`
        );

        // Handle save button click
        $(document).on('click', `.save-edit-btn[data-comment-id="${commentId}"]`, function() {
            const updatedContent = $(`#edit-textarea-${commentId}`).val();

            // Ensure the CSRF token is included in the AJAX request
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            if (!csrfToken) {
                console.error('CSRF token is missing from the page.');
            }

            $.ajax({
                url: '/recipe%20culinary/community/comments/edit.php',
                method: 'POST',
                data: {
                    ajax: true,
                    comment_id: commentId,
                    content: updatedContent,
                    csrf_token: csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        commentTextElement.html(response.updated_content); // Server sends already escaped and <br> converted content
                    } else {
                        alert(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('An error occurred while saving the comment. Please try again.');
                }
            });
        });

        // Handle cancel button click
        $(document).on('click', `.cancel-edit-btn[data-comment-id="${commentId}"]`, function() {
            commentTextElement.text(originalContent);
        });
    });
</script>
<?php include __DIR__ . '/../../footer.php'; ?>