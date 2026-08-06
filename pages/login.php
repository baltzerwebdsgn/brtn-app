<?php
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("
    SELECT users.*, households.household_name 
    FROM users 
    LEFT JOIN households ON users.household_id = households.id
    WHERE users.username = :identifier1 OR users.email = :identifier2
    ");
    $stmt->execute([
        'identifier1' => $username,
        'identifier2' => $username,
        ]);
    $user = $stmt->fetch();
 
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['household_id'] = $user['household_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['household_name'] = $user['household_name'];
        header('Location: index.php?page=home');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
    <form action="index.php?page=login" method="POST" class="login-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
        <div class="task-card">
            <!-- Username / Email -->
            <div class="form-group" >
                <label for="username" class="subheading">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username or email"
                    required
                    autocomplete="username"
                    class="login"
                >
            </div>
            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="subheading">Password</label>
                <input 
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                    class="login"
                >
            </div>
            <div class="form-group">
                <button type="submit" class="btn-primary active" id="loginbtn">Log in</button>
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