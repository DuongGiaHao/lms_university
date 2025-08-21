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

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// Get quiz info
$stmt = $db->prepare("SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quiz) {
    echo '<div class="alert alert-danger">Quiz not found.</div>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Quiz</title>
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
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-eye"></i> View Quiz</h1>
                    <a href="quizzes.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-question-circle"></i> Quiz Info</div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Course</dt>
                            <dd class="col-sm-9"><?= htmlspecialchars($quiz['course_title']) ?></dd>
                            <dt class="col-sm-3">Title</dt>
                            <dd class="col-sm-9"><?= htmlspecialchars($quiz['title']) ?></dd>
                            <dt class="col-sm-3">Description</dt>
                            <dd class="col-sm-9"><?= htmlspecialchars($quiz['description']) ?></dd>
                            <dt class="col-sm-3">Time Limit</dt>
                            <dd class="col-sm-9"><?= $quiz['time_limit'] ?> minutes</dd>
                            <dt class="col-sm-3">Max Attempts</dt>
                            <dd class="col-sm-9"><?= $quiz['max_attempts'] ?></dd>
                            <dt class="col-sm-3">Due Date</dt>
                            <dd class="col-sm-9"><?= date('Y-m-d H:i', strtotime($quiz['due_date'])) ?></dd>
                            <dt class="col-sm-3">Created At</dt>
                            <dd class="col-sm-9"><?= date('Y-m-d', strtotime($quiz['created_at'])) ?></dd>
                        </dl>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
