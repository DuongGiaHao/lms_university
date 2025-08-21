<?php
require_once '../config/database.php';
requireLogin();
if (!hasRole('admin')) {
  header('Location: ../auth/login.php');
  exit();
}
$database = new Database();
$conn = $database->getConnection();
$sql = "SELECT c.*, u.first_name, u.last_name FROM courses c LEFT JOIN users u ON c.instructor_id = u.id ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Course Management - University LMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/dashboard.css" rel="stylesheet">
  <style>
    .action-link { text-decoration: none; margin: 0 4px; }
  </style>
</head>
<body>
  <?php include '../includes/admin_navbar.php'; ?>
  <div class="container-fluid">
    <div class="row">
      <?php include '../includes/admin_sidebar.php'; ?>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">
            <i class="fas fa-book"></i> Courses Management
          </h1>
          <a href="create_course.php" class="btn btn-success"><i class="fas fa-plus"></i> Add Course</a>
        </div>
        <div class="row mb-4">
          <div class="col-12">
            <div class="card shadow">
              <div class="card-header bg-primary text-white">
                <i class="fas fa-book"></i> Course List
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Course Name</th>
                        <th scope="col">Instructor</th>
                        <th scope="col">Course Code</th>
                        <th scope="col">Credits</th>
                        <th scope="col">Year</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php if ($courses && count($courses) > 0): $i = 1; ?>
                      <?php foreach($courses as $row): ?>
                        <tr>
                          <td><?= $i++ ?></td>
                          <td><?= htmlspecialchars($row['title']) ?></td>
                          <td><?= htmlspecialchars(trim(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? ''))) ?: 'N/A' ?></td>
                          <td><?= htmlspecialchars($row['course_code']) ?></td>
                          <td><?= $row['credits'] ?></td>
                          <td><?= $row['year'] ?></td>
                          <td>
                            <?php if ($row['status'] === 'active'): ?>
                              <span class="badge bg-success">Active</span>
                            <?php else: ?>
                              <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                          </td>
                          <td><?= $row['created_at'] ?></td>
                          <td>
                            <a href="edit_course.php?id=<?= $row['id'] ?>" class="action-link text-primary"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_course.php?id=<?= $row['id'] ?>" class="action-link text-danger" onclick="return confirm('Are you sure you want to delete?')"><i class="fas fa-trash"></i> Delete</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr><td colspan="9" class="text-center">No courses found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
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
