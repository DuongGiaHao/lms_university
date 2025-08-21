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

// Lấy danh sách các assignment của instructor
$query = "SELECT a.*, c.title AS course_title FROM assignments a JOIN courses c ON a.course_id = c.id WHERE c.instructor_id = ? ORDER BY a.due_date DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Instructor</title>
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
                    <h1 class="h2"><i class="fas fa-tasks"></i> Assignments</h1>
                    <a href="create_assignment.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create Assignment</a>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table"></i> Assignment List
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Course</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Due Date</th>
                                        <th>Max Points</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><?= $assignment['id'] ?></td>
                                        <td><?= htmlspecialchars($assignment['course_title']) ?></td>
                                        <td><?= htmlspecialchars($assignment['title']) ?></td>
                                        <td><?= htmlspecialchars($assignment['description']) ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($assignment['due_date'])) ?></td>
                                        <td><?= $assignment['max_points'] ?></td>
                                        <td><?= date('Y-m-d', strtotime($assignment['created_at'])) ?></td>
                                        <td>
                                            <a href="edit_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="delete_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this assignment?');"><i class="fas fa-trash"></i></a>
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
