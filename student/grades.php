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
// Lấy danh sách điểm các assignment đã được chấm
$sql = "
    SELECT g.id, a.title AS assignment_title, c.title AS course_title, g.grade, g.feedback, g.graded_at, u.first_name AS instructor_first, u.last_name AS instructor_last, s.file_url
    FROM grading g
    JOIN assignments a ON g.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON g.graded_by = u.id
    LEFT JOIN submissions s ON g.assignment_id = s.assignment_id AND g.student_id = s.student_id
    WHERE g.student_id = :student_id
    ORDER BY g.graded_at DESC
";
$stmt = $db->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Grades</title>
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
                    <h1 class="h2"><i class="fas fa-chart-line"></i> My Grades</h1>
                </div>
                <div class="row">
                    <?php if (count($grades) > 0): ?>
                        <?php foreach ($grades as $grade): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Assignment: <?= htmlspecialchars($grade['assignment_title']) ?></h5>
                                        <p class="card-text text-muted mb-2">Course: <?= htmlspecialchars($grade['course_title']) ?></p>
                                        <p class="card-text mb-2">Grade: <span class="fw-bold text-success"><?= htmlspecialchars($grade['grade']) ?></span></p>
                                        <p class="card-text mb-2">Instructor: <?= htmlspecialchars($grade['instructor_first'] . ' ' . $grade['instructor_last']) ?></p>
                                        <p class="card-text text-secondary mb-2">Graded At: <?= htmlspecialchars($grade['graded_at']) ?></p>
                                        <p class="card-text">Feedback: <?= nl2br(htmlspecialchars($grade['feedback'])) ?></p>
                                        <?php if (!empty($grade['file_url'])): ?>
                                            <a href="<?= htmlspecialchars($grade['file_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-2"><i class="fas fa-file-alt"></i> View Submitted File</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">You have no grades yet.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
