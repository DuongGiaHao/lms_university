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

// Lấy thông tin khóa học
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

    if ($title && $course_code && $credits > 0 && $year > 0 && $max_students > 0) {
        try {
            $stmt = $db->prepare("UPDATE courses SET title=?, description=?, course_code=?, credits=?, semester=?, year=?, max_students=?, status=? WHERE id=? AND instructor_id=?");
            $stmt->execute([
                $title,
                $description,
                $course_code,
                $credits,
                $semester,
                $year,
                $max_students,
                $status,
                $course_id,
                $_SESSION['user_id']
            ]);
            $success = true;
            // Refresh course info
            $stmt = $db->prepare($query);
            $stmt->execute([$course_id, $_SESSION['user_id']]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Edit Course - Instructor</title>
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
                    <h1 class="h2"><i class="fas fa-edit"></i> Edit Course</h1>
                </div>
                <?php if ($success): ?>
                    <div class="alert alert-success">Course updated successfully!</div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger">Error: <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-book"></i> Edit Course Info
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="title" value="<?= htmlspecialchars($course['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="description"><?= htmlspecialchars($course['description']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="course_code" class="form-label">Course Code</label>
                                <input type="text" class="form-control" name="course_code" id="course_code" value="<?= htmlspecialchars($course['course_code']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="credits" class="form-label">Credits</label>
                                <input type="number" class="form-control" name="credits" id="credits" value="<?= $course['credits'] ?>" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="semester" class="form-label">Semester</label>
                                <input type="text" class="form-control" name="semester" id="semester" value="<?= htmlspecialchars($course['semester']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="year" class="form-label">Year</label>
                                <input type="number" class="form-control" name="year" id="year" value="<?= $course['year'] ?>" min="2000" required>
                            </div>
                            <div class="mb-3">
                                <label for="max_students" class="form-label">Max Students</label>
                                <input type="number" class="form-control" name="max_students" id="max_students" value="<?= $course['max_students'] ?>" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status">
                                    <option value="active" <?= $course['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $course['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Course</button>
                            <a href="courses.php" class="btn btn-secondary">Back to Courses</a>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
