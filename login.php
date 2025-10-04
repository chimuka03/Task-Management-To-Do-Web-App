<?php
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_POST && verifyCSRF($_POST['csrf'] ?? '')) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, verified FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password']) && $user['verified']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } elseif (!$user['verified']) {
            $error = "Account not verified. Check email or contact support.";
        } else {
            $error = "Invalid username or password.";
        }
    }
}

$csrf = generateCSRF();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - To-Do App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Task Management To-Do App</h1>
        </header>
        <form method="POST">
            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <h2>Login</h2>
            <?php if ($error): ?>
                <div class="error message"><?php echo $error; ?></div>
            <?php endif; ?>
            <input type="text" name="username" placeholder="Username" required value="<?php echo $_POST['username'] ?? ''; ?>">
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>No account? <a href="register.php">Register here</a></p>
        
    </div>
</body>
</html>