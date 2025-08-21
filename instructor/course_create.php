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

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_code = trim($_POST['course_code']);
    $credits = intval($_POST['credits']);
    $semester = trim($_POST['semester']);
    $year = intval($_POST['year']);
    $max_students = intval($_POST['max_students']);
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
    $course_image = isset($_FILES['course_image']) && $_FILES['course_image']['size'] > 0 ? $_FILES['course_image'] : null;
    $image_path = null;
    if ($course_image) {
        $target_dir = '../assets/img/courses/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($course_image['name']);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($course_image['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }
    if ($title && $course_code && $credits > 0 && $year > 0 && $max_students > 0) {
        try {
            $stmt = $db->prepare("INSERT INTO courses (title, description, instructor_id, course_code, credits, semester, year, max_students, status, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $title,
                $description,
                $_SESSION['user_id'],
                $course_code,
                $credits,
                $semester,
                $year,
                $max_students,
                $status,
                $image_path
            ]);
            header('Location: courses.php');
            exit();
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
    <title>Create Course - Instructor</title>
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
                    <h1 class="h2"><i class="fas fa-plus"></i> Create Course</h1>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success">Course created successfully!</div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger">Error: <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-book"></i> New Course
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="course_image" class="form-label">Course Image</label>
                                <input type="file" class="form-control" name="course_image" id="course_image" accept="image/*">
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
                                <label for="course_code" class="form-label">Course Code</label>
                                <input type="text" class="form-control" name="course_code" id="course_code" required>
                            </div>
                            <div class="mb-3">
                                <label for="credits" class="form-label">Credits</label>
                                <input type="number" class="form-control" name="credits" id="credits" value="3" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="semester" class="form-label">Semester</label>
                                <input type="text" class="form-control" name="semester" id="semester">
                            </div>
                            <div class="mb-3">
                                <label for="year" class="form-label">Year</label>
                                <input type="number" class="form-control" name="year" id="year" value="2025" min="2000" required>
                            </div>
                            <div class="mb-3">
                                <label for="max_students" class="form-label">Max Students</label>
                                <input type="number" class="form-control" name="max_students" id="max_students" value="50" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3 d-flex justify-content-start">
                                <a href="courses.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left"></i> Back to Courses</a>
                                <button type="submit" class="btn btn-primary">Create Course</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
