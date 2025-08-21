<?php
require_once '../config/database.php';
require_once '../includes/admin_sidebar.php';

$database = new Database();
$db = $database->getConnection();

// Fetch enrollments with student and course info
$query = "SELECT e.*, u.first_name, u.last_name, c.title AS course_title
          FROM enrollments e
          JOIN users u ON e.student_id = u.id
          JOIN courses c ON e.course_id = c.id
          ORDER BY e.enrollment_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments - Admin</title>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-user-graduate"></i> Enrollments</h1>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table"></i> Enrollment List
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Enrollment Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrollments as $enroll): ?>
                                    <tr>
                                        <td><?= $enroll['id'] ?></td>
                                        <td><?= htmlspecialchars($enroll['first_name'] . ' ' . $enroll['last_name']) ?></td>
                                        <td><?= htmlspecialchars($enroll['course_title']) ?></td>
                                        <td><?= date('Y-m-d', strtotime($enroll['enrollment_date'])) ?></td>
                                        <td><span class="badge bg-<?php
                                            switch ($enroll['status']) {
                                                case 'enrolled': echo 'primary'; break;
                                                case 'completed': echo 'success'; break;
                                                case 'dropped': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?= ucfirst($enroll['status']) ?>
                                        </span></td>
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
</body>
</html>
