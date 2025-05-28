<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle post reaction
if (isset($_POST['react_post_id'], $_POST['reaction_type'])) {
    $postId = $_POST['react_post_id'];
    $reaction = $_POST['reaction_type'];

    try {
        $stmt = $pdo->prepare("INSERT INTO reactions (post_id, user_id, reaction_type)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE reaction_type = VALUES(reaction_type)");
        $stmt->execute([$postId, $_SESSION['user_id'], $reaction]);
        $success = "Reacted successfully!";
    } catch (PDOException $e) {
        $error = "Error reacting: " . $e->getMessage();
    }
}

// Handle new post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_content'])) {
    $content = trim($_POST['post_content']);
    $imagePath = null;

    if (!empty($_FILES['post_image']['name'])) {
        $uploadDir = 'uploads/';
        $imageName = basename($_FILES['post_image']['name']);
        $imagePath = $uploadDir . time() . '_' . $imageName;
        move_uploaded_file($_FILES['post_image']['tmp_name'], $imagePath);
    }

    if (!empty($content) || $imagePath) {
        try {
            $clean_content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $clean_content, $imagePath]);
            $success = "Post published successfully!";
        } catch (PDOException $e) {
            $error = "Error publishing post: " . $e->getMessage();
        }
    } else {
        $error = "Post content or image is required.";
    }
}

// Fetch posts
$posts = [];
try {
    $stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading posts: " . $e->getMessage();
}

// Fetch reactions
$reactions = [];
$stmt = $pdo->query("SELECT post_id, reaction_type, COUNT(*) as count FROM reactions GROUP BY post_id, reaction_type");
foreach ($stmt as $row) {
    $reactions[$row['post_id']][$row['reaction_type']] = $row['count'];
}

$pageTitle = "Your Feed";
require_once 'template/header.php';
?>

<div class="feed-container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></h1>

    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <form class="post-form" method="POST" enctype="multipart/form-data">
        <textarea name="post_content" placeholder="What's on your mind?"></textarea>
        <input type="file" name="post_image" accept="image/*">
        <button type="submit" class="btn">Post</button>
    </form>

    <div class="posts">
        <?php if (empty($posts)): ?>
            <p>No posts yet. Be the first to post!</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <?= htmlspecialchars($post['username']) ?>
                        <span class="post-date">
                            <?= date('M j, Y g:i a', strtotime($post['created_at'])) ?>
                        </span>
                    </div>
                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                        <?php if (!empty($post['image_path'])): ?>
                            <div class="post-image">
                                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" style="max-width: 100%; height: auto;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="post-reactions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="react_post_id" value="<?= $post['id'] ?>">
                            <input type="hidden" name="reaction_type" value="like">
                            <button type="submit">👍 Like</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="react_post_id" value="<?= $post['id'] ?>">
                            <input type="hidden" name="reaction_type" value="love">
                            <button type="submit">❤️ Love</button>
                        </form>

                        <div class="reaction-summary">
                            <?php
                            $postReactions = $reactions[$post['id']] ?? [];
                            foreach ($postReactions as $type => $count) {
                                echo "<span>$type: $count</span> ";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="post-share">
                        <form method="POST" action="share.php">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit">🔗 Share</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'template/footer.php'; ?>
