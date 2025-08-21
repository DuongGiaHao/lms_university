<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}
$student_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();
// Lấy danh sách quiz của các khóa học mà sinh viên đã enroll
$sql = "
    SELECT q.id AS quiz_id, q.title, q.description, q.due_date, q.time_limit, q.max_attempts, c.title AS course_title,
        qa.id AS attempt_id, qa.attempt_number, qa.score, qa.completed_at
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN quizzes q ON c.id = q.course_id
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = :student_id
    WHERE e.student_id = :student_id
    ORDER BY q.due_date ASC
";
$stmt = $db->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Quizzes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../includes/student_navbar.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-question-circle"></i> My Quizzes</h1>
                </div>
                <div class="row">
                    <?php if (count($quizzes) > 0): ?>
                        <?php foreach ($quizzes as $quiz): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Quiz: <?= htmlspecialchars($quiz['title']) ?></h5>
                                        <p class="card-text text-muted mb-2">Course: <?= htmlspecialchars($quiz['course_title']) ?></p>
                                        <p class="card-text mb-2"><?= nl2br(htmlspecialchars($quiz['description'])) ?></p>
                                        <p class="card-text text-secondary mb-2">Due: <?= htmlspecialchars($quiz['due_date']) ?></p>
                                        <p class="card-text mb-2">Time Limit: <?= htmlspecialchars($quiz['time_limit']) ?> min | Max Attempts: <?= htmlspecialchars($quiz['max_attempts']) ?></p>
                                        <?php if ($quiz['attempt_id']): ?>
                                            <p class="text-success fw-bold">✅ Attempted (Score: <?= htmlspecialchars($quiz['score']) ?>)</p>
                                            <p class="text-muted small">Completed: <?= htmlspecialchars($quiz['completed_at']) ?></p>
                                        <?php else: ?>
                                            <a href="quiz_start.php?quiz_id=<?= $quiz['quiz_id'] ?>" class="btn btn-primary"><i class="fas fa-play"></i> Start Quiz</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">You have no quizzes.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
