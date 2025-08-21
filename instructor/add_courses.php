<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/database.php';
$database = new Database();
$conn = $database->getConnection();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_code = trim($_POST['course_code']);
    $credits = intval($_POST['credits']);
    $semester = trim($_POST['semester']);
    $year = intval($_POST['year']);
    $max_students = intval($_POST['max_students']);
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
    $instructor_id = $_SESSION['user_id'];
    $params = [
        ':title' => $title,
        ':description' => $description,
        ':instructor_id' => $instructor_id,
        ':course_code' => $course_code,
        ':credits' => $credits,
        ':semester' => $semester,
        ':year' => $year,
        ':max_students' => $max_students,
        ':status' => $status
    ];
    $sql = "INSERT INTO courses (title, description, instructor_id, course_code, credits, semester, year, max_students, status) VALUES (:title, :description, :instructor_id, :course_code, :credits, :semester, :year, :max_students, :status)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute($params)) {
        header('Location: courses.php');
        exit();
    } else {
        $message = "<div class='alert alert-danger'>Add course failed!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - University LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<?php include '../includes/instructor_navbar.php'; ?>
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/instructor_sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-plus"></i> Add Course</h1>
            </div>
            <div class="card mb-4">
                <div class="card-header bg-success text-white"><i class="fas fa-plus"></i> Add Course</div>
                <div class="card-body">
                    <?php if ($message) echo $message; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Course Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" name="course_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Credits</label>
                            <input type="number" name="credits" class="form-control" value="3" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="text" name="semester" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" min="2000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Maximum Students</label>
                            <input type="number" name="max_students" class="form-control" value="50" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">Add</button>
                            <a href="courses.php" class="btn btn-secondary">Cancel</a>
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
