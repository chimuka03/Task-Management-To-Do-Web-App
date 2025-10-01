<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $db = (new Database())->connect();

    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $deadline = $_POST['deadline'];
    $user_id = $_SESSION['user_id'];

    $stmt = $db->prepare("INSERT INTO tasks (user_id, title, description, status, deadline) 
                          VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $status, $deadline]);

    header("Location: tasks.php"); // reload tasks page
}
?>
