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

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($course_id <= 0) {
    header('Location: courses.php');
    exit();
}

$query = "SELECT * FROM courses WHERE id = ? AND instructor_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
    header('Location: courses.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details - Instructor</title>
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
                    <h1 class="h2"><i class="fas fa-eye"></i> Course Details</h1>
                    <div>
                        <a href="courses.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
                        <a href="course_edit.php?id=<?= $course['id'] ?>" class="btn btn-warning ms-2"><i class="fas fa-edit"></i> Edit</a>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-book"></i> Course Information
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Title</dt>
                            <dd class="col-sm-9"><?= htmlspecialchars($course['title']) ?></dd>
                            <dt class="col-sm-3">Description</dt>
                            <dd class="col-sm-9"><?= nl2br(htmlspecialchars($course['description'])) ?></dd>
                            <dt class="col-sm-3">Course Code</dt>
                            <dd class="col-sm-9"><?= htmlspecialchars($course['course_code']) ?></dd>
                            <dt class="col-sm-3">Credits</dt>
                            <dd class="col-sm-9"><?= $course['credits'] ?></dd>
                            <dt class="col-sm-3">Semester</dt>
                            <dd class="col-sm-9"><?= htmlspecialchars($course['semester']) ?></dd>
                            <dt class="col-sm-3">Year</dt>
                            <dd class="col-sm-9"><?= $course['year'] ?></dd>
                            <dt class="col-sm-3">Max Students</dt>
                            <dd class="col-sm-9"><?= $course['max_students'] ?></dd>
                            <dt class="col-sm-3">Status</dt>
                            <dd class="col-sm-9"><span class="badge bg-<?= $course['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($course['status']) ?></span></dd>
                            <dt class="col-sm-3">Created At</dt>
                            <dd class="col-sm-9"><?= date('Y-m-d', strtotime($course['created_at'])) ?></dd>
                        </dl>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
