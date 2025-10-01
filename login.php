<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = (new Database())->connect();

    $password = trim($_POST['password']); // only password is taken

    if (empty($password)) {
        die("Password is required.");
    }

    // Fetch the single user (assuming you only have one main user)
    $stmt = $db->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username']; // optional, you can remove this if unused
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Invalid password.";
    }
}
?>
