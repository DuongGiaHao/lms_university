<?php
require_once '../config/database.php';
require_once '../includes/instructor_sidebar.php';

requireLogin();
if (!hasRole('instructor')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Lấy danh sách các quiz của instructor
$query = "SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE c.instructor_id = ? ORDER BY q.due_date DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes - Instructor</title>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-question-circle"></i> Quizzes</h1>
                    <a href="quiz_create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Quiz</a>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table"></i> Quiz List
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Course</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Time Limit (min)</th>
                                        <th>Max Attempts</th>
                                        <th>Due Date</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quizzes as $quiz): ?>
                                    <tr>
                                        <td><?= $quiz['id'] ?></td>
                                        <td><?= htmlspecialchars($quiz['course_title']) ?></td>
                                        <td><?= htmlspecialchars($quiz['title']) ?></td>
                                        <td><?= htmlspecialchars($quiz['description']) ?></td>
                                        <td><?= $quiz['time_limit'] ?></td>
                                        <td><?= $quiz['max_attempts'] ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($quiz['due_date'])) ?></td>
                                        <td><?= date('Y-m-d', strtotime($quiz['created_at'])) ?></td>
                                        <td>
                                            <a href="quiz_edit.php?id=<?= $quiz['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="quiz_delete.php?id=<?= $quiz['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this quiz?');"><i class="fas fa-trash"></i></a>
                                            <a href="quiz_questions.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-list"></i> Questions</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
