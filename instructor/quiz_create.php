<?php
require_once '../config/database.php';
require_once '../includes/instructor_sidebar.php';

requireLogin();
if (!hasRole('instructor')) {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection ();

// Lấy danh sách các khóa học của instructor để chọn khi tạo quiz
$query = "SELECT id, title FROM courses WHERE instructor_id = ? ORDER BY title ASC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $time_limit = intval($_POST['time_limit']);
    $max_attempts = intval($_POST['max_attempts']);
    $due_date = $_POST['due_date'];
    $created_by = $_SESSION['user_id'];
    $quiz_file = isset($_FILES['quiz_file']) && $_FILES['quiz_file']['size'] > 0 ? $_FILES['quiz_file'] : null;
    $file_path = null;
    if ($quiz_file) {
        $target_dir = '../assets/files/quizzes/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($quiz_file['name']);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($quiz_file['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }
    if ($course_id && $title && $due_date && $max_attempts > 0 && $time_limit > 0) {
        try {
            $stmt = $db->prepare("INSERT INTO quizzes (course_id, title, description, time_limit, max_attempts, due_date, created_by, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $course_id,
                $title,
                $description,
                $time_limit,
                $max_attempts,
                $due_date,
                $created_by,
                $file_path
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - Instructor</title>
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
                    <h1 class="h2"><i class="fas fa-plus"></i> Create Quiz</h1>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success">Quiz created successfully!</div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger">Error: <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-question-circle"></i> New Quiz
                    </div>
                    <div class="card-body">
                        <form method="post" id="quizForm" enctype="multipart/form-data">
                            <div class="mb-3">
                            <div class="mb-3">
                                <label for="quiz_file" class="form-label">Quiz File</label>
                                <input type="file" class="form-control" name="quiz_file" id="quiz_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar,.jpg,.png,.jpeg">
                            </div>
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-select" name="course_id" id="course_id" required>
                                    <option value="">Select course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="description"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                <input type="number" class="form-control" name="time_limit" id="time_limit" value="30" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="max_attempts" class="form-label">Max Attempts</label>
                                <input type="number" class="form-control" name="max_attempts" id="max_attempts" value="1" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="datetime-local" class="form-control" name="due_date" id="due_date" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Quiz</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
