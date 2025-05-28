<nav>
    <div class="brand">JustTrends</div>
    <ul>
        <?php if(isset($_SESSION['user'])): ?>
            <li><a href="feed.php">Feed</a></li>
            <li><a href="search.php">Search</a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Sign Up</a></li>
        <?php endif; ?>
    </ul>
</nav>