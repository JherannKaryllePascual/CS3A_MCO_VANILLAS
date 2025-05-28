<?php
session_start();
include 'db.php';

$error = '';

if (!isset($_SESSION['temp_user'])) {
    header("Location: signup.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_otp = trim($_POST['otp'] ?? '');

    if (empty($input_otp)) {
        $error = "Please enter the OTP.";
    } else {
        $temp_user = $_SESSION['temp_user'];
        if ($input_otp == $temp_user['otp']) {
            try {
                // Insert user into database
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$temp_user['username'], $temp_user['email'], $temp_user['password']]);

                // Clear temp user session
                unset($_SESSION['temp_user']);

                // Set success message and redirect to login
                $_SESSION['signup_success'] = "Registration successful! Please log in.";
                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
                error_log("OTP Verification error: " . $e->getMessage());
                $error = "A system error occurred. Please try again later.";
            }
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>OTP Verification | JustTrends</title>
    <link rel="stylesheet" href="static/css/style.css" />
</head>
<body>
    <h1 class="brand">JustTrends</h1>

    <div class="form-container">
        <h2 class="form-title">Verify Your Email</h2>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="otp_verification.php" method="POST">
            <div class="form-group">
                <input type="text" name="otp" required placeholder="Enter OTP" />
            </div>
            <button type="submit" class="btn">Verify</button>
        </form>
    </div>
</body>
</html>
