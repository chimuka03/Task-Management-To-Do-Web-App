<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch stats
$stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed FROM tasks WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
$completion_rate = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - To-Do App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        </header>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="tasks.php">My Tasks</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" onclick="return confirm('Logout?')">Logout</a></li>
            </ul>
        </nav>
        <div class="stats">
            <div class="stat-item">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Tasks</p>
            </div>
            <div class="stat-item">
                <h3><?php echo $stats['completed']; ?></h3>
                <p>Completed</p>
            </div>
            <div class="stat-item">
                <h3><?php echo $completion_rate; ?>%</h3>
                <p>Completion Rate</p>
            </div>
        </div>
        <div class="message success">Manage your tasks and profile below.</div>
    </div>
</body>
</html>