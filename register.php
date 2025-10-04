<?php
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$messages = [];
$error = '';

if ($_POST && verifyCSRF($_POST['csrf'] ?? '')) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check uniqueness
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, verified) VALUES (?, ?, ?, 1)");  // Auto-verify for local
            if ($stmt->execute([$username, $email, $hashed])) {
                $messages[] = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed. Try again.";
            }
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
    <title>Register - To-Do App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Task Management To-Do App</h1>
        </header>
        <form method="POST">
            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <h2>Register</h2>
            <?php if ($error): ?>
                <div class="error message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php foreach ($messages as $msg): ?>
                <div class="success message"><?php echo $msg; ?></div>
            <?php endforeach; ?>
            <input type="text" name="username" placeholder="Username" required value="<?php echo $_POST['username'] ?? ''; ?>">
            <input type="email" name="email" placeholder="Email" required value="<?php echo $_POST['email'] ?? ''; ?>">
            <input type="password" name="password" placeholder="Password (min 6 chars)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>