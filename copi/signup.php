<?php
session_start();
include 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords don't match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username or email already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                // Set success message in session to display on login page
                $_SESSION['signup_success'] = "Registration successful! Please log in.";
                
                // Redirect to login page
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Signup error: " . $e->getMessage());
            $error = "A system error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | JustTrends</title>
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <h1 class="brand">JustTrends</h1>
    
    <div class="form-container">
        <h2 class="form-title">Create Your Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="signup.php" method="POST">
            <div class="form-group">
                <input type="text" name="username" required placeholder="Username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="email" name="email" required placeholder="Email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="password" name="password" required placeholder="Password">
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" required placeholder="Confirm Password">
            </div>
            
            <button type="submit" class="btn">Sign Up</button>
            
            <div class="footer-note">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </form>
    </div>
</body>
</html>

