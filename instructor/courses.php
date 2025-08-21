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

// Thêm khóa học mới
$add_success = false;
$add_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
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
            $stmt = $db->prepare("INSERT INTO courses (title, description, instructor_id, course_code, credits, semester, year, max_students, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $title,
                $description,
                $_SESSION['user_id'],
                $course_code,
                $credits,
                $semester,
                $year,
                $max_students,
                $status
            ]);
            $add_success = true;
        } catch (PDOException $e) {
            $add_error = $e->getMessage();
        }
    } else {
        $add_error = 'Please fill in all required fields.';
    }
}

// Lấy danh sách các khóa học của instructor
$query = "SELECT * FROM courses WHERE instructor_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Instructor</title>
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
                    <h1 class="h2"><i class="fas fa-book"></i> My Courses</h1>
                    <a href="add_courses.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Course</a>
                </div>
                <?php if ($add_success): ?>
                    <div class="alert alert-success">Course added successfully!</div>
                <?php elseif ($add_error): ?>
                    <div class="alert alert-danger">Error: <?= htmlspecialchars($add_error) ?></div>
                <?php endif; ?>
                <!-- Modal Add Course -->
                <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="post">
                        <div class="modal-header">
                          <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
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
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table"></i> Course List
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Course Code</th>
                                        <th>Credits</th>
                                        <th>Semester</th>
                                        <th>Year</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?= $course['id'] ?></td>
                                        <td><?= htmlspecialchars($course['title']) ?></td>
                                        <td><?= htmlspecialchars($course['course_code']) ?></td>
                                        <td><?= $course['credits'] ?></td>
                                        <td><?= htmlspecialchars($course['semester']) ?></td>
                                        <td><?= $course['year'] ?></td>
                                        <td><span class="badge bg-<?= $course['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($course['status']) ?>
                                        </span></td>
                                        <td><?= date('Y-m-d', strtotime($course['created_at'])) ?></td>
                                        <td>
                                            <a href="course_edit.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="course_view.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            <a href="course_delete.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?');"><i class="fas fa-trash"></i></a>
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
