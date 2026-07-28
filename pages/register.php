<?php
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email =  $_POST['email'];
    $password = $_POST['psw'];
    $passwordConfirm = $_POST['psw_confirm'];

    if ($password !== $passwordConfirm) {
        $error = 'Passwords do not match.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users(username, email, password_hash, role) VALUES (:username, :email, :password_hash, 'head')");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);
        header('Location: index.php?page=login');
        exit;
        } catch (PDOException $e) {
            $error = 'That username or email is already taken.';
        }
        
    }
}
?>
<form action="index.php?page=register" method="POST" class="register_form">
    <div class="task-card">
        <div class="form-group">
            <label for="username"><b>Username</b></label>
            <input type ="text" placeholder="Enter username" name="username" id="username_reg" required>
        </div>
        <div class="form-group">
            <label for="email"><b>Email</b></label>
            <input type ="text" placeholder="Enter email" name="email" id="email_reg" required>
        </div>
        <!-- <div class="form-group">
            <label for="username"><b>Name</b></label>
            <input type ="text" placeholder="Enter your first name" name="name" id="name_reg" required>
        </div> -->
        <div class="form-group">
            <label for="psw"><b>Password</b></label>
            <input type="password" placeholder="Enter password" name="psw" id="psw_reg" required>
        </div>
        <div class="form-group">
            <label for="psw_"><b>Confirm Password</b></label>
            <input type="password" placeholder="Confirm password" name="psw_confirm" id="ps_reg_confirm" required>
            <?php if ($error): ?>
                <small class="alert" style="color: red;"><?= htmlspecialchars($error) ?></small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <small>By creating an account you agree to our <a href = "#">Terms & Privacy</a>.</small>
        </div>
        <div class="form-group">
            <!-- <label for="psw"><b>Household Name</b></label>
            <input type="text" placeholder="Enter a name" name="household-name" id="hn-reg" required> -->
        </div>
        <div class="form-group">
            
            <button type="submit" class="active" id="registerbtn">Create Household</button>
        </div>
        <div class="form-options">
        <!--<a href="#">Forgot Password?</a>-->
        <p><a href="index.php?page=login">Already have an account? Log in</a></p>
        </div>
    </div>    
</form>
<?php
?>