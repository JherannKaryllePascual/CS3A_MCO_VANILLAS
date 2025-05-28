<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $postId = (int) $_POST['post_id'];

    $stmt = $pdo->prepare("SELECT content FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    $original = $stmt->fetch();

    if ($original) {
        $sharedContent = "🔁 Shared Post:\n" . $original['content'];
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $sharedContent]);
    }
}

header('Location: feed.php');
exit();
?>
