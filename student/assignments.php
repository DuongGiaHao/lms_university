<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
  header("Location: ../auth/login.php");
  exit();
}

$student_id = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// Fetch all assignments for enrolled courses (PDO)
$sql = "
  SELECT a.id AS assignment_id, a.title, a.description, a.due_date, c.title AS course_title, s.grade
  FROM enrollments e
  JOIN courses c ON e.course_id = c.id
  JOIN assignments a ON c.id = a.course_id
  LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = :student_id
  WHERE e.student_id = :student_id
  ORDER BY a.due_date ASC
";
$stmt = $db->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Assignments</title>
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
          <h1 class="h2"><i class="fas fa-tasks"></i> My Assignments</h1>
        </div>
        <div class="row">
          <?php if (count($result) > 0): ?>
            <?php foreach ($result as $row): ?>
              <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                  <div class="card-body">
                    <h5 class="card-title">📝 <?= htmlspecialchars($row['title']) ?></h5>
                    <p class="card-text text-muted mb-2">Course: <?= htmlspecialchars($row['course_title']) ?></p>
                    <p class="card-text mb-2"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                    <p class="card-text text-secondary mb-2">Due: <?= htmlspecialchars($row['due_date']) ?></p>
                    <?php if ($row['grade'] !== null): ?>
                      <p class="text-success fw-bold">✅ Graded: <?= htmlspecialchars($row['grade']) ?></p>
                    <?php else: ?>
                      <a href="submit_assignment.php?assignment_id=<?= $row['assignment_id'] ?>" class="btn btn-primary"><i class="fas fa-upload"></i> Submit Assignment</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="alert alert-info">You have no assignments.</div>
            </div>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>