<?php
$error = null;
$username = '';
$email = '';
$name =  '';
$householdName = '';
$usernameTaken = false;
$emailTaken = false;
$passwordMismatch = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email =  $_POST['email'];
    $name = $_POST['name'];
    $password = $_POST['psw'];
    $passwordConfirm = $_POST['psw_confirm'];
    $householdName = $_POST['household-name'];

    $checkStmt = $pdo->prepare("SELECT username, email FROM users where username = :username OR email = :email");
    $checkStmt-> execute ([
        'username' => $username,
        'email' => $email,
    ]);
    $matches = $checkStmt->fetchAll();

    foreach ($matches as $row) {
        if ($row['username'] === $username) {
            $usernameTaken = true;
        }
        if ($row['email'] === $email) {
            $emailTaken = true;
        }
    }

    if ($passwordMismatch) {
        $error = 'Passwords do not match.';
    } elseif ($usernameTaken || $emailTaken) {
        $error = "That username or email is already taken.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO households(household_name) VALUES (:household_name)");
            $stmt->execute([
                'household_name' => $householdName,
            ]);
            $householdId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO users(username, email, name, password_hash, role, household_id) VALUES (:username, :email, :name, :password_hash, 'head', :household_id)");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'name' => $name,
            'password_hash' => $passwordHash,
            'household_id' => $householdId,
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
            <label for="username" class="subheading">
                Username
            </label>
            <input 
            type ="text" 
            placeholder="Enter username" 
            name="username" 
            id="username_reg" 
            value="<?= htmlspecialchars($username) ?>"
            class="reg <?= $usernameTaken ? 'input-error' : '' ?>"
            required>
        </div>
        <div class="form-group">
            <label for="email" class="subheading">
                Email
            </label>
            <input 
            type ="text" 
            placeholder="Enter email" 
            name="email" 
            id="email_reg" 
            value="<?= htmlspecialchars($email) ?>"
            class="reg <?= $emailTaken ? 'input-error' : '' ?>"
            required>
        </div>
        <div class="form-group">
            <label for="name_reg" class="subheading">
                Name
            </label>
            <input 
            type ="text" 
            placeholder="Enter your first name" 
            name="name" 
            id="name_reg" 
            value="<?= htmlspecialchars($name) ?>"
            class="reg"
            required>
        </div>
        <div class="form-group">
            <label for="hn_reg" class="subheading">
                Household Name
            </label>
            <input 
            type="text" 
            placeholder="Enter a name" 
            name="household-name" 
            id="hn_reg" 
            value="<?= htmlspecialchars($householdName) ?>"
            class="reg"
            required>
        </div>
        <div class="form-group">
            <label for="psw" class="subheading">
                Password
            </label>
            <input 
            type="password" 
            placeholder="Enter password" 
            name="psw" 
            id="psw_reg" 
            class="reg <?= $passwordMismatch ? 'input-error' : '' ?>"
            required>
        </div>
        <div class="form-group">
            <label for="psw_" class="subheading">
                Confirm Password
            </label>
            <input 
            type="password" 
            placeholder="Confirm password" 
            name="psw_confirm" 
            id="ps_reg_confirm" 
            class="reg <?= $passwordMismatch ? 'input-error' : '' ?>"
            required>
            <?php if ($error): ?>
                <small class="danger"><?= htmlspecialchars($error) ?></small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <small>By creating an account you agree to our <a href = "index.php?page=terms&from=register">Terms & Privacy</a>.</small>
        </div>
        <div class="form-group">
            
            <button type="submit" class="btn-primary" id="registerbtn">Create Household</button>
        </div>
        <div class="form-options">
        <!--<a href="#">Forgot Password?</a>-->
        <p><a href="index.php?page=login">Already have an account? Log in</a></p>
        </div>
    </div>    
</form>
<?php
?>