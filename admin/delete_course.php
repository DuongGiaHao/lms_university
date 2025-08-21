<?php
require_once '../config/database.php';
requireLogin();
if (!hasRole('admin')) {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: courses.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$id = intval($_GET['id']);

// Xoá khoá học
$stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
$stmt->execute([$id]);

header('Location: courses.php?msg=deleted');
exit();
