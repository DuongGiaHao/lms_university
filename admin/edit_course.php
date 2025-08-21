<?php
require_once '../config/database.php';
requireLogin();
if (!hasRole('admin')) {
  header('Location: ../auth/login.php');
  exit();
}
$database = new Database();
$conn = $database->getConnection();
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
// Lấy dữ liệu khoá học
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
  die("Không tìm thấy khoá học.");
}
// Lấy danh sách giảng viên
$instructors = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'instructor'");
// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $instructor_id = intval($_POST['instructor_id']);
  $course_code = trim($_POST['course_code']);
  $credits = intval($_POST['credits']);
  $semester = trim($_POST['semester']);
  $year = intval($_POST['year']);
  $max_students = intval($_POST['max_students']);
  $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
  $update_stmt = $conn->prepare("UPDATE courses SET title=?, description=?, instructor_id=?, course_code=?, credits=?, semester=?, year=?, max_students=?, status=? WHERE id=?");
  $update_stmt->execute([$title, $description, $instructor_id, $course_code, $credits, $semester, $year, $max_students, $status, $course_id]);
  if ($update_stmt->rowCount() > 0) {
    $message = "✅ Cập nhật khoá học thành công.";
    // Refresh course data
    $course = array_merge($course, [
      'title' => $title,
      'description' => $description,
      'instructor_id' => $instructor_id,
      'course_code' => $course_code,
      'credits' => $credits,
      'semester' => $semester,
      'year' => $year,
      'max_students' => $max_students,
      'status' => $status
    ]);
  } else {
    $message = "❌ Cập nhật khoá học thất bại hoặc không có thay đổi.";
  }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chỉnh sửa khoá học - University LMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
  <?php include '../includes/admin_navbar.php'; ?>
  <div class="container-fluid">
    <div class="row">
      <?php include '../includes/admin_sidebar.php'; ?>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="card shadow mt-4">
          <div class="card-header bg-primary text-white">
            <i class="fas fa-edit"></i> Edit Course
          </div>
          <div class="card-body">
            <?php if ($message): ?>
              <div class="alert alert-info"> <?= $message ?> </div>
            <?php endif; ?>
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Course Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($course['title']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" required><?= htmlspecialchars($course['description']) ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Instructor</label>
                <select name="instructor_id" class="form-control" required>
                  <option value="">-- Select Instructor --</option>
                  <?php while ($ins = $instructors->fetch()): ?>
                    <option value="<?= $ins['id'] ?>" <?= ($ins['id'] == $course['instructor_id']) ? 'selected' : '' ?>><?= htmlspecialchars($ins['last_name'] . ' ' . $ins['first_name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Course Code</label>
                <input type="text" name="course_code" class="form-control" value="<?= htmlspecialchars($course['course_code']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Credits</label>
                <input type="number" name="credits" class="form-control" value="<?= $course['credits'] ?>" min="1" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Semester</label>
                <input type="text" name="semester" class="form-control" value="<?= htmlspecialchars($course['semester']) ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-control" value="<?= $course['year'] ?>" min="2000" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Maximum Students</label>
                <input type="number" name="max_students" class="form-control" value="<?= $course['max_students'] ?>" min="1" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                  <option value="active" <?= $course['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $course['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100">Update Course</button>
            </form>
            <div class="mt-3 text-center">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
