<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$results = [];
$search_term = '';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['q'])) {
    $search_term = trim($_GET['q']);
    
    if (!empty($search_term)) {
        try {
            $stmt = $pdo->prepare("
                SELECT posts.*, users.username 
                FROM posts 
                JOIN users ON posts.user_id = users.id 
                WHERE content LIKE ? 
                ORDER BY posts.created_at DESC
            ");
            $stmt->execute(["%$search_term%"]);
            $results = $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Search error: " . $e->getMessage());
        }
    }
}

$pageTitle = "Search";
require_once 'template/header.php';
?>

<div class="search-container">
    <h1>Search Posts</h1>
    
    <form method="GET" class="search-form">
        <input type="text" name="q" placeholder="Search posts..." 
               value="<?= htmlspecialchars($search_term) ?>">
        <button type="submit" class="btn">Search</button>
    </form>
    
    <?php if (!empty($search_term)): ?>
        <div class="results">
            <h2>Results for "<?= htmlspecialchars($search_term) ?>"</h2>
            
            <?php if (empty($results)): ?>
                <p>No posts found matching your search.</p>
            <?php else: ?>
                <?php foreach ($results as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <?= htmlspecialchars($post['username']) ?>
                            <span class="post-date">
                                <?= date('M j, Y g:i a', strtotime($post['created_at'])) ?>
                            </span>
                        </div>
                        <div class="post-content">
                            <?= nl2br(htmlspecialchars($post['content'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'template/footer.php'; ?>