<?php
session_start();
require_once '../config/database.php';
require_once '../includes/instructor_sidebar.php';

requireLogin();
if (!hasRole('instructor')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

if ($id) {
    $stmt = $db->prepare("DELETE FROM assignments WHERE id = ?");
    if ($stmt->execute([$id])) {
        header('Location: assignments.php?delete_success=1');
        exit();
    } else {
        $message = '❌ Delete failed.';
    }
} else {
    $message = '❌ Invalid assignment ID.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/instructor_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/instructor_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-trash"></i> Delete Assignment</h1>
                </div>
                <?php if ($message): ?>
                    <div class="alert alert-danger"> <?= $message ?> </div>
                <?php endif; ?>
                <a href="assignments.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Assignments</a>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
