<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
  header("Location: ../auth/login.php");
  exit();
}

$instructor_id = $_SESSION['user_id'];
$message = '';

// Get list of courses the instructor owns
$database = new Database();
$db = $database->getConnection();
$courses_stmt = $db->prepare("SELECT id, title FROM courses WHERE instructor_id = ?");
$courses_stmt->execute([$instructor_id]);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $course_id = $_POST['course_id'];
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $due_date = $_POST['due_date'];
  $max_score = isset($_POST['max_score']) ? floatval($_POST['max_score']) : 100;
  $assignment_file = isset($_FILES['assignment_file']) && $_FILES['assignment_file']['size'] > 0 ? $_FILES['assignment_file'] : null;
  $file_path = null;
  if ($assignment_file) {
    $target_dir = '../assets/files/assignments/';
    if (!is_dir($target_dir)) {
      mkdir($target_dir, 0777, true);
    }
    $file_name = time() . '_' . basename($assignment_file['name']);
    $target_file = $target_dir . $file_name;
    if (move_uploaded_file($assignment_file['tmp_name'], $target_file)) {
      $file_path = $target_file;
    }
  }
  if ($title && $due_date && $course_id > 0 && $max_score > 0) {
    try {
      $stmt = $db->prepare("INSERT INTO assignments (course_id, title, description, due_date, max_points, created_by, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->execute([
        $course_id,
        $title,
        $description,
        $due_date,
        $max_score,
        $instructor_id,
        $file_path
      ]);
      $message = "✅ Assignment created successfully.";
    } catch (PDOException $e) {
      $message = "❌ Failed to create assignment: " . $e->getMessage();
    }
  } else {
    $message = 'Please fill in all required fields.';
  }

  $stmt = $db->prepare("INSERT INTO assignments (course_id, title, description, due_date, created_by) VALUES (?, ?, ?, ?, ?)");
  if ($stmt->execute([$course_id, $title, $description, $due_date, $instructor_id])) {
    $message = "✅ Assignment created successfully.";
  } else {
    $message = "❌ Failed to create assignment.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Assignment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body class="bg-light">
  <?php include '../includes/instructor_navbar.php'; ?>
  <div class="container-fluid">
    <div class="row">
      <?php include '../includes/instructor_sidebar.php'; ?>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2"><i class="fas fa-plus"></i> Create Assignment</h1>
        </div>
        <?php if ($message): ?>
          <div class="alert alert-info mb-4"><?= $message ?></div>
        <?php endif; ?>
        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-tasks"></i> New Assignment
          </div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="assignment_file" class="form-label">Assignment File</label>
                <input type="file" class="form-control" name="assignment_file" id="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar,.jpg,.png,.jpeg">
              </div>
              <div class="mb-3">
                <label for="course_id" class="form-label">Course</label>
                <select name="course_id" id="course_id" required class="form-select">
                  <option value="">Select course</option>
                  <?php foreach ($courses as $row): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="title" class="form-label">Assignment Title</label>
                <input type="text" name="title" id="title" required class="form-control">
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4" class="form-control"></textarea>
              </div>
              <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" name="due_date" id="due_date" required class="form-control">
              </div>
              <div class="mb-3">
                <label for="max_score" class="form-label">Max Score</label>
                <input type="number" name="max_score" id="max_score" value="100" min="1" step="0.01" required class="form-control">
              </div>
              <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary"></i> Create Assignment</button>
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