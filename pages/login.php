<?php
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
 
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['household_id'] = $user['household_id'];
        header('Location: index.php?page=home');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
    <form action="index.php?page=login" method="POST" class="login-form">
        <div class="task-card">
            <!-- Username / Email -->
            <div class="form-group" >
                <label for="username"><b>Username</b></label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    required
                    autocomplete="username"
                >
            </div>
            <!-- Password Field -->
            <div class="form-group">
                <label for="password"><b>Password</b></label>
                <input 
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >
            </div>
            <div class="form-group">
                <button type="submit" class="active" id="loginbtn">Log in</button>
            </div>
            <?php if ($error): ?>
                <p class="danger"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <!-- Remember Me & Forget Password Utilites -->
            <div class="form-options">
            <!--<label class="checkbox-label">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <a href = "forgot-password" class="forgot-link"> Forgot password?</a>-->
            <a href="index.php?page=register">New here? Create a Household</a>
            </div>
        </div>
    </form>
<?php
?>