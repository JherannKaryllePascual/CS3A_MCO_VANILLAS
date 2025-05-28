<?php
session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: feed.php");
    exit();
}

$error = '';
$success = $_SESSION['signup_success'] ?? '';
unset($_SESSION['signup_success']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                header("Location: feed.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "A system error occurred. Please try again later.";
        }
    }
}

$pageTitle = "Login";
require_once 'template/header.php';
?>

<div class="form-container">
    <h2>Login to Your Account</h2>
    
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    
    <div class="footer-note">
        Don't have an account? <a href="signup.php">Sign up</a>
    </div>
</div>

<?php require_once 'template/footer.php'; ?>