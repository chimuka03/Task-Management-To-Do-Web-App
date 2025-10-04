<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current user info
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    // User not found (should not happen)
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_POST && verifyCSRF($_POST['csrf'] ?? '')) {
    $csrf = generateCSRF();

    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($new_username) || empty($new_email) || empty($current_password)) {
        $error = "Username, email, and current password are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($current_password, $row['password'])) {
            $error = "Current password is incorrect.";
        } else {
            // Check if username or email already taken by others
            $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$new_username, $new_email, $user_id]);
            if ($stmt->fetch()) {
                $error = "Username or email already in use by another account.";
            } else {
                // Update username and email
                $update_password = false;
                if (!empty($new_password)) {
                    if ($new_password !== $confirm_password) {
                        $error = "New password and confirmation do not match.";
                    } elseif (strlen($new_password) < 6) {
                        $error = "New password must be at least 6 characters.";
                    } else {
                        $update_password = true;
                    }
                }

                if (!$error) {
                    if ($update_password) {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                        $stmt->execute([$new_username, $new_email, $hashed, $user_id]);
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                        $stmt->execute([$new_username, $new_email, $user_id]);
                    }
                    $success = "Profile updated successfully.";
                    $_SESSION['username'] = $new_username;
                    // Refresh user data
                    $user['username'] = $new_username;
                    $user['email'] = $new_email;
                }
            }
        }
    }
} else {
    $csrf = generateCSRF();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profile - To-Do App</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="container">
        <header>
            <h1>Your Profile</h1>
        </header>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="tasks.php">My Tasks</a></li>
                <li><a href="logout.php" onclick="return confirm('Logout?')">Logout</a></li>
            </ul>
        </nav>

        <?php if ($error): ?>
            <div class="error message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>" />
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>" />

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>" />

            <hr />

            <p><strong>Change Password (optional):</strong></p>
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password" />

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" />

            <hr />

            <label for="current_password">Current Password (required to save changes):</label>
            <input type="password" id="current_password" name="current_password" required placeholder="Enter current password" />

            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>