<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: ../auth/login.php");
  exit();
}

$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) AS instructor FROM courses c JOIN users u ON c.instructor_id = u.id");
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Courses</title>
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
          <h1 class="h2"><i class="fas fa-book"></i> Available Courses</h1>
        </div>
        <div class="row">
          <?php foreach ($courses as $course): ?>
            <div class="col-md-6 mb-4">
              <div class="card shadow-sm h-100">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                  <p class="card-text text-muted mb-2">Instructor: <?= htmlspecialchars($course['instructor']) ?></p>
                  <p class="card-text mb-3"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                  <?php if (isset($course['price'])): ?>
                    <p class="fw-bold text-primary">$<?= htmlspecialchars($course['price']) ?></p>
                  <?php endif; ?>
                  <a href="course_view.php?id=<?= isset($course['id']) ? $course['id'] : '' ?>" class="btn btn-primary">
                    <i class="fas fa-search"></i> View
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>