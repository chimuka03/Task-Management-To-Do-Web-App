<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle actions (with CSRF verification)
if ($_POST && verifyCSRF($_POST['csrf'] ?? '')) {
    $csrf = generateCSRF();

    // Add task
    if (isset($_POST['add_task'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $priority = $_POST['priority'] ?? 'medium';
        $due_date = $_POST['due_date'] ?? null;
        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, priority, due_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $description, $priority, $due_date]);
            $success = "Task added successfully!";
        } else {
            $error = "Title is required.";
        }
    }

    // Update task (edit or status change)
    if (isset($_POST['update_task'])) {
        $task_id = (int)$_POST['task_id'];
        $title = trim($_POST['edit_title']);
        $description = trim($_POST['edit_description']);
        $status = $_POST['edit_status'];
        $priority = $_POST['edit_priority'];
        $due_date = $_POST['edit_due_date'] ?? null;
        if (!empty($title) && $task_id > 0) {
            $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ?, priority = ?, due_date = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $status, $priority, $due_date, $task_id, $user_id]);
            $success = "Task updated successfully!";
        } else {
            $error = "Invalid update data.";
        }
    }

    // Delete task
    if (isset($_POST['delete_task'])) {
        $task_id = (int)$_POST['task_id'];
        if ($task_id > 0) {
            $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$task_id, $user_id]);
            $success = "Task deleted successfully!";
        } else {
            $error = "Invalid task ID.";
        }
    }
}

// Handle GET toggle for status (simple toggle without form for quick use)
if (isset($_GET['toggle'])) {
    $task_id = (int)$_GET['toggle'];
    if ($task_id > 0) {
        $stmt = $conn->prepare("UPDATE tasks SET status = CASE WHEN status = 'pending' THEN 'completed' ELSE 'pending' END WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $success = "Task status toggled!";
    }
    header("Location: tasks.php" . ($_GET['search'] ? "?search=" . urlencode($_GET['search']) : ""));
    exit();
}

// Search, Filter, and Pagination
$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = "user_id = ?";
$params = [$user_id];
if ($search) {
    $where .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter !== 'all') {
    $where .= " AND status = ?";
    $params[] = $filter;
}

// Count total tasks for pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM tasks WHERE $where");
$count_stmt->execute($params);
$total_tasks = $count_stmt->fetch()['total'];
$total_pages = ceil($total_tasks / $limit);

// Fetch tasks - FIXED: Interpolate LIMIT/OFFSET directly (safe for integers)
$query = "SELECT * FROM tasks WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);  // No need to include limit/offset in params
$tasks = $stmt->fetchAll();

// Edit mode: If editing a specific task, fetch it
$edit_task = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $edit_stmt->execute([$edit_id, $user_id]);
    $edit_task = $edit_stmt->fetch();
    if (!$edit_task) {
        header("Location: tasks.php");
        exit();
    }
}

$csrf = generateCSRF();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - To-Do App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>My Tasks</h1>
        </header>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a></li>
            </ul>
        </nav>

        <!-- Messages -->
        <?php if (isset($error)): ?>
            <div class="error message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Search and Filter -->
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search tasks..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="filter">
                <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
                <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo $filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
            <button type="submit">Filter</button>
            <?php if ($search || $filter !== 'all'): ?>
                <a href="tasks.php" style="margin-left: 10px;">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Add Task Form -->
        <form method="POST">
            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
            <h2>Add New Task</h2>
            <input type="text" name="title" placeholder="Task Title" required value="<?php echo $_POST['title'] ?? ''; ?>">
            <textarea name="description" placeholder="Description (optional)"><?php echo $_POST['description'] ?? ''; ?></textarea>
            <select name="priority">
                <option value="low">Low Priority</option>
                <option value="medium" selected>Medium Priority</option>
                <option value="high">High Priority</option>
            </select>
            <input type="date" name="due_date" placeholder="Due Date (optional)">
            <button type="submit" name="add_task">Add Task</button>
        </form>

        <!-- Tasks List -->
        <h2>Your Tasks (<?php echo $total_tasks; ?> total)</h2>
        <?php if (empty($tasks)): ?>
            <p>No tasks found. Add one above!</p>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task-item <?php echo $task['status'] === 'completed' ? 'completed' : ''; ?> priority-<?php echo $task['priority']; ?>">
                    <div>
                        <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p><?php echo htmlspecialchars($task['description'] ?: 'No description'); ?></p>
                        <small>Priority: <?php echo ucfirst($task['priority']); ?> | Status: <?php echo ucfirst($task['status']); ?> | Created: <?php echo $task['created_at']; ?>
                            <?php if ($task['due_date']): ?> | Due: <?php echo $task['due_date']; ?><?php endif; ?></small>
                    </div>
                    <div>
                        <a href="?toggle=<?php echo $task['id']; ?>&<?php echo http_build_query(['search' => $search, 'filter' => $filter, 'page' => $page]); ?>" 
                           onclick="return confirm('Toggle status?')">Toggle Status</a> |
                        <a href="?edit=<?php echo $task['id']; ?>&<?php echo http_build_query(['search' => $search, 'filter' => $filter, 'page' => $page]); ?>">Edit</a> |
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this task?');">
                            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <button type="submit" name="delete_task" class="delete-btn">Delete</button>
                        </form>
                    </div>

                    <!-- Inline Edit Form (shows if editing this task) -->
                    <?php if ($edit_task && $edit_task['id'] == $task['id']): ?>
                        <form method="POST" class="edit-form">
                            <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="text" name="edit_title" placeholder="Title" required value="<?php echo htmlspecialchars($edit_task['title']); ?>">
                            <textarea name="edit_description" placeholder="Description"><?php echo htmlspecialchars($edit_task['description']); ?></textarea>
                            <select name="edit_status">
                                <option value="pending" <?php echo $edit_task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $edit_task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                            <select name="edit_priority">
                                <option value="low" <?php echo $edit_task['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                                <option value="medium" <?php echo $edit_task['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="high" <?php echo $edit_task['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                            </select>
                            <input type="date" name="edit_due_date" value="<?php echo $edit_task['due_date']; ?>">
                            <button type="submit" name="update_task">Update</button>
                            <a href="tasks.php?<?php echo http_build_query(['search' => $search, 'filter' => $filter, 'page' => $page]); ?>">Cancel</a>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>">Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" 
                       <?php echo $i === $page ? 'style="background: #333;"' : ''; ?>><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>