<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: ../auth/login.php");
  exit();
}

$database = new Database();
$db = $database->getConnection();

// Get course id
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$course_id) {
  echo "<div class='alert alert-danger'>Invalid course ID.</div>";
  exit();
}

// Handle enroll POST before any output
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
  $student_id = $_SESSION['user_id'];
  // Check if already enrolled
  $stmt = $db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
  $stmt->execute([$student_id, $course_id]);
  if ($stmt->fetch()) {
    $enroll_error = 'Bạn đã đăng ký khóa học này.';
  } else {
    // Check max students
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments WHERE course_id = ? AND status = 'enrolled'");
    $stmt->execute([$course_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_count = $current ? intval($current['total']) : 0;
    // Get max_students from course
    $stmt = $db->prepare("SELECT max_students FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course_info = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_students = $course_info ? intval($course_info['max_students']) : 0;
    if ($max_students > 0 && $current_count >= $max_students) {
      $enroll_error = 'Khóa học đã đủ số lượng sinh viên.';
    } else {
      $stmt = $db->prepare("INSERT INTO enrollments (student_id, course_id, status, enrollment_date) VALUES (?, ?, 'enrolled', NOW())");
      if ($stmt->execute([$student_id, $course_id])) {
        header("Location: dashboard.php?enroll_success=1");
        exit();
      } else {
        $enroll_error = 'Đăng ký thất bại.';
      }
    }
  }
}

// Get course info
$stmt = $db->prepare("SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) AS instructor FROM courses c JOIN users u ON c.instructor_id = u.id WHERE c.id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) {
  echo "<div class='alert alert-danger'>Course not found.</div>";
  exit();
}

// Get materials
$stmt = $db->prepare("SELECT * FROM course_materials WHERE course_id = ? ORDER BY upload_date DESC");
$stmt->execute([$course_id]);
$materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get assignments
$stmt = $db->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY due_date DESC");
$stmt->execute([$course_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get quizzes
$stmt = $db->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY due_date DESC");
$stmt->execute([$course_id]);
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Course Details - Student</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
  <?php include '../includes/student_navbar.php'; ?>
  <div class="container-fluid">
    <div class="row">
      <?php include '../includes/student_sidebar.php'; ?>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2"><i class="fas fa-book"></i> <?= htmlspecialchars($course['title']) ?></h1>
        </div>
        <?php
        if (isset($_GET['enroll_success'])) {
          echo '<div class="alert alert-success">Đăng ký thành công!</div>';
        }
        ?>
        <div class="mb-4">
          <h5>Instructor: <?= htmlspecialchars($course['instructor']) ?></h5>
          <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student'): ?>
        <?php
          $student_id = $_SESSION['user_id'];
          $enrolled = false;
          $stmt = $db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
          $stmt->execute([$student_id, $course_id]);
          if ($stmt->fetch()) {
            $enrolled = true;
          }
        ?>
        <?php if (!$enrolled): ?>
        <form method="POST" class="mb-3">
          <button type="submit" name="enroll" class="btn btn-success">
            <i class="fas fa-user-plus"></i> Enroll
          </button>
        </form>
        <?php
          if (isset($_POST['enroll'])) {
            // Check max students
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM enrollments WHERE course_id = ? AND status = 'enrolled'");
            $stmt->execute([$course_id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_count = $current ? intval($current['total']) : 0;
            $max_students = isset($course['max_students']) ? intval($course['max_students']) : 0;
            if ($max_students > 0 && $current_count >= $max_students) {
              echo '<div class="alert alert-danger">Khóa học đã đủ số lượng sinh viên.</div>';
            } else {
              $stmt = $db->prepare("INSERT INTO enrollments (student_id, course_id, status, enrollment_date) VALUES (?, ?, 'enrolled', NOW())");
              if ($stmt->execute([$student_id, $course_id])) {
                header("Location: ../instructor/students.php?course_id=$course_id&student_id=$student_id");
                exit();
              } else {
                echo '<div class="alert alert-danger">Đăng ký thất bại.</div>';
              }
            }
          }
        ?>
        <?php else: ?>
          <div class="alert alert-warning">You are already enrolled in this course.</div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-3"><strong>Course Code:</strong> <?= htmlspecialchars($course['course_code']) ?></div>
            <div class="col-md-3"><strong>Credits:</strong> <?= htmlspecialchars($course['credits']) ?></div>
            <div class="col-md-3"><strong>Semester:</strong> <?= htmlspecialchars($course['semester']) ?></div>
            <div class="col-md-3"><strong>Year:</strong> <?= htmlspecialchars($course['year']) ?></div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="card mb-4">
              <div class="card-header"><i class="fas fa-file"></i> Materials</div>
              <div class="card-body">
                <?php if ($materials): ?>
                  <ul class="list-group">
                    <?php foreach ($materials as $m): ?>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                          <strong><?= htmlspecialchars($m['title']) ?></strong><br>
                          <?= htmlspecialchars($m['description']) ?>
                        </span>
                        <a href="<?= $m['file_path'] ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-download"></i> Download</a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="text-muted">No materials available.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card mb-4">
              <div class="card-header"><i class="fas fa-tasks"></i> Assignments</div>
              <div class="card-body">
                <?php if ($assignments): ?>
                  <ul class="list-group">
                    <?php foreach ($assignments as $a): ?>
                      <li class="list-group-item">
                        <strong><?= htmlspecialchars($a['title']) ?></strong> - Due: <?= htmlspecialchars($a['due_date']) ?><br>
                        <?= htmlspecialchars($a['description']) ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="text-muted">No assignments available.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="card mb-4">
              <div class="card-header"><i class="fas fa-question"></i> Quizzes</div>
              <div class="card-body">
                <?php if ($quizzes): ?>
                  <ul class="list-group">
                    <?php foreach ($quizzes as $q): ?>
                      <li class="list-group-item">
                        <strong><?= htmlspecialchars($q['title']) ?></strong> - Due: <?= htmlspecialchars($q['due_date']) ?><br>
                        <?= htmlspecialchars($q['description']) ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <div class="text-muted">No quizzes available.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>