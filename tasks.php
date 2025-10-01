<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$db = (new Database())->connect();
$stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>My Tasks</title>
</head>
<body>
  <h1>Hello, <?php echo $_SESSION['username']; ?>! Here are your tasks:</h1>

  <a href="logout.php">Logout</a>

  <form method="POST" action="task_add.php">
    <input type="text" name="title" placeholder="Task Title" required>
    <textarea name="description" placeholder="Description"></textarea>
    <select name="status">
      <option value="pending">Pending</option>
      <option value="in-progress">In Progress</option>
      <option value="completed">Completed</option>
    </select>
    <input type="date" name="deadline">
    <button type="submit">Add Task</button>
  </form>

  <ul>
    <?php foreach($tasks as $task): ?>
      <li>
        <?php echo htmlspecialchars($task['title']); ?> - 
        <strong><?php echo $task['status']; ?></strong> 
        (Deadline: <?php echo $task['deadline']; ?>)
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
